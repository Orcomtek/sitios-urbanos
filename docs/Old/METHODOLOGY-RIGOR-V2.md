# METHODOLOGY RIGOR — Official Operating Framework

## 1. Purpose

RIGOR is the official implementation methodology used to design, build, validate, and evolve complex software systems under strict architectural control.

Its purpose is to prevent:

- architectural drift
- premature coding
- hidden assumptions
- scope contamination
- fragile execution
- loss of continuity across sessions, agents, or team members

RIGOR is especially designed for projects that combine:

- large functional scope
- high architectural sensitivity
- multi-phase implementation
- AI-assisted development
- long-lived product evolution

It is not a lightweight workflow. It is a deliberate control system for building serious software without sacrificing rigor, traceability, or long-term maintainability.

---

## 2. Core Philosophy

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

## 3. When RIGOR Should Be Used

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

## 4. The Main Operating Rule

The methodology has one hard operational rule:

> No implementation happens without prior planning and approval.

This means every meaningful implementation step must pass through:

1. **Task definition**
2. **Implementation plan**
3. **Explicit approval**
4. **Execution**
5. **Validation**
6. **Checkpoint or closure**

No step may skip that sequence unless explicitly authorized.

---

## 5. Core Methodological Principles

### 5.1 Architecture Before Code
The project must first be understood structurally before code is written.

### 5.2 One Task at a Time
Execution must remain granular. Large uncontrolled batches are prohibited.

### 5.3 No Silent Assumptions
If a requirement, constraint, or architectural implication is unclear, it must be clarified before implementation.

### 5.4 Frozen Decisions Matter
Once architecture decisions are approved, they become frozen and must not be reopened casually.

### 5.5 Validation Is Mandatory
A task is not complete because code exists. It is complete only after validation.

### 5.6 Fail Closed, Not Open
In sensitive systems, uncertainty must result in controlled failure, not permissive behavior.

### 5.7 Implementation Must Respect Scope
A task may not absorb future work simply because it seems convenient.

### 5.8 Continuity Must Be Preserved
Critical project knowledge must live in versioned documentation, not only in conversation memory.

---

## 6. RIGOR Lifecycle

Every meaningful block of work should move through the following lifecycle:

### Phase A — Planning
The block is analyzed and defined. No code is written yet.

### Phase B — Approval
The implementation approach is approved explicitly.

### Phase C — Execution
Only the approved scope is implemented.

### Phase D — Validation
The result is verified technically and, where needed, manually.

### Phase E — Closure
The block is documented, committed, and marked as completed.

---

## 7. Required Artifacts in RIGOR

RIGOR relies on a set of artifacts that make execution traceable and portable.

### 7.1 Project-Level Documents
These define the product and architecture baseline.

Examples:

- `PRD.md`
- `MVP-BOUNDARY.md`
- `ARCHITECTURE.md`
- `APP-STRUCTURE.md`
- `CODE-CONVENTIONS.md`
- `PROJECT-RULES.md`
- `BACKLOG-RIGOR.md`

### 7.2 Block Documents
These define execution by bounded implementation unit.

Examples:

- task lists
- validation checklists
- planning checkpoints
- subphase execution docs

### 7.3 Agent Rules
These constrain AI-assisted execution.

Examples:

- `.agents/rules/...`
- project-specific instructions
- precedence rules
- safety rules
- UI/module rules
- architecture rules

### 7.4 Continuity Artifacts
These preserve project state across chats and agents.

Examples:

- `PROJECT-CONTINUITY-HANDOFF.md`
- decision registries
- bootstrap prompts for new chats

### 7.5 Version Control Checkpoints
Clean commits must accompany meaningful validated milestones.

---

## 8. Standard RIGOR Workflow Per Task

Each task should follow this exact pattern.

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
Validation may include:
- technical checks
- manual tests
- automated tests
- visual verification
- architecture compliance review

### Step 8 — Close the Task
The task is documented and committed only after passing validation.

---

## 9. What RIGOR Explicitly Forbids

RIGOR forbids the following behaviors:

- coding before planning
- reopening frozen decisions casually
- merging multiple phases into one uncontrolled implementation
- introducing architecture outside the approved scope
- “quick fixes” that weaken structure
- skipping validation because something “looks fine”
- trusting frontend state for backend authority
- carrying critical project context only in chat memory
- vague progress claims without evidence
- silent deviations from the agreed stack or architecture

---

## 10. Approval Rules

Approval in RIGOR is explicit.

A task is only approved when:

- the scope is clear
- the implementation plan is coherent
- it respects frozen decisions
- the risks are acceptable
- it does not absorb future scope

If approval is not explicit, execution must not begin.

---

## 11. Validation Rules

Validation is not cosmetic. It is structural.

A block should be considered validated only when the relevant forms of proof have been reviewed.

### 11.1 Manual Validation
Examples:
- UI renders correctly
- expected route works
- empty state displays properly
- access denied behavior is correct

### 11.2 Automated Validation
Examples:
- Pest tests
- route checks
- model relation verification
- feature tests
- tenant isolation tests

### 11.3 Architectural Validation
Examples:
- no forbidden scope introduced
- no trust in request-injected tenant IDs
- model classification respected
- middleware responsibilities remain clean

### 11.4 Build / Compile Validation
Examples:
- `npm run build`
- static typing checks
- artisan route checks
- migration execution

---

## 12. Decision Freezing

One of the most important concepts in RIGOR is the freezing of approved decisions.

A decision should become frozen when:

- it has been discussed sufficiently
- it has architectural implications
- it affects future implementation order
- reopening it casually would create drift

Examples of freeze-worthy decisions:
- stack
- route strategy
- multi-tenancy model
- boundary model
- active context strategy
- validation strategy
- naming conventions with architectural consequences

Frozen decisions may only be changed through deliberate re-approval.

---

## 13. Block Structure in RIGOR

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

## 14. RIGOR and AI-Assisted Development

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

## 15. Continuity Between Chats and Sessions

RIGOR assumes that project continuity is fragile unless deliberately preserved.

Therefore, the methodology requires:

- continuity handoff documents
- documented next step
- project checkpoints
- frozen decisions registry
- primary continuity docs inside the repo
- no dependence on chat memory alone

A new chat should be able to resume the project by reading the continuity artifacts, not by guessing history.

---

## 16. Version Control Discipline

RIGOR requires version control to reflect validated milestones, not random work accumulation.

Commits should be:

- clean
- meaningful
- aligned with completed blocks
- created after validation

Bad examples:
- `fix stuff`
- `changes`
- `initial commit` after major work

Good examples:
- commit messages tied to validated milestones
- commit after block closure
- commit after architectural checkpoint

---

## 17. Escalation Rules

When a task reveals a deeper architectural issue, RIGOR requires escalation rather than improvisation.

This means:

- pause implementation
- define the new architectural concern explicitly
- plan it as its own task or subphase
- obtain approval
- only then continue

This prevents hidden architecture from creeping into unrelated tasks.

---

## 18. RIGOR Quality Standard

A task done under RIGOR should satisfy all of the following:

- The scope was clear before coding
- The implementation matched the approved plan
- Validation exists
- Frozen rules were respected
- The result can be understood later by another engineer
- The work is versioned and documented
- Future phases can safely build on top of it

If those conditions are not met, the task may be implemented, but it is not RIGOR-complete.

---

## 19. Minimal Template for Future RIGOR Tasks

Every future task should ideally answer these questions before execution:

1. What is the exact task?
2. What is in scope?
3. What is explicitly out of scope?
4. Which files are likely affected?
5. What risks exist?
6. What frozen decisions constrain this task?
7. How will the result be validated?
8. What is the expected closure condition?

---

## 20. Final Definition

RIGOR is the official methodology for building serious systems under controlled architecture.

It is defined by:

- explicit planning
- bounded execution
- frozen decisions
- validation before continuation
- continuity preservation
- traceable implementation
- architectural discipline over speed

It should be treated not as a convenience process, but as a **project operating system**.