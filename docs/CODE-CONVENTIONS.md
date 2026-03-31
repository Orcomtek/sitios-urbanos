# Code Conventions — Sitios Urbanos

## 1. Purpose

This document defines the **coding standards** for the project.

All code MUST follow these conventions.

---

## 2. General Principles

Code must be:

- clear
- readable
- predictable
- maintainable

Avoid clever code. Prefer explicit code.

---

## 3. Naming Conventions

### Classes

Use PascalCase:

- `CreateInvoiceAction`
- `ProcessPaymentService`

---

### Methods

Use camelCase:

- `createInvoice()`
- `processPayment()`

---

### Variables

Use descriptive names:

- `invoiceAmount`
- `communityId`

Avoid:

- `data`
- `value`
- `temp`

---

### Files

Use clear naming:

- `CreateInvoiceAction.php`
- `FinanceService.php`

---

## 4. Controllers

Controllers MUST:

- be thin
- delegate logic to Actions/Services

Controllers MUST NOT:

- contain business logic
- handle complex workflows

---

## 5. Actions

Actions represent:

- a single use case

Example:

- `CreateInvoiceAction`
- `RegisterParcelAction`

---

## 6. Services

Services handle:

- reusable logic
- shared domain operations

---

## 7. Models

Models MUST:

- define relationships
- define scopes

Models MUST NOT:

- contain complex business logic
- call external services

---

## 8. Validation

Use Form Requests.

Avoid inline validation for complex cases.

---

## 9. Formatting

Use Laravel Pint.

Code must:

- be consistently formatted
- follow PSR standards

---

## 10. Comments

Use comments only when necessary.

Good:

- explaining WHY

Bad:

- explaining WHAT the code already shows

---

## 11. Error Handling

- use exceptions
- avoid silent failures
- return clear responses

---

## 12. Logging

Log important events:

- payments
- errors
- security events

---

## 13. Duplication

Avoid duplicate code.

If logic repeats → extract to service/action.

---

## 14. Complexity

Avoid:

- long methods
- nested logic
- unclear flows

---

## 15. Frontend Conventions

Vue components must:

- be small
- be reusable
- be clean

Avoid:

- business logic in frontend
- duplicated logic

---

## 16. File Size

Prefer:

- small files
- focused responsibility

---

## 17. Consistency

Always follow existing patterns.

Do not introduce new styles arbitrarily.

---

## 18. Agent Responsibility

You MUST:

- follow conventions strictly
- not introduce inconsistent code
- maintain readability

If unsure:
→ STOP and ask