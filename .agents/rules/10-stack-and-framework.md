---
trigger: always_on
---

# Stack & Framework Rules — Sitios Urbanos

This document defines the **mandatory technical stack and development standards**.

These rules are NOT suggestions. They are enforced constraints.

---

## Official Stack (NON-NEGOTIABLE)

The project MUST use:

- Laravel 13
- PHP 8.4+
- Vue 3
- Inertia.js
- Tailwind CSS 4
- Vite
- PostgreSQL
- Laravel Reverb (for real-time features)

You are NOT allowed to:

- downgrade versions
- replace frameworks
- introduce alternative stacks (React, Angular, etc.)

---

## Laravel Structure Rules

The project must follow a **clean and scalable architecture**.

### Controllers

Controllers must be:

- thin
- focused on request/response
- free of business logic

Controllers SHOULD:

- validate requests
- call Actions or Services
- return responses

Controllers MUST NOT:

- contain complex logic
- perform heavy data processing
- handle multi-step workflows

---

### Business Logic Placement

Business logic must live in:

- `Actions` (preferred for use-case execution)
- `Services` (shared logic)

Examples:

- CreateInvoiceAction
- ProcessPaymentAction
- RegisterParcelAction

---

### Models

Eloquent models must:

- represent data structure
- define relationships
- include scopes

Models MUST NOT:

- contain heavy business logic
- orchestrate workflows
- access external services

---

### Validation

Validation must be handled using:

- Form Requests (preferred)

Validation MUST NOT be embedded directly in controllers when complex.

---

### Database

- PostgreSQL is mandatory
- Use migrations for all schema changes
- Use JSONB for flexible structures where appropriate

---

## Frontend Rules (Vue + Inertia)

### General Principle

The frontend is a **presentation layer**, not the source of truth.

---

### Vue Components

Components must:

- be modular
- be reusable
- be clean and readable

Components MUST NOT:

- contain business logic
- make critical decisions (permissions, pricing, etc.)
- assume data correctness

---

### State Management

- Prefer server-driven state via Inertia
- Avoid unnecessary global state complexity

---

### Routing

- Laravel handles routing
- Inertia connects backend routes to Vue pages

---

## API & Backend Communication

- All critical logic must be executed in backend
- Frontend must consume structured responses
- Never trust frontend input without validation

---

## Real-Time (Reverb)

- Reverb is the only approved real-time solution
- Use events and broadcasting
- Do not introduce external WebSocket services

---

## Code Quality

The code must be:

- readable
- maintainable
- predictable

---

### Naming Conventions

Use clear and explicit names:

- `CreateCommunityAction`
- `GenerateInvoiceAction`
- `AssignParcelToUnitAction`

Avoid generic names like:
- `Helper`
- `Utils`
- `Manager`

---

### File Organization

Respect project structure defined in:

- `APP-STRUCTURE.md`

Do NOT:

- create arbitrary folders
- duplicate logic across layers

---

## Package Usage

You are NOT allowed to install new packages unless:

1. There is a clear justification
2. It is approved

---

## Error Handling

- Use Laravel exception handling
- Avoid silent failures
- Always return meaningful responses

---

## Logging

- Use Laravel logging system
- Log critical flows:
  - payments
  - webhooks
  - security events

---

## Security Basics

- Never trust client input
- Always validate
- Always authorize actions

---

## Language Policy

Technical instructions, architecture discussions, and internal agent planning may be written in English.

However, user-facing application content MUST be Spanish-first.

This includes:

- navigation labels
- dashboard texts
- form labels
- validation messages shown to users
- notifications shown to users
- page titles
- empty states
- button labels

You MUST NOT hardcode English user-facing texts in the application UI unless explicitly approved.

Code-level naming should remain technically consistent and preferably in English for maintainability, but visible product UI must be in Spanish.

---

## Agent Responsibility

When implementing code:

- follow these rules strictly
- do not improvise architecture
- do not introduce alternative patterns

If unsure:
→ STOP and ask