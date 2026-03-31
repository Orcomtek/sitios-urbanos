---
trigger: always_on
---

# Quality, Testing & Review Rules — Sitios Urbanos

This document enforces the **RIGOR methodology** inside the agent behavior.

These rules are mandatory.

---

## Core Principle

No implementation is valid without:

- planning
- validation
- review

---

## Mandatory Workflow (RIGOR)

Before ANY implementation, you MUST:

1. Identify the RIGOR block
2. Generate a Task List
3. Generate an Implementation Plan
4. List affected files
5. Identify risks
6. WAIT for approval

You MUST NOT:

- start coding immediately
- skip planning
- assume requirements

---

## Task List Requirements

A Task List must:

- be structured
- be clear
- be executable
- break work into steps

---

## Implementation Plan Requirements

An Implementation Plan must include:

- execution order
- architecture alignment
- dependencies
- validation strategy

---

## Approval Requirement

You MUST:

- wait for human approval before executing
- not proceed automatically

---

## Implementation Phase

During implementation, you MUST:

- follow architecture rules
- follow stack rules
- respect multi-tenancy
- respect financial constraints

---

## Post-Implementation Requirements

After implementation, you MUST provide:

1. Summary of changes
2. Files modified/created
3. Tests executed
4. Test results
5. Manual validation steps
6. Remaining risks

---

## Testing Rules

You MUST:

- test critical flows
- validate edge cases when relevant
- ensure no breaking changes

---

## Validation Philosophy

Code is NOT complete when:

- it compiles
- it runs

Code is complete when:

- it is validated
- it is reviewed
- it is understood

---

## Error Transparency

You MUST:

- report errors clearly
- not hide issues
- not fake success

---

## Documentation Awareness

If implementation affects behavior, you MUST:

- check if documentation needs updates
- report documentation gaps

---

## Risk Awareness

You MUST:

- identify risks before implementation
- report unresolved risks after implementation

---

## Agent Responsibility

You MUST:

- follow RIGOR strictly
- never skip steps
- never rush execution

If unsure:
→ STOP and ask