# 04-07 — Security Deep Layer (Access, QR, Packages, Panic Button)  
## System Requirements Specification (SRS)  
### Sitios Urbanos

---

## 1. Purpose

This document defines:

- access control within the community  
- visitor management  
- package handling  
- QR code generation and validation  
- security event logging  
- panic button behavior  
- operation in environments with and without front desk  
- operational and forensic traceability of security events  

This module establishes the **physical security operational layer** of the system and must be designed to support both daily operation and **post-incident auditing and forensic reconstruction**.

---

## 2. Core Principle

Every access or security-related event must be:

- authorized  
- verifiable  
- recorded  
- auditable  
- attributable  
- traceable in time  

---

## 3. Forensic Traceability Principle

This module must be designed under a **forensic-grade operational audit standard**.

### Official Rule

In case of a serious incident, the system must allow clear reconstruction of:

- who created an authorization  
- who approved or issued it  
- who validated the entry  
- when each action occurred  
- through which channel or device it was executed  
- whether identity validation was performed  
- whether validation was skipped  
- who skipped validation (if applicable)  
- the exact state of the access at each moment  

---

### Important Clarification

The system must remain:

- fast  
- practical  
- usable  

while still being **strong in evidentiary traceability**.

---

## 4. Supported Security Models

---

### 4.1 With Front Desk

- manual validation by guard  
- system used as support and evidence layer  

---

### 4.2 Without Front Desk

- autonomous access  
- QR-based validation  
- pre-authorized access flows  
- reinforced digital traceability  

---

### 4.3 Hybrid

- combination of human and digital validation  

---

### 4.4 Core Rule

The system must operate correctly in all three scenarios.

---

## 5. Access Control

---

## 5.1 Access Types

- residents  
- visitors  
- providers  
- delivery personnel  
- security staff  
- other authorized actors  

---

## 5.2 Rules

- every access must be linked to a valid destination (unit or equivalent)  
- every access must be linked to the tenant  
- every access must be recorded  
- every access must be reconstructable  

---

## 5.3 Minimum Event Data

Each access must generate:

- timestamp (date and time)  
- tenant  
- destination (unit or equivalent)  
- access type  
- access status  
- creator actor  
- validator actor  
- creation channel  
- validation channel  
- validation evidence or omission evidence  

---

## 6. Visitors

---

## 6.1 Creation

Visitors may be created through:

- resident pre-authorization  
- administration pre-authorization  
- real-time front desk registration  
- other authorized flows  

---

## 6.2 Minimum Data

- name  
- visitor type  
- identification (optional based on configuration)  
- destination (unit or equivalent)  
- expected date and time  
- validity window  
- optional notes  

---

## 6.3 Statuses

- created  
- authorized  
- validated_on_entry  
- inside  
- exited  
- expired  
- cancelled  
- rejected  

---

## 6.4 Rule

Every state transition must record:

- actor  
- timestamp  
- channel  
- reason (when applicable)  

---

## 7. Identity Validation

---

## 7.1 General Rule

When required by policy or operational flow, visitor identity must be validated at entry.

---

## 7.2 Validation Methods

The system must support:

- visual/manual validation  
- document-based validation  
- QR-based validation  
- front desk-assisted validation  
- future enhanced validation mechanisms  

---

## 7.3 Critical Rule

Identity validation must never be implicit.

The system must explicitly record one of the following:

- identity validated  
- identity partially validated  
- identity not validated / validation skipped  

---

## 7.4 If Validation Is Skipped

The system must record:

- that validation was skipped  
- who skipped it  
- when it was skipped  
- why it was skipped (if required by policy)  

---

## 7.5 Objective

In a post-incident audit scenario, there must be no ambiguity regarding whether identity validation occurred.

---

## 8. QR Codes

---

## 8.1 Use Cases

- visitor access  
- provider access  
- resident access (future)  
- temporary authorized access  

---

## 8.2 Rules

- must be unique  
- must be time-bound  
- must have expiration  
- must not be reusable outside policy  
- must be linked to a specific authorization  
- must allow invalidation if access is revoked  

---

## 8.3 Validation

QR validation must verify:

- status  
- expiration  
- tenant  
- destination  
- usage status  
- authorization validity  

---

## 8.4 Operational Evidence

Each QR scan must record:

- timestamp  
- validation result  
- device or channel  
- validating actor (if applicable)  

---

## 9. Package Handling

---

## 9.1 Registration

The system must allow:

- package registration  
- assignment to unit or resident  
- notification to resident  
- delivery tracking  

---

## 9.2 Statuses

- received  
- notified  
- delivered  
- returned  
- cancelled (if applicable)  

---

## 9.3 Rules

- full traceability  
- record who received the package at front desk  
- record who delivered it  
- record who collected it  
- timestamps at each stage  

---

## 9.4 Minimum Evidence

The system must clearly show:

- when the package arrived  
- who received it  
- who it was assigned to  
- when it was notified  
- who delivered it  
- who received it  

---

## 10. Security Log (Bitácora)

---

## 10.1 Definition

A centralized operational log of all relevant security events.

---

## 10.2 Includes

- access events  
- visitor flows  
- package handling  
- incidents  
- alerts  
- validation omissions  
- cancellations and rejections  
- critical state changes  

---

## 10.3 Rule

No relevant security event may go unrecorded.

---

## 10.4 Integrity Rule

The log must be designed so that events cannot be silently altered through normal operational flows.

---

## 10.5 Objective

To support:

- internal audits  
- incident reconstruction  
- investigations  
- operational evidence  

---

## 11. Action Attribution

---

## 11.1 Critical Rule

All critical actions must be attributable.

---

## 11.2 Actions Requiring Attribution

- authorization creation  
- authorization approval  
- entry validation  
- validation omission  
- package registration  
- package delivery  
- panic button activation  
- incident updates  

---

## 11.3 Minimum Attribution Data

- actor  
- role  
- timestamp  
- tenant  
- channel or device  
- result of action  

---

## 12. Panic Button

---

## 12.1 Definition

A feature that allows immediate alert generation in emergency situations.

---

## 12.2 Activation

- from the app  
- from authorized interfaces  
- from future devices  

---

## 12.3 Behavior

Must:

- generate immediate alert  
- register event  
- notify defined actors  
- provide full traceability  
- allow post-event reconstruction  

---

## 12.4 Recipients

- front desk  
- administration  
- configured contacts  
- other actors defined by policy  

---

## 12.5 Rules

- must avoid unnecessary mass triggering  
- must be auditable  
- must record origin, channel, and timestamp  
- must differentiate real, cancelled, or failed activations  

---

## 13. Security Notifications

---

## 13.1 Events

- visitor arrival  
- access authorization  
- entry validation  
- package received  
- package delivered  
- alerts  
- panic button activation  
- incidents  

---

## 13.2 Channels

- app  
- email  
- SMS (optional)  
- future channels  

---

## 13.3 Rule

Notifications must respect event priority and criticality.

---

## 14. Security Incidents

---

## 14.1 Definition

The system must support registration of security-related incidents.

---

## 14.2 Minimum Data

- incident type  
- description  
- timestamp  
- involved actors  
- location or unit  
- status  
- reporting actor  

---

## 14.3 Rule

Incidents must be linkable to:

- access events  
- visitors  
- packages  
- QR validations  
- panic button events  
- PQRS (if escalated)  

---

## 15. Integration with Other Modules

---

## 15.1 Operations

- units  
- residents  
- authorized users  

---

## 15.2 Governance

- security-related PQRS  
- incident reporting  

---

## 15.3 Finance

- future service-related integrations  

---

## 16. Security Rules

The system must NOT:

- allow unregistered access  
- allow cross-tenant access  
- rely on frontend authority  
- allow QR reuse outside policy  
- lose traceability  
- allow validation omission without evidence  
- allow silent deletion of operational records  

---

## 17. Testing Requirements

The system must validate:

- visitor creation  
- QR validation  
- access flows  
- package handling  
- panic button behavior  
- security logs  
- action attribution  
- validation omission tracking  
- event reconstruction capability  

---

## 18. Expected Outcome

The system guarantees:

- controlled access  
- traceability  
- action attribution  
- operational evidence  
- adaptability across security models  
- post-incident audit capability  

---

## 19. Strategic Importance

This module defines:

- perceived security  
- user trust  
- real-world adoption  
- operational defensibility  

---

### If implemented poorly

- operational risk increases  
- trust decreases  
- weak evidentiary position  

---

### If implemented correctly

the system becomes a critical and defensible component of community operations  

---

## 20. Scope and Responsibility Clarification

The system must provide strong traceability and evidentiary capabilities.

---

### Important Rule

The system must enable demonstration of:

- existing controls  
- applied controls  
- omitted controls  
- responsible actors  
- timing of actions  
- system behavior  

---

### However

The system must NOT claim absolute legal immunity.

Its role is to provide a clear technical basis to distinguish:

- human operational failure  
- validation omission  
- misuse  
- actual system vulnerability  

---

## 21. Legal and Data Protection Considerations (Reference)

This module handles sensitive information:

- visitors  
- access records  
- incident logs  

It must comply with:

👉 SRS Part 8 — Data Protection, Privacy and Legal Compliance (Colombia)

---

### Considerations

- identity protection  
- sensitive data handling  
- access control  
- auditing  
- data retention policies