# METHODOLOGY RIGOR — Official Operating Framework (v4.1)

## 1. Origins & Purpose

**RIGOR** stands for:
- **R**evisión
- **I**terativa
- **G**uiada
- **O**ficial
- **R**igurosa

Created by **Camilo Alcalá** (CEO and Senior Solution Architect at Orcomtek), RIGOR is the official implementation methodology used to design, build, validate, and evolve complex software systems under strict architectural control.

**The Context of Creation:** This methodology was specifically forged to govern and orchestrate advanced AI coding agents (such as Antigravity and Windsurf) in enterprise-grade projects. While fully applicable and highly recommended for human-only engineering teams, RIGOR was born to solve the unique challenges of AI-assisted development: preventing AI agents from hallucinating, assuming context, over-engineering, or drifting from the core architecture.

Its ultimate purpose is to prevent:
- architectural drift
- premature coding
- hidden assumptions
- scope contamination
- fragile execution
- loss of continuity across sessions, agents, or team members

---

## 2. What RIGOR Means in Practice

When a project operates under RIGOR, the following practical mandates are automatically assumed and enforced:

1. **Phased & Block-Based Work:** We do not skip steps. We do not mix multiple architectural layers at once. Every block is validated before moving to the next.
2. **Zero Assumptions:** No knowledge is taken for granted. Explanations must be step-by-step. Environments and configurations are confirmed before advancing.
3. **Official Documentation First:** Development relies strictly on official documentation (Laravel, Vue, Tailwind, Herd, etc.). Forum noise or generic YouTube tutorials are discarded unless absolutely necessary.
4. **Plan Before Execution:** For any meaningful task: Task List → Implementation Plan → Review → Execution.
5. **Critical Agent Review:** The Architect analyzes every AI proposal, compares it against the PRD/SRS, architecture, and commercial decisions, and corrects deviations *before* approving execution.
6. **Controlled Execution:** Only the necessary scope is approved. Premature complexity and unnecessary packages are strictly avoided.
7. **Validation is Technical and Manual:** Routes, views, migrations, tests, diffs, walkthroughs, and visual verification.
8. **Clean Commits:** Every block leaves a healthy, verified return point. We do not build on top of unverified code.
9. **Continuous Alignment:** Every action must align with the PRD/SRS, the established architecture, the backlog, and the previously frozen commercial/functional decisions.

---

## 3. Core Philosophy

RIGOR is based on one central principle:

> Correct architecture must exist before implementation expands.

The methodology assumes that the biggest risks in complex systems are usually not syntax errors, but rather:
- wrong assumptions
- incorrect boundaries
- premature abstractions
- hidden coupling
- poor execution order
- weak validation discipline

RIGOR therefore prioritizes:
- explicit planning before coding
- single-task execution
- frozen decisions
- validation before progression
- architecture as source of truth
- implementation discipline over speed

---

## 4. When RIGOR Should Be Used

RIGOR is recommended when a project has one or more of the following characteristics:
- multi-tenant architecture
- financial logic
- security-sensitive access rules
- modular SaaS product design
- multiple user roles
- complex domain rules
- long implementation horizon
- AI-assisted execution with agents
- requirement for precise continuity across sessions

RIGOR is not intended for throwaway prototypes or trivial one-file experiments.

---

## 5. The Main Operating Rule

The methodology has one hard operational rule:

> No implementation happens without prior planning and approval.

This means every meaningful implementation step must pass through:
1. **Task definition**
2. **Implementation plan**
3. **Explicit approval**
4. **Execution**
5. **Validation**
6. **Closing Audit (The Hard Gate)**
7. **Checkpoint or closure**

No step may skip that sequence unless explicitly authorized.

---

## 6. Core Methodological Principles

### 6.1 Architecture Before Code
The project must first be understood structurally before code is written.

### 6.2 One Task at a Time
Execution must remain granular. Large uncontrolled batches are prohibited.

### 6.3 No Silent Assumptions
If a requirement, constraint, or architectural implication is unclear, it must be clarified before implementation.

### 6.4 Frozen Decisions Matter
Once architecture decisions are approved, they become frozen and must not be reopened casually.

### 6.5 Validation Is Mandatory
A task is not complete because code exists. It is complete only after validation.

### 6.6 Fail Closed, Not Open
In sensitive systems, uncertainty must result in controlled failure, not permissive behavior.

### 6.7 Implementation Must Respect Scope
A task may not absorb future work simply because it seems convenient.

### 6.8 Continuity Must Be Preserved
Critical project knowledge must live in versioned documentation, not only in conversation memory.

---

## 7. RIGOR Lifecycle

Every meaningful block of work should move through the following lifecycle:

### Phase A — Planning
The block is analyzed and defined. No code is written yet.

### Phase B — Approval
The implementation approach is approved explicitly.

### Phase C — Execution
Only the approved scope is implemented.

### Phase D — Validation
The result is verified technically and, where needed, manually.

### Phase E — Closure (The Hard Gate)
The block cannot be closed until it passes the strict **Closing Audit Checklist**. Once passed, the block is documented, committed, and marked as completed.

---

## 8. Required Artifacts in RIGOR

RIGOR relies on a set of artifacts that make execution traceable and portable.

### 8.1 Project-Level Documents
These define the product and architecture baseline.
Examples: `PRD.md`, `MVP-BOUNDARY.md`, `ARCHITECTURE.md`, `APP-STRUCTURE.md`, `CODE-CONVENTIONS.md`, `PROJECT-RULES.md`, `BACKLOG-RIGOR.md`

### 8.2 Block Documents
These define execution by bounded implementation unit.
Examples: task lists, validation checklists, planning checkpoints, subphase execution docs

### 8.3 Agent Rules
These constrain AI-assisted execution.
Examples: `.agents/rules/...`, project-specific instructions, precedence rules, safety rules, UI/module rules, architecture rules

### 8.4 Continuity & Contingency Artifacts
These preserve project state across chats and agents.
Examples: 
- `PROJECT-CONTINUITY-HANDOFF.md`
- `PROJECT-CONTINGENCIES.md`
- decision registries
- bootstrap prompts for new chats

### 8.5 Version Control Checkpoints
Clean commits must accompany meaningful validated milestones.

---

## 9. Standard RIGOR Workflow Per Task

Each task should follow this exact pattern:

### Step 1 — Identify the Task
The task must be small, concrete, and bounded.
### Step 2 — Generate Task List
The work must be decomposed into explicit substeps.
### Step 3 — Generate Implementation Plan
The execution logic, files affected, risks, and order of operations must be explained.
### Step 4 — Review Against Frozen Architecture
The plan must be checked against already-approved project rules and constraints.
### Step 5 — Approve or Reject
No coding starts until the plan is approved.
### Step 6 — Execute Only the Approved Scope
The implementation must stay inside the approved boundary.
### Step 7 — Validate
Validation includes technical checks, automated tests, and architectural compliance.
### Step 8 — The Closing Audit (Hard Gate)
Execute the mandatory audit (Visual, Backlog, and Tenant Isolation).
### Step 9 — Close the Task
The task is documented and committed only after passing the audit.

---

## 10. What RIGOR Explicitly Forbids

RIGOR forbids the following behaviors:
- coding before planning
- reopening frozen decisions casually
- merging multiple phases into one uncontrolled implementation
- introducing architecture outside the approved scope
- “quick fixes” that weaken structure
- skipping validation because something “looks fine”
- normalizing silent errors (e.g., swallowed 422 validations)
- trusting frontend state for backend authority
- carrying critical project context only in chat memory
- vague progress claims without evidence
- silent deviations from the agreed stack or architecture

---

## 11. Approval Rules

Approval in RIGOR is explicit. A task is only approved when:
- the scope is clear
- the implementation plan is coherent
- it respects frozen decisions
- the risks are acceptable
- it does not absorb future scope

If approval is not explicit, execution must not begin.

---

## 12. Validation & Closure Rules (The Hard Gate)

Validation is not cosmetic. It is structural. A block is only considered validated when the relevant forms of proof have been reviewed.

Furthermore, a block **CANNOT BE CLOSED** unless it passes the **Closing Audit Checklist**:

1. **Visual & UX Validation:** The Architect must manually confirm the UI renders without silent errors, without hardcoded enums (unless specified in MVP), and with premium SaaS standards.
2. **Backlog / SRS Contrast:** The delivered code must fulfill 100% of the acceptance criteria stipulated for this specific block in the functional requirements documentation.
3. **Multitenant Isolation Check:** Strict verification that data processed is hermetically isolated to the active `community_id`.

---

## 13. Decision Freezing

One of the most important concepts in RIGOR is the freezing of approved decisions.

A decision should become frozen when:
- it has been discussed sufficiently
- it has architectural implications
- it affects future implementation order
- reopening it casually would create drift

Examples of freeze-worthy decisions: stack, route strategy, multi-tenancy model, boundary model, active context strategy, validation strategy, naming conventions. Frozen decisions may only be changed through deliberate re-approval.

---

## 14. Block Structure in RIGOR

Implementation should be organized in blocks and, where needed, subphases.

A block typically contains:
- objective
- scope
- exclusions
- task list
- implementation plan
- validation checklist
- checkpoint or closure note

This prevents the system from becoming one continuous stream of loosely connected work.

---

## 15. Contingency Management (Dynamic Contingency Plan)

To handle technical debt, UX refactoring, or CRO/business pivots without contaminating the primary Backlog, RIGOR employs a Dynamic Contingency Plan.

### 15.1 Naming and Status
- **Nomenclature:** Contingencies are named sequentially (e.g., `Contingency 1`, `Contingency 2`).
- **Statuses:** Every Block and Contingency must bear one of three states:
  - `[Pending]`
  - `[Resolved]`
  - `[Incomplete]` (Reserved strictly for end-of-workday pauses or force majeure blockers; never for sloppy code).

### 15.2 Origins
Contingencies originate from:
- UX audits demanding premium refactoring.
- CRO (Conversion Rate Optimization) and commercial pivots dictated by the Architect.
- Tech debt inherited from early rapid iterations.
- *Note:* Contingencies should NOT originate from AI laziness. If omissions are found after a block is closed, they are formally logged as Contingencies to prevent normalizing errors.

### 15.3 The Dual Documentation Flow
- **Formal Record:** `PROJECT-CONTINGENCIES.md` resides in the repository as the immutable ledger of structural adjustments.
- **Informal Inbox:** A dynamic cloud document (e.g., Google Drive) acts as the daily scratchpad for the Architect. The AI is prompted to read this inbox, synthesize the findings into formal contingencies, and update the repository ledger.

---

## 16. RIGOR and AI-Assisted Development

RIGOR is especially powerful when using AI agents or coding assistants.

Without strong methodology, AI tends to:
- overreach scope
- optimize too early
- assume missing context
- mix concerns
- produce fast but fragile output

RIGOR counters this by forcing the assistant to behave as follows:
- plan first
- ask or stop when unclear
- stay inside scope
- respect frozen decisions
- validate outputs
- avoid silent architecture changes

In this sense, RIGOR is not only a software methodology; it is also a **governance model for AI-assisted implementation**.

---

## 17. Continuity Between Chats and Sessions

RIGOR assumes that project continuity is fragile unless deliberately preserved.

Therefore, the methodology requires:
- continuity handoff documents (`PROJECT-CONTINUITY-HANDOFF.md`)
- contingency ledgers (`PROJECT-CONTINGENCIES.md`)
- documented next step
- project checkpoints
- frozen decisions registry
- no dependence on chat memory alone

A new chat should be able to resume the project by reading the continuity artifacts, not by guessing history.

---

## 18. Version Control Discipline

RIGOR requires version control to reflect validated milestones, not random work accumulation.

Commits should be:
- clean
- meaningful
- aligned with completed blocks
- created after validation

Bad examples: `fix stuff`, `changes`, `initial commit` after major work.
Good examples: commit messages tied to validated milestones, commit after block closure, commit after architectural checkpoint.

---

## 19. Escalation Rules

When a task reveals a deeper architectural issue, RIGOR requires escalation rather than improvisation.

This means:
- pause implementation
- define the new architectural concern explicitly
- plan it as its own task, subphase, or formally log it as a Contingency.
- obtain approval
- only then continue

This prevents hidden architecture from creeping into unrelated tasks.

---

## 20. RIGOR Quality Standard

A task done under RIGOR should satisfy all of the following:
- The scope was clear before coding
- The implementation matched the approved plan
- Validation exists (Visual, Backlog, Multitenant)
- Frozen rules were respected
- The result can be understood later by another engineer
- The work is versioned and documented
- Future phases can safely build on top of it

If those conditions are not met, the task may be implemented, but it is not RIGOR-complete.

---

## 21. Minimal Template for Future RIGOR Tasks

Every future task should ideally answer these questions before execution:
1. What is the exact task?
2. What is in scope?
3. What is explicitly out of scope?
4. Which files are likely affected?
5. What risks exist?
6. What frozen decisions constrain this task?
7. How will the result be validated?
8. Does it pass the Closing Audit (Visual, SRS, Isolation)?

---

---

## 22. CI/CD Validation — Automation of the Hard Gate (UNDER IMPLEMENTATION)

This rule represents the evolution toward total technical autonomy. Once implemented, the "Hard Gate" will transition from a manual review to a fully integrated Continuous Integration (CI) flow.

- **Static Analysis Automation:** A block cannot be marked as completed unless it passes a PHPStan analysis (maximum level) and a strict TypeScript type audit.
- **Test Coverage (Pest/Vitest):** A minimum coverage threshold will be required for any new code introduced within the block.
- **Commit Blocking:** The system will prevent merging or closing a task if the validation pipeline yields a single warning or error.
- **Purpose:** To eliminate human error in syntax and basic technical debt review, freeing the Architect to focus exclusively on logic, architecture, and UX.

## 23. Rollback Protocol — Execution Failure Management (UNDER IMPLEMENTATION)

This is a safety protocol designed to protect the repository's integrity when Phase C (Execution) becomes unviable.

- **Insurmountable Block Detection:** If a technical limitation or architectural inconsistency is discovered during execution that invalidates the approved Implementation Plan, execution must stop immediately.
- **State Cleanup:** A Git rollback must be performed to the last known healthy state (the commit marking the closure of the previous block).
- **Prohibition of "Patch-on-Patch" Solutions:** Attempting to fix a failed plan on the fly is strictly forbidden. The system must return to a clean state, escalate the issue, re-plan, and re-approve.
- **Purpose:** To prevent the introduction of "ghost code" or remnants of failed logic into the production system.

## 24. Final Definition

RIGOR is the official methodology for building serious systems under controlled architecture.

It is defined by:
- explicit planning
- bounded execution
- frozen decisions
- validation before continuation
- strict auditing prior to closure
- continuity preservation
- traceable implementation
- architectural discipline over speed

It should be treated not as a convenience process, but as a **project operating system**.