# 04-01 — Authentication and Tenant Entry  
## System Requirements Specification (SRS)  
### Sitios Urbanos

---

## 1. Purpose

This document defines the exact system behavior for:

- user authentication
- community discovery
- private community selection
- secure redirection into tenant runtime
- initial entry into the Sitios Urbanos SaaS ecosystem

This module is the critical entry point of the platform.

It must fully respect:

- subdomain-based multi-tenancy
- email-first authentication
- private tenant discovery
- fail-closed security behavior

---

## 2. Scope

This SRS part covers:

- login flow
- credential validation
- session initialization
- community discovery after login
- private community selector
- selection validation
- redirection to tenant runtime
- initial handoff from Control Plane to Tenant Runtime

This document does NOT yet define:

- tenant resolution internals
- TenantContext lifecycle
- role resolution internals
- module access behavior inside tenant runtime

Those belong to later SRS parts.

---

## 3. Core Concept

Sitios Urbanos uses **Email-First Authentication**.

This means:

1. the user authenticates first as a global identity
2. the system then discovers which communities the user belongs to
3. the user selects one allowed community
4. the system redirects the user to the correct tenant subdomain

This prevents:

- public exposure of communities
- insecure tenant guessing
- poor tenant discovery UX
- mixing identity resolution with tenant resolution

---

## 4. Official Entry Domain

The official global entry domain is:

app.sitiosurbanos.com

This is the Control Plane entry point for all authenticated users.

The system must not ask users to guess or manually enter tenant subdomains before authentication.

---

## 5. Official Flow

### Step 1 — Login Page

The user accesses:

https://app.sitiosurbanos.com/login

The page must display:

- email input
- password input
- recovery path
- standard auth UI

The page must NOT display:

- tenant selector
- public community dropdown
- community slug input

---

### Step 2 — Credential Submission

The user submits credentials.

The backend validates:

- email exists
- password is correct
- user is active
- user is allowed to access the platform

If credentials are invalid, authentication fails.

---

### Step 3 — Session Initialization

If credentials are valid, the backend initializes a valid authenticated session.

This session must be compatible with future redirection into tenant subdomains.

This implies the authentication strategy must support shared session or secure cross-subdomain access.

---

### Step 4 — Community Discovery

After login, the backend retrieves the communities associated with the authenticated user.

The source of truth is the membership relationship, such as:

- `community_user`

The system retrieves, at minimum:

- community id
- community display name
- community slug
- membership status
- visible role metadata if needed for selector display
- duplicate-name descriptor if needed

---

### Step 5 — Private Community Selector

If the user belongs to multiple communities, the system must show a **private community selector**.

This selector must only be visible after successful authentication.

It must not expose communities outside the user’s own memberships.

Each option should display enough information to avoid ambiguity, for example:

- community display name
- city
- short address
- or other secondary descriptor

This is especially important when multiple communities share the same visible name.

---

### Step 6 — Single Community Shortcut

If the user belongs to exactly one valid community, the system may skip the selector and redirect automatically.

This behavior is allowed as a UX optimization.

---

### Step 7 — Community Selection Validation

When the user selects a community, the backend must validate that:

- the selected community belongs to the authenticated user
- the membership is active
- the target community is valid for runtime access

The backend must never trust tenant selection solely from frontend input.

---

### Step 8 — Redirect to Tenant Runtime

After validation, the system redirects the user to:

https://{communitySlug}.app.sitiosurbanos.com

Example:

https://altosdelparque.app.sitiosurbanos.com

From this point onward, tenant runtime logic begins.

---

## 6. Functional Requirements

### 6.1 Login

The system must allow a user to log in using:

- email
- password

Future expansion may support additional methods, but email/password is the required baseline.

---

### 6.2 Private Community Discovery

The system must retrieve only communities explicitly associated with the authenticated user.

No public or guessed community discovery is allowed.

---

### 6.3 Community Selection

The system must allow selection only from the authenticated user’s valid community memberships.

---

### 6.4 Automatic Redirect

The system must support automatic redirect when there is exactly one accessible community.

---

### 6.5 Multi-Community Support

The system must support users belonging to multiple communities.

The community selector must remain private and scoped to the authenticated user.

---

### 6.6 Duplicate Community Names

The system must support multiple communities with the same display name.

Rules:

- display name may repeat
- slug must be globally unique
- selector UI must show a secondary descriptor when duplicate names exist

---

## 7. Security Requirements

### 7.1 No Public Community Selector

The system must never show a public tenant selector before authentication.

Reason:

- privacy
- security
- anti-enumeration
- better UX

---

### 7.2 No Tenant Guessing as Official Flow

The official entry flow must not depend on users guessing subdomains before authentication.

Direct access to a tenant subdomain may exist technically, but it is not the primary or recommended entry flow.

---

### 7.3 Backend Validation of Selection

Community selection must always be validated server-side.

The frontend is never the authority.

---

### 7.4 Fail-Closed Principle

If community selection is invalid or inconsistent, the system must deny the operation.

The system must not silently recover into another tenant.

---

## 8. Session Requirements

### 8.1 Session Continuity

Authentication initiated in:

app.sitiosurbanos.com

must support secure continuity into:

{communitySlug}.app.sitiosurbanos.com

---

### 8.2 Shared Session Strategy

The platform must support one of the following:

- shared cookie session across subdomains
- secure token exchange strategy

Preferred approach:

- secure shared session cookies across `.sitiosurbanos.com`

---

### 8.3 Session Security

The session mechanism must support:

- secure cookies
- HttpOnly
- proper SameSite settings
- production-grade secure behavior

Detailed implementation belongs to technical architecture, but this requirement is mandatory.

---

## 9. API / Endpoint Baseline

### 9.1 Login

`POST /login`

#### Input
- email
- password

#### Result
- valid session
- redirect intent toward community selection or automatic redirect

---

### 9.2 Fetch User Communities

This may be represented as:

`GET /api/user/communities`

or equivalent server-side hydration through Inertia / backend rendering.

It must return only the authenticated user’s communities.

---

### 9.3 Select Community

This may be represented as:

`POST /select-community`

#### Input
- community_id

#### Output
- validated redirect URL

The backend must validate that the selected community belongs to the user.

---

## 10. Error Handling

### 10.1 Invalid Credentials
Result:
- authentication error
- remain in login flow

---

### 10.2 User Without Communities
Result:
- controlled post-login state
- clear message
- optional contact/support guidance

The system must not crash or enter invalid runtime behavior.

---

### 10.3 Invalid Community Selection
Result:
- deny operation
- do not redirect
- return controlled authorization error state

---

### 10.4 Invalid Tenant Runtime Access After Redirect
Handled later by tenant resolution layer, but expected result is:

- `404`

The platform must avoid revealing whether the tenant exists for unauthorized users.

---

## 11. UX Requirements

### 11.1 Login UX
The login page must be:

- simple
- fast
- identity-first
- free from tenant complexity

---

### 11.2 Community Selector UX
The selector must be:

- private
- unambiguous
- clear when duplicate names exist
- optimized for users with multiple communities

---

### 11.3 Redirect UX
Redirection into tenant runtime should feel:

- immediate
- natural
- safe
- consistent

---

## 12. Explicit Non-Goals

This module must NOT:

- expose communities publicly
- use tenant path-based routing as official entry
- trust frontend community selection without validation
- treat session as tenant authority
- mix authentication flow with tenant runtime business logic

---

## 13. Dependencies

This module depends on:

- user identity model
- community model
- membership relationship (`community_user` or equivalent)
- global auth/session system
- domain architecture already defined
- control plane vs tenant runtime separation already defined

---

## 14. Expected Outcome

At the end of this flow, the system must guarantee:

- the user is authenticated
- the user has selected a valid community
- redirection to the correct tenant subdomain has occurred
- tenant runtime can safely begin its own resolution logic

---

## 15. Strategic Importance

This module defines:

- how users enter the SaaS
- how private tenant discovery works
- how multi-community support begins
- the first security boundary of the platform

If this layer is weak, the entire multi-tenant platform becomes fragile.

This module is therefore foundational and non-negotiable.