---
trigger: always_on
---

# UI and Module System — Sitios Urbanos

---

## 1. Purpose

This rule defines the standards for:

- user interface (UI)
- user experience (UX)
- module structure
- frontend responsibilities

The goal is to ensure:

- clarity
- consistency
- scalability
- usability under real conditions

---

## 2. Core Principle

The frontend is a **rendering layer**, not a decision-making layer.

---

## 3. Frontend Responsibilities

The frontend MUST:

- render data
- trigger actions
- display state
- provide feedback to the user

---

## 4. Forbidden Responsibilities

The frontend MUST NOT:

- define business rules
- define tenant context
- assign `community_id`
- perform authorization logic
- calculate financial values
- act as source of truth

---

## 5. Spanish-First UX (Colombia)

---

### Rule

All UI MUST be written in **Spanish (Colombia)**.

---

### Requirements

- use locally appropriate terminology
- avoid generic or foreign expressions
- ensure clarity for non-technical users

---

### Example

- Avoid: "Vacante"
- Prefer: "Disponible" or context-appropriate labels

---

## 6. Clarity Over Complexity

The UI MUST:

- be intuitive
- minimize cognitive load
- avoid unnecessary fields
- present only relevant information

---

## 7. Consistency Rules

All modules MUST follow consistent patterns:

---

### Forms

- consistent field structure
- clear labels
- validation feedback
- predictable submission behavior

---

### Tables

- consistent layout
- clear column naming
- predictable actions (edit, view, etc.)

---

### Actions

- consistent button placement
- clear call-to-action labels
- no ambiguous behavior

---

## 8. Module System (CRITICAL)

---

### Rule

The system MUST be modular.

---

### Requirements

- modules must be independently scalable
- modules must not be hardcoded into navigation
- modules must follow consistent structure

---

### Examples of modules

- Units
- Residents
- Security
- Finance
- Governance
- Ecosystem

---

## 9. Navigation Rules

---

### Rule

Navigation MUST:

- reflect user role
- reflect tenant context
- remain clean and minimal

---

### Forbidden

- overloaded menus
- irrelevant options
- mixed tenant/global navigation

---

## 10. Role-Based UI

---

### Rule

The UI MUST adapt based on:

- user role (admin, resident, guard, etc.)
- tenant context

---

### Behavior

- show only relevant modules
- hide unauthorized actions
- maintain clarity

---

## 11. UX Under Stress (CRITICAL)

---

### Applies to

- panic button
- visitor validation
- security flows

---

### Rule

The UI MUST:

- minimize steps
- provide immediate feedback
- avoid complex flows
- be usable under pressure

---

## 12. Feedback and State

The system MUST provide:

- success feedback
- error feedback
- loading states
- validation messages

---

### Rule

Users must always understand what is happening.

---

## 13. Error Handling UX

---

### Rule

Errors MUST be:

- clear
- actionable
- non-technical

---

### Forbidden

- vague errors
- system-level messages exposed to user

---

## 14. Data Representation

---

### Rule

Data MUST be:

- readable
- structured
- contextualized

---

### Example

Instead of raw values:

- show labels
- show relationships
- show relevant context

---

## 15. Performance and Responsiveness

---

### Rule

The UI MUST:

- load quickly
- avoid unnecessary re-renders
- provide smooth interactions

---

## 16. Accessibility (Basic Level)

---

### Rule

The UI MUST:

- be readable
- use clear contrast
- avoid overly complex interactions

---

## 17. Forbidden Patterns

The UI MUST NOT:

- expose internal IDs unnecessarily
- require technical knowledge
- depend on perfect user input
- break when data is incomplete

---

## 18. Testing Requirements

The system MUST validate:

- UI consistency across modules
- correct rendering per role
- correct behavior under tenant context
- usability in critical flows

---

## 19. Strategic Importance

This rule ensures:

- user adoption
- product clarity
- scalability of modules
- professional perception

---

## 20. Consequence of Violation

Breaking this rule leads to:

- confusing UX
- user rejection
- increased support burden
- inconsistent product behavior

---

## 21. Final Principle

The system must behave as:

- a clear system
- a consistent system
- a scalable UI system

Not as a collection of disconnected screens.