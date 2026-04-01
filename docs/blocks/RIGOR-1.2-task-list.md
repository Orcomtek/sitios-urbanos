# RIGOR 1.2 — Task List (Multi-Tenancy Core)

## Scope

This block defines the core multi-tenancy runtime behavior for Sitios Urbanos.

The main goal is to establish how the application identifies, resolves, and stores the active tenant context safely.

---

## Goal

Implement the technical foundation for:

- tenant context resolution
- active community selection
- tenant-aware request lifecycle
- safe tenant context propagation

---

## Out of Scope

This block does NOT include:

- finance modules
- governance modules
- reservations
- QR access
- marketplace
- subdomain deployment to production
- advanced permissions system

---

## Core Objective

After this block, the system should be able to:

1. determine which community the user is entering
2. store or resolve the active tenant context safely
3. make future tenant-owned modules depend on that context

---

## Key Questions This Block Must Solve

- How does the user choose an active community?
- Where is the active community stored?
- How is tenant context resolved on each request?
- How do we prevent cross-tenant leakage once a tenant is active?
- Which middleware is responsible for tenant context?
- Which models will later require tenant scoping?

---

## Planned Work Areas

### T1 — Tenant Entry Strategy
Define how a user enters a selected community from `/comunidades`.

### T2 — Active Community Context
Define where and how the active community is stored:
- session
- route parameter
- subdomain
- hybrid approach

### T3 — Tenant Resolver
Design the service or support class responsible for resolving the current tenant.

### T4 — Tenant Middleware
Design the middleware responsible for validating and injecting tenant context.

### T5 — Tenant-Safe Architecture Rules
Define which future models must be tenant-scoped and which must NOT be.

### T6 — Validation Strategy
Define how this block will be tested and manually verified before modules depend on it.

---

## Non-Negotiable Rules

- Community is the tenant boundary model
- Community MUST NOT have a global tenant scope
- Tenant-owned models in future phases WILL require tenant scoping
- No cross-tenant access can be allowed once context is active
- Multi-tenancy must be explicit, not implicit
- No shortcuts through frontend state

---

## Expected Deliverables

- tenant entry strategy
- active tenant storage strategy
- resolver design
- middleware design
- architecture rules for tenant-scoped models
- validation plan

---

## Current Status

- RIGOR 1.1 complete
- Ready to begin RIGOR 1.2 planning