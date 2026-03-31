---
trigger: always_on
---

# Safety & Local Operations Rules — Sitios Urbanos

This document defines what the agent is allowed and NOT allowed to do in the local environment.

These rules are critical to avoid destructive or unsafe actions.

---

## Core Principle

The agent MUST NOT perform any action that can:

- break the local environment
- corrupt data
- expose secrets
- alter infrastructure unintentionally

---

## .env Protection (CRITICAL)

You MUST NOT:

- modify `.env`
- expose environment variables
- change database credentials
- alter API keys

If a change is needed:
→ ask for explicit approval

---

## Database Safety Rules

You MUST NOT:

- drop tables
- truncate tables
- run destructive migrations
- alter production-like data

Allowed actions:

- create migrations
- propose schema changes
- suggest safe updates

---

## Migration Rules

Migrations MUST:

- be reversible
- be explicit
- avoid data loss

You MUST NOT:

- delete existing columns without approval
- rename critical fields without migration strategy

---

## Command Execution Rules

You MUST NOT run or suggest running commands that:

- delete data
- reset database without warning
- force destructive operations

Examples to avoid:

- `php artisan migrate:fresh`
- `php artisan db:wipe`
- `rm -rf`

Unless explicitly approved.

---

## Local Services Protection

The agent MUST NOT:

- modify Herd configuration
- change DBngin settings
- alter local ports
- install/remove system services

---

## File System Safety

You MUST NOT:

- delete large parts of the project
- overwrite critical files blindly
- remove configuration files

---

## External Services

You MUST NOT:

- expose API keys
- simulate real payment processing without sandbox
- send real notifications without approval

---

## Secrets Handling

Secrets MUST:

- never be hardcoded
- never be logged
- never be exposed

---

## Destructive Actions Policy

If an action can:

- delete data
- overwrite important structures
- break environment

You MUST:

1. clearly explain the risk
2. ask for approval
3. wait for confirmation

---

## Agent Responsibility

You MUST:

- act conservatively
- protect data integrity
- prioritize safety over speed

If unsure:
→ STOP and ask