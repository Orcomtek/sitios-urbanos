# ePayco Native Split & Webhook Integration — Architecture Manual

> **Sitios Urbanos — Internal Technical Documentation**
> Version: 1.0 · Block 40 (40.1–40.4) · June 2026

---

## Table of Contents

1. [Executive Summary & Business Logic](#1-executive-summary--business-logic)
2. [Database Schema](#2-database-schema)
3. [Core Engine: SplitEngineService](#3-core-engine-splitengineservice)
4. [Webhook & Security](#4-webhook--security)
5. [Frontend Flow & Tamper Prevention](#5-frontend-flow--tamper-prevention)
6. [File Manifest](#6-file-manifest)

---

## 1. Executive Summary & Business Logic

### 1.1 ePayco Operating Model

Sitios Urbanos uses ePayco's **Agregador con Cuentas Aliadas** (Aggregator with Allied Accounts) model. In this arrangement:

- **Sitios Urbanos** is the **Agregador** (Master Merchant). The ePayco master account and API keys belong to the platform.
- Each **Community** (tenant) registers as a **Cuenta Aliada** (Allied Account / Sub-merchant) under the Agregador. The community receives its own `epayco_allied_account_id`.

When a resident pays an invoice through the platform, ePayco executes a **split payment**: it distributes the funds between the Community's allied bank account and the Sitios Urbanos master account in a single atomic transaction.

### 1.2 SaaS Take-Rate Model

The platform monetizes through a **per-transaction commission** applied exclusively when payments are processed through the platform infrastructure. This is the core SaaS take-rate.

**Critical business rules:**

| Rule | Description |
|---|---|
| Commission applies | Only on **internal** (platform-processed) payments via ePayco |
| Commission does NOT apply | On **external** payments (bank transfer, cash, manual registration) |
| Commission is tenant-configurable | Each community can have a different rate/amount |
| Commission types | **Fixed** (flat COP amount) or **Percentage** (basis points) |
| Commission authority | Backend-only — frontend never calculates or defines commission |

### 1.3 Fund Distribution

For a payment of `$100,000 COP` with a fixed commission of `$1,500 COP`:

```
Resident pays  →  $100,000 COP  →  ePayco
                                      ├─→  Community Allied Account:  $98,500 COP  (98.50%)
                                      └─→  Sitios Urbanos Master:      $1,500 COP  ( 1.50%)
```

The percentage sent to ePayco is **reverse-calculated** from the integer COP commission. The platform's internal ledger always stores the exact integer COP values, not the percentage.

---

## 2. Database Schema

### 2.1 `payments` Table

The payments table stores every payment attempt with full split orchestration metadata.

| Column | Type | Description |
|---|---|---|
| `id` | `uuid` (PK) | Universally unique payment identifier |
| `community_id` | `bigint` (FK) | Tenant scope — references `communities.id` |
| `unit_id` | `bigint` (FK, nullable) | Residential unit associated with this payment |
| `invoice_id` | `uuid` (FK, nullable) | Invoice being paid |
| `method` | `string` | Payment method enum (`internal_epayco`, `external_bank_transfer`, etc.) |
| `status` | `string` | Lifecycle state: `pending`, `confirmed`, `failed`, `refunded` |
| `gateway_status` | `string` (nullable) | Raw ePayco state: `Aceptada`, `Rechazada`, `Pendiente`, `Fallida`, `Reversada` |
| `signature_verified` | `boolean` | Whether the webhook signature was cryptographically validated |
| `gateway_payload` | `jsonb` (nullable) | Full ePayco webhook payload — stored for forensic audit |
| `amount` | `integer` | Total payment amount in COP (integer, never decimal) |
| `net_amount` | `integer` (nullable) | Amount disbursed to the community after commission |
| `platform_commission` | `integer` | Platform take-rate in COP (integer) |
| `paid_at` | `timestamp` (nullable) | When payment was confirmed |
| `external_reference` | `string` (nullable) | ePayco's `x_ref_payco` — used for webhook lookup |
| `idempotency_key` | `string` (nullable) | Unique key per community to prevent duplicate payment creation |
| `created_at` / `updated_at` | `timestamps` | Standard Laravel timestamps |

**Indexes:**
- `(community_id, status)` — tenant-scoped payment queries
- `(community_id, idempotency_key)` — unique constraint for idempotency

**Migrations:**
- `2026_04_06_224604_create_payments_table` — base schema
- `2026_04_07_000214_add_split_orchestration_fields_to_payments_table` — added `gateway_status`, `signature_verified`, `gateway_payload`, `net_amount`, `paid_at`

### 2.2 `financial_settings` Table (Split Configuration Columns)

Per-tenant split configuration stored alongside existing billing parameters.

| Column | Type | Default | Description |
|---|---|---|---|
| `epayco_allied_account_id` | `string` (nullable) | `null` | Community's ePayco Cuentas Aliadas sub-account ID |
| `commission_type` | `string` | `'fixed'` | `'fixed'` (flat COP) or `'percentage'` (basis points) |
| `commission_value` | `integer` | `1500` | If `fixed`: COP amount (e.g., `1500` = $1,500 COP). If `percentage`: hundredths of percent (e.g., `350` = 3.50%) |

**Migration:** `2026_06_27_122519_add_split_config_to_financial_settings_table`

### 2.3 Integer COP Discipline

All monetary values are stored as **integers representing Colombian Pesos (COP)**. COP has no subdivision (no centavos in practice for digital payments), so integers provide exact arithmetic without floating-point drift.

When `invoice.total` arrives from PostgreSQL as a decimal string (e.g., `"10000000.00"`), it is explicitly cast to integer via `(int) round((float) $value)` before any database write or split calculation.

---

## 3. Core Engine: SplitEngineService

**File:** `app/Services/Financial/SplitEngineService.php`

### 3.1 Purpose

The `SplitEngineService` is the centralized commission calculator. It receives a payment amount and a tenant's financial settings, then returns the complete split distribution including the reverse-calculated percentage required by ePayco's API.

### 3.2 Resolution Chain

```
SplitEngineService::calculate($amount, $settings, $communityId)
    │
    ├─ Commission Type → $settings->commission_type ?? config('finance.commission.type')
    ├─ Commission Value → $settings->commission_value ?? config('finance.commission.value')
    └─ Allied Account   → $settings->epayco_allied_account_id (REQUIRED — throws if null)
```

The service follows a **tenant-first, config-fallback** resolution strategy. If a tenant has no `FinancialSetting` record (or its commission fields are null), the service falls back to global defaults in `config/finance.php`.

### 3.3 Commission Math

#### Fixed Commission

```
commission = min(configured_value, amount)
```

The `min()` cap ensures the commission never exceeds the payment amount, preventing negative `net_amount`.

**Example:** For `amount = 100,000` and `commission_value = 1,500`:
```
commission     = min(1500, 100000) = 1,500 COP
net_amount     = 100,000 - 1,500   = 98,500 COP
split_percentage = (98500 / 100000) × 100 = 98.50%
```

#### Percentage Commission

```
commission = floor(amount × (commission_value / 10000))
```

`commission_value` is stored in **hundredths of a percent** (basis points). This avoids floating-point representation issues for values like 3.50%.

| Stored Value | Effective Rate | Calculation |
|---|---|---|
| `100` | 1.00% | `floor(amount × 100 / 10000)` |
| `350` | 3.50% | `floor(amount × 350 / 10000)` |
| `500` | 5.00% | `floor(amount × 500 / 10000)` |

### 3.4 `floor()` Protection

The `floor()` function is critical for percentage commissions. It ensures the platform **never overcharges** the community — any fractional peso is absorbed by the platform.

**Example with non-clean division:** For `amount = 99,999` and `commission_value = 333` (3.33%):
```
raw = 99,999 × 333 / 10000 = 3,329.9667
floor(3329.9667) = 3,329 COP  ← platform absorbs 0.9667 COP
```

Without `floor()`, using `round()` could produce `3,330`, overcharging the community by ~1 peso. At volume, this protects against systematic rounding bias against tenants.

### 3.5 Reverse-Calculation for ePayco's API

ePayco's `splitpayment` API only accepts a **percentage** for the primary receiver's fee — it does not accept absolute amounts. For fixed commissions, the service must reverse-calculate a percentage:

```
split_percentage = round((net_amount / amount) × 100, 2)
```

This means a `$1,500 COP` fixed commission on a `$100,000 COP` payment becomes `98.50%` for the allied account. The exact integer COP values (`platform_commission`, `net_amount`) are persisted in the `payments` table, ensuring ledger immutability even if ePayco's actual bank disbursement drifts by fractions due to independent rounding.

### 3.6 Guard: Missing Allied Account

If `epayco_allied_account_id` is null (or settings are null with no fallback), the service throws `SplitConfigurationException`. This prevents payments from processing without a destination account.

The controller catches this and returns a user-friendly 422 in Spanish:
> *"La comunidad no tiene configurada una cuenta aliada de ePayco. Contacte al administrador."*

### 3.7 Return Shape

```php
[
    'platform_commission' => int,       // Exact COP deducted by platform
    'net_amount'          => int,       // COP disbursed to community
    'split_receiver_id'   => string,    // ePayco allied account ID
    'split_percentage'    => float,     // Reverse-calculated % for ePayco API
    'commission_type'     => string,    // 'fixed' or 'percentage'
    'commission_value'    => int,       // Raw configured value
]
```

---

## 4. Webhook & Security

**File:** `app/Http/Controllers/Webhook/EpaycoWebhookController.php`

### 4.1 Route Registration

```
POST /webhooks/epayco  →  EpaycoWebhookController@handle
```

Registered on the **central domain** (`app.sitiosurbanos.com`), outside tenant middleware. This is necessary because ePayco sends webhook notifications without any subdomain context — the controller must resolve the payment globally.

**Route file:** `routes/webhooks.php` — bound to `config('app.central_domain')`.

### 4.2 Processing Pipeline

The webhook controller follows a strict 4-step pipeline:

```
Step 1: Cryptographic Signature Validation
    │  ↓ fail → 401 + log spoofing attempt
Step 2: Payment Lookup (global scope bypass)
    │  ↓ fail → 404 + log missing payment
Step 3: Idempotency Check
    │  ↓ duplicate → 200 + log skip
Step 4: Payment Reconciliation + Split Audit
    └─→ 200 + log success
```

### 4.3 Step 1: Signature Validation

**Service:** `EpaycoService::generateSignature()`

ePayco signs each webhook payload with a SHA-256 hash constructed from:

```
SHA256("{p_cust_id_cliente}^{p_key}^{x_ref_payco}^{x_transaction_id}^{x_amount}^{x_currency_code}")
```

Where:
- `p_cust_id_cliente` — platform's ePayco customer ID (from `config('epayco.p_cust_id_cliente')`)
- `p_key` — platform's ePayco secret key (from `config('epayco.p_key')`)
- `x_ref_payco`, `x_transaction_id`, `x_amount`, `x_currency_code` — from the incoming payload

**Timing-safe comparison:**

```php
if (! hash_equals($generatedSignature, $incomingSignature)) {
    abort(401, 'Invalid signature');
}
```

`hash_equals()` prevents timing attacks where an attacker could deduce the correct signature byte-by-byte by measuring response times. This function compares both strings in constant time regardless of where the first difference occurs.

### 4.4 Step 2: Payment Lookup (TenantScoped Bypass)

```php
$payment = Payment::withoutGlobalScopes()
    ->where('external_reference', $refPayco)
    ->first();
```

The `withoutGlobalScopes()` call is **intentional and necessary**. Webhook requests arrive without tenant context (no subdomain, no authenticated user). The `TenantScoped` global scope would otherwise restrict the query to a community that doesn't exist in the request context.

**Safety justification:** The signature validation in Step 1 already cryptographically proves the request originates from ePayco. The payment lookup by `external_reference` (which is ePayco's `x_ref_payco`) then binds to a specific payment row that already carries `community_id`. No cross-tenant risk exists because:

1. The signature cannot be forged
2. The `external_reference` is unique to one payment
3. The payment row already has `community_id` embedded

### 4.5 Step 3: Idempotency

ePayco may send the same webhook multiple times (network retries, confirmation callbacks). The controller checks:

```php
if ($payment->signature_verified && $payment->gateway_status === $incomingState) {
    return response()->json(['status' => 'already_processed'], 200);
}
```

This prevents:
- Duplicate ledger entries
- Double status transitions
- Multiple `paid_at` timestamp overwrites

Returns `200` to prevent ePayco from retrying further.

### 4.6 Step 4: Reconciliation & Split Audit

On reconciliation, the controller updates:

| Field | Value |
|---|---|
| `gateway_payload` | Full webhook payload (JSONB — forensic archive) |
| `gateway_status` | Raw ePayco state string (`Aceptada`, `Rechazada`, etc.) |
| `signature_verified` | `true` |
| `status` | Mapped to internal `PaymentStatus` enum |
| `paid_at` | `now()` if status is `CONFIRMED` |

**State mapping:**

| ePayco State | Internal Status |
|---|---|
| `Aceptada` | `CONFIRMED` |
| `Rechazada` | `FAILED` |
| `Pendiente` | `PENDING` |
| `Fallida` | `FAILED` |
| `Reversada` | `REFUNDED` |

**Split audit trail (on CONFIRMED only):**

```php
Log::info('[ePayco Webhook] Split audit trail', [
    'payment_id'          => $payment->id,
    'platform_commission' => $payment->platform_commission,
    'net_amount'          => $payment->net_amount,
    'community_id'        => $payment->community_id,
    'split_payload'       => /* x_split_payment, x_split_type, x_split_primary_receiver, x_split_primary_receiver_fee */
]);
```

This log enables post-hoc verification that the split distribution ePayco reported matches what the platform calculated at checkout time.

---

## 5. Frontend Flow & Tamper Prevention

### 5.1 The Problem with Client-Side Split Payloads

If the frontend constructed the split payment parameters (allied account ID, percentage) directly from client-side data, a malicious actor could:

1. Intercept the ePayco modal initialization
2. Replace the `split_primary_receiver` with their own ePayco account
3. Modify the `split_primary_receiver_fee` to redirect funds

### 5.2 The Pre-Checkout API Pattern

The system prevents this by **never allowing the frontend to compute split parameters**. Instead:

```
 ┌─────────────────────────────┐
 │  Resident clicks "Pagar"   │
 └──────────┬──────────────────┘
            ▼
 ┌─────────────────────────────┐
 │  Vue: POST /api/finance/    │◄── Axios call (relative URL)
 │    invoices/{id}/pay        │
 └──────────┬──────────────────┘
            ▼
 ┌─────────────────────────────────────────────────────┐
 │  Backend: CreatePaymentAttemptAction                │
 │  1. Validate tenant ownership                      │
 │  2. Validate invoice status (PENDING only)          │
 │  3. Load FinancialSetting from DB                   │
 │  4. SplitEngineService::calculate()                 │
 │  5. Create Payment row with integer COP values      │
 │  6. Return PaymentIntentResource with split params   │
 └──────────┬──────────────────────────────────────────┘
            ▼
 ┌─────────────────────────────┐
 │  Vue: Receives JSON with    │
 │  gateway.split block        │
 │  (server-computed, signed)  │
 └──────────┬──────────────────┘
            ▼
 ┌─────────────────────────────┐
 │  Vue: Opens ePayco modal    │
 │  with split params injected │
 │  from server response       │
 └─────────────────────────────┘
```

### 5.3 PaymentIntentResource — Split Payload Structure

The `PaymentIntentResource` injects the ePayco-specific split parameters into the JSON response:

```json
{
    "data": {
        "id": "uuid",
        "invoice_id": "uuid",
        "status": "pending",
        "amount": 100000,
        "platform_commission": 1500,
        "net_amount": 98500,
        "gateway": {
            "provider": "epayco",
            "flow": "split",
            "split": {
                "splitpayment": "true",
                "split_app_id": "allied_12345",
                "split_merchant_id": "allied_12345",
                "split_type": "02",
                "split_primary_receiver": "allied_12345",
                "split_primary_receiver_fee": "98.5"
            }
        },
        "created_at": "2026-06-27 12:00:00"
    }
}
```

The frontend simply passes these values through to `handler.open()` — it never generates, modifies, or interprets them.

### 5.4 Frontend UX States

The checkout modal provides feedback during the pre-checkout API call:

| State | UX Behavior |
|---|---|
| Loading | Spinner on button, "Procesando..." text, both buttons disabled |
| Error (422 - missing config) | Red alert with Spanish message from backend |
| Error (network/other) | Generic retry message |
| Success | ePayco modal opens, confirmation modal closes |

### 5.5 Admin Configuration UI

Administrators configure the split parameters via the Financial Settings page (`Settings/Edit.vue`). Three fields:

| Field | Input Type | Description |
|---|---|---|
| ID Cuenta Aliada ePayco | Text | Community's allied account identifier |
| Tipo de Comisión | Select | `Fija (COP)` or `Porcentaje (%)` |
| Valor de Comisión | Number | COP amount or hundredths of % with contextual hint |

The hint text dynamically changes based on the selected commission type:
- Fixed: *"Ej: 1500 = $1.500 COP fijos por transacción."*
- Percentage: *"Ej: 350 = 3.50% de comisión por transacción."*

---

## 6. File Manifest

### 6.1 Backend — Core Services

- **`app/Services/Financial/SplitEngineService.php`** — Centralized commission calculator. Dual-branch (fixed/percentage) integer COP math with `floor()` protection and reverse-percentage calculation for ePayco's API.
- **`app/Services/Financial/EpaycoService.php`** — ePayco signature generator. Constructs SHA-256 hash from platform credentials and transaction payload fields.

### 6.2 Backend — Actions

- **`app/Actions/Finance/CreatePaymentAttemptAction.php`** — Pre-checkout orchestrator. Validates tenant ownership, loads `FinancialSetting`, invokes `SplitEngineService`, creates `Payment` row with integer COP amounts, returns payment + split metadata.

### 6.3 Backend — Controllers

- **`app/Http/Controllers/Api/Finance/InvoicePaymentController.php`** — API endpoint for checkout initialization. Enforces tenant boundary, validates resident access to invoice unit, handles `SplitConfigurationException`.
- **`app/Http/Controllers/Webhook/EpaycoWebhookController.php`** — Webhook receiver. 4-step pipeline: signature validation → payment lookup → idempotency → reconciliation + split audit.
- **`app/Http/Controllers/Tenant/Admin/Financial/SettingController.php`** — Admin settings CRUD. Validates and persists split configuration fields.

### 6.4 Backend — Resources

- **`app/Http/Resources/Finance/PaymentIntentResource.php`** — JSON serializer for payment intents. Injects `gateway.split` block with ePayco-specific field names from server-computed split data.

### 6.5 Backend — Models & Enums

- **`app/Models/FinancialSetting.php`** — Eloquent model for per-tenant financial configuration. Casts `commission_type` to `CommissionType` enum.
- **`app/Models/Community.php`** — Added `financialSetting(): HasOne` relationship.
- **`app/Enums/CommissionType.php`** — Backed string enum: `Fixed = 'fixed'`, `Percentage = 'percentage'`.
- **`app/Enums/PaymentStatus.php`** — Payment lifecycle states (pre-existing).
- **`app/Enums/PaymentMethod.php`** — Payment method classification (pre-existing).

### 6.6 Backend — Exceptions

- **`app/Exceptions/SplitConfigurationException.php`** — Thrown when allied account ID is missing. Factory method: `missingAlliedAccount(int $communityId)`.

### 6.7 Database — Migrations

- **`2026_04_06_224604_create_payments_table.php`** — Base payments schema with `amount`, `platform_commission`, `idempotency_key`.
- **`2026_04_07_000214_add_split_orchestration_fields_to_payments_table.php`** — Added `gateway_status`, `signature_verified`, `gateway_payload`, `net_amount`, `paid_at`.
- **`2026_06_27_122519_add_split_config_to_financial_settings_table.php`** — Added `epayco_allied_account_id`, `commission_type`, `commission_value` to `financial_settings`.

### 6.8 Configuration

- **`config/finance.php`** — Global commission defaults (fallback when tenant has no `FinancialSetting`).
- **`config/epayco.php`** — ePayco credentials: `public_key`, `p_cust_id_cliente`, `p_key`, `testing` flag.

### 6.9 Routes

- **`routes/webhooks.php`** — `POST /webhooks/epayco` on central domain (no tenant middleware).
- **`routes/api.php`** — `POST /api/finance/invoices/{invoice}/pay` on tenant subdomain (auth + tenant middleware).

### 6.10 Frontend

- **`resources/js/Pages/Tenant/Resident/Financial/Statement/Index.vue`** — Resident checkout page. Pre-checkout API call, split payload injection into ePayco modal, loading/error states.
- **`resources/js/Pages/Tenant/Admin/Financial/Settings/Edit.vue`** — Admin configuration page. Allied account ID, commission type, commission value fields with contextual hints.

### 6.11 Tests

- **`tests/Unit/Services/Financial/SplitEngineServiceTest.php`** — 7 unit tests: fixed/percentage calculation, cap protection, `floor()` behavior, exception on missing config, zero amount edge case.
- **`tests/Feature/Finance/SplitEngineConfigFallbackTest.php`** — Feature tests for `config()` fallback resolution.
- **`tests/Feature/Finance/EpaycoWebhookControllerTest.php`** — Webhook pipeline integration tests (pre-existing).
