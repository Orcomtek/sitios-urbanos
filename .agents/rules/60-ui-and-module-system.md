---
trigger: always_on
---

# UI & Module System Rules — Sitios Urbanos

This document defines how UI, dashboards, navigation, and modules must behave.

These rules are critical for scalability.

---

## Core Principle

The UI must be:

- dynamic
- role-aware
- module-driven
- backend-controlled

---

## No Hardcoding Rule (CRITICAL)

You MUST NOT:

- hardcode sidebar menus
- hardcode dashboards
- hardcode modules

Everything must be driven by backend configuration.

---

## Module System (FOUNDATIONAL)

The system must support modules such as:

- finance
- security
- governance
- marketplace (future)
- documents
- reservations

Each module:

- can be enabled/disabled per community
- can expose features
- can modify UI behavior

---

## Capabilities-Based UI

The backend must define:

- what the user can see
- what modules are active
- what actions are allowed

Frontend must ONLY render based on this.

---

## Sidebar Behavior

Sidebar MUST be:

- dynamic
- role-based
- module-aware

It must:

- show only relevant sections
- hide unavailable modules
- adapt to permissions

---

## Dashboard Behavior

Dashboards MUST:

- change based on role (admin, resident, guard)
- change based on enabled modules
- display relevant widgets only

You MUST NOT:

- create a single static dashboard for all users

---

## Widget System

Dashboards must be composed of widgets such as:

- announcements
- pending payments
- parcels
- reservations
- PQRS status

Widgets MUST:

- be modular
- be reusable
- be dynamically injected

---

## Backend → Frontend Contract

Backend must provide structured data:

- modules enabled
- permissions
- navigation structure
- dashboard configuration

Frontend MUST:

- render based on backend payload
- not assume structure

---

## Role-Based UI

Different roles MUST have different experiences:

- Admin → full control
- Resident → personal view
- Guard → operational view

---

## UI Consistency Rules

You MUST:

- follow design system
- use consistent spacing
- use consistent typography
- use consistent iconography

---

## Performance Considerations

UI MUST:

- avoid unnecessary re-renders
- use efficient data loading
- cache when appropriate

---

## Future Scalability

The UI must be ready for:

- new modules
- new roles
- new widgets

Without major refactors.

---

## Agent Responsibility

You MUST:

- design UI as a system, not pages
- avoid shortcuts
- ensure scalability

If unsure:
→ STOP and ask