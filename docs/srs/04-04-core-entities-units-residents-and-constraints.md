# 04-04 — Core Entities (Units, Residents, Constraints)  
## System Requirements Specification (SRS)  
### Sitios Urbanos

---

## 1. Purpose

This document defines:

- core entities (Units and Residents)
- essential attributes
- relationships between entities
- data integrity rules
- mandatory constraints
- creation, update, and deletion behavior

This module guarantees database consistency and tenant-safe domain modeling.

---

## 2. Core Principle

Every tenant-owned entity MUST:

- be linked through `community_id`
- respect strict multi-tenant isolation
- never trust frontend-provided ownership data
- always validate against `TenantContext`

---

## 3. Entity: Unit

---

## 3.1 Definition

A `Unit` represents a property within a community.

It may correspond to:

- apartment
- house
- commercial unit
- office
- warehouse

---

### Important Rule

Property types MUST NOT be rigidly hardcoded.

The system MUST support a configurable catalog of property types, with initial suggested values but allowing future extension without structural redesign.

---

## 3.2 Classification

- `property_type`

---

### Modeling Rule

`property_type` MUST be based on a configurable catalog.

Suggested initial values:

- apartment
- house
- commercial
- office
- warehouse

---

## 3.3 Core Attributes

### Identification

- `id`
- `community_id` (required)
- `identifier` (e.g., "Apto 101", "Casa 12")

---

### Status

- `status`:
  - occupied
  - available
  - maintenance

---

### Parking

- `has_parking` (boolean)
- `parking_count` (integer, nullable)
- `parking_identifiers` (nullable)

---

### Rule

If `has_parking = true`, the system MUST allow defining:

- number of private parking spaces
- optional identifiers (e.g., A3, 147, S2-04)

This is required for operational tracking, census, and future reporting such as detecting unused private parking spaces.

---

### Storage (Deposits)

- `has_storage` (boolean)
- `storage_count` (integer, nullable)
- `storage_identifiers` (nullable)

---

### Rule

If `has_storage = true`, the system MUST allow defining:

- number of storage units
- optional identifiers

This supports operational control and reporting.

---

### Metadata

- `created_at`
- `updated_at`
- `deleted_at` (soft delete)

---

## 3.4 Constraints

### Tenant Isolation

Every unit MUST belong to a valid `community_id`.

---

### Uniqueness

`identifier` MUST be unique within the community.

Database rule:

- unique (community_id, identifier)

---

### Backend Authority

`community_id` MUST NOT be accepted from frontend input.

It MUST be injected via `TenantContext`.

---

### Soft Deletes

Units MUST NOT be physically deleted if they have:

- residents
- historical data
- financial records

---

### Future Evolution Note

Parking and storage are currently modeled as attributes, but the system MUST be designed to evolve into dedicated sub-entities if required.

---

## 4. Entity: Resident

---

## 4.1 Definition

A `Resident` represents a person operationally associated with a unit within a community.

---

## 4.2 Relationships

A Resident:

- belongs to a Unit
- belongs to a Community
- may be linked to a User

---

## 4.3 Attributes

### Identification

- `id`
- `community_id`
- `unit_id`
- `user_id` (nullable)

---

### Personal Data

- `full_name`
- `email` (nullable)
- `phone` (nullable)

---

### Resident Type

- `resident_type`:
  - owner
  - tenant

---

### Operational State

- `is_active` (boolean)

---

### Financial Visibility

- `pays_administration` (boolean)

Defines whether the tenant is responsible for administration fees visibility.

---

### Metadata

- `created_at`
- `updated_at`
- `deleted_at`

---

## 4.4 Constraints

### Tenant Consistency

Resident MUST belong to the same `community_id` as the Unit.

---

### Relationship Integrity

`unit_id` MUST belong to the same tenant.

Cross-tenant assignments MUST NOT be possible.

---

### Nullable User

Residents MUST be allowed without a linked User.

---

### Validation

On create/update:

- validate tenant consistency
- validate unit ownership
- validate relationship coherence

---

## 5. Critical Rule: Single Active Tenant

---

## 5.1 Definition

Each Unit may have:

- multiple historical tenants
- ONLY ONE active tenant at a time

---

## 5.2 Constraint

For each unit:

- only one record where:
  - resident_type = tenant
  - is_active = true

---

## 5.3 Enforcement

Must be enforced:

- at application level (mandatory)
- optionally at database level (recommended)

---

## 6. Unit–Resident Relationship

---

## 6.1 Rule

A Unit may have multiple Residents:

- owners
- tenants
- historical records

---

## 6.2 Structure

- Unit hasMany Residents
- Resident belongsTo Unit

---

## 6.3 Consistency Rule

All Residents of a Unit MUST share the same `community_id`.

---

## 7. Deletion Rules

---

## 7.1 Unit

Hard deletion MUST NOT be allowed if:

- it has residents
- it has history

Soft delete MUST be used.

---

## 7.2 Resident

Must support:

- soft delete
- future cleanup policies

---

## 8. Critical Validations

The system MUST validate:

- unit belongs to tenant
- resident belongs to tenant
- user (if linked) belongs to community
- uniqueness constraints
- single active tenant rule

---

## 9. Creation Rules

---

## 9.1 Unit

Must:

- assign `community_id` via TenantContext
- validate identifier uniqueness
- apply default values

---

## 9.2 Resident

Must:

- validate unit within tenant
- validate tenant consistency
- validate resident type
- enforce single active tenant rule

---

## 10. Update Rules

---

## 10.1 Unit

Allowed:

- status changes
- feature updates

NOT allowed:

- changing community

---

## 10.2 Resident

Allowed:

- activation/deactivation
- type changes
- financial visibility changes

Must validate consistency at all times.

---

## 11. Security Rules

The system MUST NOT:

- accept external `community_id`
- accept invalid `unit_id`
- break tenant isolation
- allow multiple active tenants
- create inconsistent relationships

---

## 12. Testing Requirements

Tests MUST validate:

- uniqueness within tenant
- tenant isolation
- resident creation
- cross-tenant assignment rejection
- single active tenant rule
- soft delete behavior

---

## 13. Expected Outcome

The system guarantees:

- consistent entities
- strict tenant isolation
- correct property modeling
- correct occupancy modeling
- enforceable business rules
- scalable data structure

---

## 14. Strategic Importance

This module defines:

- the real database structure
- operational data integrity
- future scalability

If done incorrectly:

- the system becomes inconsistent
- data integrity is compromised
- refactoring becomes inevitable

If done correctly:

- the system scales cleanly
- new modules integrate seamlessly