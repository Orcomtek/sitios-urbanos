# Architecture — Sitios Urbanos

## 1. Overview

Sitios Urbanos is a **multi-tenant SaaS application** built with:

- Laravel 13 (backend)
- Vue 3 + Inertia.js (frontend)
- PostgreSQL (database)

---

## 2. Multi-Tenancy Strategy

### 2.1 Model

- Single database
- Logical isolation using `community_id`
- Global scopes enforced in all tenant models

---

### 2.2 Data Isolation

All tenant-related tables MUST include:

- `community_id`

Rules:

- no query without tenant scope
- no cross-tenant access
- no global queries unless explicitly approved

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

### 3.1 Domains

| Domain | Purpose |
|------|--------|
| sitiosurbanos.com | marketing / landing |
| app.sitiosurbanos.com | main application |
| {slug}.sitiosurbanos.com | tenant access |

---

### 3.2 Routing Logic

- domain-based tenant resolution
- middleware identifies community
- sets tenant context globally

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
- event broadcasting scoped by community

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