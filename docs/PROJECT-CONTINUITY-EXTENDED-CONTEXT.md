# PROJECT CONTINUITY — EXTENDED CONTEXT
## Sitios Urbanos

---

## 1. Purpose of This Document

This document captures **high-value contextual knowledge** that is not strictly architectural but is **critical for correct decision-making**.

It complements:

- `PROJECT-CONTINUITY-HANDOFF.md` → source of truth for architecture and state
- This document → source of truth for **intent, reasoning, product vision, and implicit constraints**

---

## 2. Core Product Vision

Sitios Urbanos is not just a system.

It is a **multi-tenant SaaS platform for property management (Propiedad Horizontal)** designed to:

- digitize administration
- centralize operations
- improve communication
- enforce governance
- enable financial control
- scale across LATAM

---

## 3. Strategic Philosophy

### 3.1 This is NOT a CRUD System

This platform is NOT:

- a basic admin panel
- a simple CRUD app
- a collection of modules

It is:

> A structured operating system for residential communities.

### 3.2 Governance is the Core Engine

The system is designed around:

- rules
- control
- validation
- traceability

NOT around:

- convenience
- flexibility at the cost of control

### 3.3 Backend is the Absolute Authority

The system enforces:

- no trust in frontend data
- no trust in request payloads for critical fields
- no trust in client-provided tenant identifiers

Everything critical must be:

- derived
- validated
- enforced in backend

---

## 4. Multi-Tenancy Philosophy

### 4.1 Tenant = Community

- Each **Community** is a tenant
- It is the **ONLY tenant boundary**

### 4.2 No Cross-Tenant Leakage

This is non-negotiable:

- A user MUST NEVER access data from another community
- Enumeration attempts must fail silently with 404
- The system must behave as if other tenants do not exist

### 4.3 Fail Closed

If something is unclear:

- deny access
- throw exception
- return 404

NEVER:

- fallback permissively
- guess context
- assume intent

---

## 5. Domain Model Philosophy

### 5.1 Three Model Types

#### Tenant Boundary Model

- Example: `Community`
- Defines tenant scope
- NEVER scoped by tenant
- NEVER contains `community_id`

#### Tenant-Owned Models

- MUST include `community_id`
- MUST depend on TenantContext
- MUST NOT trust request data

#### Global Models

- Example: `User`
- NOT scoped to a single tenant
- Connected via pivot (`community_user`)

---

## 6. Entry and Navigation Model

### 6.1 Control Plane vs Tenant Runtime

There are two worlds:

#### Control Plane

- `/comunidades`
- user sees all assigned communities
- no tenant context active

#### Tenant Runtime

- `/c/{community_slug}`
- everything is scoped
- tenant context MUST be active

### 6.2 Path-Based Scoping (Frozen)

Canonical format:

```text
/c/{community_slug}
```

This is:

- required
- non-negotiable
- backend authoritative

### 6.3 No Session-Based Tenant Authority

Session may be used in the future ONLY for:

- UX hints (e.g. last selected community)

But NEVER for:

- security
- data isolation
- context authority

---

## 7. Frontend Philosophy

### 7.1 Frontend is a Rendering Layer

Frontend must:

- render data
- trigger actions
- display state

Frontend must NOT:

- define business rules
- define tenant context
- assign `community_id`
- perform authorization logic

### 7.2 Spanish-First UX

All UI must be:

- Spanish-first
- clear
- structured
- professional

### 7.3 Clean, Premium UI (No Noise)

UI principles:

- minimalism
- clarity
- hierarchy
- consistency

Avoid:

- clutter
- unnecessary animations
- visual overload

---

## 8. Design System (High-Level)

The system will use:

- Tailwind CSS 4
- consistent spacing
- clear typography hierarchy
- reusable layout system

### 8.1 Layout Structure (Already Implemented)

- Sidebar
- Topbar
- Content area

### 8.2 Layout Philosophy

Layouts must be:

- persistent (Inertia)
- predictable
- modular

---

## 9. URL & Domain Strategy

### 9.1 Subdomain vs Path (Current Decision)

Current MVP decision:

- Use path-based scoping (`/c/{slug}`)

Future possibility:

- subdomains (e.g. `community.app.com`)

But NOT now.

### 9.2 Domain Rendering in UI

Communities may show:

- subdomain if exists
- fallback to slug

---

## 10. Security Philosophy

### 10.1 Zero Trust at Boundaries

- never trust request input
- never trust frontend
- always validate server-side

### 10.2 Enumeration Protection

If user tries:

- invalid slug
- unauthorized community

System must:

- return 404
- not reveal existence

---

## 11. Execution Model

This project uses a three-layer execution model:

### 11.1 Roles

- Camilo → Project Owner / Decision Maker
- ChatGPT → Architect / Planner / Auditor (RIGOR)
- Antigravity → Code Execution Agent

### 11.2 Critical Rule

ChatGPT MUST:

- NOT assume it writes final code
- produce plans and audits
- validate Antigravity outputs

Antigravity is:

- the implementation layer

---

## 12. RIGOR Enforcement

All execution must follow:

- planning before coding
- approval before execution
- validation before closure

No exceptions.

---

## 13. What Has Been Built (Context)

RIGOR 1.1 includes:

- Laravel base
- Vue + Inertia
- Breeze authentication
- Layout system
- Community listing
- PostgreSQL
- Tests passing

---

## 14. What Is Coming (Context)

RIGOR 1.2 will introduce:

- TenantContext
- Resolver
- Middleware
- Route scoping
- Isolation enforcement

---

## 15. Product Intent Behind Decisions

### 15.1 Why Path-Based First

- simpler
- more controllable
- easier to debug
- avoids DNS complexity

### 15.2 Why Strict Isolation

Because:

- legal risk
- data privacy
- trust in platform
- SaaS credibility

### 15.3 Why Backend Authority

Because:

- frontend is manipulable
- APIs can be abused
- security must be centralized

---

## 16. Mental Model for Future Development

Always ask:

- Does this respect tenant boundaries?
- Is this trusting frontend?
- Is this introducing hidden coupling?
- Is this violating frozen decisions?
- Is this mixing responsibilities?

If YES → STOP.

---

## 17. What Must NEVER Happen

- cross-tenant queries without scope
- `community_id` coming from request
- controllers resolving tenant manually
- global queries on tenant-owned models
- implicit behavior instead of explicit validation

---

## 18. Continuity Rule

If this project is resumed in a new chat:

This document MUST be loaded alongside:

- `PROJECT-CONTINUITY-HANDOFF.md`

This document is NOT optional.

---

## 19. Final Statement

This document exists to preserve:

- intent
- reasoning
- philosophy
- product direction

It ensures that:

> Even if the chat is lost, the thinking is not.