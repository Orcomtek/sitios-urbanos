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

ChatGPT (and any AI agent) MUST:

- NOT act as code executor
- plan, review, validate, and audit
- enforce RIGOR discipline
- protect architecture and scope
- NOT execute code directly
- NOT modify this document

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

### Rule 7. Mandatory Methodology — RIGOR 4.2
This project is governed strictly by **RIGOR 4.2**. No implementation is allowed without passing through the full lifecycle:
1.  **Task Definition:** Small, concrete, and bounded.
2.  **Implementation Plan:** Technical logic, affected files, and risks.
3.  **Explicit Approval:** The Architect must approve the plan before a single line of code is written.
4.  **Execution:** Strictly limited to the approved scope.
5.  **Validation:** Technical verification (Routes, Models, Logic).
6.  **Closing Audit (The Hard Gate):** Mandatory check against Visual/UX standards, 100% SRS compliance, and Tenant Isolation.
7.  **Closure:** Documentation and clean commit.

**Strict Prohibition:** Skipping the Closing Audit or normalizing "silent errors" (like swallowed 422 validations) is a breach of RIGOR 4.2.

---

## 8. Execution Backlog (RIGOR 4.2)

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

### Rule 17. Required Continuity Content
Every handoff or block closure must contain:
1.  **Current Block Status:** [Resolved], [Pending], or [Incomplete].
2.  **Last Validated Milestone:** What is physically working and tested.
3.  **Closing Audit Evidence:**
    * **Visual/UX:** Confirmation of premium UI and error handling.
    * **SRS Compliance:** Verification against functional requirements.
    * **Tenant Isolation:** Evidence that data is strictly scoped to `community_id`.
4.  **Frozen Decisions:** Any new architectural or business rules established.
5.  **Pending Contingencies:** Any debt or UX refactor discovered and logged in `PROJECT-CONTINGENCIES.md`.
6.  **Next Immediate Step:** Precise instruction for the next session.

---

### Rule 18. New Chat Resume Protocol
When starting a new session or switching agents, the following protocol is mandatory:
1.  **Mandatory Document Bundle:** The agent MUST read the following files before any action:
    * `METHODOLOGY-RIGOR.md` (To understand governance).
    * `PROJECT-CONTINUITY-HANDOFF.md` (To understand current state).
    * `PROJECT-CONTINGENCIES.md` (To understand technical debt and pending refactors).
    * `ARCHITECTURE.md` & `APP-STRUCTURE.md` (To understand boundaries).
2.  **State Verification:** The agent must summarize its understanding of the "Last Validated Milestone" and "Next Step" before requesting approval to proceed.
3.  **Context Alignment:** Any deviation from the "Frozen Decisions" found in the Handoff must be flagged immediately.

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

## 25. 🧊 Frozen Business & Architectural Decisions

* **Monetization & Global Services Access (Default-Closed Rule):** All global services within the Sitios Urbanos ecosystem (Marketplace, Add-ons, External Services, Global Categories) are **default-closed** to community administrations. Sitios Urbanos owns the supply, controls the publishing, and manages these modules exclusively via the SuperAdmin panel. Granting creation/management permissions to a local community administration is strictly a **transactional exception** (enabled via a *Feature Flag* linked to a premium plan or membership sale). Native internal management modules (e.g., visitors, PQRS) retain their standard local administration behavior.

# Arquitectura y Roadmap: RIGOR 3.0

---

## RIGOR 3.0 — Block 1 (Infrastructure & Multi-Domain Foundation) ✅ COMPLETED

### 🔹 Completed Work
* Subdomain architecture implemented.
* Control Plane and Tenant Runtime separated.
* Session shared across subdomains.
* Path-based tenancy removed from runtime.
* Tests updated to domain-based routing.
* Local development strategy documented.

### 🔹 System State
* Stable.
* Infrastructure ready for authentication layer.
* No known tenant leakage risks.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 2 (Authentication & Entry)**

---

## RIGOR 3.0 — Block 2 (Authentication & Entry) ✅ COMPLETED

### 🔹 Completed Work
* Login flow anchored in `app.sitios-urbanos.test`.
* Private community selector implemented through backend validation.
* Single-community auto-redirect implemented.
* Zero-community controlled state implemented.
* Tenant runtime root route established with temporary redirect to `units.index`.
* Session persistence across subdomains manually validated in browser.
* Root tenant redirect bug fixed by explicitly forwarding `community_slug`.

### 🔹 System State
* Stable.
* Authentication and tenant entry flow operational.
* Shared session behavior confirmed.

### 🔹 Risks / Notes
* Tenant runtime still uses temporary landing redirect to `units.index`.
* Full tenant runtime hardening remains pending in Block 3.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 3 (Tenant Runtime Hardening)**

---

## RIGOR 3.0 — Block 3 (Tenant Runtime Hardening) ✅ COMPLETED

### 🔹 Completed Work
* `TenantScope` and `TenantScoped` implemented.
* `TenantContext` enforced as runtime authority.
* `Unit` and `Resident` models protected via automatic scoping.
* `community_id` removed from fillable properties (payload manipulation prevented).
* Route model binding secured via tenant scope.
* Middleware priority corrected (`TenantMiddleware` before `SubstituteBindings`).
* Cross-tenant access denied (404).
* Manual validation of tenant isolation performed.

### 🔹 System State
* Stable.
* Tenant runtime isolation enforced.
* No cross-tenant leakage in HTTP runtime.

### 🔹 Risks / Notes
* Outside `TenantContext` (queues, artisan, control plane), scope behaves benignly.
* Queue tenancy not yet implemented.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 4 (Core Entities Hardening)**

---

## RIGOR 3.0 — Block 4 (Core Entities Hardening) ✅ COMPLETED

### 🔹 Completed Work
* `Unit` aligned with SRS (`property_type`, `parking`, `storage`).
* JSONB identifiers implemented.
* `Resident` aligned with SRS (`full_name`, `resident_type`, `is_active`, `pays_administration`).
* `user_id` remains nullable.
* One-active-tenant-per-unit enforced (application + DB level).
* Hard rejection implemented.
* Factories and tests updated.
* Constraint validated through automated and manual tests.

### 🔹 System State
* Stable.
* Core domain entities aligned with business rules.
* Data integrity significantly improved.

### 🔹 Risks / Notes
* Partial unique index only active in PostgreSQL.
* SQLite fallback relies on application-level validation.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 5 (Financial Engine Foundation)**

---

## RIGOR 3.0 — Block 5 (Financial Engine Foundation) ✅ COMPLETED

### 🔹 Completed Work
* `Invoice`, `Payment`, `LedgerEntry` models implemented.
* `resident_id` used as operational payer reference.
* `issued_at` added to Invoice.
* Strict enums for statuses and methods implemented.
* Integer-based monetary handling (COP).
* Unit-centric ledger implemented with immutability enforced.
* Idempotency implemented via `idempotency_key`.
* 1 payment → 1 invoice constraint enforced.
* Transactional integrity using `DB::transaction()`.

### 🔹 System State
* Stable.
* Financial foundation operational.
* Ledger integrity enforced.
* Ready for payment gateway integration.

### 🔹 Risks / Notes
* No refund engine yet.
* No multi-invoice payments yet.
* No overdue logic yet.
* ePayco integration pending.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 6 (Payments Integration + Notifications)**

---

## RIGOR 3.0 — Block 6 (Aggregator Payments + Split Orchestration) ✅ COMPLETED

### 🔹 Completed Work
* `Payment` model extended for aggregator orchestration.
* `external_reference` and `idempotency_key` separated correctly.
* Webhook endpoint implemented (`/api/webhooks/epayco`).
* Cryptographic signature validation enforced.
* Strict webhook validation pipeline implemented.
* Idempotent webhook processing ensured.
* Payment → Invoice state synchronization implemented.
* Ledger entries for payment and platform commission created.
* `net_amount` and `platform_commission` handled via configuration.
* Internal vs External payment logic separated.

### 🔹 System State
* Stable.
* Payment orchestration functional.
* Financial integrity enforced end-to-end.
* Ready for real ePayco environment integration.

### 🔹 Risks / Notes
* Commission currently uses placeholder configuration.
* No refund engine yet.
* No multi-invoice payments yet.
* No advanced reconciliation UI yet.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 7 (Notifications + User Financial UX)**

---

## RIGOR 3.0 — Block 7 (Notifications + Financial UX Layer) ✅ COMPLETED

### 🔹 Completed Work
* `PaymentConfirmed` and `PaymentFailed` domain events implemented.
* `SendPaymentNotifications` listener created.
* Mail-based notification system (no database notifications).
* Recipient resolution via Invoice → Resident → User.
* `FinancialStateController` exposing invoice/payment state.
* Tenant-scoped API endpoints implemented.
* Event dispatch tied strictly to valid financial state transitions.
* No notifications on invalid or tampered webhooks.
* Full test coverage for events, notifications, and API.

### 🔹 System State
* Stable.
* Financial UX layer operational.
* Notifications working correctly.
* API ready for frontend integration.

### 🔹 Risks / Notes
* No notification inbox/history yet.
* No push/SMS yet.
* No advanced UX/UI yet.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 8 (Legal + Data Layer + Compliance Hardening)**

---

## RIGOR 3.0 — Block 8 (Legal + Data + Compliance Hardening) ✅ COMPLETED

### 🔹 Completed Work
* Append-only security log foundation implemented.
* Legal consent tracking foundation implemented.
* Resident anonymization action implemented.
* Retention manager baseline implemented.
* Sensitive resident deletion flow now logs security events.
* Privacy and compliance foundations added without exposing public compliance APIs.

### 🔹 System State
* Stable.
* Sensitive actions now have a compliance-ready technical base.
* Privacy hardening foundations are active.

### 🔹 Risks / Notes
* No public anonymization endpoint yet.
* No full legal/compliance dashboard yet.
* Retention manager remains foundational and non-destructive by default.

### 🔹 Next Step
👉 **RIGOR 3.0 — Bloque de consolidación / revisión estratégica del roadmap siguiente**

---

## RIGOR 3.0 — Block 9 (PQRS + Governance Core) ✅ COMPLETED

### 🔹 Completed Work
* PQRS entity implemented with tenant safety.
* Resident-based operational identity enforced.
* Anonymous PQRS supported with output-level identity protection.
* PQRS type and status enums defined.
* Admin state transitions and responses implemented via action.
* Integration with `SecurityLog` for traceability.
* Tests for tenant isolation, anonymity, transitions, and logging completed.

### 🔹 System State
* Stable.
* Governance core operational.
* PQRS ready for real usage scenarios.

### 🔹 Risks / Notes
* No advanced workflow (SLA, assignments) yet.
* No UI/dashboard yet.
* Anonymity enforced at model serialization level (future: API Resources).

### 🔹 Next Step
👉 **RIGOR 3.0 — Bloque 10: Governance UX + Notifications + Admin Flow**

---

## RIGOR 3.0 — Block 10 (Governance UX + Admin Flow) ✅ COMPLETED

### 🔹 Completed Work
* Full PQRS API implemented (create, list, show, update).
* Role-based visibility enforced per tenant.
* Anonymous PQRS fully protected at API layer via Resource.
* Admin flow implemented using `UpdatePqrsStateAction`.
* Notifications for PQRS creation and updates implemented.
* Tenant-safe routing and access control enforced.
* End-to-end feature tests implemented.

### 🔹 System State
* Stable.
* PQRS module fully operational.
* Governance flow usable end-to-end via API.

### 🔹 Risks / Notes
* No UI yet (intentional).
* No SLA or advanced workflow.
* No analytics/reporting layer.

### 🔹 Next Step
👉 **RIGOR 3.0 — Block 11 (Financial UX + Account Statement Core)**

---

## RIGOR 3.0 — Block 11 (Financial UX + Account Statement Core) ✅ COMPLETED

### 🔹 Completed Work
* Account Statement API implemented.
* Balance derived strictly from `LedgerEntry`.
* Invoice history endpoint implemented.
* Payment history endpoint implemented.
* `PLATFORM_COMMISSION` correctly isolated from unit balance.
* Tenant-safe financial access enforced.
* API Resources implemented for all outputs.
* Feature tests and manual validation completed.

### 🔹 System State
* Stable.
* Financial engine now visible and understandable for users.
* Ready for real-world usage.

### 🔹 Risks / Notes
* No UI yet (intentional).
* No reporting layer.
* No export features.

### 🔹 Next Step
👉 **Bloque 12 — Financial Operations UX (Payments Flow + UX Real)**

---

## RIGOR 3.0 — Block 12 (Payments UX + Execution Flow) ✅ COMPLETED

### 🔹 Completed Work
* Payment initiation endpoint implemented.
* Pending payment intents safely reused.
* Payable invoice rule enforced.
* Tenant-safe payment initiation enforced.
* Financial state endpoints hardened with ownership validation.
* `PaymentIntentResource` added for frontend-safe handoff.
* Feature tests for payment initiation and financial state updated.

### 🔹 System State
* Stable.
* Residents can now initiate payments safely from pending invoices.
* Payment flow is now consumable end-to-end through API.

### 🔹 Risks / Notes
* No expiration engine for pending intents yet.
* No payment frontend yet.
* No multi-invoice payment support yet.

### 🔹 Next Step
👉 **Definir el siguiente frente estratégico: Governance extendido o Marketplace / Ecosystem foundation**

---

## RIGOR 3.0 — Block 13 (Governance Extended: Announcements + Documents + Voting Core) ✅ COMPLETED

### 🔹 Completed Work
* Announcement module implemented.
* Document repository module implemented.
* Poll / voting core implemented.
* Voting eligibility and one-vote-per-unit enforced.
* Tenant-safe governance APIs implemented.
* API Resources for governance entities implemented.
* Tests for tenant isolation, access rules, and voting constraints completed.

### 🔹 System State
* Stable.
* Governance extended layer operational.
* Community communication and decision foundations active.

### 🔹 Risks / Notes
* No legal/certified voting yet.
* No governance analytics yet.
* No frontend yet.
* Voting remains MVP/simple by design.

### 🔹 Next Step
👉 **Definir el siguiente macrofrente: Marketplace / Ecosystem foundation o Operations / Security extension**

---

## RIGOR 3.0 — Block 14 (Visitors & Access Core) ✅ COMPLETED

### 🔹 Completed Work
* Visitor registration system implemented.
* Visitor state machine implemented (`pending` → `entered` → `exited`).
* Tenant-safe visitor access enforced.
* Role-based operation (resident create, admin/guard operate).
* `TransitionVisitorStatusAction` implemented.
* `VisitorResource` implemented.
* Feature tests covering access, isolation, and transitions.

### 🔹 System State
* Stable.
* Core access/visitor flow operational.
* Ready for real-world usage in communities.

### 🔹 Risks / Notes
* No QR or automated validation yet.
* No package integration yet.
* No panic/emergency flows yet.

### 🔹 Next Step
👉 **Bloque 15 — Packages (Paquetería)**

---

## RIGOR 3.0 — Block 15 (Packages Core) ✅ COMPLETED

### 🔹 Completed Work
* Package lifecycle system implemented.
* Unit-based modeling enforced.
* Package reception and delivery flows operational.
* Strict state transitions (`received` → `delivered` / `returned`).
* Role-based operations (admin/guard).
* API endpoints implemented.
* Feature tests covering isolation, access, and transitions.

### 🔹 System State
* Stable.
* Visitors + Packages fully operational.
* Core operations layer now usable in real communities.

### 🔹 Risks / Notes
* No notifications yet (intentional).
* No OCR or automation.
* No analytics.
* No audit log integration yet (can come later).

### 🔹 Next Step
👉 **Bloque 16 — Panic Button + Notifications**

---

## RIGOR 3.0 — Block 16 (Panic Button + Notifications) ✅ COMPLETED

### 🔹 Completed Work
* Emergency event system implemented.
* Panic trigger flow operational.
* Strict state machine (`active` → `acknowledged` → `resolved`).
* Anti-duplicate logic per unit + type.
* Role-based operations (resident trigger, admin/guard handle).
* `SecurityLog` fully integrated.
* Minimal notification system implemented.
* Full API + tests.

### 🔹 System State
* Stable.
* Operational + security layer now complete.
* Platform now supports real-world emergency flows.

### 🔹 Risks / Notes
* No SMS yet (future block).
* No realtime push (future block).
* No escalation logic yet.
* No SLA/priority engine yet.

### 🔹 Next Step
👉 **Bloque 17 — Access Extensions (QR / Invitaciones)**

---

## RIGOR 3.0 — Block 17 (Access Extensions: QR / Invitations) ✅ COMPLETED

### 🔹 Completed Work
* Access invitation subsystem implemented.
* Secure single-use invitation lifecycle enforced.
* Expiration and revocation logic implemented.
* Tenant-safe invitation visibility and consumption implemented.
* Security logging integrated for creation, consumption, and revocation.
* Invitation API endpoints implemented.
* Feature tests covering isolation, ownership, expiration, and single-use constraints completed.

### 🔹 System State
* Stable.
* Visitors + Packages + Emergency + Invitations now form a real operational security layer.
* Product is increasingly ready for serious pilots.

### 🔹 Risks / Notes
* No hardware integration yet.
* No QR scanner integration yet.
* No multi-use invitation support yet.
* No visitor auto-linking/auto-creation yet.

### 🔹 Next Step (Decisión Estratégica)
👉 **Definir siguiente macrofrente: Marketplace / Ecosystem foundation o Admin UX / Operational cockpit**

---

## BLOCK 18 — Operational Dashboard Core (COMPLETED)

### Objective
Provide a unified operational cockpit endpoint aggregating critical tenant activity for Admin and Guard roles, without introducing analytics complexity or frontend dependencies.

### Endpoint
GET /api/cockpit/dashboard

Tenant-scoped via subdomain:
{community_slug}.app.sitiosurbanos.com

---

### Architecture

- Controller:
  - App\Http\Controllers\Api\Cockpit\DashboardController

- Action:
  - App\Actions\Dashboard\GetOperationalDashboardAction
  - Centralizes aggregation logic
  - Prevents controller bloat
  - Reuses existing domain logic (no duplication)

- Resource:
  - App\Http\Resources\DashboardResource
  - Ensures stable and safe response structure

---

### Role-Based Visibility

#### Admin
Receives full dashboard:
- emergencies
- visitors
- packages
- pqrs
- polls
- finance

#### Guard
Receives operational subset:
- emergencies
- visitors
- packages

#### Resident
- Access denied (403 Forbidden)

---

### Widgets Definition

#### Emergencies
- active_count
- recent_active

#### Visitors
- pending_count
- entered_count

#### Packages
- pending_pickup_count
- recent_pending

#### PQRS (Admin only)
- open_count

#### Polls (Admin only)
- active_count

#### Finance (Admin only)
- pending_invoices_count
- pending_amount
- recent_confirmed_payments_count

---

### Key Constraints

- No analytics or BI logic
- No frontend layer introduced
- No duplication of business logic
- Strict TenantScoped enforcement
- Compact, actionable data only

---

### Testing

tests/Feature/Dashboard/DashboardTest.php

Validated:
- tenant isolation
- role visibility
- correct widget counts
- resident forbidden access

---

### Status

✅ Completed  
✅ Audit-approved  
✅ Ready for cockpit evolution (Block 19)

## BLOCK 19 — Guard / Security Work Queue (COMPLETED)

### Objective
Provide a unified operational work queue for guard/admin users so they can act on pending security/operations tasks from one place.

### Endpoint
GET /api/cockpit/work-queue

Tenant-scoped via subdomain.

---

### Architecture

- Controller:
  - App\Http\Controllers\Api\Cockpit\WorkQueueController

- Action:
  - App\Actions\Cockpit\GetOperationalWorkQueueAction
  - Aggregates only actionable items
  - Reuses existing domain transitions
  - Avoids controller bloat

- Resource:
  - App\Http\Resources\WorkQueueResource

---

### Role-Based Access

#### Admin
- allowed
- sees full operational queue

#### Guard
- allowed
- sees full operational queue

#### Resident
- forbidden (403)

---

### Included Task Types

#### Visitors
- status: pending
- type: visitor_pending
- action: enter

#### Packages
- status: received
- type: package_received
- action: deliver

#### Invitations
- status: active and not expired
- type: invitation_active
- action: consume

#### Emergencies
- status: active
- type: emergency_active
- action: acknowledge

---

### Queue Limits
- top 10 items per category
- max practical payload = 40 tasks

---

### Key Constraints
- no historical/completed items
- no financial items
- no new business logic
- no frontend introduced
- strict TenantScoped isolation
- action names aligned with real domain endpoints

---

### Testing
tests/Feature/Cockpit/WorkQueueTest.php

Validated:
- resident forbidden access
- admin/guard allowed
- actionable items only
- correct type/action mapping
- tenant isolation

---

### Status
✅ Completed
✅ Audit-approved
✅ Ready for admin work queue evolution

## BLOCK 20 — Admin Work Queue (COMPLETED)

### Objective
Provide an admin-only operational work queue to centralize actionable administrative tasks in one place.

### Endpoint
GET /api/cockpit/admin-work-queue

Tenant-scoped via subdomain.

---

### Architecture

- Controller:
  - App\Http\Controllers\Api\Cockpit\AdminWorkQueueController

- Action:
  - App\Actions\Cockpit\GetAdminWorkQueueAction
  - Aggregates admin-only actionable tasks
  - Reuses existing module states and constraints

- Resource:
  - App\Http\Resources\AdminWorkQueueResource

---

### Role-Based Access

#### Admin
- allowed
- sees full admin queue

#### Guard
- forbidden (403)

#### Resident
- forbidden (403)

---

### Included Task Types

#### PQRS
- statuses: open, in_progress
- type: pqrs_open
- action: respond

#### Polls
- status: open
- type: poll_active
- action: review

#### Invoices
- status: pending
- type: invoice_pending
- action: review

#### Announcements
- active/current
- type: announcement_active
- action: view

---

### Queue Limits
- top 10 items per type

---

### Key Constraints
- admin-only access
- no analytics
- no duplication of domain logic
- strict tenant isolation
- no overlap with guard/security queue

---

### Testing
tests/Feature/Cockpit/AdminWorkQueueTest.php

Validated:
- admin access allowed
- guard forbidden
- resident forbidden
- correct task filtering
- tenant isolation

---

### Status
✅ Completed
✅ Audit-approved
✅ Ready for cockpit shell / navigation hardening

## BLOCK 21 — UI Shell / Navigation Hardening (COMPLETED)

### Objective
Provide a minimal but functional cockpit shell so Admin and Guard users can navigate and consume cockpit modules coherently.

### Web Routes
- /cockpit
- /cockpit/work-queue
- /cockpit/admin-work-queue

Tenant runtime:
{community_slug}.app.sitiosurbanos.com

---

### Architecture

- Controller:
  - App\Http\Controllers\Tenant\CockpitController

- Layout / Shell:
  - Cockpit shell integrated into the existing tenant runtime frontend
  - role-aware sidebar
  - working topbar/account menu
  - working logout flow

- Helper:
  - resources/js/lib/useApiData.ts
  - reusable frontend fetch helper for cockpit pages

- Pages:
  - Cockpit/Dashboard.vue
  - Cockpit/WorkQueue.vue
  - Cockpit/AdminWorkQueue.vue

---

### Role-Based Visibility

#### Admin
- Dashboard
- Work Queue
- Admin Work Queue

#### Guard
- Dashboard
- Work Queue

#### Resident
- forbidden / out of scope

---

### Key Fixes Included
- cockpit API data loads correctly in tenant runtime
- login/logout now reflect state correctly without manual refresh
- top-right account dropdown restored
- blank-screen issues in Units and Residents resolved
- cockpit rendering loops corrected (no string character iteration bug)

---

### UX Status
- Functionally operational
- Visually minimal / intentionally basic
- Ready for future cockpit polish iterations

---

### Testing
tests/Feature/Tenant/CockpitAccessTest.php

Validated:
- admin route access
- guard route restrictions
- resident forbidden access

Manual validation also confirmed:
- login
- logout
- dashboard rendering
- queue rendering

---

### Status
✅ Completed
✅ Functionally validated
✅ Ready for future cockpit polish and module expansion

## BLOCK 22 — Guard UX / Operational Actions (COMPLETED)

### Objective
Turn the guard work queue into a real operational UI so guard/admin users can execute existing backend transitions directly from the cockpit.

### Scope
This block focused on operational actions inside the existing cockpit work queue, without introducing new backend business logic or new domain features.

### Frontend Area
- `resources/js/Pages/Cockpit/WorkQueue.vue`

### What Changed
The previous raw/diagnostic task visualization was replaced with an actionable operational interface using direct action buttons per task type.

### Supported Queue Actions

#### Visitors
- task type: `visitor_pending`
- UI action: `Registrar entrada`
- backend endpoint:
  - `PATCH /api/security/visitors/{id}/enter`

#### Packages
- task type: `package_received`
- UI action: `Marcar entregado`
- backend endpoint:
  - `PATCH /api/security/packages/{id}/deliver`

#### Invitations
- task type: `invitation_active`
- UI action: `Consumir acceso`
- backend endpoint:
  - `PATCH /api/security/invitations/{id}/consume`

#### Emergencies
- task type: `emergency_active`
- UI action: `Atender`
- backend endpoint:
  - `PATCH /api/security/emergencies/{id}/ack`

### UX Rules Applied
- per-item loading state
- direct operational actions
- no heavy confirmation friction
- queue refresh after successful action
- minimal error feedback
- no duplicated business logic in Vue

### Refresh Strategy
After a successful action, the queue is refreshed using the existing `useApiData('/api/cockpit/work-queue')` flow via `await refetch()` so the backend remains the single source of truth.

### Error Handling
- uses backend error message when available
- safe fallback message:
  - `Error al ejecutar la acción`

### Architecture Notes
- no new backend transitions were created
- existing approved backend endpoints/actions were reused
- no domain logic was duplicated in frontend
- no new feature scope was introduced

### Validation Status
- action mapping validated
- role scope preserved
- queue remains tenant-safe through existing backend architecture
- UI now supports real guard-side operational actions

### Status
✅ Completed  
✅ Audit-approved  
✅ Ready to support resident-facing cockpit evolution next

## BLOCK 23 — Resident Cockpit Core (COMPLETED)

### Objective
Provide a resident-only cockpit so residents can access their most relevant information from one place without using the admin/guard operational cockpit.

### API Endpoint
- `GET /api/cockpit/resident`

### Web Route
- `/cockpit/resident`

Tenant runtime:
`{community_slug}.app.sitiosurbanos.com`

---

### Architecture

- Controller:
  - `App\Http\Controllers\Api\Cockpit\ResidentCockpitController`
  - resident-only access
  - admin/guard forbidden

- Action:
  - `App\Actions\Cockpit\GetResidentCockpitAction`
  - aggregates only resident-relevant data
  - scopes data to the authenticated user’s active unit(s) in the current tenant

- Frontend Page:
  - `resources/js/Pages/Cockpit/ResidentCockpit.vue`

- Navigation:
  - resident sees `Cabina del Residente`
  - admin/guard do not use this cockpit as their primary cockpit

---

### Included Widgets

#### Finance
- pending invoices count
- pending amount
- compact financial summary

#### Packages
- received / pending pickup packages only
- compact recent list

#### Invitations
- active, non-expired invitations only
- excludes used / revoked / expired

#### PQRS
- open / in-progress resident cases
- compact recent list

#### Visitors
- compact resident-facing visitor summary
- intended to reflect operational states (pending / entered)
- note: copy/labeling may require future polish if wording still references "expected"

---

### Key Constraints
- resident-only cockpit
- no admin/guard access
- no emergency widget in this block
- no analytics
- no duplicated backend business logic
- strict tenant isolation
- strict active-unit scoping

---

### Testing
- `tests/Feature/Cockpit/ResidentCockpitApiTest.php`
- `tests/Feature/Cockpit/ResidentCockpitWebTest.php`

Validated:
- resident allowed
- admin forbidden
- guard forbidden
- tenant isolation
- only active resident unit data included

---

### Validation Status
- resident cockpit renders correctly
- Vite/Inertia resolution fixed
- resident shell is functional
- backend contracts are consumed safely
- ready for future resident UX expansion

---

### Notes
- one minor follow-up polish item remains:
  - keep visitor widget wording fully aligned with operational states (`pending` / `entered`) rather than “expected” wording if still present in UI copy

### Status
✅ Completed  
✅ Audit-approved  
✅ Functionally validated

## BLOCK 24 — Resident Payments UX (COMPLETED)

### Objective
Enable residents to view their pending invoices and initiate payments directly from the cockpit using existing backend financial flows.

---

### Implementation

#### Frontend
- Updated:
  - `resources/js/Pages/Cockpit/ResidentCockpit.vue`

- Added:
  - "Tus Facturas" section inside resident cockpit
  - list of pending invoices (from `finance.recent_invoices`)

Each invoice shows:
- amount
- due date
- unit reference
- description

---

### Actions

- Added "Pagar" button per invoice
- Executes:
  `POST /api/finance/invoices/{invoice}/pay`

- Uses existing backend logic:
  - `InvoicePaymentController`
  - `CreatePaymentAttemptAction`

---

### UX Behavior

- Loading state per invoice:
  `processingPayment === invoice.id`

- Error handling:
  - uses Laravel response message
  - fallback message: "Error al iniciar el pago"

- No frontend financial calculations implemented

---

### Testing

Updated:
- `tests/Feature/Cockpit/ResidentCockpitApiTest.php`

Validations:
- only invoices from resident active units returned
- tenant isolation preserved

Also validated:
- `InvoicePaymentTest` suite (9 tests, 28 assertions)

---

### Architecture Compliance

- No new backend logic introduced
- No financial calculations in frontend
- All actions delegated to backend
- Strict tenant and unit scoping respected

---

### Notes

- Payment feedback is minimal (MVP-compliant)
- Recommended future improvement:
  - refresh invoice list after payment attempt
  - display updated payment state

---

### Status

✅ Completed  
✅ Audit-approved  
✅ Production-safe MVP  

## BLOCK 25 — Resident PQRS UX

### Summary
Implemented the resident-facing PQRS UX allowing users to create and manage their requests (petitions, complaints, claims, suggestions) through a dedicated cockpit page.

### Key Features
- Dedicated route: `/cockpit/resident/pqrs`
- Resident-only access enforced at controller level
- Full reuse of existing PQRS API endpoints
- List view with:
  - subject
  - type
  - status
  - created_at
  - anonymity flag
- Inline expansion for:
  - description
  - admin response
- Creation form with:
  - type
  - subject
  - description
  - is_anonymous
- UX feedback:
  - loading state
  - success message
  - automatic list refresh
  - form reset after creation

### Architecture Notes
- No backend logic duplication
- UI strictly consumes `/api/governance/pqrs`
- Anonymity respected and displayed clearly
- Spanish-first UX

### Access Control
- Resident: full access
- Guard/Admin: 403 Forbidden

### Testing
- Web access tests for resident-only route
- Existing PQRS feature tests validated no regression
- Tenant isolation preserved

### Status
✅ Completed and ready for extension

## BLOCK 26 — Resident Operational Actions

### Summary
Implemented the resident operational cockpit allowing users to manage invitations, register visitors, and view packages through a dedicated page.

### Key Features
- Route: `/cockpit/resident/operations`
- Resident-only access (Admin/Guard forbidden)
- Sections:
  - Invitations (create + revoke)
  - Visitors (create + list)
  - Packages (view only)

### Invitations
- Creation using existing API payload (no new fields introduced)
- Revocation of active invitations
- Default type set to `manual_code`
- No QR flow introduced

### Visitors
- Create visitor flow
- List of visitors with operational status (pending, entered, exited)

### Packages
- Resident visibility only
- No operational actions added in this block

### Architecture Notes
- Strict reuse of existing backend APIs
- No duplication of business logic
- Tenant and unit scoping handled by backend
- Active units injected via controller

### Access Control
- Resident: allowed
- Guard/Admin: forbidden (403)

### Testing
- ResidentOperationsTest:
  - resident access allowed
  - admin forbidden
  - guard forbidden

### Status
✅ Completed and aligned with RIGOR 3.0 constraints

## BLOCK 27 — Unified Notifications Layer

### Status
✅ Completed

### Description
Implemented a unified notification system using Laravel database notifications. All core domain events now dispatch structured notifications scoped by tenant.

### Key Features
- Standardized payload with:
  - community_id
  - type (specific)
  - title, message
  - entity_id, entity_type
- API:
  - GET /api/notifications
  - PATCH /api/notifications/{id}/read
  - PATCH /api/notifications/read-all
- UI:
  - Notification dropdown in Topbar
  - Unread badge
  - Mark-as-read interaction

### Tenant Isolation
- Enforced via:
  where('data->community_id', TenantContext::id())

### Hooks Implemented
- Package received
- Invitation consumed
- Visitor registered
- PQRS created/updated
- Payment confirmed/failed
- Emergency triggered

### Constraints
- No websockets
- No polling
- No backend logic duplication

### Ready for
➡️ Real-time layer (future)
➡️ Activity timeline (Block 28)

## BLOCK 28 — Activity Timeline / Audit UX

### Status
✅ Completed

### Description
Implemented a unified activity timeline by aggregating existing SecurityLog and Notification data without introducing new persistence layers.

### Role Scoping
- Admin/Guard:
  - Tenant-wide operational timeline (SecurityLog)
- Resident:
  - Personal timeline (Notifications only)

### Endpoint
GET /api/cockpit/activity

### Payload
- source (security_log | notification)
- type (snake_case event)
- title
- message
- created_at
- entity_id (optional)
- entity_type (optional)

### Constraints
- No new timeline table
- No duplication of audit logic
- No realtime/polling
- Strict tenant isolation

### UI
- Minimal chronological timeline
- Spanish-first labels
- Empty state supported

### Ready for
➡️ Realtime layer (future)
➡️ Advanced filters/search
➡️ Audit exports

## BLOCK 29 — Realtime Layer Foundations (COMPLETED)

### Summary
Implemented a minimal, tenant-isolated realtime layer using Laravel Reverb and Echo. The system uses a signal-based approach to notify frontend surfaces (notifications, work queues, activity timeline) and trigger safe data refetching via existing API endpoints.

### Key Decisions
- Realtime is used strictly as a signal mechanism (no business logic in frontend)
- Two private channels:
  - User channel for notifications
  - Tenant channel for admin/guard operational signals
- Residents are strictly excluded from tenant-level channels
- Debounce strategy (300ms) prevents excessive refetching
- Full fallback behavior when realtime is unavailable

### Integrated Surfaces
- Notification dropdown (user channel)
- Work Queue (tenant channel)
- Admin Work Queue (tenant channel)
- Activity Timeline (tenant channel)

### Architecture Notes
- No payload-based rendering from broadcast events
- Frontend relies exclusively on axios refetch for state consistency
- Strict tenant isolation enforced at channel authorization level

### Status
BLOCK 29 fully completed and production-ready.

## BLOCK 30 — P2P Ecosystem Backend Core (COMPLETED)

### Objective
Implement the backend core of the tenant-scoped P2P ecosystem (listings/classifieds) with strict ownership, moderation, and privacy rules.

### Domain
- Listing / Classifieds
- Tenant-scoped by `community_id`
- Operational ownership via `resident_id`

### Implemented Components

#### Model & Schema
- `Listing` model created
- `TenantScoped` applied
- migration includes:
  - `community_id`
  - `resident_id`
  - `title`
  - `description`
  - `price`
  - `category`
  - `status`
  - `show_contact_info`

#### Enums
- `ListingCategory`
  - items
  - services
  - real_estate
- `ListingStatus`
  - active
  - paused
  - reported
  - removed

#### Actions
- `CreateListingAction`
  - forces authenticated active resident ownership
  - ignores malicious `resident_id` payload
  - starts with `active` status
- `UpdateListingAction`
  - resident can update only own listings
- `ModerateListingAction`
  - admin-only status moderation
  - does not edit owner content as if it were theirs

#### API
- thin `ListingController`
- validation requests integrated
- ecosystem routes exposed under tenant-safe API scope

#### Resource
- `ListingResource`
- hides contact info when `show_contact_info = false`
- does not expose unnecessary resident PII

### Access Rules
- Resident:
  - can create
  - can update own listings
- Admin:
  - can moderate status (`reported`, `removed`)
- Guard:
  - forbidden from listings management in this block

### Testing
`ListingTest.php` validates:
- tenant isolation
- resident ownership
- spoofed `resident_id` ignored
- privacy flag behavior
- guard forbidden
- admin moderation behavior

### Status
✅ Completed  
✅ Audit-approved  
✅ Ready for resident-facing P2P UX

## BLOCK 31 — Resident P2P UX (Ecosystem)

### Status
✅ Completed

### Description
Implemented the frontend experience for the P2P Ecosystem inside the Resident Cockpit, allowing residents to explore community listings and manage their own classifieds.

### Key Features
- **Route:** `/cockpit/resident/ecosystem` (Resident-only).
- **Tabs/Sections:** - `Explorar Comunidad`: Views all active listings in the tenant.
  - `Mis Anuncios`: Manages the resident's own listings (Create, Edit, Pause).
- **Privacy Enforcement:** Contact information (email/phone) is gracefully hidden with a fallback message ("Contacto vía administración") if the user opted out (`show_contact_info = false`).
- **Data Fetching:** Relies strictly on existing APIs (`/api/ecosystem/listings`) using `axios` and a `refetch` pattern to avoid duplicating state logic in Vue.

### Hotfixes Applied (PostgreSQL Stabilization)
- Migrated the `notifications` table `data` column from `text` to `jsonb` using a raw DB statement to support strict JSON querying (`->>`).
- Patched `ListingController` to explicitly scope Resident resolution by `community_id` to prevent cross-tenant multi-role bugs.
- Fixed PHP 8.1 Enum casting in query builders (added `->value`).

### Architecture Constraints Maintained
- Zero business logic duplicated in Vue.
- Strict tenant isolation at the API and routing levels.
- Admin/Guard strictly forbidden from the resident ecosystem view (403).

## BLOCK 31 — Resident P2P UX (Marketplace Core) Mejorado

### Status
✅ Completed

### Description
Implemented the frontend experience for the P2P Ecosystem inside the Resident Cockpit, allowing residents to explore community classifieds and manage their own listings in a unified, single-page layout.

### Key Features
- **Route:** `/cockpit/resident/ecosystem` (Resident-only).
- **Navigation:** Added "Clasificados" to the resident sidebar.
- **Layout (Single Page):** - `Explorar anuncios`: Views all active listings in the tenant. If the resident sees their own listing here, it is badged as "Tu anuncio".
  - `Mis anuncios`: Manages the resident's own listings (Create, Edit, Pause/Reactivate).
- **Empty States:** Clear Spanish fallbacks when no listings exist ("Aún no hay anuncios...", "Aún no has publicado...").
- **Privacy Enforcement:** Contact information is gracefully hidden with a fallback message ("Contacto: vía administración") if the user opted out (`show_contact_info = false`).
- **Data Fetching:** Relies strictly on `axios` and a `refetch()` pattern for mutations without duplicating state logic in Vue.

### Architecture Constraints Maintained
- Zero business logic duplicated in Vue.
- Strict tenant isolation at the API and routing levels.
- Admin/Guard strictly forbidden from the resident ecosystem view (403).
- Cross-tenant or cross-resident edits strictly blocked and tested (403).

## BLOCK 32 — Ecosystem Admin Moderation UX

### Status
✅ Completed

### Description
Integrated P2P listing moderation directly into the existing Admin Work Queue, allowing administrators to review and hide active listings violating community rules without needing a separate dashboard.

### Key Features
- **Backend Aggregation:** `GetAdminWorkQueueAction` updated to fetch recent `active` listings (Limit: 10).
- **Task Mapping:** Listings surface with `type: 'listing_active'` and `action: 'moderate'`.
- **Frontend Action:** Added the "Ocultar anuncio" button to `AdminWorkQueue.vue`, which triggers `PATCH /api/ecosystem/listings/{id}/moderate` with `{ status: 'removed' }`.
- **State Management:** Implemented individual loading states ("Procesando...") and triggers `debouncedRefetch()` to dynamically clear the moderated item from the UI.

### Architecture Constraints Maintained
- No new frontend business logic; strictly consumes the endpoint built in Block 30.
- Preserved strict tenant isolation within the Admin Work Queue.
- Maintained CI/CD stability by wrapping PostgreSQL-specific migration statements to prevent SQLite memory test failures.

## BLOCK 33 — Providers Ecosystem Backend Core

### Status
✅ Completed

### Description
Implemented the backend core for the Professional Services Directory (Providers Ecosystem), allowing the administration to manage a trusted list of external service providers (plumbers, electricians, etc.) for the community.

### Implemented Components
- **Model & Schema:** `Provider` model with `TenantScoped` and `SoftDeletes`. Uses PostgreSQL `jsonb` for `contact_details`.
- **Enums:** `ProviderStatus` (active, inactive) and `ProviderCategory` (plumbing, electrical, cleaning, maintenance, others).
- **Actions:** `RegisterProviderAction`, `UpdateProviderAction`, `DeleteProviderAction` (executes soft delete).
- **Validation:** `StoreProviderRequest` and `UpdateProviderRequest` strictly enforce the `contact_details` array structure (`type` and `value`).
- **Access Control (`ProviderPolicy`):** - Admin: Full CRUD.
  - Resident & Guard: Read-only, restricted to `active` providers.
- **Routing:** Manual resolution of `$providerId` in `ProviderController` to bypass `SubstituteBindings` race conditions against the `TenantMiddleware`, ensuring bulletproof tenant isolation.

### Architecture Constraints Maintained
- Zero cross-tenant data leakage (verified via Pest tests).
- Residents strictly blocked from mutating provider data.
- Historical data preserved via logical deletion (`softDeletes`).

### Block 33.5 — Service Requests Extension
- **Model:** `ProviderServiceRequest` created with `TenantScoped` to allow residents to request jobs from active providers.
- **Enums:** `ServiceRequestStatus` (pending, accepted, completed, cancelled) and `ServiceUrgency`.
- **Security:** Addressed payload spoofing by forcing the backend to infer the `resident_id` directly from the authenticated session context (`$request->user()->currentResident`).
- **Access Control:** Admins have global read access for auditing. Residents have full CRUD but strictly scoped to their own requests. Guards are explicitly blocked.

### Block 34 — Providers Ecosystem UX (Frontend)
- **Admin Interface:** Created `/cockpit/admin/providers`. Implemented a robust data table and a Slide-over/Modal for creating and editing providers.
- **Dynamic Forms:** Successfully implemented reactive arrays for `contact_details` (allowing multiple phones/emails) mapping correctly to the backend JSONB column.
- **Strict Validation UI:** Forced the UI to map and render Inertia 422 validation errors directly under the corresponding inputs, specifically catching the `status` requirement.
- **Resident Interface:** Created `/cockpit/resident/providers`. Implemented a read-only masonry grid layout with inline category filters.
- **Service Request Modal:** Implemented the "Solicitar Servicio" modal in the Resident view, successfully hitting the Block 33.5 backend.
- **Tenant Isolation Verified:** Manually validated that providers created in one community (`test-community`) are strictly invisible to residents of another community.

## BLOCK 35 — 360 Census & Granular Resident Architecture

### Status
✅ Completed

### Description
A deep refactoring of the resident and unit entities to establish strict Role-Binding. A resident is not simply a user; they represent a specific legal and operational link to a unit. This block builds the foundation for complex community demographics.

**Scope & Execution Details:**
* **Owner (Propietario):** Grants access to financial dashboards, assembly voting with property coefficients, and the ability to authorize tenants.
* **Tenant (Inquilino):** Enforces a strict database and application rule: Only 1 active Tenant per unit at a time. Grants access to the operational dashboard (QR access, PQRS, Packages).
* **Co-Resident/Dependent:** Allows family members or roommates to exist in the census to receive packages and gate access, strictly isolated from voting and financial data.

### [Completed] Block 35: 360 Census & Granular Resident Architecture
* **Objective:** Implement strict Role-Binding per unit (Owner, Tenant, Dependent) and enforce the "1 Active Tenant" business rule.
* **Execution details:**
  * Created `ResidentType` Enum and applied strict casting/scopes in the `Resident` model.
  * Engineered `ResidentOnboardingService` to handle transitions using DB transactions.
  * Implemented automated deactivation cascade: Onboarding a new Tenant automatically deactivates the previous Tenant and their Dependents, preserving forensic data (No soft-deletes) while enforcing the 1-active-tenant rule.
  * Patched Mass Assignment and Multi-Tenant (`community_id`) constraints at the service layer.

## BLOCK 35.5 — Dynamic Taxonomies & UI CRUD Refactoring (Frontend Debt Rescue)
**Phase:** Core Infrastructure (Emergent)
**Objective:** Restore and stabilize the inherited V1 Vue frontend to seamlessly connect with the new semantic routing and multi-tenant constraints implemented in previous blocks.

**Key Deliverables:**
1. **Ziggy Routing Fix:** Resolve the `ERR_CERT_COMMON_NAME_INVALID` SSL error by ensuring Inertia/Ziggy correctly targets the dynamic tenant subdomain (`{slug}.app.sitios-urbanos.test`) during POST/PUT operations, bypassing the incorrect global `core` domain fallback.
2. **Taxonomy Integration:** Eradicate hardcoded select options in Vue components (e.g., Property Types). Integrate the `system_taxonomies` database table to drive these dropdowns dynamically via Inertia shared props or dedicated backend controllers.
3. **CRUD UI Restoration:** Update `Units/Index.vue`, `Units/Form.vue`, `Residents/Index.vue`, and `Residents/Form.vue`. Strip out legacy V1 permission gates (`v-if="can(...)"`) that are incorrectly hiding action buttons, and wire the forms to the new semantic endpoints (`tenant.admin.core.*`).

## BLOCK 36.0 — Topological Matrix & Infrastructure Generator
**Phase:** Core Infrastructure (Onboarding)
**Objective:** Replace manual, one-by-one unit creation with an algorithmic matrix generator to map the physical layout of a community, preventing security vulnerabilities like "phantom units".
**Key Deliverables:**
1. **Topological Algorithm:** A backend service that accepts matrix parameters (e.g., 3 Towers, 10 Floors, 4 Units per Floor, plus nomenclature rules) and generates the entire `units` database structure in a single database transaction.
2. **Amenity Inheritance:** Ability to assign structural JSONB amenities during matrix creation (e.g., "All 1st-floor units include a patio").
3. **Soft-Delete Enforcement:** Units cannot be hard-deleted to preserve forensic audit logs. Introduce `SoftDeletes` for structural adjustments (e.g., merging two apartments).

## BLOCK 36.1 — Asynchronous Bulk Import Engine (ETL)
**Phase:** Core Infrastructure (Data Population)
**Objective:** A queue-based ETL (Extract, Transform, Load) pipeline to populate the Topological Matrix with human and financial data via CSV uploads, preventing server timeouts.
**Key Deliverables:**
1. **Job Queues:** Implement `ResidentImportJob` and `FinancialBalanceImportJob` using Laravel Horizon/Redis.
2. **Validation Layer:** Strict CSV validation targeting the existing matrix (e.g., rejecting a resident if the CSV maps them to an uncreated/phantom unit).
3. **Progress UI:** Real-time upload and processing feedback for the administrator (e.g., "Importing 400 residents... 80% complete").

## BLOCK 36.2 — Global UX/UI Standardization (Modal-First Architecture)
**Phase:** Technical & Cognitive Debt Rescue
**Objective:** Eliminate cognitive friction and UI inconsistencies inherited from V1, applying CRO/Neuromarketing principles.
**Key Deliverables:**
1. **Action Modals:** Refactor all primary CRUD creation and editing forms (Units, Residents, Providers) into central screen Modals.
2. **SlideOver Scoping:** Restrict SlideOvers strictly to "View/Read-Only" complex data profiles.
3. **Server-Side DataTable Engine (`Units/Index.vue`):** Implement built-in sorting, text search (Identifier), property_type filters, and dynamic pagination (`per_page` array: 25, 50, 100) driven natively by Laravel Eloquent and Inertia dynamic requests.
4. **Topological Matrix Sector Scoping (`VisualMatrix.vue`):** Prevent matrix layout fragmentation by implementing standalone Block/Sector view filters instead of standard table pagination.
5. **Dynamic Toast Notification System:** Replace all browser-native `alert()` commands with an elegant, un-intrusive Toast component (Success/Error/Warning) with auto-dismiss logic.

## BLOCK 37 — Identity, Access & Onboarding Engine (RBAC)

### Status
⏳ Pending

### Description
The bridge between raw data and real users. This block brings the records imported in the ETL to life, establishing the authentication system, strict role assignment, and credential dispatch so Administrators and Residents can access their respective Control Planes.

**Scope & Execution Details:**
*   **Access Management (Internal RBAC):** Granular profile creation for the Tenant Admin. Limited permissions mapping for Sub-administrators, Accountants, and Auditors.
*   **Asynchronous Invitation Flow:** A system to trigger emails with temporary, cryptographically signed links to residents imported in Block 36.1, allowing them to securely set their passwords.
*   **Resident Base Dashboard (Cockpit):** The main view upon resident login, integrating the Dynamic QR Code Generator (large TOTP on screen) for immediate access, and the urgent notifications panel.

## BLOCK 38 — Extended Operational Module & Support (PQRS)

### Status
⏳ Pending

### Description
Before processing payments, the community must be able to communicate and update its data. This block closes the Administrator's Control Plane in terms of customer service and census, while enabling basic self-management for the Resident.

**Scope & Execution Details:**
*   **Census Self-Management (My Unit):** Interface for residents to register/update their family group (co-residents), vehicles (license plates for gatehouse cross-referencing), and pets.
*   **PQRS System (Helpdesk):** Administrator inbox with status traceability (Open, In Progress, Resolved). A simple form in the resident's Cockpit to raise tickets.
*   **Mass Communications:** Digital bulletin board and broadcasting tool for the Administrator, capable of queuing messages via Email, SMS, or in-app push notifications.

## BLOCK 39 — Core Financial Ledger

### Status
⏳ Pending

### Description
The fundamental accounting engine. Operating prior to payment gateways, this block establishes the mathematical truth of the property: who owes what, due dates, and audit trails.

**Scope & Execution Details:**
*   **Recurring Billing (Cron Jobs):** Automated engine that evaluates the coefficient of each unit and mass-generates invoices (HOA fees/Administration) on the 1st of every month.
*   **Balance & Accounting Notes Management:** Interface for the administrator to load initial balances, apply credit notes (surpluses), or debit notes (e.g., fines).
*   **Resident Financial Plane:** Deployment of the "Current Account Status" widget ($0 or Pending Balance) and the history view to download dynamically generated Invoices and Clearance Certificates (Paz y Salvos) in PDF format.

## BLOCK 40 — Payment Gateway & Split Engine (ePayco)

### Status
⏳ Pending

### Description
The connection of the Ledger with real money and the primary monetization engine of the platform. It implements real-time financial orchestration using the aggregator model.

**Scope & Execution Details:**
*   **Quick Pay Button:** Integration of the ePayco checkout directly into the resident's Cockpit.
*   **Real-Time Split Engine:** Mathematical logic to intercept the payment, calculate gateway costs, extract the Sitios Urbanos "Take-Rate" (Commission), and disburse the net funds to the community's bank account.
*   **Automated Reconciliation:** Secure webhooks listening to ePayco to instantly mark Block 39 invoices as "Paid," eliminating human intervention.

## BLOCK 41 — Dunning & Soft-Collection Strategy

### Status
⏳ Pending

### Description
Accounts receivable automation. Replaces manual collection with a financial flow focused on retention and user experience, protecting the building's cash flow.

**Scope & Execution Details:**
*   **Interest & Reminder Engine:** Automated application of late fees based on Tenant policies, alongside empathetic notifications (Days 1-3) regarding failed or pending payments.
*   **Automated Service Degradation:** Gradual locking of non-essential services (e.g., amenity reservations, voting) for units in default, while always keeping the payment portal accessible.
*   **Collections Dashboard (Admin):** Managerial board to visualize global delinquency, manage grace periods, and export reports for legal collection.

## BLOCK 42 — Security Control Plane (Logbook & Cryptographic Scanner)

### Status
⏳ Pending

### Description
The operational boundary of the community. Equips security personnel with fast, error-proof tools to control who enters and exits the ecosystem.

**Scope & Execution Details:**
*   **Access Scanner (PWA):** Ultra-fast mobile interface for the guard. Camera reader that validates Block 37 QR codes (TOTP) in milliseconds, mutating the state to "Consumed" to prevent replay attacks.
*   **Digital Logbook & Parcels:** High-speed form to register manual visitors (ID, Name, Destination Unit) and package reception (triggering a Toast/Push to the resident).
*   **VIP Pre-authorization:** View for the guard to quickly validate guests pre-approved by the resident in their self-management module.

## BLOCK 43 — Forensic Audit & Hardware Security

### Status
⏳ Pending

### Description
The legal shield of the platform. Guarantees that all security actions are irrefutable and that gatehouse accounts cannot be compromised externally.

**Scope & Execution Details:**
*   **Forensic Log (Incidents):** Immutable digital book for shift changes and incident reporting, with the capability to attach photographic evidence (stored in S3).
*   **Hardware Binding (Device Tokens):** Implementation of asymmetric validation for the `guard` role. Ensures operational sessions can only be opened from authorized physical devices (gatehouse tablets), blocking remote access.

## BLOCK 44 — Governance (Voting & Document Management)

### Status
⏳ Pending

### Description
Digitization of the community's legal bureaucracy, streamlining assemblies and access to official information.

**Scope & Execution Details:**
*   **Weighted Voting Engine:** Creation of official ballots. Real-time quorum validation and mathematical result calculation based strictly on the unit's *coefficient* (from Block 36 Matrix).
*   **Voting Booth (Resident):** Secure, locked interface to cast votes during virtual assemblies.
*   **Document Repository:** Organized cloud storage for the Administrator to upload Minutes, Bylaws, and Balance Sheets, accessible 24/7 by the Resident.

## BLOCK 45 — Resident P2P Ecosystem (Classifieds & Bookings)

### Status
⏳ Pending

### Description
Modules designed to maximize retention, daily app usage, and the circular economy among neighbors.

**Scope & Execution Details:**
*   **Amenity Bookings (Dynamic Allocation):** Interactive calendar for common areas (BBQ, Social Room). Includes financial validation (access gate if in default) and definition of billable rental rates.
*   **P2P Classifieds Wall:** UI to browse, create, and pause neighborhood listings.
*   **Privacy & Moderation Engine:** Masking of contact data based on resident preferences, and a reporting panel for the Administrator to take down inappropriate posts.

## BLOCK 46 — B2B2C Marketplace & Provider Directory

### Status
⏳ Pending

### Description
The external commercial layer and the second monetization pillar of the SaaS. Connects local businesses with the purchasing power of the community.

**Scope & Execution Details:**
*   **Approval & Directory (Lead Gen):** Administrator filter to validate providers (plumbers, ISPs). Resident interface to explore services and view ratings.
*   **Transactional Marketplace:** Virtual storefronts for local businesses (Minimarkets, Pharmacies) with an integrated shopping cart.
*   **Commercial Micro-Split:** Integration of these purchases with the Block 40 engine to extract a direct transactional Take-Rate for Sitios Urbanos on every order.

## BLOCK 47 — Artificial Intelligence (Legal RAG)

### Status
⏳ Pending

### Description
Extreme reduction of administrative burden through a generative chatbot confined to the specific legal documents of each building.

**Scope & Execution Details:**
*   **Document Ingestion (Embeddings):** Processing of the Community Rulebooks (from Block 44) into a vector database.
*   **Resident Chatbot:** Widget in the Cockpit to answer operational and coexistence questions.
*   **Strict Context Framing:** Guardrails to prevent hallucinations, ensuring the AI only responds based on local regulations and cites the exact source.

## BLOCK 48 — SuperAdmin Control Plane (The Business / /system)

### Status
⏳ Pending

### Description
The global dashboard for Orcomtek. Now that the entire system operates, this block allows governing, monetizing, and scaling the Multi-Tenant architecture.

**Scope & Execution Details:**
*   **Master Dashboard & Audit:** Global metrics (MRR, transacted GMV, Churn Rate). Forensic logs of server status.
*   **SaaS Tiers & Subscriptions:** Creation of billing plans (Base, Pro, Elite) that lock or enable specific modules (e.g., Marketplace or RAG) for each community.
*   **Tenant Feature Overrides:** Commercial negotiation module to configure exceptions (e.g., custom gateway commission % or extra free SMS) per building. Emergency suspension button for SaaS non-payment.

## BLOCK 49 — Master UI/UX, Omnichannel & Demo Data Pack

### Status
⏳ Pending

### Description
The final polish. Transforms the MVP into an Enterprise product ready for corporate sales, high-impact demonstrations, and production deployment.

**Scope & Execution Details:**
*   **Total Visual Refinement:** Audit of "Quiet Luxury", Bento Grids, micro-interactions, and Empty States across all control planes.
*   **Asynchronous Omnichannel Engine:** Final activation of Redis Workers to dispatch SMS (AWS SNS), mass emails, and Push Notifications under *Fair Use* rules.
*   **Idempotent Seeders (Demo Pack):** Suite for generating fake data that creates complete communities (units, debts, residents, histories, and votes) in seconds for QA testing and flawless sales demos.
*   **Production Hardening:** Strict configuration of CORS, API Rate Limiting, automated database backups, and concurrency tuning in Laravel Reverb.