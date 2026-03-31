# RIGOR 1.1 — T2 Subphase B (List Communities - User Portal)

## Goal

Implement the first multi-tenant boundary of the system by allowing authenticated users to list ONLY the communities they belong to.

This establishes:
- Tenant isolation via pivot relationship
- First real domain model (Community)
- First secure data boundary

---

## Scope

This subphase includes:

- Community model
- community_user pivot
- User ↔ Community relationships
- GetUserCommunitiesAction
- CommunityController@index
- Route: GET /comunidades (auth protected)
- Vue page: Communities/Index.vue
- Pest tests for tenant isolation

---

## Out of Scope

- No Control Plane (superadmin)
- No tenant switching yet
- No subdomain routing yet
- No permissions system beyond pivot role
- No UI complexity (simple list only)

---

## Critical Rules

- Community model MUST NOT have global tenant scope
- Data isolation MUST happen via pivot (user_id ↔ community_id)
- No business logic in controllers
- Spanish-only UI
- Follow RIGOR (no implementation without approval)

---

## Task List

### B1 — Database

- [ ] Create communities table
- [ ] Create community_user pivot table
- [ ] Add constraints and enums

### B2 — Models

- [ ] Community model
- [ ] User relationships
- [ ] Factories

### B3 — Actions

- [ ] GetUserCommunitiesAction

### B4 — Controller & Routes

- [ ] CommunityController@index
- [ ] Route /comunidades (auth)

### B5 — Frontend

- [ ] Communities/Index.vue
- [ ] Spanish UI
- [ ] Empty state

### B6 — Testing

- [ ] Pest test: auth required
- [ ] Pest test: user sees own communities
- [ ] Pest test: isolation enforced