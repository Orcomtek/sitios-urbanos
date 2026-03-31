---
trigger: always_on
---

# Finance & Security Rules — Sitios Urbanos

This document defines all **financial integrity, payments, and security rules**.

These are CRITICAL rules.

---

## Core Principle

Money-related operations must be:

- accurate
- traceable
- idempotent
- secure

---

## Payment Gateway (ePayco)

Sitios Urbanos uses:

- ePayco (split payments model)

You MUST:

- validate every webhook
- verify signatures
- trust only verified events

---

## Webhook Validation (MANDATORY)

Every incoming payment notification MUST:

1. Validate signature (`x_signature` or equivalent)
2. Validate transaction reference (`x_ref_payco`)
3. Confirm status from ePayco if needed

You MUST NOT:

- trust raw POST data
- process unverified payments

---

## Idempotency (CRITICAL)

Webhooks may be received multiple times.

You MUST:

- prevent duplicate processing
- check if transaction already exists

Example rule:

- if `x_ref_payco` already processed → ignore

---

## Transaction Lifecycle

Every transaction must have states:

- pending
- paid
- failed
- refunded (future)

Never skip states.

---

## Ledger System (MANDATORY)

Every financial movement MUST be recorded.

This includes:

- payments
- fees
- commissions
- adjustments

You MUST NOT:

- update balances without ledger entry

---

## Split Payments (Core Business Logic)

All payments must consider:

- community share
- Orcomtek commission
- gateway costs

Rules:

- commission must be configurable
- commission must be calculated server-side
- frontend MUST NOT calculate money

---

## Backend Authority

The backend is the ONLY source of truth for:

- amounts
- commissions
- balances
- payment status

Frontend MUST NOT:

- calculate totals
- define payment outcomes

---

## Security Rules

You MUST:

- validate all inputs
- sanitize data
- protect endpoints
- authenticate all sensitive operations

---

## Sensitive Operations

Extra care required for:

- payment processing
- invoice generation
- balance updates
- refunds (future)

---

## Logging (MANDATORY)

You MUST log:

- webhook reception
- payment processing
- errors
- inconsistencies

---

## Error Handling

You MUST:

- fail safely
- never corrupt financial data
- avoid partial updates

---

## SMS & Fair Use (Panic Button)

- Each community has limited SMS quota
- Must enforce limits
- Must fallback gracefully (push/notification)

---

## Background Jobs (Finance)

Heavy operations must use queues:

- webhook processing
- OCR (parcels)
- notifications

---

## Agent Responsibility

You MUST:

- treat financial logic as critical
- double-check all calculations
- avoid assumptions

If unsure:
→ STOP and ask