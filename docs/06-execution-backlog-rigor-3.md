# 06 — Execution Backlog (RIGOR 3.0)  
## Sitios Urbanos

---

## 1. Purpose

This document defines the official execution plan of the system based on:

- PRD
- SRS
- MVP Boundary

This backlog organizes development into controlled execution blocks in order to:

- avoid technical debt
- guarantee architectural coherence
- allow continuous validation
- ensure execution quality

---

## 2. Execution Principle

The system must NOT be developed as isolated or random features.

It must be built in layers:

- each block has clear dependencies
- each block must be validated before continuing
- each block must leave a stable foundation

---

## 3. Backlog Structure

Development is divided into:

# RIGOR BLOCKS

---

## BLOCK 1 — Infrastructure and Multi-Domain Foundation

### Objective

Support real SaaS architecture with subdomains.

### Includes

- subdomain configuration
- wildcard domains
- shared cookies configuration
- cross-subdomain session handling
- domain-based routing
- local and production-ready environment configuration

### Expected Outcome

A stable foundation for real multi-tenant execution.

---

## BLOCK 2 — Authentication and Entry

### Objective

Implement the Email-First entry flow.

### Includes

- login
- private community selector
- redirect to tenant subdomain
- persistent session across subdomains
- global logout behavior

### Expected Outcome

Secure and coherent platform entry.

---

## BLOCK 3 — Tenant Runtime Hardening

### Objective

Harden multi-tenant isolation.

### Includes

- subdomain-based tenant resolution
- robust tenant middleware
- solid TenantContext behavior
- secure internal fallback
- isolation stress tests

### Expected Outcome

Strict tenant isolation with zero leakage.

---

## BLOCK 4 — Core Entities Refactor

### Objective

Align the data model with the approved SRS.

### Includes

#### Units
- configurable property types
- structured parking and storage attributes

#### Residents
- owner vs tenant distinction
- one active tenant per unit
- owner-controlled tenant lifecycle

### Expected Outcome

A clean and scalable domain model.

---

## BLOCK 5 — Security Deep Layer

### Objective

Implement auditable and defensible security flows.

### Includes

- visitors
- QR flows
- identity validation states
- omission registration
- security log
- action attribution
- package handling
- panic button

### Expected Outcome

An operationally defensible security system.

---

## BLOCK 6 — Financial Core

### Objective

Implement payments and monetization.

### Includes

- ePayco split integration
- internal payments
- external payments
- configurable commissions
- baseline ledger

### Expected Outcome

A monetizable and traceable financial system.

---

## BLOCK 7 — Governance

### Objective

Implement the institutional layer.

### Includes

- PQRS
- anonymous PQRS
- announcements
- documents

### Expected Outcome

Structured communication and institutional traceability.

---

## BLOCK 8 — Ecosystem

### Objective

Activate the internal economic layer.

### Includes

- basic marketplace
- basic P2P ecosystem

### Expected Outcome

A real product differentiator from the MVP stage.

---

## 4. Mandatory Execution Order

The order is mandatory:

1. Infrastructure  
2. Authentication  
3. Tenant Runtime  
4. Core Entities  
5. Security  
6. Finance  
7. Governance  
8. Ecosystem  

---

## 5. Advancement Rules

### Rule 1

Do not move to the next block if the current block is not validated.

### Rule 2

Each block must:

- work completely
- pass tests
- not break previous blocks

### Rule 3

Do not mix blocks.

---

## 6. Validation Rules

Each block must validate:

- functionality
- isolation
- traceability
- coherence with SRS

---

## 7. Final Outcome

The expected result is:

- a complete system
- scalable architecture
- monetizable platform
- no structural technical debt

---

## 8. Strategic Importance

If this backlog is followed:

- the system grows in an orderly way
- architecture remains coherent
- execution remains controllable

If it is not followed:

- the system becomes fragile
- technical debt grows
- product integrity breaks