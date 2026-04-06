---
trigger: always_on
---

# Security and Forensic Traceability Rule — Sitios Urbanos

---

## 1. Purpose

This rule enforces **security, traceability, and forensic audit requirements** across the entire system.

The system MUST be designed not only for operation, but also for:

- incident reconstruction
- auditability
- operational accountability
- legal defensibility

---

## 2. Core Principle

Every critical action MUST be:

- recorded
- attributable
- timestamped
- reconstructable

---

## 3. No Silent Operations

The system MUST NOT perform any critical action without leaving trace.

---

### Critical Actions Include (but are not limited to):

- access authorization
- visitor creation
- entry validation
- identity validation
- identity validation omission
- QR generation and validation
- package registration and delivery
- panic button activation
- state transitions
- incident creation or updates

---

## 4. Action Attribution

Every critical action MUST include:

- actor (who performed it)
- role
- tenant
- timestamp
- channel or device (if available)
- result of the action

---

## 5. Identity Validation States

The system MUST explicitly record:

- validated
- partially validated
- skipped

---

### If validation is skipped:

The system MUST record:

- that validation was skipped
- who skipped it
- when it was skipped
- reason (if required by policy)

---

## 6. Security Log (Bitácora)

The system MUST maintain a centralized log of:

- access events
- validation events
- package flows
- incidents
- critical state changes

---

### Rules:

- logs MUST NOT be silently editable
- logs MUST preserve history
- logs MUST support reconstruction of events

---

## 7. QR Security

QR flows MUST:

- be unique
- be time-bound
- have expiration
- be tied to a specific authorization
- be invalidatable

---

### QR validation MUST verify:

- status
- expiration
- tenant
- destination
- usage state

---

## 8. No Frontend Trust

The system MUST NOT:

- trust frontend for validation
- rely on client-side logic for security
- accept unverified inputs

All validation MUST happen in backend.

---

## 9. Tenant Isolation in Security

Security events MUST:

- be scoped to tenant
- never leak across tenants
- never allow cross-tenant operations

---

## 10. Incident Reconstruction Capability

The system MUST allow reconstruction of:

- who authorized access
- who validated entry
- whether identity was validated
- whether validation was skipped
- sequence of events
- timing of events

---

## 11. Panic Button Requirements

The panic system MUST:

- generate immediate events
- record activation
- record origin
- notify defined actors
- allow reconstruction of activation

---

## 12. Package Handling Traceability

The system MUST record:

- who received the package
- when it was received
- who delivered it
- who collected it
- timestamps for each step

---

## 13. Validation of Omission

The system MUST treat omission as a first-class event.

Skipping validation is NOT invisible.

---

## 14. Testing Requirements

Security flows MUST be tested for:

- traceability
- attribution
- omission recording
- cross-tenant isolation
- log integrity

---

## 15. Forbidden Behaviors

The system MUST NOT:

- allow unlogged operations
- allow silent data modification
- allow missing attribution
- allow missing timestamps
- allow identity validation ambiguity

---

## 16. Strategic Importance

This rule protects:

- operational integrity
- audit capability
- legal defensibility
- trust in the platform

---

## 17. Consequence of Violation

Breaking this rule may result in:

- inability to reconstruct incidents
- legal exposure
- loss of trust
- operational failure

---

## 18. Final Principle

The system must behave as:

- a reliable operational system
- an auditable system
- a defensible system

Not just as a functional application.