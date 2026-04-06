---
trigger: always_on
---

# Rule Precedence — Sitios Urbanos

---

## 1. Purpose

This rule defines the **priority order between all rules**.

When two or more rules conflict, this precedence MUST be used to resolve the conflict.

---

## 2. Core Principle

Higher priority rules override lower priority rules.

No exceptions.

---

## 3. Priority Order (Highest to Lowest)

---

### 1. SRS (System Requirements Specification)

- Defines system behavior
- Defines constraints
- Defines flows

👉 This is the **highest authority**

---

### 2. Domain & Tenant Entry Strategy

- Defines domain structure
- Defines tenant resolution
- Defines authentication entry

👉 Protects SaaS architecture integrity

---

### 3. Tenant Architecture & Isolation

- Defines multi-tenant behavior
- Defines isolation rules

👉 Protects data separation

---

### 4. Security & Forensic Traceability

- Defines logging
- Defines attribution
- Defines audit requirements

👉 Protects operational and legal integrity

---

### 5. Data Protection & Legal Compliance

- Defines privacy
- Defines data handling
- Defines legal constraints

👉 Protects regulatory compliance

---

### 6. MVP Boundary

- Defines scope limits
- Prevents feature creep

👉 Protects execution discipline

---

### 7. Execution Backlog (RIGOR Blocks)

- Defines execution order
- Defines block dependencies

👉 Protects development sequence

---

### 8. Stack & Framework Rules

- Defines tools and technologies

👉 Supports implementation, not architecture

---

### 9. UI & UX Rules

- Defines presentation layer

👉 Lowest priority

---

## 4. Conflict Resolution Rules

If two rules conflict:

1. Apply the higher priority rule
2. Ignore the lower priority rule in that context
3. DO NOT attempt to merge conflicting logic

---

## 5. No Interpretation Rule

The agent MUST NOT:

- reinterpret rule intent
- “balance” conflicting rules
- create hybrid solutions

---

## 6. Clarification Requirement

If conflict cannot be resolved clearly:

- STOP execution
- request clarification

---

## 7. Strategic Importance

This rule ensures:

- consistency
- predictability
- architectural integrity

---

## 8. Consequence of Violation

Breaking this rule leads to:

- inconsistent system behavior
- hidden architectural issues
- loss of control over development

---

## 9. Final Principle

The system must behave as:

- a rule-driven system
- a deterministic system

NOT as an improvisational system.