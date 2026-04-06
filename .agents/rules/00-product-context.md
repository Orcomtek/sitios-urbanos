---
trigger: always_on
---

# Product Context — Sitios Urbanos

---

## 1. Purpose

This workspace is for building **Sitios Urbanos**, a multi-tenant SaaS platform designed to operate as a **modular super-app for residential communities (Propiedad Horizontal)** in Colombia and Latin America.

The system must not behave as a simple management tool.

It must operate as:

- an operational system
- a financial system
- a governance system
- a security system
- an economic ecosystem

---

## 2. Product Vision

Sitios Urbanos is a **multi-tenant SaaS ecosystem** that digitalizes and centralizes all critical operations of a residential community.

It must:

- support multiple communities simultaneously
- scale across Colombia and LATAM
- provide real operational value from day one
- evolve into a network of connected communities

---

## 3. Supported Community Types

The system MUST work for:

### 3.1 Guarded Communities
- with security staff (portería)
- manual + digital validation

### 3.2 Self-Managed Communities
- no guards
- autonomous access
- cameras / automation
- low administrative structure

---

### Core Rule

The system MUST provide value even without physical security staff.

---

## 4. Core Product Dimensions

The platform operates across five integrated dimensions:

---

### 4.1 Core Operations

- units (properties)
- residents
- ownership and tenancy relationships
- amenities and reservations
- community structure

---

### 4.2 Security Layer (Operational + Auditable)

Includes:

- visitor flows
- QR-based access (when applicable)
- identity validation
- package management
- panic button
- full security logs

---

### Critical Rule

Security must:

- work with or without guards
- be traceable
- be auditable
- support incident reconstruction

Security is a **strategic wedge**, but also an **operational layer**.

---

### 4.3 Financial Engine (Primary Monetization Layer)

Includes:

- administration fees
- charges and invoices
- payments via ePayco split
- external payment registration
- transaction ledger

---

### Critical Rule

Commissions apply ONLY when transactions occur inside the platform.

All financial behavior MUST be:

- traceable
- configurable
- tenant-scoped

---

### 4.4 Governance & Community (Primary Adoption Driver)

Includes:

- announcements
- PQRS (including anonymous)
- document management
- voting (basic MVP level)

---

### Critical Rule

Governance is a **core adoption driver**, especially in communities without security staff.

---

### 4.5 Ecosystem (CRITICAL)

Includes:

- marketplace (basic)
- service providers
- P2P (classifieds between neighbors)

---

### Critical Rule

The ecosystem MUST exist in the MVP at a basic level.

This is a key differentiator of the product.

---

## 5. Domain Architecture (Mandatory)

The system MUST follow:

- app.sitiosurbanos.com → entry point
- {communitySlug}.app.sitiosurbanos.com → tenant runtime

---

### Rules

- tenant is resolved by subdomain
- NO public community selector
- authentication is email-first
- fallback is internal only

---

## 6. MVP Scope (STRICT)

The MVP MUST include:

- multi-tenancy via subdomains
- email-first authentication
- multi-community access
- role-based dashboards
- core operations (units, residents)
- governance (PQRS, announcements, documents)
- financial base (payments, ledger)
- security layer (visitors, QR, logs, panic button)
- ecosystem base (marketplace + P2P)

---

## 7. Explicitly OUT of MVP

The system MUST NOT include:

- advanced AI systems
- IoT integrations
- advanced OCR
- complex legal voting systems
- inter-community marketplace
- automation-heavy features

---

## 8. Multi-Tenant Principle

Sitios Urbanos is multi-tenant by design.

---

### Rules

- tenant is resolved by subdomain
- all data is scoped by tenant
- users may belong to multiple tenants
- NO data leakage between tenants

---

### This is NON-NEGOTIABLE

---

## 9. Data, Legal and Privacy Layer

The system MUST:

- comply with Colombian data laws
- protect personal data
- support anonymization
- support retention policies
- support traceable deletion

---

### Applies to ALL modules

---

## 10. Product Behavior Rules

The system MUST:

- prioritize clarity over complexity
- be usable by non-technical users
- behave as a premium SaaS product
- provide immediate value

---

## 11. UX Philosophy

- Spanish-first (Colombia)
- clean and modern UI
- role-based dashboards
- minimal cognitive load
- fast interaction flows

---

## 12. Strategic Insight

The product MUST NOT depend exclusively on security features.

The strongest adoption drivers are:

- financial clarity
- governance tools
- communication
- usability

---

## 13. Agent Responsibility

When working in this workspace, the agent MUST:

- strictly respect SRS
- strictly respect MVP boundaries
- NOT invent features
- NOT assume missing requirements
- align all technical decisions with product context

---

### If anything is unclear:

STOP and request clarification.