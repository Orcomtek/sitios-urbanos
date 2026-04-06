# 04-03 — Users, Roles and Access Model  
## System Requirements Specification (SRS)  
### Sitios Urbanos

---

## 1. Purpose

This document defines the system behavior for:

- global user model
- community-based roles
- multi-role support
- multi-community support
- relationships between users, residents, and units
- base access control within tenant runtime
- owner–tenant relationship
- occupancy rules
- activation/deactivation lifecycle
- retention and cleanup policies for operational profiles

This module defines the operational identity structure of the platform.

---

## 2. Core Principle

Sitios Urbanos MUST clearly separate two concepts:

### 2.1 User (User)
Global digital identity.

### 2.2 Resident (Resident)
Operational identity within a community.

### Official Rule

The platform MUST NOT assume that every operational person is a digital user.

Therefore:

- User = digital access identity  
- Resident = operational/occupancy identity  

This separation is mandatory.

---

## 3. Global Identity Model

### 3.1 Definition of User

A User represents a global digital identity in the platform.

---

### 3.2 Key Characteristics

A User:

- does NOT belong to a single fixed community
- may belong to multiple communities
- may have multiple roles
- may be linked to one or more residents
- may be associated with multiple units
- may exist without being the main occupant of a unit

---

### 3.3 Responsibilities

A User is responsible for:

- authentication
- accessing the platform
- navigating between communities
- navigating between roles
- selecting operational unit context
- executing authorized actions
- providing usage traceability

---

## 4. Operational Identity Model

### 4.1 Definition of Resident

A Resident represents a person operationally associated with a unit within a community.

---

### 4.2 Can Represent

- owner
- tenant
- primary occupant
- census record
- non-activated user

---

### 4.3 Official Rule

The primary relationship of occupancy MUST be modeled through Resident, NOT directly through User.

---

### 4.4 Justification

Because:

- not every resident has a digital account
- not every occupant should authenticate
- census and operational data must exist independently of login
- the system must support lifecycle evolution (activation, validation, history)

---

## 5. Resident to User Relationship

### 5.1 Rule

Resident.user_id MUST be nullable.

---

### 5.2 Meaning

- a resident may exist without a user account
- a resident may later be linked to a user
- the system does not depend on universal user creation

---

### 5.3 Key Rule

This relationship is for digital linkage, NOT for defining occupancy.

---

## 6. Community Relationship

### 6.1 General Rule

Users relate to communities through a pivot structure (for example: community_user).

---

### 6.2 Purpose of the Pivot

This relationship represents:

- user membership in a community
- role(s) within that community
- membership status
- optional linkage to units
- minimum traceability

---

### 6.3 Expected Fields

The pivot should support at least:

- user_id
- community_id
- role
- unit_id (nullable)
- status

---

## 7. Base Roles

### 7.1 Approved Roles

- admin
- resident
- guard

---

### 7.2 Future Roles

The system MUST support future roles such as:

- provider
- accountant
- board_member
- others

---

### 7.3 Rule

The access model MUST NOT be hardcoded only for initial roles.

---

## 8. Multi-Role Support

### 8.1 Rule

A user MAY have multiple roles within the same community.

---

### 8.2 Examples

- resident + provider
- resident + admin
- guard + other operational roles

---

### 8.3 Cross-Community Roles

A user MAY have different roles across different communities.

---

### 8.4 UX Rule

The system MUST NOT require re-login for role switching.

---

## 9. Unit Relationship

### 9.1 Rule

Operational relationship with units MUST be modeled through Resident.

---

### 9.2 Structure

- Community has many Units  
- Unit has many Residents  
- Resident may link to User  

---

### 9.3 Rule

Occupancy MUST NOT depend solely on User.

---

## 10. Owner vs Tenant

### 10.1 Rule

The system MUST distinguish between:

- owner
- tenant

---

### 10.2 Impact Areas

- financial visibility
- permissions
- operational access
- notifications
- occupancy control
- future leasing logic

---

## 11. Owner–Tenant Relationship

### 11.1 Owner Capabilities

An owner MUST be able to:

- create tenant
- update tenant
- activate/deactivate tenant
- remove tenant access
- define financial visibility
- define operational access

---

### 11.2 Critical Rule

Each unit MUST have ONLY ONE active tenant at a time.

---

### 11.3 Clarification

- historical tenants are allowed
- only one active tenant is allowed

---

## 12. Financial Visibility Rule

The owner MUST be able to define whether the tenant:

- sees administration fees as their responsibility
- or not

---

## 13. Activation and Deactivation

### 13.1 Activation

Occurs when a resident is linked to a valid user with permissions.

---

### 13.2 Deactivation

Must be supported for:

- tenants
- inactive residents
- expired access profiles

---

### Rule

Deactivation MUST NOT imply deletion.

---

## 14. Retention and Cleanup

### 14.1 General Rule

The platform MUST support retention and cleanup policies.

---

### 14.2 Actions

- deactivate
- archive
- delete (if allowed)

---

### 14.3 Example Policy

If a tenant has no activity for 180 days:

- action MUST be configurable:
  - deactivate
  - archive
  - delete

---

### 14.4 Rule

Retention MUST be configurable per tenant agreement.

---

## 15. Access Resolution Order

The system MUST evaluate:

1. authentication  
2. tenant resolution  
3. TenantContext initialization  
4. community membership  
5. roles  
6. permissions  

---

### Rule

Permissions MUST NOT be evaluated before tenant resolution.

---

## 16. Multi-Community

### Rule

A user MAY belong to multiple communities.

---

### Implication

Access MUST always be evaluated per active tenant.

---

## 17. Multi-Unit

### Rule

A user MAY be associated with multiple units.

---

### UX Requirement

The system MUST allow unit selection without requiring re-login.

---

### Important Distinction

- tenant = community  
- unit = internal context  

---

## 18. Security Rules

### Prohibited

The system MUST NOT:

- trust frontend role data
- trust unit_id without validation
- assume all residents are users
- allow multiple active tenants per unit
- mix tenant, role, and unit logic incorrectly

---

### Required Validations

- tenant consistency
- unit ownership
- relationship integrity
- tenant isolation
- permission correctness

---

## 19. Edge Cases

### User without Resident
Allowed.

---

### Resident without User
Allowed.

---

### Multiple Residents per User
Allowed if coherent.

---

### Tenant Change
Must update active state, not accumulate active tenants.

---

## 20. Testing Requirements

Tests MUST validate:

- multi-community users
- multi-role users
- multi-unit users
- resident without user
- user-resident linkage
- single active tenant per unit
- tenant deactivation
- financial visibility rules
- invalid relationship blocking

---

## 21. Observability

The system SHOULD log:

- role assignments
- membership changes
- resident linkage
- tenant activation/deactivation
- occupancy transitions
- cleanup executions

---

### Rule

Internal logs MAY be detailed.  
User responses MUST remain secure.

---

## 22. Expected Outcome

The system guarantees:

- clear separation between User and Resident
- roles resolved per tenant
- multi-role support
- multi-unit support
- owner–tenant model enforced
- one active tenant per unit
- configurable retention policies
- secure access model

---

## 23. Strategic Importance

This module connects:

- identity
- access
- occupancy
- financial visibility
- tenant relationships
- operational logic

If this module fails:

- permissions break
- data relationships break
- UX breaks
- financial flows break

This module is critical for system integrity.