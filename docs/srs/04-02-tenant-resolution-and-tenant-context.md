
---

### Required Validations

- user is authenticated
- community exists
- membership exists
- membership is active (if applicable)

---

## 14. Relation to Roles and Permissions

Tenant resolution MUST occur before permission logic.

---

### Correct Order

1. authentication
2. tenant resolution
3. `TenantContext` initialization
4. role/permission resolution
5. module execution

---

### Key Rule

Permissions MUST NOT be evaluated before tenant is resolved.

---

## 15. Multi-Community Behavior

A user may belong to multiple communities.

---

### Rule

Only ONE tenant may be active per request.

---

### Implication

Tenant switching occurs between requests, not within the same request.

---

## 16. Multi-Unit Behavior

A user may own multiple units within a community.

---

### Key Distinction

- tenant = community
- unit = internal operational context

---

### Rule

Unit selection MUST NOT affect tenant resolution.

---

## 17. Data Isolation Rules

Once `TenantContext` is set:

All tenant-owned data MUST operate strictly within that tenant.

---

### Examples

- Units
- Residents
- Payments
- Packages
- PQRS
- Reservations

---

### Mandatory Rule

Cross-tenant data access MUST NOT be possible.

---

## 18. Controllers and Actions Rules

### Controllers

- assume tenant is already resolved
- remain thin
- do not re-resolve tenant

---

### Actions / Services

- may use `TenantContext`
- must validate resource ownership
- must fail if resource does not belong to active tenant

---

## 19. Errors and Edge Cases

### Invalid Slug
→ `404`

---

### User Not Belonging
→ `404`

---

### Disabled Community
→ `404`

---

### Missing Authentication
→ standard auth flow (no tenant execution)

---

### Invalid Path Fallback Usage in Production
→ MUST be restricted by environment

---

### Missing TenantContext Usage
→ controlled exception

---

## 20. Testing Requirements

The system MUST include tests validating:

- subdomain resolution
- path fallback resolution (controlled)
- invalid slug rejection
- membership validation
- correct `TenantContext` initialization
- fail-closed behavior
- no tenant enumeration

---

## 21. Observability Requirements

The system SHOULD log internally:

- received slug
- resolved community
- membership failures
- denied access reasons

---

### Important Rule

Internal logs MAY contain detail.

User-facing responses MUST remain minimal and secure.

---

## 22. Explicit Prohibitions

The system MUST NOT:

- trust `community_id` from requests
- rely on session as tenant authority
- allow frontend to define tenant
- expose path-based resolution publicly
- operate tenant-owned models without context
- reveal tenant existence

---

## 23. Expected Outcome

After execution of this module, the system guarantees:

- every tenant request has a valid active community
- user membership is verified
- `TenantContext` is properly initialized
- all subsequent logic operates within a secure tenant scope
- any inconsistency fails with `404`

---

## 24. Strategic Importance

This module defines the **core isolation boundary** of the multi-tenant system.

If this layer fails:

- data leakage occurs
- tenant isolation breaks
- security is compromised
- system trust is lost

This module is therefore critical and non-negotiable.