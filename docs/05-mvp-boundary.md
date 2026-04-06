# 05 — MVP Boundary  
## Sitios Urbanos

---

## 1. MVP Objective

The Sitios Urbanos MVP must demonstrate:

- real value for the residential community
- daily adoption by users
- complete operational usefulness
- monetization from the beginning
- technical viability of the multi-tenant model

This is NOT a demo.

This is a usable and sellable system.

---

## 2. MVP Principles

### 2.1 The MVP is not a minimal technical shell

It must be:

- commercially viable
- operationally useful
- coherent as a system

---

### 2.2 It must cover the five strategic dimensions

- Operations
- Security
- Finance
- Governance
- Ecosystem

---

### 2.3 It must work for:

- communities with front desk / guards
- communities without front desk

---

## 3. MVP Scope

### 3.1 Core Operations

Includes:

- unit creation with configurable property types
- basic property characteristics:
  - has_parking
  - parking_count
  - parking_identifiers (optional)
  - has_storage
  - storage_count
  - storage_identifiers (optional)

---

### Residents

Includes:

- resident creation
- unit assignment
- resident type:
  - owner
  - tenant

---

### Rules

- one active tenant per unit
- owner manages tenant lifecycle
- tenant can be deactivated

---

### 3.2 Security (CRITICAL)

Includes:

- visitor creation
- visitor entry flow
- full access states
- access validation

---

### QR

Includes:

- QR generation
- expiration
- validation

---

### Identity Validation

The system must explicitly record:

- validated
- partially validated
- skipped (with traceability)

---

### Package Handling

Includes:

- package registration
- package notification
- package delivery

---

### Security Log

Includes:

- access log
- event logging
- full traceability

---

### Panic Button

Includes:

- activation
- event registration
- notification

---

### 3.3 Finance

Includes:

- basic account statement
- payment registration
- ePayco payment readiness
- external payment registration

---

### Rule

Commission applies ONLY if the payment is processed through the platform.

---

### Configuration

- commission must be parameterizable

---

### 3.4 Governance

Includes:

- PQRS
- anonymous PQRS
- announcements
- documents

---

### Base Structure

- statuses
- traceability
- role-based control

---

### 3.5 Amenities

Includes:

- reservation of common areas
- basic reservation management

---

### 3.6 Ecosystem (MANDATORY)

#### Basic Marketplace

- simple provider listings
- service requests

---

#### Basic P2P (CRITICAL)

- item publication
- interaction between neighbors

---

### Rule

This must exist in the MVP.
It cannot be postponed.

---

## 4. What the MVP Does NOT Include

- advanced OCR
- IoT hardware integrations
- complex automation
- advanced analytics
- distributed reputation systems
- inter-community marketplace

---

## 5. Technical Rules of the MVP

The MVP must include:

- mandatory multi-tenancy
- strict tenant isolation
- active subdomain architecture
- complete logs
- full traceability

---

## 6. Success Metrics

The MVP is successful if:

- a community can operate with it end-to-end
- residents actually use it
- payments can be registered
- real interaction exists (PQRS / P2P)
- the platform can be sold and monetized

---

## 7. Expected Outcome

The MVP must result in:

- a functional system
- a commercializable product
- a scalable base