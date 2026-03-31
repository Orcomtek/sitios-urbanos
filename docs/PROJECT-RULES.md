# Project Rules — Sitios Urbanos

## 1. Purpose

This document defines the **non-negotiable rules of the project**.

These rules apply to:

- architecture
- development
- product decisions
- agent behavior

---

## 2. Core Principles

The project must be:

- scalable
- maintainable
- secure
- predictable

---

## 3. MVP Discipline

You MUST:

- respect MVP boundaries
- reject features outside scope
- avoid over-engineering

If a feature is not in MVP:
→ it is NOT implemented

---

## 4. Multi-Tenancy (CRITICAL)

The system is multi-tenant.

You MUST:

- isolate all data using `community_id`
- enforce tenant scope everywhere
- prevent cross-tenant access

This rule overrides convenience.

---

## 5. Backend Authority

The backend is the source of truth.

Frontend MUST NOT:

- define permissions
- calculate financial values
- control business logic

---

## 6. Financial Integrity (CRITICAL)

You MUST:

- ensure all transactions are traceable
- use a ledger system
- validate all payments

You MUST NOT:

- modify balances without record
- trust frontend values

---

## 7. Modularity

The system must be modular.

You MUST:

- separate domains
- avoid tight coupling
- design for feature activation

---

## 8. Simplicity First

You MUST:

- prioritize simple solutions
- avoid premature complexity
- deliver usable features early

---

## 9. No Assumptions

You MUST:

- not assume requirements
- not invent features
- not guess behavior

If unclear:
→ STOP and ask

---

## 10. Documentation Awareness

You MUST:

- respect documentation
- update docs when behavior changes
- detect inconsistencies

---

## 11. Consistency

You MUST:

- follow existing patterns
- avoid introducing new styles randomly

---

## 12. Security

You MUST:

- validate all inputs
- protect sensitive data
- avoid exposing internal logic

---

## 13. Performance

You MUST:

- avoid unnecessary complexity
- optimize only when needed

---

## 14. Responsibility

You MUST:

- think before implementing
- validate decisions
- maintain system integrity

---

## 15. RIGOR Alignment

All work must follow RIGOR:

- plan first
- implement after approval
- validate before continuing

---

## 16. Final Rule

If any rule conflicts with:

- speed
- convenience
- shortcuts

You MUST choose:

→ correctness and system integrity