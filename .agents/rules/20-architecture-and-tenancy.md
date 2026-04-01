---
trigger: always_on
---

# Architecture & Multi-Tenancy Rules — Sitios Urbanos

This document defines the **core architecture and tenant isolation rules** for Sitios Urbanos.

These rules are **CRITICAL** and **NON-NEGOTIABLE**.

---

## Purpose

This file exists to reinforce the frozen architecture decisions already established in the core project documentation.

It does **NOT** replace:

- `docs/ARCHITECTURE.md`
- `docs/PROJECT-CONTINUITY-HANDOFF.md`
- `docs/PROJECT-CONTINUITY-EXTENDED-CONTEXT.md`
- other frozen RIGOR decisions

Its role is to ensure that any assistant or execution agent respects the approved multi-tenancy model without guessing.

---

## Core Multi-Tenant Principle

Sitios Urbanos is a **multi-tenant SaaS platform**.

The architecture enforces:

- single database
- logical isolation using `community_id`
- explicit backend-controlled tenant resolution
- fail-closed behavior when tenant context is missing or invalid

Cross-tenant access must never happen by accident, convention, or convenience.

---

## Current Tenant Runtime Strategy

The current official runtime strategy is:

- **Path-Based Scoping**
- canonical tenant runtime path:
  - `/c/{community_slug}`

This means:

- the active tenant context is derived from the route
- the backend is the authority for validating the tenant
- frontend must never define the tenant context

### Important clarification

Subdomain-based tenant scoping is **not** the current runtime authority.

It may be considered in the future, but the current frozen strategy is strictly:

- `/c/{community_slug}`

---

## Control Plane vs Tenant Runtime

These two contexts must remain logically separate.

### Control Plane

Examples:
- `/comunidades`
- global user-facing entry point
- no active tenant runtime context yet

### Tenant Runtime

Examples:
- `/c/{community_slug}/...`
- tenant-specific flows
- active tenant context must already be resolved and available

You MUST NOT mix control-plane logic with tenant-runtime logic.

---

## Model Classification Rules (CRITICAL)

All models must belong clearly to one of these categories.

### 1. Tenant Boundary Models

Example:
- `Community`

Rules:
- `Community` defines the tenant boundary
- `Community` MUST NOT carry a `community_id`
- `Community` MUST NOT be treated as a tenant-owned child model
- `Community` MUST NOT have a tenant global scope applied

### 2. Global / Control-Plane Models

Example:
- `User`

Rules:
- Global models are not owned by a single tenant
- Global models MUST NOT carry a `community_id` in their main schema
- Relationships to communities must occur via pivot structures such as:
  - `community_user`

### 3. Tenant-Owned Models

Examples:
- `Unit`
- `Resident`
- `Invoice`
- `Transaction`
- `Parcel`
- `PQRS`

Rules:
- Tenant-owned models MUST carry a `community_id`
- Tenant-owned models belong to exactly one `Community`
- Tenant-owned models MUST depend on backend tenant context
- Tenant-owned models MUST NEVER trust `community_id` from:
  - requests
  - payloads
  - frontend forms
  - headers
  - API clients

Ownership must always be derived and enforced by backend logic.

---

## Tenant Context Rules

The active tenant context is backend-owned.

This means:

- it must be resolved server-side
- it must be explicit
- it must fail closed when missing
- it must never be inferred from frontend authority

`TenantContext` is the approved runtime holder for the active community during the request lifecycle.

Future tenant-owned flows must rely on that backend context rather than trusting raw request ownership data.

---

## Resolver Rules

Tenant resolution must be explicit and pivot-based.

Approved pattern:

- resolve through the authenticated user's `communities()` relationship
- validate the route slug against authorized community membership
- fail with 404 for:
  - invalid slug
  - user not attached to that community
  - inactive community

Global `Community` lookup must not be used as the source of truth for tenant access.

---

## Middleware Bridge Rules

The tenant middleware acts as the HTTP bridge between:

- route parameter extraction
- tenant resolution
- `TenantContext` population

Its responsibilities are limited to:

- reading `community_slug`
- delegating to the approved resolver
- populating `TenantContext`
- translating failures correctly

The middleware must:

- run strictly after authentication
- avoid direct database queries
- remain orchestration-focused

---

## Database Integrity Rules

The database layer must reinforce tenant isolation structurally.

### Required rules

- All tenant-owned tables MUST include `community_id`
- `community_id` in tenant-owned tables MUST reference `communities.id` through a foreign key
- Boundary models MUST NOT include `community_id`
- Global models MUST NOT include `community_id` in their main schema

### Uniqueness rules

- Globally unique constraints SHOULD NOT be used for tenant-owned business data unless the field is universally unique by nature
- When uniqueness is only meaningful inside a tenant, composite uniqueness with `community_id` is the correct approach

Examples:
- `['community_id', 'unit_number']`
- `['community_id', 'internal_reference']`

---

## Data Ownership Assignment Rules

Tenant ownership must never be accepted from the client.

This means:

- do NOT trust raw `community_id` from requests
- do NOT let frontend decide tenant ownership
- do NOT allow payload-driven tenant assignment for tenant-owned models

Backend logic must remain the sole authority on ownership.

---

## Query Safety Rules

Developers and agents must treat tenant queries with caution.

Required mindset:

- assume leakage is possible if boundaries are vague
- prefer explicit tenant-safe reasoning
- never rely on convenience over isolation

You MUST NOT casually write unscoped access patterns against tenant-owned data.

If there is any doubt about whether a query respects tenant boundaries:

- stop
- review the model classification
- review the active tenant context
- re-evaluate before proceeding

---

## Sensitive Domains

Extra care is required for tenant isolation in domains such as:

- payments
- invoices
- ledger
- parcels
- QR access
- panic alerts
- governance records
- reservations

These areas must always respect the tenant boundary explicitly.

---

## Explicitly Deferred from This Rule Set

The following are **not** frozen here as mandatory current implementation mechanisms:

- exact global scope implementation mechanism
- exact trait mechanism (`CommunityScoped`, `TenantScoped`, etc.)
- exact `creating` event hook strategy
- background job tenant rehydration mechanics
- event broadcasting tenancy mechanics
- Pest architecture enforcement tests
- validation strategy details

Those may be addressed in later RIGOR blocks, but they are **not** the core of this document.

---

## Agent Responsibility

Any assistant or execution agent working on Sitios Urbanos must:

- preserve tenant isolation at all times
- respect the boundary/global/tenant-owned model classification
- respect path-based tenant runtime strategy
- avoid trusting frontend ownership input
- avoid silent scope drift
- stop when architecture is unclear instead of improvising

If unsure:

- STOP
- ask
- or escalate for architectural review