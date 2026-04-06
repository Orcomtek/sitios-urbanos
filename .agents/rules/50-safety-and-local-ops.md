---
trigger: always_on
---

# Safety and Local Operations — Sitios Urbanos

---

## 1. Purpose

This rule defines how the system must behave in **real-world operational environments**, especially in:

- Colombia
- Latin America
- residential communities with varying levels of organization and infrastructure

The system must be designed for reality, not ideal conditions.

---

## 2. Core Principle

The system MUST operate correctly under:

- imperfect data
- incomplete processes
- human error
- low digital maturity environments

---

## 3. Operational Reality

The system MUST assume:

- inconsistent administrative processes
- varying levels of technology adoption
- partial or delayed data entry
- manual operations coexisting with digital flows

---

## 4. Community Types (MANDATORY SUPPORT)

The system MUST support:

---

### 4.1 Guarded Communities

- security staff (portería)
- manual access validation
- assisted digital flows

---

### 4.2 Semi-Structured Communities

- partial administration
- mixed manual/digital processes
- inconsistent enforcement

---

### 4.3 Self-Managed Communities

- no guards
- autonomous access
- residents operate the system directly

---

## 5. Critical Rule

The system MUST deliver value even when:

- there is no security staff
- there is no strict administration
- processes are informal

---

## 6. Human Error Handling

The system MUST:

- tolerate user mistakes
- allow correction of data
- avoid rigid blocking flows when possible
- preserve traceability even when corrected

---

## 7. Partial Data Scenarios

The system MUST function when:

- residents are not fully registered
- units are incomplete
- contact data is missing

---

### Rule

Partial data MUST NOT break the system.

---

## 8. Offline / Delayed Operation Reality

The system MUST consider:

- delayed data entry
- actions recorded after the fact
- inconsistent timing of events

---

### Example

- visitor entered but registered later
- package delivered but logged after

---

### Rule

The system MUST:

- allow delayed recording
- preserve chronological traceability

---

## 9. Security Without Guards

The system MUST support security flows without physical guards.

Includes:

- self-authorized visits
- QR-based access
- notification-based validation
- digital logs

---

### Rule

Security MUST NOT depend on human gatekeepers.

---

## 10. Panic Button (Operational Reality)

The panic system MUST:

- work under stress conditions
- trigger fast notifications
- operate even under limited resources

---

### SMS Constraint

- SMS usage is limited per tenant
- must enforce fair use
- must fallback to alternative channels when needed

---

### Critical Rule

The panic button MUST NOT fail silently.

---

## 11. Communication Constraints

The system MUST consider:

- users may ignore notifications
- users may not respond immediately
- communication may fail

---

### Rule

The system MUST:

- support retries when applicable
- provide visibility of pending actions
- avoid blocking flows unnecessarily

---

## 12. Administrative Variability

The system MUST support:

- strict administrators
- flexible administrators
- minimal administration environments

---

### Rule

The system MUST NOT assume:

- perfect enforcement of rules
- consistent administrative behavior

---

## 13. Operational Flexibility

The system MUST:

- allow configuration per tenant
- adapt to different community rules
- avoid rigid global behavior

---

## 14. Data Correction and Auditability

The system MUST allow:

- correction of mistakes
- updates to records

---

### Critical Rule

All corrections MUST:

- be traceable
- preserve historical context
- not overwrite audit history silently

---

## 15. Legal and Operational Safety

The system MUST:

- provide sufficient traceability for incident review
- support reconstruction of events
- protect platform from liability due to missing data logs

---

## 16. UX Under Stress

The system MUST:

- be usable under pressure (e.g. panic situations)
- minimize friction in critical actions
- avoid complex multi-step flows in emergencies

---

## 17. Tenant-Specific Rules

Operational rules MUST be:

- configurable per community
- adaptable to agreements with each tenant

---

## 18. Forbidden Assumptions

The system MUST NOT assume:

- all users are trained
- all processes are followed correctly
- all data is accurate
- all actions are timely

---

## 19. Testing Requirements

The system MUST validate:

- flows with incomplete data
- delayed operations
- manual corrections
- no-guard scenarios
- panic button fallback behavior

---

## 20. Strategic Importance

This rule ensures:

- real-world usability
- adoption across different community types
- operational resilience
- product-market fit

---

## 21. Consequence of Violation

Breaking this rule leads to:

- system rejection by users
- operational friction
- low adoption
- real-world failure

---

## 22. Final Principle

The system must behave as:

- a resilient system
- a flexible system
- a real-world system

Not as an idealized or rigid platform.