# 01 — Domains Architecture & Tenant Entry Strategy  
## Sitios Urbanos

---

## 1. Purpose

This document defines the **official domains architecture** and **tenant entry strategy** for Sitios Urbanos.

It establishes:

- how users enter the system
- how tenant context is resolved
- separation between public and authenticated environments
- multi-community behavior
- multi-role behavior
- multi-unit behavior

This document overrides any previous path-based strategy (e.g. `/c/{community_slug}`).

The system is now **hostname-based (subdomain-driven)**.

---

## 2. Core Concept: Tenant Entry Strategy

Tenant Entry Strategy defines:

> How a user is authenticated and routed into a specific tenant (community).

Flow:

1. Identify the user (global identity)
2. Resolve available communities
3. Select community
4. Redirect to tenant-specific domain
5. Resolve roles and permissions
6. Resolve active unit context (if applicable)

This is critical for:

- security
- privacy
- UX
- scalability
- session handling
- architecture consistency

---

## 3. Core Principle

Sitios Urbanos uses a **SaaS-grade subdomain architecture**.

Tenant MUST be resolved via hostname.

NOT via path.

---

## 4. Official Domain Architecture

### 4.1 Main Marketing Domain
sitiosurbanos.com

Purpose:

- marketing
- product info
- pricing
- lead generation

---

### 4.2 Global Auth Entry (Control Plane)
app.sitiosurbanos.com

Purpose:

- login
- registration
- password recovery
- email-first authentication
- private community selector

---

### 4.3 Tenant Authenticated Runtime
{communitySlug}.app.sitiosurbanos.com

Purpose:

- dashboards
- modules
- operations
- tenant-specific logic

Tenant is resolved via hostname.

---

### 4.4 Public Tenant Landing (Web Builder)
{communitySlug}.sitiosurbanos.com

Purpose:

- public information
- landing page
- directory
- marketing per community

If disabled → 404 or redirect.

---

### 4.5 Admin (Orcomtek Control)
admin.sitiosurbanos.com

Purpose:

- tenant management
- billing
- plans
- monitoring
- support

---

### 4.6 Providers Portal
providers.sitiosurbanos.com

Purpose:

- provider onboarding
- marketplace
- services

---

### 4.7 API
api.sitiosurbanos.com

Purpose:

- integrations
- mobile apps
- webhooks
- external services

---

## 5. Context Separation Rules

### MUST separate:

- Marketing
- Public tenant
- Auth entry
- Tenant runtime
- Admin
- Providers
- API

No mixing contexts.

---

## 6. Authentication Strategy (Email-First)

### Rule:

User identity is resolved BEFORE tenant.

---

### Flow:

1. User logs in at:
app.sitiosurbanos.com

2. Backend retrieves:
- user communities (community_user)

3. If multiple:
→ show PRIVATE selector

4. User selects community

5. Redirect to:
{communitySlug}.app.sitiosurbanos.com

---

### Critical Rule:

❌ NO public tenant selector before login  
❌ NO exposing communities publicly  

---

## 7. Identity Model

### User = Global Entity

- not tenant-bound
- can belong to multiple communities
- can have multiple roles
- can have multiple units

---

### Pivot: community_user

Must contain:

- user_id
- community_id
- role
- unit_id (nullable)
- status

---

## 8. Multi-Community Behavior

User can belong to multiple communities.

System MUST:

- show only assigned communities
- never expose others
- allow switching via selector

---

## 9. Multi-Role Behavior

User can have multiple roles inside same community.

System MUST:

- NOT require re-login
- resolve permissions dynamically
- adapt UI and navigation

---

## 10. Multi-Unit Behavior

User can own/manage multiple units inside same community.

System MUST:

- allow switching between owned units inside the same session
- maintain tenant context while switching units
- update UI dynamically without re-authentication

---

## 11. Landlord → Tenant Management (CRITICAL)

### New Core Rule

A landlord (owner) can manage tenants (renters) from their panel.

---

### Capabilities:

- create tenant
- update tenant
- remove tenant access
- define active occupant
- configure permissions

---

### Financial Rule

Landlord can define:

- whether tenant is responsible for administration fees

Because:

> This is REQUIRED for LATAM markets (especially Colombia),
where administration fees are often included in rent.

---

### Tenant Capabilities (if enabled)

- packages
- visitors
- announcements
- PQRS
- reservations

Financial visibility depends on landlord settings.

---

## 12. Tenant Resolution

Tenant MUST be resolved from:
{communitySlug}.app.sitiosurbanos.com

Backend must:

- extract slug
- validate existence
- validate membership
- fail if invalid

---

## 13. SSO (Single Sign-On)

User logs in once.

Must access subdomains without re-login.

---

### Requirement:

Shared session or secure token exchange between:

- app.sitiosurbanos.com
- *.app.sitiosurbanos.com

---

## 14. Local vs Production

Production:

- subdomain-based

Development:

- may simulate

BUT architecture MUST follow subdomain model.

---

## 15. Impact on Current System

### Valid:

- TenantContext
- Resolver
- Middleware
- Security rules

### Must be adapted:

- path-based routing
- tenant resolution via URL path

---

## 16. Frozen Decisions

- subdomain architecture REQUIRED
- email-first REQUIRED
- no public tenant selector
- multi-community supported
- multi-role supported
- multi-unit supported
- landlord manages tenants
- financial responsibility configurable
- hostname-based resolution REQUIRED

---

## 17. Final Statement

This document defines the **official domain and tenant entry architecture**.

All future:

- PRD
- SRS
- MVP
- backend
- frontend

MUST align with this.

## 18. Session Strategy (CRITICAL)

The system MUST use shared session cookies across subdomains.

Example:

- cookie domain: `.sitiosurbanos.com`

This allows:

- login in app.sitiosurbanos.com
- access in *.app.sitiosurbanos.com

Alternative (if needed):

- token-based exchange

But default approach MUST be cookie-based session sharing.

## 19. Tenant Context Source of Truth

The tenant MUST be resolved ONLY from:

- hostname (subdomain)

It MUST NOT be resolved from:

- request payload
- session
- frontend input
- query params

TenantContext service is the ONLY source of truth.