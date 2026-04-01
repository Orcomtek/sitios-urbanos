# PROJECT CONTINUITY HANDOFF — Sitios Urbanos

**Document Purpose:** Technical continuity handoff for future chats, agents, and implementation sessions.  
**Project:** Sitios Urbanos  
**Project Type:** Multi-tenant SaaS  
**Domain:** Property Management / Propiedad Horizontal  
**Primary Region:** Colombia and LATAM  
**Methodology Status:** RIGOR mandatory and active  
**Architecture Status:** Foundation completed, Multi-Tenancy Core planned and frozen  

---

## Execution Model

This project uses a tripartite execution model:

- Camilo Alcalá = Project Owner / Approver
- ChatGPT = Architect / Planner / Auditor under RIGOR
- Google Antigravity = Code execution agent

Rule:
ChatGPT must not assume it is the direct coding executor when working under this project continuity model.
Its role is to:
- plan
- review
- freeze decisions
- audit implementation plans
- validate outputs produced by Antigravity

## 1. Executive Overview

Sitios Urbanos is a **multi-tenant SaaS platform** designed for property management (**Propiedad Horizontal**) in Colombia and LATAM.

The project is being developed under a **strict architecture-first model**, meaning architectural correctness, isolation, and long-term maintainability take priority over implementation speed.

The platform must evolve under a controlled technical process that protects the following core principles:

- Strict tenant isolation
- Backend authority over security-sensitive decisions
- Fail-safe behavior at all tenant boundaries
- Clean separation of concerns
- Predictable long-term scalability
- Controlled scope execution through phased delivery

This document is the **authoritative continuity baseline** for resuming work in future conversations. Frozen decisions documented here must be treated as binding unless explicitly changed by the project owner.

---

## 2. Locked Technology Stack

The following technical stack is **locked** and must be treated as the approved engineering baseline for the project:

- **Backend:** Laravel 13
- **Language:** PHP 8.4+
- **Frontend:** Vue 3 + Inertia.js
- **Styling:** Tailwind CSS 4
- **Database:** PostgreSQL
- **Testing:** Pest PHP
- **Development Environment:** Laravel Herd

### Stack Governance Rules

- No alternative stack should be proposed inside the current scope.
- No architectural deviation should be introduced without explicit approval.
- The selected stack must be assumed to be the contractual and technical base for ongoing implementation.

---

## 3. Mandatory Delivery Methodology — RIGOR

This project MUST follow the **RIGOR methodology** without exception.

RIGOR is not optional guidance. It is the operational framework governing how implementation is planned, approved, executed, and validated.

### 3.1 Core Operating Rules

- Work strictly **task-by-task**
- **No code is written without prior planning**
- Every implementation step requires:
  - A **Task List**
  - An **Implementation Plan**
  - **Explicit approval**
- No assumptions may be made about missing business or technical context
- Every step must be validated before the next step begins
- Each implementation block must pass through the following lifecycle:
  - **Planned**
  - **Implemented**
  - **Validated**

### 3.2 Critical RIGOR Constraints

- DO NOT skip steps
- DO NOT merge phases
- DO NOT optimize ahead
- DO NOT introduce logic outside approved scope
- DO NOT reopen frozen decisions without authorization
- DO NOT improvise architecture during implementation
- DO NOT silently redefine scope based on convenience

### 3.3 Expected Implementation Discipline

All future work on Sitios Urbanos must preserve:

- Architectural integrity
- Tenant isolation guarantees
- Step-by-step implementation control
- Explicit validation before progression
- Clear separation between planning and coding

---

## 4. Current Project Status

### 4.1 Implemented vs Planned

- **Implemented:** RIGOR 1.1
- **Planned but not implemented:** RIGOR 1.2

This distinction is intentional and must remain clear in future sessions to avoid confusing approved architecture with completed runtime behavior.

### 4.2 Latest Completed Implementation Block

**RIGOR 1.1 fully implemented**

Confirmed outcome:

- Community listing flow working and tested

### 4.3 RIGOR 1.1 — Foundation

**Status:** ✅ COMPLETED

The foundational phase has already been implemented successfully.

#### Completed Items

- Laravel base project setup
- Vue 3 + Inertia integration
- Authentication via Laravel Breeze
- Base layout system
- Community listing feature at `/comunidades`
- PostgreSQL configuration
- Initial commits completed

#### Meaning of Completion

This means the project already has a stable technical base and is ready to continue into the next major architectural phase.

### 4.4 RIGOR 1.2 — Multi-Tenancy Core

**Status:** 🟡 FULLY PLANNED (NOT IMPLEMENTED)

This phase has already been architected and approved.

#### Frozen State

All strategic decisions for RIGOR 1.2 are:

- Approved
- Frozen
- Ready for execution

No replanning of this phase should occur unless explicitly requested by the project owner.

---

## 5. Primary Continuity Documents

The following documents are part of the primary continuity and implementation context for this project:

- `docs/PROJECT-CONTINUITY-HANDOFF.md`
- `docs/PRD.md`
- `docs/MVP-BOUNDARY.md`
- `docs/ARCHITECTURE.md`
- `docs/APP-STRUCTURE.md`
- `docs/CODE-CONVENTIONS.md`
- `docs/PROJECT-RULES.md`
- `docs/BACKLOG-RIGOR.md`
- `docs/blocks/*`
- `.agents/rules/*`

### Document Usage Rule

When resuming work, these documents must be treated as the primary reference set together with this handoff file. If a future assistant finds ambiguity, it must first consult this continuity hierarchy before proposing any change.

---

## 6. Frozen Architecture for RIGOR 1.2 — Multi-Tenancy Core

The following architecture is **frozen** and must be implemented exactly as approved.

---

### 6.1 T1 — Tenant Entry Strategy

The project will use **Path-Based Scoping** for tenant access.

#### Approved Canonical Route

```text
/c/{community_slug}
```

#### Rules

- This is the approved tenant entry pattern.
- Tenant context begins from the route.
- The route structure must remain stable unless explicitly redesigned.
- No alternative tenant entry model should be introduced during implementation.

---

### 6.2 T2 — Active Tenant Context

The active tenant must be represented through a dedicated backend service named `TenantContext`.

#### Architectural Intent

`TenantContext` is the runtime holder of the currently active community for the authenticated request lifecycle.

#### Rules

- Backend-only state
- Fail-safe behavior required
- No implicit guessing of tenant state
- No uncontrolled request trust
- No direct reliance on frontend-provided tenant authority

#### Required API

```php
set(Community $community): void
get(): ?Community
require(): Community
```

#### Design Meaning

- `set(...)` registers the active tenant context
- `get()` returns the active tenant if available
- `require()` must enforce fail-safe access by throwing when context is missing

This task is the next implementation target and must be executed before downstream tenant-safe behavior is built.

---

### 6.3 T3 — Tenant Resolver

Tenant resolution must happen **explicitly** and only through the authenticated user relationship.

#### Approved Resolution Pattern

```php
$user->communities()->where('slug', $slug)
```

#### Resolver Rules

- NEVER use global community lookup
- NEVER resolve a tenant outside user-authorized scope
- NEVER trust raw route data without authorization-aware resolution
- Resolution must remain explicit and controlled

#### Mandatory Failure Cases

Resolution failures must return `404` for the following conditions:

- Invalid slug
- User not assigned to the community
- Community inactive

#### Security Rationale

The resolver is not just a locator. It is part of the tenant isolation boundary and must behave accordingly.

---

### 6.4 T4 — Tenant Middleware Bridge

A dedicated middleware layer must bridge route data and runtime tenant context.

#### Responsibilities

- Read `community_slug`
- Call the tenant resolver
- Populate `TenantContext`

#### Middleware Rules

- Must run **AFTER** authentication
- Must **NOT** query the database directly
- Must **NOT** contain business logic
- Must only orchestrate resolution and context registration
- Must remain infrastructure-focused

#### Architectural Meaning

This middleware is a transport bridge between route context and backend runtime state. It must not absorb responsibilities that belong to actions, services, or domain logic.

---

### 6.5 T5 — Tenant-Safe Architecture Rules

The model layer must follow explicit tenant-safe classification rules.

#### 6.5.1 Tenant Boundary Models

Example: `Community`

Rules:

- No `community_id`
- No tenant global scope
- Represents the tenant boundary itself
- Must not be treated as a tenant-owned child model

#### 6.5.2 Tenant-Owned Models

Rules:

- MUST include `community_id`
- MUST depend on `TenantContext`
- MUST NOT trust request data for tenant assignment
- MUST be query-scoped safely within the active community
- MUST preserve isolation by design, not by convention alone

#### 6.5.3 Global Models

Example: `User`

Rules:

- No `community_id`
- Related through pivot table `community_user`
- Global models may participate in multiple tenant relationships
- Global models do not belong directly to a single tenant

#### Architectural Implication

The model layer must make tenant boundaries explicit. Any ambiguity here increases the risk of cross-tenant leakage and is therefore unacceptable.

---

### 6.6 T6 — Validation Strategy

Validation is mandatory and must exist at multiple levels.

#### Unit Validation

Must cover:

- `TenantContext` behavior
- Resolver correctness
- Failure behavior
- Missing-context enforcement

#### Feature Validation

Must cover:

- HTTP `404` isolation behavior
- Middleware execution behavior
- Route-level tenant protection
- Authenticated resolution flow

#### Manual Validation

Must cover:

- URL tampering attempts
- Unauthorized tenant access attempts
- Cross-tenant navigation checks
- Missing-context scenarios
- Incorrect slug scenarios

#### Validation Rule

No task may be considered complete until its planned validation has passed.

---

## 7. Critical Non-Negotiable Rules

The following rules are absolute and must never be violated:

- Cross-tenant access MUST fail closed
- No model should leak data across communities
- No controller should manually resolve tenants
- No request should inject `community_id`
- `Community` is the ONLY tenant boundary
- No convenience shortcut may weaken isolation
- Authorization must remain compatible with tenant isolation
- Tenant safety must be enforced structurally, not assumed behaviorally

These are **hard architectural constraints**, not recommendations.

---

## 8. Items Not Yet Implemented

The following items remain pending and are not yet present in the codebase as completed runtime behavior:

- `TenantContext` service
- Tenant Resolver Action
- Tenant Middleware
- Tenant route group `/c/{community_slug}`
- Tenant-scoped models
- Isolation enforcement in queries

### Practical Meaning

The system is not yet operating as a true tenant-isolated runtime, even though the tenant architecture has already been fully defined.

---

## 9. Mandatory Next Step

The next required implementation step is:

👉 **RIGOR 1.2 — T2 Implementation**

### Immediate Target

Implement:

- `TenantContext`

### Next Exact Step

Request:

- **Task List**
- **Implementation Plan**

For:

- **TenantContext**

### Scope Discipline for This Step

During this task:

- Do not re-plan previous phases
- Do not modify frozen architecture
- Do not jump ahead into unrelated tenant features
- Do not merge T2 with later tasks unless explicitly approved
- Do not introduce alternative approaches

This step must remain limited to the approved `TenantContext` foundation.

---

## 10. Resume Protocol for a New Chat

When resuming this project in a future chat, this file must be used as the primary continuity reference:

```text
docs/PROJECT-CONTINUITY-HANDOFF.md
```

The additional supporting documents listed in **Primary Continuity Documents** should also be considered part of the continuity context.

Then the assistant must receive the following instruction:

```text
Continue Sitios Urbanos using RIGOR methodology.
We are starting RIGOR 1.2 implementation at T2 (TenantContext).
Request Task List + Implementation Plan for TenantContext.
Do not re-plan previous phases. Do not change frozen decisions.
```

### Purpose of This Protocol

This avoids:

- Reopening approved architectural work
- Scope drift
- Re-analysis of already frozen decisions
- Loss of continuity across sessions

---

## 11. Expected Behavior from Any Future Assistant

Any assistant continuing this project MUST:

- Respect all frozen decisions
- Follow RIGOR strictly
- Avoid reopening architectural discussions
- Avoid introducing alternative approaches
- Avoid skipping validation steps
- Operate at senior engineer / software architect level
- Keep implementation aligned with approved scope
- Prioritize correctness over speed
- Prioritize isolation over convenience
- Treat this document as the authoritative handoff baseline

### Default Behavior Under Uncertainty

If uncertainty appears during implementation, the assistant must default to:

- Staying within the approved scope
- Preserving frozen architecture
- Requesting approval before deviation
- Protecting tenant isolation first

---

## 12. Governance Interpretation Rules

If there is any ambiguity while resuming work, the following interpretation order applies:

1. This handoff document
2. Frozen architecture decisions already approved
3. RIGOR methodology constraints
4. Explicit new instructions from the project owner

### Governance Principle

Nothing in future implementation should override frozen architecture silently.

---

## 13. Final Continuity Statement

This document exists to preserve implementation continuity across chats, sessions, and agents.

Its purpose is to ensure Sitios Urbanos continues from the exact approved architectural state without:

- regressions,
- scope drift,
- architectural improvisation,
- or unnecessary re-analysis.

It must be treated as a **living continuity artifact**, but all frozen sections remain unchanged unless explicitly reapproved by the project owner.

### Final Instruction to Future Sessions

Resume from the frozen state.  
Do not redesign what is already approved.  
Implement only what corresponds to the next RIGOR block.  
Validate before advancing.

**Operational Resume Point:** Ready to request Task List + Implementation Plan for RIGOR 1.2 — T2 (TenantContext).