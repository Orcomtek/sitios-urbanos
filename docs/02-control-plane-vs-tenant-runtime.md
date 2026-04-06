# 02 — Control Plane vs Tenant Runtime  
## Sitios Urbanos  
### Official Version (Antigravity Ready)

---

## 1. Purpose

This document defines the **official separation between Control Plane and Tenant Runtime** within Sitios Urbanos.

It establishes:

- global vs tenant-specific responsibilities  
- architectural boundaries  
- tenant isolation rules  
- system entry behavior  

This separation is **critical and non-negotiable**.

---

## 2. Core Concept

Sitios Urbanos operates in two distinct contexts:

### 2.1 Control Plane  
Global system layer (no active tenant)

### 2.2 Tenant Runtime  
Tenant-specific operational layer (active community)

👉 These contexts must **never be mixed**.

---

## 3. Control Plane Definition

The Control Plane is where **no tenant context exists yet**.

### Responsibilities

- authentication  
- account recovery  
- email verification  
- global profile  
- fetching user communities  
- private community selector  
- redirecting to tenant  

### Hosted at

app.sitiosurbanos.com

---

## 4. Tenant Runtime Definition

The Tenant Runtime is where a **specific community is active**.

All core operations happen here.

### Hosted at

{communitySlug}.app.sitiosurbanos.com

Example:

altosdelparque.app.sitiosurbanos.com

---

## 5. Core Rule

> Control Plane and Tenant Runtime are strictly separated contexts.

They must not share:

- business logic  
- state authority  
- routing responsibility  

---

## 6. Control Plane Responsibilities

- authentication flows  
- user identity  
- community discovery  
- community selection  
- tenant redirection  

---

## 7. Tenant Runtime Responsibilities

- resolve tenant via hostname  
- execute system modules  
- manage units, residents, payments, etc  
- enforce role-based permissions  
- support multi-unit context  
- guarantee tenant isolation  

---

## 8. Tenant Resolution Strategy

Tenant is resolved **only via subdomain**:

{communitySlug}.app.sitiosurbanos.com

### Resolution flow

1. extract `communitySlug` from hostname  
2. find community  
3. validate existence  
4. validate user membership  
5. initialize TenantContext  
6. fail if any step fails  

---

## 9. Tenant Source of Truth

Tenant context is defined by:

- hostname  
- backend resolver  
- TenantContext  

### NEVER by:

- frontend input  
- query parameters  
- session as authority  
- community_id from client  

---

## 10. Fail-Closed Rule

If there is any ambiguity → deny access.

Cases:

- invalid slug → 404  
- user not attached → 404  
- unresolved tenant → 404  

---

## 11. Official Domains

### Control Plane

app.sitiosurbanos.com

### Tenant Runtime

{communitySlug}.app.sitiosurbanos.com

### Public (future)

{communitySlug}.sitiosurbanos.com

---

## 12. Routing Rules

### Control Plane

- /login  
- /comunidades  
- /profile  

### Tenant Runtime

- /dashboard  
- /units  
- /residents  
- /payments  
- /pqrs  
- /reservations  

### Deprecated

/c/{community_slug}

---

## 13. Middleware Responsibilities

### Control Plane

- auth  
- guest  
- global logic  

### Tenant Runtime

- auth  
- tenant resolver  
- membership validation  
- TenantContext initialization  
- tenant security  

---

## 14. UX Principles

### Control Plane

- simple  
- lightweight  
- no business logic  

### Tenant Runtime

- full experience  
- role-based  
- module-driven  
- unit-aware  

---

## 15. Multi-Community Flow

1. user logs in  
2. system fetches communities  
3. user selects community  
4. system redirects to tenant subdomain  

### Important

❌ No public selector before authentication  

---

## 16. Multi-Role Support

A user may have multiple roles.

System must:

- resolve permissions dynamically  
- avoid forcing re-login  

---

## 17. Multi-Unit Support

A user may own multiple units.

System must:

- provide unit selector inside tenant  
- maintain session continuity  

---

## 18. Naming & Slug Rules (CRITICAL)

### 18.1 Problem

Different communities may share the same display name.

---

### 18.2 Official Rule

- name → NOT unique  
- slug → MUST be globally unique  
- id → internal authority  

---

### 18.3 Subdomain Rule

Each tenant must have a unique subdomain:

{communitySlug}.app.sitiosurbanos.com

---

### 18.4 Slug Generation

Step 1:

Parques de Castilla → parques-de-castilla

Step 2 (if duplicated):

- parques-de-castilla-bogota  
- parques-de-castilla-norte  
- parques-de-castilla-2  

👉 Semantic > numeric

---

### 18.5 UX Rule for Duplicates

If duplicate names exist, UI must display a secondary descriptor:

Example:

Parques de Castilla  
Bogotá · Suba  

Parques de Castilla  
Soacha · Ciudad Verde  

---

### 18.6 Suggested Data Model

id  
name  
slug (unique)  
city  
short_address  
nit (optional)  

---

### 18.7 Name vs Slug Mutability

- name → flexible  
- slug → stable  

Because slug affects:

- subdomains  
- links  
- integrations  
- certificates  

---

## 19. Forbidden Practices

- resolving tenant from frontend  
- trusting community_id from request  
- mixing control plane with tenant logic  
- using path-based resolution  
- using session as tenant authority  

---

## 20. Impact on Current System

### Still valid

- TenantContext  
- Resolver  
- Middleware  
- multi-tenant logic  

### Must be migrated

- path-based routing  
- outdated documentation  

---

## 21. Final Statement

Sitios Urbanos is structured as:

### Control Plane
- identity  
- access  
- routing  

### Tenant Runtime
- operations  
- modules  
- tenant context  

👉 This separation is officially frozen.