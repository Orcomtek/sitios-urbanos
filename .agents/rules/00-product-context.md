---
trigger: always_on
---

# Product Context — Sitios Urbanos

This workspace is for building **Sitios Urbanos**, a premium multi-tenant SaaS platform for managing residential communities (Propiedad Horizontal) in Colombia and Latin America.

---

## Product Vision

Sitios Urbanos is designed as a **modular super-app** that digitalizes and centralizes all operations of a residential community.

It must work for both:

1. **Guarded communities (with security staff / portería)**
2. **Self-managed communities (no guards, automated access, cameras, or low administration)**

The system must deliver value even when there is **no physical security staff**, focusing on:

- governance
- financial transparency
- communication
- community management

---

## Core Product Dimensions

The platform evolves across five dimensions:

### 1. Core Operations
- Census 360 (units, residents, family members)
- Amenities and reservations
- Community structure and data

### 2. Security Wedge
- Dynamic QR access (when applicable)
- Parcel management (manual + OCR async)
- Panic button with controlled SMS usage

⚠️ Important:
Security features must be optional and adaptable to communities WITHOUT guards.

---

### 3. Financial Engine (Primary Monetization Layer)

- Administration fees
- Extra charges and invoices
- Payment processing via ePayco Split
- Transaction ledger and traceability

This is a **core pillar**, not an add-on.

---

### 4. Governance & Community (CRITICAL for MVP)

- Announcements board
- PQRS (peticiones, quejas, reclamos, sugerencias)
- Document repository (acts, rules, files)
- Simple voting (initial version)

⚠️ Governance is a **primary adoption driver**, especially for communities without security staff.

---

### 5. Local Ecosystem (Future Expansion)

- Marketplace
- Local services
- Classifieds (P2P)
- Real estate exposure

🚫 NOT part of initial MVP implementation (only architecture-ready).

---

## MVP Scope (STRICT BOUNDARY)

The MVP includes ONLY:

- Multi-tenancy by subdomain
- Universal authentication (single login, multi-community access)
- Role-based dashboards (Resident, Admin, Guard)
- Core operations (Census, Amenities)
- Governance (Announcements, PQRS, Documents, Simple Voting)
- Financial base (Invoices, payments, ledger)
- Basic security wedge (QR, parcels, panic button)

---

## Explicitly OUT of MVP

The following must NOT be implemented in early blocks:

- Full marketplace system
- Public web builder for communities
- Advanced AI (RAG, assistants, automation)
- IoT integrations (hardware devices, sensors)
- Complex voting with legal certification
- Cross-community ecosystem features

---

## Multi-Tenant Principle

Sitios Urbanos is **multi-tenant by design**.

- Each community is isolated logically
- All tenant data is scoped by `community_id`
- Users can belong to multiple communities
- The system must never mix data between tenants

This is a **non-negotiable constraint**.

---

## Product Behavior Rules

The system must:

- prioritize clarity over complexity
- be usable by non-technical users
- feel like a high-quality SaaS product, not a legacy admin panel
- provide immediate value even with minimal configuration

---

## UX Philosophy

- Spanish-first interface
- Clean, modern, minimal UI
- Role-based dashboards
- Dynamic modules (no hardcoded navigation)
- Focus on real-time feedback where applicable

---

## Strategic Insight (CRITICAL)

The product must not depend on security features to succeed.

The strongest adoption drivers are:

- financial clarity
- governance tools
- communication
- ease of use

Security is a **wedge**, not the entire product.

---

## Agent Responsibility

When working in this workspace, you must:

- respect the MVP scope strictly
- not introduce features outside defined boundaries
- not assume missing product requirements
- align all technical decisions with this product context

If something is unclear:
→ STOP and ask instead of assuming