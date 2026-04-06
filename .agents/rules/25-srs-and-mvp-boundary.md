---
trigger: always_on
---

# SRS and MVP Boundary Rule — Sitios Urbanos

---

## 1. Purpose

This rule enforces strict adherence to:

- SRS (System Requirements Specification)
- MVP Boundary

The system MUST be built according to defined specifications.

No feature, behavior, or architecture decision may violate these constraints.

---

## 2. SRS as Source of Truth

The SRS documents define:

- system behavior
- data structures
- constraints
- flows
- responsibilities

---

### Rule

The SRS is the **primary contract** of the system.

---

### Mandatory Behavior

The system MUST:

- follow SRS definitions strictly
- NOT reinterpret requirements
- NOT introduce alternative logic
- NOT simplify critical flows

---

## 3. MVP Boundary

The MVP document defines:

- what MUST be built
- what MUST NOT be built yet

---

### Rule

The MVP is a **hard boundary**.

---

### Forbidden Behavior

The system MUST NOT:

- implement features outside MVP
- anticipate future modules
- introduce optional expansions
- add “nice-to-have” features

---

## 4. No Feature Invention

The agent MUST NOT:

- invent new features
- expand scope beyond SRS/MVP
- assume missing requirements
- “improve” product definition autonomously

---

## 5. No Premature Optimization

The system MUST NOT:

- build scalability features not required yet
- add abstraction layers without need
- introduce complex patterns prematurely

---

## 6. Strict Scope Discipline

Every implementation MUST:

- belong to the current execution block
- align with backlog structure
- respect execution order

---

## 7. Block Isolation Rule

The system MUST NOT:

- mix multiple backlog blocks
- partially implement future blocks
- create dependencies on non-built features

---

## 8. Change Control

If a requirement seems missing or incorrect:

The agent MUST:

- STOP
- request clarification
- NOT proceed with assumptions

---

## 9. Validation Requirement

Before completing any block:

The system MUST verify:

- alignment with SRS
- alignment with MVP
- no out-of-scope features were introduced

---

## 10. Strategic Importance

This rule prevents:

- scope creep
- architectural drift
- hidden technical debt
- uncontrolled product expansion

---

## 11. Consequence of Violation

Breaking this rule leads to:

- inconsistent product behavior
- unstable architecture
- loss of control over execution

---

## 12. Final Principle

The system is built by:

- discipline
- constraint
- controlled execution

NOT by improvisation.