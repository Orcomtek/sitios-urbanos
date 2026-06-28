# Financial Layer Consolidation — Block 40

**Date:** 2026-06-27  
**Block:** 40 (Financial Engine — ePayco Split, Webhook, Ledger)  
**Status:** ✅ Completed

---

## 1. Overview

Block 40 involved building the complete financial engine for Sitios Urbanos: ePayco split-payment generation, webhook processing, ledger recording, and the resident financial statement. During execution, a critical architectural inconsistency was discovered and resolved: a **legacy `Invoice` model** coexisted with a **new namespace-scoped model**, causing ambiguous imports and test failures.

This document records the consolidation decision, the specific type-casting bugs fixed for PostgreSQL compatibility, and the tenant context leak found and corrected in tests.

---

## 2. Invoice Model Consolidation

### 2.1 Problem

Two `Invoice` classes existed simultaneously:

| Location | Namespace | Status |
|---|---|---|
| `app/Models/Invoice.php` | `App\Models\Invoice` | **Legacy — deleted** |
| `app/Models/Financial/Invoice.php` | `App\Models\Financial\Invoice` | **Canonical — kept** |

The legacy model was a shallow stub with no relationships, no `TenantScoped` trait, and no `HasUuids`. It predated the Block 40 financial engine design. Several actions and the webhook controller had inadvertently imported it, causing silent failures where queries hit an unscoped model without UUIDs.

### 2.2 Resolution

1. **Deleted** `app/Models/Invoice.php` entirely.
2. **Confirmed** all usages (actions, controllers, tests) import from `App\Models\Financial\Invoice`.
3. The canonical model at `app/Models/Financial/Invoice.php` is the sole Invoice class. It has:
   - `HasUuids` (primary key is a UUID)
   - `TenantScoped` (automatic `community_id` scoping)
   - `SoftDeletes`
   - Relationships: `community()`, `unit()`, `user()`, `items()`, `payments()`
   - `InvoiceStatus` enum cast: `PENDING`, `PAID`, `CANCELLED`

### 2.3 Canonical Invoice Model

```
app/Models/Financial/
├── Invoice.php          ← Canonical. HasUuids, TenantScoped, SoftDeletes.
├── InvoiceItem.php
└── FinancialAdjustment.php
```

**Rule:** Any new code referencing invoices MUST import `App\Models\Financial\Invoice`. Never recreate a root-level Invoice model.

---

## 3. PostgreSQL Strict-Type Fixes

PostgreSQL enforces strict column typing. During Block 40 test execution, two categories of type errors were encountered and fixed.

### 3.1 UUID Column Enforcement

**Problem:** PostgreSQL rejected raw string comparisons against UUID columns (e.g., `payment.id`) when the comparison value was not a valid UUID format.

**Context:** The ePayco webhook payload field `x_id_invoice` can contain either:
- A UUID (when our system sends the payment's `id` directly)
- A custom string key (`idempotency_key`)

**Fix in `ProcessEpaycoWebhookAction`:**

```php
// Before (broke on PostgreSQL when $internalReference was not a UUID):
$payment = Payment::where('id', $internalReference)->orWhere('idempotency_key', $internalReference)->first();

// After (safe for PostgreSQL strict UUID typing):
$payment = Payment::where(function ($query) use ($internalReference) {
    if (Str::isUuid($internalReference)) {
        $query->where('id', $internalReference);
    }
    $query->orWhere('idempotency_key', $internalReference);
})->first();
```

The `Str::isUuid()` guard ensures the UUID column comparison is only made when the input is a valid UUID, preventing PostgreSQL from throwing a type error.

### 3.2 Integer Cast for Commission and Ledger Amounts

**Problem:** Commission-related columns (`commission_value`, `amount` in ledger entries) are stored as `integer` in PostgreSQL but were compared against PHP floats in calculations, causing implicit cast mismatches.

**Fixes applied:**
- `LedgerEntry::create()` calls in `ProcessEpaycoWebhookAction` now cast amounts to `int` explicitly:
  ```php
  'amount' => -(int) abs($payment->amount),
  'amount' => (int) $payment->platform_commission,
  ```
- The `commission_value` field in `financial_settings` is cast as `'integer'` in `FinancialSetting` model casts.

**Rule:** All monetary integer columns (amounts stored in integer COP) must be explicitly cast to `(int)` before insertion.

---

## 4. Tenant Context Leak Fixes in Tests

### 4.1 Problem

Several Block 40 feature tests were polluting each other via a shared singleton `TenantContext` instance. A test that called `app(TenantContext::class)->set($community)` would leak that community into subsequent tests, causing cross-tenant query contamination and false positives.

### 4.2 Root Cause

`TenantContext` is a **singleton** in the service container — correct for production (one context per request lifecycle), problematic in tests where each test is its own isolated "request".

### 4.3 Fix

All financial feature tests that require a tenant context now explicitly set it at the start of each test:

```php
app(TenantContext::class)->set($community);
```

Tests use `RefreshDatabase` for database isolation. Tests that do NOT require a tenant context leave it unset and assert graceful failure.

**Rule:** Never assume `TenantContext` is set between tests. Always call `app(TenantContext::class)->set($community)` before any tenant-scoped test operation.

---

## 5. Commission Field Security — Hotfix (Post Block 40)

> Applied as a pre-Block 41 hotfix on 2026-06-27.

### 5.1 Vulnerability

Tenant Admins could modify `commission_type` and `commission_value` — the SaaS take-rate — via the Financial Settings form. These fields represent Sitios Urbanos' commercial revenue and must be immutable from the tenant's perspective.

### 5.2 Fixes Applied

**Backend (`SettingController::update()`):**

`commission_type` and `commission_value` were removed from the validation allowlist. Any value sent in the request body for these fields is silently ignored. The exclusion is documented with an explicit comment:

```php
// commission_type and commission_value are intentionally excluded.
// These are SaaS take-rate fields managed exclusively by Sitios Urbanos
// and must never be modifiable by tenant admins.
```

**Frontend (`Edit.vue`):**

- Both fields removed from the reactive `useForm` object — they are never submitted to the server.
- Read-only display versions rendered with `:value` (not `v-model`), `disabled` / `readonly` attributes.
- "🔒 Configurado por Sitios Urbanos" pill badge displayed next to each field label.
- Fields styled with `bg-gray-50 opacity-75 cursor-not-allowed`.

### 5.3 Defense in Depth

| Layer | Protection |
|---|---|
| Frontend | Fields not in `useForm`, never submitted |
| Backend | Fields not in validation allowlist, ignored if sent |

---

## 6. File Reference

| File | Action | Notes |
|---|---|---|
| `app/Models/Invoice.php` | **Deleted** | Legacy stub removed entirely |
| `app/Models/Financial/Invoice.php` | Canonical | `HasUuids` + `TenantScoped` + `SoftDeletes` |
| `app/Actions/Finance/ProcessEpaycoWebhookAction.php` | Modified | UUID guard + integer casts |
| `app/Http/Controllers/Tenant/Admin/Financial/SettingController.php` | Modified | Commission fields excluded from update |
| `resources/js/Pages/Tenant/Admin/Financial/Settings/Edit.vue` | Modified | Commission fields read-only with lock badge |
