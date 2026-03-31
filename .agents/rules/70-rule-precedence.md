---
trigger: always_on
---

# Rule Precedence — Sitios Urbanos

This document defines which rules take priority when multiple instruction sources exist.

These precedence rules are mandatory.

---

## Core Principle

If multiple rule sources exist, the agent MUST follow the highest-priority source.

If there is any conflict:
→ follow the higher-priority rule
→ if still unclear, STOP and ask

---

## Official Priority Order

### Priority 1 — Sitios Urbanos project documents

These are the highest authority:

- docs/PRD.md
- docs/MVP-BOUNDARY.md
- docs/ARCHITECTURE.md
- docs/APP-STRUCTURE.md
- docs/CODE-CONVENTIONS.md
- docs/PROJECT-RULES.md
- docs/BACKLOG-RIGOR.md
- docs/ANTIGRAVITY-PROJECT-RULES.md

---

### Priority 2 — Sitios Urbanos agent rules

These are workspace-specific agent rules:

- .agents/rules/00-product-context.md
- .agents/rules/10-stack-and-framework.md
- .agents/rules/20-architecture-and-tenancy.md
- .agents/rules/30-finance-and-security.md
- .agents/rules/40-quality-testing-and-reviews.md
- .agents/rules/50-safety-and-local-ops.md
- .agents/rules/60-ui-and-module-system.md
- .agents/rules/70-rule-precedence.md

---

### Priority 3 — Project-level agent/tooling files

These are helpful, but subordinate to Sitios Urbanos rules:

- AGENTS.md
- GEMINI.md
- Laravel Boost guidelines
- skill-specific helper files

---

### Priority 4 — Generic framework defaults

These include:

- general Laravel conventions
- default framework guidance
- package author recommendations
- tooling defaults

These are valid ONLY when they do not conflict with higher-priority rules.

---

## Conflict Resolution Rules

If Laravel Boost, AGENTS.md, GEMINI.md, skills, or tooling suggest something that conflicts with:

- the MVP scope
- multi-tenancy rules
- financial integrity rules
- UI/module system rules
- RIGOR workflow

You MUST follow Sitios Urbanos rules.

---

## RIGOR Priority

If any rule source suggests faster execution, less detail, or skipping planning,
you MUST ignore that and follow RIGOR.

RIGOR always requires:

- one task at a time
- explicit planning
- explicit approval
- validation before continuation

---

## Business-Critical Priority

For these domains, Sitios Urbanos rules always override generic framework advice:

- multi-tenancy
- finance
- payments
- ledger
- security
- role-based UI
- modules
- governance MVP

---

## Documentation Conflicts

If documentation or rule files disagree, you MUST:

1. stop
2. identify the conflict clearly
3. ask for clarification

You MUST NOT guess.

---

## Agent Responsibility

You MUST:

- respect precedence
- not merge conflicting instructions silently
- not prefer convenience over project rules

If unsure:
→ STOP and ask