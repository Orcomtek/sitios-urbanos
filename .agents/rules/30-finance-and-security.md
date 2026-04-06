---
trigger: always_on
---

# Finance and Security — Sitios Urbanos

---

## 1. Purpose

This rule defines the official standards for:

- financial integrity
- payment processing
- commission handling
- split payment logic
- financial traceability
- operational security requirements linked to finance
- Panic Button SMS fair use rules
- critical backend authority over money-related behavior

These are **critical rules**.

---

## 2. Core Principle

Money-related operations MUST always be:

- accurate
- traceable
- idempotent
- secure
- tenant-scoped
- backend-controlled

The system must behave as a reliable financial platform, not as a loose payment wrapper.

---

## 3. Financial Scope

This rule applies to:

- administration fees
- fines
- extraordinary charges
- internal platform-processed payments
- externally registered payments
- commissions
- ledger records
- future marketplace / provider / P2P transactions
- Panic Button SMS consumption rules
- security-sensitive operations connected to financial or quota usage

---

## 4. Payment Model

The system supports two main types of payments:

### 4.1 Internal Payments (Platform-Processed)

These are payments executed through the platform using integrated payment infrastructure, primarily:

- ePayco split payments

These payments MAY generate commission according to configuration.

---

### 4.2 External Payments (Off-Platform)

These are payments made outside the platform, for example:

- bank transfer
- cash payment
- manual payment at administration office
- direct deposit to community account

These payments MUST be registrable in the system for accounting and operational purposes.

These payments MUST NOT generate automatic platform commission.

---

## 5. Commission Rules (CRITICAL)

### 5.1 Core Rule

Commission applies ONLY when:

- the payment is processed through the platform infrastructure

---

### 5.2 Forbidden Commission Cases

The system MUST NOT apply commission when:

- payment is made externally
- payment is registered manually
- payment bypasses the platform infrastructure

---

### 5.3 Reason

Commission is justified ONLY when the platform provides transaction-processing value.

This rule is commercially critical and MUST NOT be broken.

---

## 6. Commission Configuration

### 6.1 Rule

Commission MUST be:

- configurable
- adjustable per tenant (community)
- adjustable per transaction type
- adjustable for future ecosystem flows

---

### 6.2 Forbidden

The system MUST NOT:

- hardcode commission values
- assume a single global fixed commission
- calculate money rules in frontend

---

## 7. Split Payments (Core Business Logic)

All platform-processed payments must be able to consider:

- community share
- Sitios Urbanos commission
- gateway costs
- future actor participation if required

### Rules

- commission must be configurable
- split must be calculated server-side
- frontend MUST NOT calculate or define money distribution
- every distribution must be traceable

---

## 8. Payment Gateway (ePayco)

Sitios Urbanos uses:

- ePayco (split payments model)

The system MUST:

- validate every webhook
- verify signatures
- trust only verified events
- protect against duplicate processing

---

## 9. Webhook Validation (MANDATORY)

Every incoming payment notification MUST:

1. validate signature (`x_signature` or equivalent)
2. validate transaction reference (`x_ref_payco`)
3. confirm status from ePayco if required by the flow
4. reject invalid or untrusted events

### Forbidden

The system MUST NOT:

- trust raw POST data
- process unverified payments
- update financial status from unvalidated notifications

---

## 10. Idempotency (CRITICAL)

Webhooks may be received multiple times.

The system MUST:

- prevent duplicate processing
- verify whether a transaction has already been processed
- make repeated notifications safe

### Example rule

If `x_ref_payco` has already been processed:

- ignore duplicate effect
- preserve traceability
- do not create duplicate ledger movements

---

## 11. Transaction Lifecycle

Every transaction MUST have explicit lifecycle states.

Minimum states:

- pending
- paid
- failed
- refunded (future-ready)

### Rule

The system MUST NOT skip states arbitrarily.

Transitions must be controlled and traceable.

---

## 12. Financial Ledger (MANDATORY)

Every financial movement MUST be recorded in a ledger.

This includes:

- charges
- payments
- fees
- commissions
- adjustments
- reversals when applicable

### Critical Rule

The system MUST NOT:

- update balances without ledger entry
- modify financial reality silently
- allow money state changes without audit trail

---

## 13. Backend Authority

The backend is the ONLY source of truth for:

- amounts
- commissions
- balances
- payment status
- split calculations
- ledger updates

### Frontend MUST NOT:

- calculate totals authoritatively
- define payment outcomes
- define commission logic
- bypass backend financial validation

---

## 14. External Payment Handling

The system MUST allow:

- manual registration of external payments
- linkage of those payments to units, residents, or charges
- inclusion of those payments in financial reporting

### External payments MUST:

- be clearly marked as external
- NOT trigger commission
- remain fully traceable

---

## 15. Financial Security Requirements

The system MUST:

- validate all financial inputs
- sanitize incoming data
- protect sensitive endpoints
- authenticate all sensitive operations
- enforce tenant isolation over financial data

### Forbidden

The system MUST NOT:

- allow cross-tenant financial access
- allow silent balance modification
- trust frontend for money-related truth
- permit partial untraceable updates

---

## 16. Sensitive Operations

Extra care is required for:

- payment processing
- invoice generation
- balance updates
- ledger mutations
- webhook handling
- future refunds
- future charge reversals

These flows MUST be treated as critical.

---

## 17. Logging (MANDATORY)

The system MUST log at minimum:

- webhook reception
- payment processing attempts
- validation failures
- financial inconsistencies
- critical money-related errors
- retries or duplicate processing events

### Rule

Logging must support:

- debugging
- auditability
- forensic review of financial events

---

## 18. Error Handling

The system MUST:

- fail safely
- preserve data consistency
- never corrupt financial data
- avoid partial updates without rollback or controlled compensation

### Rule

No critical financial flow may leave the system in an ambiguous state.

---

## 19. Background Jobs and Queues

Heavy or asynchronous financial/security-adjacent operations MUST use queues when appropriate.

This includes:

- webhook processing
- OCR for parcels
- notification dispatch
- future reconciliation tasks

### Rule

Queued jobs must remain:

- traceable
- idempotent where needed
- tenant-safe

---

## 20. Security Interaction with Finance

Financial operations and security operations are not isolated concerns.

Some flows affect both.

Examples:

- Panic Button SMS quota usage
- incident-triggered notifications
- operational evidence associated with tenant actions

### Rule

The system MUST preserve consistent auditability across finance-security boundaries.

---

## 21. SMS and Fair Use (Panic Button)

### 21.1 Rule

Each community has a limited SMS quota.

This quota MUST be enforced.

---

### 21.2 Applies Especially To

- Panic Button notifications
- critical fallback alerts
- future high-priority SMS events

---

### 21.3 Required Behavior

The system MUST:

- track SMS usage per tenant
- enforce configured limits
- prevent uncontrolled SMS overuse
- fallback gracefully to alternative channels where possible

Examples of fallback:

- push notification
- in-app notification
- email
- future approved channels

---

### 21.4 Critical Rule

The Panic Button MUST NOT depend blindly on unlimited SMS.

It must operate under fair use and controlled fallback logic.

---

## 22. Tenant Isolation in Finance

All financial operations MUST be scoped by tenant.

This includes:

- charges
- balances
- transactions
- commissions
- payment logs
- quota usage

### Forbidden

The system MUST NOT:

- expose financial data across tenants
- reuse references incorrectly between tenants
- mix accounting data between communities

---

## 23. Testing Requirements

The system MUST validate:

- correct webhook validation
- duplicate webhook resistance
- correct commission application
- correct exclusion of external payments from commission
- ledger consistency
- split calculation consistency
- tenant isolation in financial data
- SMS quota enforcement
- fallback behavior for quota-based notifications

---

## 24. Agent Responsibility

When working with finance and security logic, the agent MUST:

- treat all financial logic as critical
- double-check calculations
- avoid assumptions
- avoid hidden shortcuts
- stop and ask when any business rule is unclear

---

## 25. Strategic Importance

This rule protects:

- monetization integrity
- payment trust
- platform credibility
- legal defensibility
- fair use control
- operational sustainability

---

## 26. Consequence of Violation

Breaking this rule may result in:

- incorrect billing
- financial disputes
- commission errors
- payment inconsistencies
- uncontrolled SMS consumption
- legal and reputational exposure

---

## 27. Final Principle

The system must behave as:

- a reliable financial system
- a transparent system
- a configurable system
- a tenant-safe system
- a security-aware system

Not as a rigid or opaque payment processor.