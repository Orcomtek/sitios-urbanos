---
trigger: always_on
---

# Architecture & Multi-Tenancy Rules — Sitios Urbanos

This document defines the **core architecture and tenant isolation rules**.

These rules are CRITICAL and NON-NEGOTIABLE.

---

## Multi-Tenant Architecture (CORE PRINCIPLE)

Sitios Urbanos is a **multi-tenant SaaS platform**.

- Single database
- Logical isolation using `community_id`
- All tenant data MUST be scoped

---

## Tenant Isolation Rules (CRITICAL)

Every query MUST:

- include `community_id`
- be filtered explicitly or implicitly

You MUST NOT:

- query global data without tenant scope
- access records from another community
- trust client-provided tenant identifiers

---

## Global Scope Enforcement

All tenant models MUST include a global scope:

- automatically filter by `community_id`
- applied at model level

Example concept:
- CommunityScoped trait or equivalent

---

## Current Tenant Resolution

The current tenant must be resolved via:

- authenticated user
- domain/subdomain
- server-side logic

You MUST NOT:

- rely on frontend to define tenant
- accept raw `community_id` from requests

---

## User ↔ Community Relationship

Users can belong to multiple communities.

Use a pivot table:

- `community_user`
  - user_id
  - community_id
  - role
  - unit_id (nullable)

This enables:

- multi-community access
- role-based permissions

---

## Domain & Subdomain Strategy

The system must support:

- main domain (landing)
- admin/control plane
- tenant subdomains

Example:

- sitiosurbanos.com → landing
- admin.sitiosurbanos.com → control plane
- {community}.sitiosurbanos.com → tenant app

---

## Control Plane vs Tenant Runtime

These MUST be separated logically:

### Control Plane
- manages tenants
- manages plans
- global configuration

### Tenant Runtime
- community-specific data
- operations
- dashboards

You MUST NOT mix both contexts.

---

## Backend Enforcement (MANDATORY)

Tenant isolation must be enforced at multiple levels:

1. Middleware (tenant resolution)
2. Model scopes
3. Policies / authorization
4. Application logic

Do NOT rely on a single layer.

---

## Data Creation Rules

When creating records:

- `community_id` MUST be set automatically
- NEVER accept it from frontend

---

## Cross-Tenant Protection

The system MUST prevent:

- data leakage
- cross-tenant queries
- unauthorized access

If there is ANY doubt:
→ STOP and re-evaluate

---

## Sensitive Domains (CRITICAL)

Extra care required in:

- payments
- invoices
- ledger
- parcels
- QR access
- panic alerts

These MUST always be tenant-scoped.

---

## Query Safety Rules

You MUST NOT write queries like:

- Model::all()
- Model::withoutScopes()

Unless explicitly justified and approved.

---

## Background Jobs & Queues

Jobs MUST:

- carry tenant context
- re-establish tenant scope before execution

---

## Events & Broadcasting

All events must:

- be scoped by community
- never broadcast cross-tenant data

---

## Agent Responsibility

You MUST:

- protect tenant isolation at all times
- validate every query mentally
- assume mistakes are possible and prevent them

If unsure:
→ STOP and ask