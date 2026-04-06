---
trigger: always_on
---

# Domains and Tenant Entry Strategy — Sitios Urbanos

---

## 1. Purpose

This rule defines the **official domain architecture and tenant entry strategy**.

This is a **non-negotiable rule**.

All routing, authentication, and tenant resolution MUST comply with this document.

---

## 2. Official Domains

The system MUST use the following structure:

### 2.1 Entry Point (Global App)

app.sitiosurbanos.com

Used for:

- login (email-first)
- session bootstrap
- community selection

---

### 2.2 Tenant Runtime (Authenticated App)

{communitySlug}.app.sitiosurbanos.com

Used for:

- all authenticated application flows
- dashboards
- modules (security, finance, governance, etc.)

---

### 2.3 Public Site (Optional Future)

{communitySlug}.sitiosurbanos.com

Used ONLY for:

- public landing pages
- web builder outputs

Must NOT be used for authenticated flows.

---

### 2.4 Admin (Future)

admin.sitiosurbanos.com

Used for:

- platform administration
- super admin operations

---

## 3. Core Principle

Tenant MUST be resolved by **hostname (subdomain)**.

---

## 4. Forbidden Patterns

The system MUST NOT:

- use path-based tenant resolution (e.g. /c/{slug})
- use query-based tenant resolution (?tenant=)
- allow mixed tenant resolution strategies
- expose tenant selection publicly

---

## 5. Email-First Authentication

### 5.1 Flow

1. User logs in at:
   app.sitiosurbanos.com

2. Backend resolves user → communities

3. User selects community

4. System redirects to:
   https://{communitySlug}.app.sitiosurbanos.com

---

### 5.2 Rules

- community selector MUST be private (post-authentication)
- NO public dropdown of communities
- NO public tenant discovery

---

## 6. Session Strategy

- session MUST be valid across subdomains
- cookies MUST be configured accordingly
- secure session handling is REQUIRED

---

## 7. Tenant Resolution

### 7.1 Rule

Tenant MUST be resolved from:

- subdomain (communitySlug)

---

### 7.2 Internal Fallback (Allowed)

If subdomain resolution fails:

- system MAY fallback internally (server-side only)
- MUST NOT expose fallback mechanism publicly

---

## 8. Fail-Closed Policy

If tenant cannot be resolved:

- return 404
- DO NOT reveal whether tenant exists or not

---

## 9. Multi-Community Support

Users may belong to multiple communities.

System MUST:

- allow switching via selector (from global entry)
- redirect accordingly

---

## 10. Multi-Role Support

Users may have multiple roles:

- admin
- resident
- guard
- future roles (provider, etc.)

System MUST:

- resolve role after tenant context is established
- NOT before

---

## 11. Unit-Level Context

Users may have multiple units within the same community.

System MUST:

- allow unit selection inside tenant runtime
- NOT require re-login per unit

---

## 12. Security Requirements

- no tenant leakage across domains
- no cross-tenant access
- no reliance on frontend for tenant authority
- backend MUST enforce tenant context

---

## 13. Strategic Importance

This rule defines:

- SaaS architecture correctness
- security boundaries
- scalability

Breaking this rule will compromise the entire system.