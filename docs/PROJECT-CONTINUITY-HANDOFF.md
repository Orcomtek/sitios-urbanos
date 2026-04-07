# PROJECT CONTINUITY HANDOFF — Sitios Urbanos

---

## 1. Purpose

This document defines the **official continuity system** for Sitios Urbanos.

It ensures:

- zero loss of context across chats
- consistent execution under RIGOR
- safe handoff between sessions, agents, or developers
- long-term architectural integrity

---

## 2. Core Principle

The project MUST NOT depend on chat memory.

All critical context MUST be externalized into documentation.

This document is the **authoritative continuity baseline**.

---

## 3. Execution Model

This project uses a structured execution model:

- **Camilo Alcalá** → Project Owner / Final Approver  
- **ChatGPT** → Architect / Planner / Auditor (RIGOR enforcement)  
- **Google Antigravity** → Code execution agent  

---

### Rule

ChatGPT MUST:

- NOT act as code executor
- plan, review, validate, and audit
- enforce RIGOR discipline
- protect architecture and scope

---

## 4. Product Overview

Sitios Urbanos is a **multi-tenant SaaS ecosystem** for residential communities.

It operates across:

- operations
- security
- finance
- governance
- ecosystem (P2P + marketplace)

---

### Target Region

- Colombia (primary)
- LATAM (expansion)

---

## 5. Architecture Status

---

### Completed

- PRD (final)
- SRS (Parts 1–8)
- MVP Boundary (final)
- Domain Architecture (subdomain-based)
- Rules system (.agents/rules)

---

### Not Yet Implemented

- RIGOR 3.0 execution blocks
- Infrastructure layer (subdomain runtime)
- Authentication flow (email-first)
- Tenant runtime (production-ready)

---

## 6. Locked Technology Stack

- Backend: Laravel 13
- Language: PHP 8.4+
- Frontend: Vue 3 + Inertia.js
- Styling: Tailwind CSS 4
- Database: PostgreSQL
- Testing: Pest PHP

---

### Stack Rule

No deviations without explicit approval.

---

## 7. Mandatory Methodology — RIGOR 3.0

---

### Core Principle

The system is built in **controlled execution blocks**, not features.

---

### Rules

- work block-by-block
- no code without planning
- require:
  - Task List
  - Implementation Plan
  - Approval
- validate before moving forward

---

### Block Lifecycle

Each block MUST be:

1. Planned  
2. Implemented  
3. Validated  
4. Documented (continuity update)

---

### Forbidden

- skipping steps
- mixing blocks
- premature optimization
- scope expansion
- architecture improvisation

---

## 8. Execution Backlog (RIGOR 3.0)

---

### Blocks

1. Infrastructure & Multi-Domain Foundation  
2. Authentication & Entry  
3. Tenant Runtime  
4. Core Entities  
5. Security Deep Layer  
6. Financial Core  
7. Governance  
8. Ecosystem  

---

### Rule

Execution order is mandatory.

---

## 9. Domain & Tenant Model (FROZEN)

---

### Entry

app.sitiosurbanos.com

---

### Tenant Runtime

{communitySlug}.app.sitiosurbanos.com

---

### Rules

- tenant resolved by subdomain
- NO path-based tenancy
- NO public selector
- email-first authentication
- fail-closed behavior (404)

---

## 10. Multi-Tenant Architecture

---

### Core Rules

- tenant context is mandatory
- backend is authority
- all data scoped by tenant
- no cross-tenant access

---

### Enforcement

- TenantContext
- middleware
- resolver
- strict query scoping

---

## 11. Financial Model (CRITICAL)

---

### Rules

- commission ONLY on platform payments
- external payments MUST be supported
- ledger is mandatory
- webhook validation required
- idempotency required
- backend controls all financial logic

---

## 12. Security Model (CRITICAL)

---

### Requirements

- full traceability
- attribution of actions
- identity validation states:
  - validated
  - partial
  - skipped

---

### Panic Button

- SMS quota controlled
- fallback required
- must NOT fail silently

---

## 13. Data & Legal Layer

---

### Must support

- Colombian data laws
- consent tracking
- anonymization
- retention policies
- traceable deletion

---

## 14. UI & UX Principles

---

- Spanish-first (Colombia)
- clean and minimal
- role-based
- frontend = rendering layer
- no business logic in frontend

---

## 15. Rules System (.agents/rules)

---

The system is governed by structured rules.

---

### Critical Rules

- domains and entry strategy
- SRS and MVP boundary
- tenancy
- finance and security
- forensic traceability
- data compliance
- testing and quality
- UI system
- rule precedence

---

### Rule

All implementation MUST comply with these rules.

---

## 16. Continuity Rule (MANDATORY)

---

### After EACH RIGOR block:

The system MUST:

1. update this document  
2. summarize progress  
3. record validation results  
4. define next step  

---

### This is NON-NEGOTIABLE

---

## 17. Required Continuity Content

Each update MUST include:

---

### Current Phase

Example:

RIGOR 3.0 — Block 1 (Infrastructure)

---

### Completed Work

- implemented features
- validated flows
- passed tests

---

### System State

- stable / unstable
- limitations

---

### Active Constraints

- tenant rules
- financial rules
- security rules
- MVP boundaries

---

### Next Steps

- next block
- dependencies

---

### Risks

- fragile areas
- warnings

---

## 18. New Chat Resume Protocol

---

### Required Documents

- PROJECT-CONTINUITY-HANDOFF.md
- PRD
- SRS
- MVP
- Backlog
- Rules

---

### Instruction Template

```text
Continue Sitios Urbanos using RIGOR 3.0.
We are currently at [BLOCK].
Request Task List + Implementation Plan.
Do not re-plan previous phases.
Do not change frozen decisions.
```

---

## 19. Forbidden Behavior

The system MUST NOT:

- rely on previous chat memory
- assume missing context
- skip continuity updates
- start execution without loading documents
- reopen frozen architecture
- mix execution blocks
- bypass validation steps

---

## 20. Agent Responsibility

The agent MUST:

- enforce RIGOR
- protect architecture
- respect SRS and MVP
- request clarification when needed
- stop under uncertainty

---

## 21. Governance Priority

If ambiguity exists, apply the following order:

1. This document  
2. SRS  
3. Domain rules  
4. Security rules  
5. MVP  
6. Backlog  

---

## 22. Strategic Importance

This document ensures:

- continuity across sessions
- safe scaling of development
- architectural consistency
- execution discipline

---

## 23. Consequence of Violation

Breaking this rule leads to:

- context loss
- inconsistent system behavior
- duplicated work
- architectural drift

---

## 24. Final Principle

The project must behave as:

- a persistent system
- a documented system
- a reconstructable system

Not as a chat-dependent process.

Current Phase

# convertir a Markdown desde aqui

RIGOR 3.0 — Block 1 (Infrastructure & Multi-Domain Foundation) ✅ COMPLETED

🔹 Completed Work
Subdomain architecture implemented
Control Plane and Tenant Runtime separated
Session shared across subdomains
Path-based tenancy removed from runtime
Tests updated to domain-based routing
Local development strategy documented
🔹 System State
Stable
Infrastructure ready for authentication layer
No known tenant leakage risks
🔹 Next Step

👉 RIGOR 3.0 — Block 2 (Authentication & Entry)

Current Phase

RIGOR 3.0 — Block 2 (Authentication & Entry) ✅ COMPLETED

Completed Work
Login flow anchored in app.sitios-urbanos.test
Private community selector implemented through backend validation
Single-community auto-redirect implemented
Zero-community controlled state implemented
Tenant runtime root route established with temporary redirect to units.index
Session persistence across subdomains manually validated in browser
Root tenant redirect bug fixed by explicitly forwarding community_slug
System State
Stable
Authentication and tenant entry flow operational
Shared session behavior confirmed
Next Step

👉 RIGOR 3.0 — Block 3 (Tenant Runtime Hardening)

Risks / Notes
Tenant runtime still uses temporary landing redirect to units.index
Full tenant runtime hardening remains pending in Block 3

Current Phase

RIGOR 3.0 — Block 3 (Tenant Runtime Hardening) ✅ COMPLETED

Completed Work
TenantScope and TenantScoped implemented
TenantContext enforced as runtime authority
Unit and Resident models protected via automatic scoping
community_id removed from fillable
payload manipulation prevented
route model binding secured via tenant scope
middleware priority corrected (TenantMiddleware before SubstituteBindings)
cross-tenant access denied (404)
manual validation of tenant isolation performed
System State
Stable
Tenant runtime isolation enforced
No cross-tenant leakage in HTTP runtime
Risks / Notes
Outside TenantContext (queues, artisan, control plane), scope behaves benignly
Queue tenancy not yet implemented
Next Step

👉 RIGOR 3.0 — Block 4 (Core Entities Hardening)

Current Phase

RIGOR 3.0 — Block 4 (Core Entities Hardening) ✅ COMPLETED

Completed Work
Unit aligned with SRS (property_type, parking, storage)
JSONB identifiers implemented
Resident aligned with SRS (full_name, resident_type, is_active, pays_administration)
user_id remains nullable
one-active-tenant-per-unit enforced (application + DB)
hard rejection implemented
factories and tests updated
constraint validated through automated and manual tests
System State
Stable
Core domain entities aligned with business rules
Data integrity significantly improved
Risks / Notes
Partial unique index only active in PostgreSQL
SQLite fallback relies on application-level validation
Next Step

👉 RIGOR 3.0 — Block 5 (Financial Engine Foundation)

Current Phase

RIGOR 3.0 — Block 5 (Financial Engine Foundation) ✅ COMPLETED

Completed Work
Invoice, Payment, LedgerEntry models implemented
resident_id used as operational payer reference
issued_at added to Invoice
strict enums for statuses and methods
integer-based monetary handling (COP)
unit-centric ledger implemented
ledger immutability enforced
idempotency implemented via idempotency_key
1 payment → 1 invoice enforced
transactional integrity using DB::transaction()
System State
Stable
Financial foundation operational
Ledger integrity enforced
Ready for payment gateway integration (future block)
Risks / Notes
No refund engine yet
No multi-invoice payments yet
No overdue logic yet
ePayco integration pending
Next Step

👉 RIGOR 3.0 — Block 6 (Payments Integration + Notifications)

Current Phase

RIGOR 3.0 — Block 6 (Aggregator Payments + Split Orchestration) ✅ COMPLETED

Completed Work
Payment model extended for aggregator orchestration
external_reference and idempotency_key separated correctly
webhook endpoint implemented (/api/webhooks/epayco)
cryptographic signature validation enforced
strict webhook validation pipeline implemented
idempotent webhook processing ensured
Payment → Invoice state synchronization implemented
ledger entries for payment and platform commission created
net_amount and platform_commission handled via configuration
internal vs external payment logic separated
System State
Stable
Payment orchestration functional
Financial integrity enforced end-to-end
Ready for real ePayco environment integration
Risks / Notes
Commission currently uses placeholder configuration
No refund engine yet
No multi-invoice payments yet
No advanced reconciliation UI yet
Next Step

👉 RIGOR 3.0 — Block 7 (Notifications + User Financial UX)

Current Phase

RIGOR 3.0 — Block 7 (Notifications + Financial UX Layer) ✅ COMPLETED

Completed Work
PaymentConfirmed and PaymentFailed domain events implemented
SendPaymentNotifications listener created
Mail-based notification system (no database notifications)
Recipient resolution via Invoice → Resident → User
FinancialStateController exposing invoice/payment state
Tenant-scoped API endpoints implemented
Event dispatch tied strictly to valid financial state transitions
No notifications on invalid or tampered webhooks
Full test coverage for events, notifications, and API
System State
Stable
Financial UX layer operational
Notifications working correctly
API ready for frontend integration
Risks / Notes
No notification inbox/history yet
No push/SMS yet
No advanced UX/UI yet
Next Step

👉 RIGOR 3.0 — Block 8 (Legal + Data Layer + Compliance Hardening)