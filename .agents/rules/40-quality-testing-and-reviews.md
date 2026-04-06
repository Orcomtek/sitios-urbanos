---
trigger: always_on
---

# Quality, Testing and Reviews — Sitios Urbanos

---

## 1. Purpose

This rule defines the **quality assurance, testing, and validation standards** for the system.

Its goal is to ensure:

- correctness
- stability
- tenant safety
- financial integrity
- security traceability
- architectural discipline

---

## 2. Core Principle

The system MUST NOT rely on assumptions.

All critical behavior MUST be:

- tested
- validated
- reproducible

---

## 3. Testing Philosophy

Testing is NOT optional.

Testing is NOT decorative.

Testing is NOT a formality.

Testing is the mechanism that guarantees system integrity.

---

## 4. Mandatory Testing Scope

The system MUST include tests for:

---

### 4.1 Tenant Isolation

Must validate:

- no cross-tenant data access
- correct tenant scoping
- rejection of invalid tenant access

---

### 4.2 Authentication and Entry

Must validate:

- email-first flow
- correct tenant redirection
- session integrity across subdomains

---

### 4.3 Core Operations

Must validate:

- units creation
- residents assignment
- owner/tenant rules
- one active tenant per unit

---

### 4.4 Security Flows

Must validate:

- visitor creation
- access validation
- identity validation states:
  - validated
  - partially validated
  - skipped
- omission traceability

---

### 4.5 Financial Logic

Must validate:

- webhook validation
- idempotency
- correct commission application
- exclusion of external payments from commission
- ledger consistency

---

### 4.6 Governance

Must validate:

- PQRS creation
- anonymous PQRS
- document access control
- role-based visibility

---

### 4.7 Ecosystem (MVP Level)

Must validate:

- P2P basic publishing
- basic marketplace interactions
- tenant isolation in interactions

---

## 5. No Fake Tests

The system MUST NOT:

- create superficial tests
- rely only on mocks for critical logic
- simulate behavior without verifying real outcomes

---

## 6. Assertion Requirements

Tests MUST include:

- explicit assertions
- clear expected outcomes
- validation of side effects

---

### Example

Not acceptable:

- "request succeeded"

Required:

- data persisted correctly
- tenant isolation preserved
- correct state transitions occurred

---

## 7. End-to-End Validation

Critical flows MUST be validated end-to-end:

- authentication → tenant → operation
- payment → webhook → ledger
- visitor → validation → log

---

## 8. Architecture Validation

The system MUST validate:

- correct tenant resolution
- correct TenantContext usage
- absence of path-based tenancy
- absence of session-based authority

---

## 9. Financial Integrity Testing

The system MUST validate:

- no duplicate transactions
- correct ledger entries
- correct split logic
- correct handling of failed payments

---

## 10. Security Validation

The system MUST validate:

- logging of critical actions
- attribution (who, when)
- omission handling
- traceability of events

---

## 11. Failure Testing

The system MUST validate:

- invalid tenant access
- invalid payment data
- invalid authentication flows
- missing tenant context

---

## 12. Manual Validation (MANDATORY)

Automated testing is not enough.

The system MUST also be validated manually:

- UI behavior
- user flows
- tenant isolation in real navigation
- critical flows (payments, access)

---

## 13. RIGOR Execution Discipline

The system MUST:

- execute one block at a time
- validate before moving forward
- avoid mixing blocks

---

### Forbidden

The system MUST NOT:

- skip validation steps
- continue after failed validation
- assume correctness without proof

---

## 14. Review Requirements

Every significant implementation MUST be:

- reviewed
- validated against SRS
- validated against MVP
- checked for architectural consistency

---

## 15. Test Integrity

Tests MUST:

- reflect real system behavior
- be maintainable
- avoid false positives

---

## 16. Strategic Importance

This rule protects:

- system reliability
- financial correctness
- tenant safety
- long-term scalability

---

## 17. Consequence of Violation

Breaking this rule leads to:

- hidden bugs
- data leakage
- financial errors
- system instability

---

## 18. Final Principle

The system must behave as:

- a verified system
- a testable system
- a predictable system

Not as an assumed or fragile system.