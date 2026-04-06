---
trigger: always_on
---

# Architecture and Tenancy — Sitios Urbanos

---

## 1. Purpose

This rule defines the **core architecture and tenant isolation model** of Sitios Urbanos.

It ensures:

- strict tenant isolation
- deterministic tenant resolution
- backend-controlled ownership
- audit-safe data access

This is a **CRITICAL and NON-NEGOTIABLE rule**.

---

## 2. Core Multi-Tenant Principle

Sitios Urbanos is a **multi-tenant SaaS platform**.

The architecture enforces:

- single database
- logical isolation via `community_id`
- backend-controlled tenant resolution
- fail-closed behavior

Cross-tenant access must NEVER occur.

---

## 3. Tenant Resolution Strategy (UPDATED — MANDATORY)

---

### 3.1 Official Rule

Tenant MUST be resolved from:

👉 subdomain (`{communitySlug}.app.sitiosurbanos.com`)

---

### 3.2 Forbidden

The system MUST NOT use:

- path-based tenancy (`/c/{slug}`)
- query-based tenancy (`?tenant=`)
- session-based authority

---

### 3.3 Internal Fallback

Allowed ONLY:

- server-side
- controlled
- never exposed publicly

---

## 4. Control Plane vs Tenant Runtime

---

### Control Plane

- `app.sitiosurbanos.com`
- no tenant context

---

### Tenant Runtime

- `{communitySlug}.app.sitiosurbanos.com`
- tenant context MUST be active

---

### Rule

No tenant operation may execute without a valid TenantContext.

---

## 5. Tenant Context (MANDATORY)

---

### Definition

`TenantContext` is the **single source of truth** for the active tenant.

---

### Rules

- MUST be resolved at request entry
- MUST be backend-controlled
- MUST be globally available in request lifecycle
- MUST fail closed when missing

---

### Forbidden

The system MUST NOT:

- infer tenant from frontend
- trust request payload
- override tenant via input
- use session as authority

---

## 6. Resolver Rules

Tenant resolution MUST:

- use authenticated user relationships (`communities()`)
- validate subdomain against authorized communities
- reject invalid access

---

### Must fail with 404 when:

- invalid subdomain
- user not part of tenant
- inactive tenant

---

### Forbidden

- global lookup as authority
- blind slug matching

---

## 7. Middleware Bridge Rules

Tenant middleware is the HTTP boundary.

Responsibilities:

- read subdomain
- call resolver
- populate TenantContext
- translate failures

---

### Rules

- must run AFTER authentication
- must NOT contain business logic
- must NOT query arbitrarily
- must orchestrate only

---

## 8. Model Classification Rules (CRITICAL)

---

### 8.1 Tenant Boundary Models

Example:
- Community

Rules:

- defines tenant boundary
- MUST NOT contain `community_id`
- MUST NOT be tenant-scoped

---

### 8.2 Global Models

Example:
- User

Rules:

- not owned by a single tenant
- MUST NOT contain `community_id`
- relationships via pivot (`community_user`)

---

### 8.3 Tenant-Owned Models

Examples:

- Unit
- Resident
- Invoice
- Transaction
- Parcel
- PQRS

Rules:

- MUST contain `community_id`
- MUST belong to one tenant
- MUST be resolved via backend

---

### Forbidden

Tenant-owned models MUST NOT trust:

- request payload
- frontend data
- headers
- API inputs

---

## 9. Database Integrity Rules

---

### Required

- all tenant tables MUST include `community_id`
- MUST have foreign key to `communities.id`
- global/boundary models MUST NOT include `community_id`

---

### Uniqueness Rules

When uniqueness is tenant-scoped:

- MUST use composite keys

Examples:

- `['community_id', 'identifier']`
- `['community_id', 'unit_number']`

---

## 10. Data Ownership Rules

---

### Rule

Ownership MUST be assigned ONLY in backend.

---

### Forbidden

- accepting `community_id` from client
- trusting frontend ownership
- payload-based assignment

---

## 11. Query Safety Rules

---

### Rule

All queries MUST be tenant-scoped.

---

### Mindset

- assume leakage risk
- prefer explicit scoping
- never rely on convenience

---

### Forbidden

- global queries
- unscoped queries
- ID-only access

---

## 12. Sensitive Domains

Extra care required in:

- payments
- ledger
- invoices
- QR access
- parcels
- panic alerts
- governance
- reservations

---

## 13. Backend Authority

---

### Rule

Backend is the ONLY authority for:

- tenant resolution
- authorization
- data access
- business rules

---

### Forbidden

Frontend MUST NOT:

- define tenant
- assign ownership
- enforce authorization
- execute business logic

---

## 14. Fail-Closed Policy

---

### Rule

If tenant cannot be resolved:

- return 404
- never reveal tenant existence

---

## 15. Testing Requirements

The system MUST validate:

- subdomain resolution
- tenant isolation
- rejection of cross-tenant access
- absence of leakage

---

## 16. Strategic Importance

This rule protects:

- data integrity
- security
- SaaS scalability
- legal defensibility

---

## 17. Consequence of Violation

Breaking this rule leads to:

- data leaks
- security breaches
- cross-tenant corruption
- system instability

---

## 18. Final Principle

Tenant context is:

- mandatory
- authoritative
- non-negotiable

The system must NEVER operate without it.