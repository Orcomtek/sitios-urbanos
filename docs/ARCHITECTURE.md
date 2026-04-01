# Architecture — Sitios Urbanos

## 1. Overview

Sitios Urbanos is a **multi-tenant SaaS application** built with:

- Laravel 13 (backend)
- Vue 3 + Inertia.js (frontend)
- PostgreSQL (database)

---

## 2. Multi-Tenancy Strategy

### 2.1 Model Classification

The system architecture enforces three strictly separated model classifications to ensure tenant safety:

#### 1. Tenant Boundary Models
- `Community` defines the structural tenant boundary.
- `Community` MUST NOT carry a `community_id`.
- `Community` MUST NOT have a tenant global scope applied.

#### 2. Global / Control-Plane Models
- `User` and standard control-plane models are shared resources.
- Global models MUST NOT carry a `community_id` directly in their structure.
- Global models' relationships to boundaries MUST occur strictly via pivot structures (e.g., `community_user`).

#### 3. Tenant-Owned Models
- Tenant-Owned models MUST carry a `community_id`.
- Tenant-Owned models MUST depend strictly on the `TenantContext` service for their resolution and scoping.
- Tenant-Owned models MUST NEVER trust a `community_id` submitted via external requests, frontend forms, or API payloads.

---

### 2.2 Database Integrity Rules

To secure the physical data tier, the database schema strictly follows these integrity constraints:

- **Foreign Keys:** All tenant-owned tables MUST utilize a strict foreign key mapping their `community_id` to `communities.id`.
- **Global Uniqueness:** Globally unique database constraints SHOULD NOT be applied to tenant-owned data fields unless the data behaves universally (e.g., auto-generated UUIDs).
- **Composite Uniqueness:** For logical uniqueness isolated to a tenant bounds, composite unique keys utilizing `['community_id', 'target_field']` MUST be preferred (e.g., identifying localized apartment unit tags).

---

### 2.3 Pivot Model

Users can belong to multiple communities:

- `community_user` table:
  - user_id
  - community_id
  - role
  - unit_id (nullable)

---

## 3. Domain Architecture

### 3.1 Domains & Routing

| Entry | Purpose |
|------|--------|
| sitiosurbanos.com | marketing / landing |
| app.sitiosurbanos.com | main application / control plane |
| /c/{community_slug} | canonical path-based tenant access |

*(Note: Subdomain-based scoping like `{slug}.sitiosurbanos.com` may be considered in the future, but the current canonical approach is strict Path-Based Scoping).*

---

### 3.2 Routing Logic

- path-based tenant resolution (`/c/{community_slug}`)
- middleware identifies community via route parameters
- sets tenant context globally for the request via `TenantContext`

---

## 4. Backend Architecture

### 4.1 Layered Structure

- Domain layer (business logic)
- Application layer (use cases)
- Infrastructure layer (external services)

---

### 4.2 Modules

Modules are isolated by domain:

- finance
- governance
- security
- operations

Each module:

- has services
- has models
- has controllers

---

## 5. Frontend Architecture

### 5.1 Stack

- Vue 3
- Inertia.js
- Tailwind CSS

---

### 5.2 Structure

- pages = views
- components = reusable UI
- layouts = dashboard wrappers

---

### 5.3 Data Flow

Backend → Inertia → Vue

No direct API consumption unless required.

---

## 6. UI Architecture

### 6.1 Dynamic UI

UI must be:

- role-based
- module-driven
- backend-controlled

---

### 6.2 Sidebar

- generated dynamically
- based on modules + permissions

---

### 6.3 Dashboard

- widget-based
- role-dependent
- configurable

---

## 7. Events & Realtime

- Laravel Reverb for websockets
- *(Future Phase: Event broadcasting scoping strategy will be formally implemented)*

Examples:

- new parcel
- payment received
- announcements

---

## 8. Jobs & Queues

- Laravel queues for background jobs
- used for:
  - OCR
  - notifications
  - payment processing

---

## 9. Database

### 9.1 PostgreSQL

- JSONB for flexible fields
- pgvector (future AI)

---

### 9.2 Core Tables

- communities
- users
- community_user
- units
- transactions
- invoices
- parcels
- pqrs

---

## 10. Payments Architecture

- ePayco integration
- webhook-based confirmation
- split logic per community
- internal ledger system

---

## 11. Security

- encrypted API keys
- webhook validation
- strict tenant isolation
- audit logs

---

## 12. Scalability

System must scale:

- horizontally (multiple tenants)
- modularly (feature activation)
- functionally (new modules)

---

## 13. Constraints

- must respect MVP boundary
- must avoid over-engineering
- must prioritize stability

---

## 14. Related Documents

- PRD.md
- MVP-BOUNDARY.md
- PROJECT-RULES.md