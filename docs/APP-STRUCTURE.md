# App Structure — Sitios Urbanos

## 1. Purpose

This document defines the **official project structure**.

All code MUST follow this structure.

---

## 2. Backend Structure (Laravel)
app/
├── Domain/
│ ├── Core/
│ ├── Finance/
│ ├── Governance/
│ ├── Security/
│ ├── Operations/
│ └── Shared/
│
├── Application/
│ ├── Actions/
│ ├── Services/
│ └── DTOs/
│
├── Infrastructure/
│ ├── Persistence/
│ ├── External/
│ └── Notifications/
│
├── Support/
│ ├── Helpers/
│ ├── Traits/
│ └── Enums/
│
├── Http/
│ ├── Controllers/
│ ├── Middleware/
│ └── Requests/


---

## 3. Domain Layer

Contains business domains:

- Finance
- Governance
- Security
- Operations

Each domain includes:

- models
- domain services
- domain rules

---

## 4. Application Layer

Handles use cases:

### Actions
- single-purpose logic
- e.g. `CreateInvoiceAction`

### Services
- reusable logic

### DTOs
- structured data transfer

---

## 5. Infrastructure Layer

Handles external concerns:

- database persistence
- payment gateway (ePayco)
- notifications
- integrations

---

## 6. Support Layer

Shared utilities:

- enums
- traits
- helpers

---

## 7. HTTP Layer

- Controllers → thin
- Requests → validation
- Middleware → tenant, auth

---

## 8. Frontend Structure


resources/js/
├── pages/
├── components/
├── layouts/
├── modules/
├── types/


---

## 9. Modules (Frontend)

Modules represent domains:

- finance
- governance
- security
- operations

Each module contains:

- components
- pages
- logic

---

## 10. Routes


routes/
├── web.php
├── api.php


Future:

- domain-based routing

---

## 11. Docs Structure


docs/
├── PRD.md
├── MVP-BOUNDARY.md
├── ARCHITECTURE.md
├── APP-STRUCTURE.md
├── PROJECT-RULES.md
├── BACKLOG-RIGOR.md


---

## 12. Naming Rules

Use clear names:

- `CreateCommunityAction`
- `ProcessPaymentAction`

Avoid:

- helpers
- utils
- generic names

---

## 13. Restrictions

You MUST NOT:

- create random folders
- mix layers
- place business logic in controllers
- duplicate logic

---

## 14. Agent Responsibility

You MUST:

- follow structure strictly
- not improvise organization
- maintain consistency

If unsure:
→ STOP and ask