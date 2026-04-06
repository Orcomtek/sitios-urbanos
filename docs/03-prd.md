# 03 — Product Requirements Document (PRD)  
## Sitios Urbanos

---

## 1. Purpose

This document defines the **official product requirements** for Sitios Urbanos.

Its purpose is to clearly establish:

- what Sitios Urbanos is  
- who it is built for  
- what problems it solves  
- what its strategic dimensions are  
- what the real product scope is  
- what the correct MVP is  
- what business logic governs the platform  
- how monetization is embedded from the beginning  
- which modules are core and which are future extensions  
- which priorities must govern architecture, SRS, backlog, and execution  

This PRD is the **official product source of truth** for:

- SRS  
- MVP Boundary  
- Architecture  
- Pricing  
- Backlog  
- Technical execution  

---

## 2. Official Product Definition

Sitios Urbanos is a **multi-tenant SaaS ecosystem for the integral management of residential communities**, conceived as:

- an operational system  
- an adaptable security platform  
- a financial and collection system  
- a communication layer  
- a progressive governance foundation  
- a local economic engine  

It must not be understood as a simple administrative application.

Sitios Urbanos is designed as a platform where the following actors interact under tenant isolation:

- Sitios Urbanos as platform operator  
- community administrations  
- property owners  
- tenants (renters)  
- security/front desk staff  
- providers  
- merchants  
- future real estate actors  
- other ecosystem participants  

---

### Structural Clarification

The operator of the system is:

👉 **Sitios Urbanos (not Orcomtek)**

This allows:

- external investment  
- legal separation  
- independent invoicing  
- future sale  
- strategic partnerships  

---

## 3. Product Vision

Sitios Urbanos must become:

👉 **the digital operating system of residential communities**

It does not digitize isolated processes — it integrates:

- operations  
- security  
- finance  
- communication  
- traceability  
- interaction between actors  
- internal economic activity  

---

### Extended Vision

Each community becomes:

- a structured system  
- a connected digital environment  
- a node in a broader economic network  

---

### Future Evolution

The platform is designed to evolve into:

- a network of interconnected communities  
- a cross-community marketplace  
- distributed trust and reputation  
- shared economic flows  

---

## 4. Strategic Positioning

Sitios Urbanos is:

- a real multi-tenant SaaS platform  
- a property management operating system  
- a collection and transaction monetization platform  
- an adaptable security layer  
- a local economic ecosystem  

It must NOT be designed as:

- only administrative software  
- only a resident portal  
- only a payment system  
- only a QR system  
- only a marketplace  

---

## 5. The Five Strategic Product Dimensions

---

### 5.1 Core Operations

The foundational layer of the system.

Includes:

- units / properties  
- residents  
- household members  
- authorized persons  
- vehicles  
- amenities  
- reservations  
- announcements  
- operational configuration  

---

### 5.2 Security

Must support:

- communities with front desk  
- communities without front desk  
- hybrid security models  
- automated environments  

Includes:

- QR access  
- visitor management  
- logs  
- package handling  
- OCR (future)  
- notifications  
- Panic Button  

---

### Critical Rule

The MVP must NOT depend solely on security.

---

### 5.3 Finance

Includes:

- administration fees  
- fines  
- payments  
- statements  
- certificates  
- peace-and-clear  
- ePayco integration  
- split payments  
- external payments  
- ledger  

---

### 5.4 Governance

Includes:

- PQRS (including anonymous)  
- announcements  
- documents  
- assemblies  
- voting  
- institutional traceability  

---

### 5.5 Local Economy (STRATEGIC CORE)

Transforms the product into an ecosystem.

Includes three layers:

---

#### 1. Professional Services Marketplace
- verified providers  
- reviews  
- service requests  
- payments  

---

#### 2. Mini-Market Express
- local commerce  
- fast purchases  
- neighborhood services  

---

#### 3. P2P Ecosystem (KEY DIFFERENTIATOR)

A system of classified listings and transactions between residents.

Enables:

- neighbor-to-neighbor sales  
- rental of goods  
- small peer services  
- internal community commerce  

---

### Strategic Value

- trust between neighbors  
- higher perceived security  
- local economic activation  
- foundation for cross-community expansion  

---

### Future Evolution

- inter-community marketplace  
- shared Sitios Urbanos network  
- distributed reputation systems  

---

## 6. Main Business Objective

---

### 6.1 SaaS Revenue

Recurring income based on:

- unit ranges  
- plan tiers  
- modules  
- usage  
- add-ons  

---

### 6.2 Transaction Revenue

👉 Commission exists ONLY when the platform infrastructure is used.

---

### Example

✔ Payment via ePayco → commission  
❌ Direct transfer → no commission  

---

## 7. Monetization Model

---

### 7.1 Direct

- subscriptions  
- add-ons  
- SMS usage  
- OCR usage  
- storage  
- premium support  

---

### 7.2 Transactional

- administration payments  
- provider services  
- marketplace  
- P2P  

---

### 7.3 Ecosystem

- memberships  
- advertising  
- featured listings  
- promoted placements  

---

### 7.4 Principle

Monetization must influence architecture from day one.

---

## 8. Commission Parameterization

Must be configurable by:

- transaction type  
- community  
- actor  
- module  
- recipient  
- percentage or fixed value  

---

## 9. External Payments

The system must allow registering:

- transfers  
- cash  
- manual payments  

👉 No automatic commission  

---

## 10. Problem Solved

Fragmented community operations:

- poor census  
- manual processes  
- lack of traceability  
- weak financial transparency  
- informal package handling  
- inefficient communication  
- disorganized reservations  

---

## 11. Colombia Market Context

The platform must be:

- Spanish-first  
- LATAM-aware  
- Colombia-first  

---

## 12. Actors

- platform operator  
- administrators  
- owners  
- tenants  
- security staff  
- providers  

---

## 13. Entry Logic

---

### Email-First

1. login at `app.sitiosurbanos.com`  
2. private community selector  
3. redirect to:

👉 `{communitySlug}.app.sitiosurbanos.com`

---

### Rule

No public community selector  

---

### Multi-Unit

Users can switch units without re-login  

---

## 14. Owner–Tenant Logic

Owners can:

- create tenants  
- activate/deactivate  
- control financial visibility  

---

## 15. System Layers

- Control Plane  
- Tenant Runtime  
- Public Web  
- Global Admin  
- Providers Portal  

---

## 16. MVP Philosophy

The MVP must demonstrate:

- operational value  
- commercial value  
- financial viability  
- scalability  

---

## 17. MVP Scope

---

### Operations
- units  
- residents  

---

### Security
- QR  
- visitors  
- packages  
- Panic Button  

---

### Finance
- payments  
- statements  
- split-ready  

---

### Governance
- PQRS  
- documents  
- announcements  

---

### Amenities
- reservations  

---

### Ecosystem (MANDATORY)

- basic marketplace  
- basic P2P  

---

## 18. Core Operations Requirements

### 18.1 Units / Properties
The unit entity cannot remain modeled as a simplistic three-field form for the final business reality.

It must evolve into an entity with:

- identifier
- property type
- relevant attributes
- financial context
- operational context
- occupancy context
- ownership and renter relationships

### 18.2 Residents
Residents must support at least:

- owner
- tenant
- relationship to unit
- contact data
- possible relation to global user
- status

### 18.3 Family / Household Members
This is core because it affects:

- package handling
- access
- census
- real household experience

### 18.4 Vehicles and Related Assets
They must be treated as part of the real household context.

---

## 19. Security Requirements

### 19.1 Dynamic QR
It must support:

- temporal validity
- usage control
- traceability
- anti-reuse
- clear issuer attribution

### 19.2 Front Desk / Security Operations
It must support:

- validation
- logs
- reception
- delivery
- incidents

and it must be adaptable both to front-desk and non-front-desk community operation models.

### 19.3 Package Handling
It must support:

- registration
- association to unit/person
- future OCR
- notification
- traceability

### 19.4 Panic Button
The MVP must explicitly include the **Panic Button** with the baseline logic already defined for the project.

This implies:

- event activation
- alert routing
- consumption policy
- operational priority
- notification fallback
- event traceability

It must not be treated as a vague or secondary idea.

---

## 20. Financial Requirements

### 20.1 Finance as Core
Finance is core to both the product and the business.

### 20.2 Minimum Capabilities
- charges
- payments
- history
- account statements
- certificates
- peace-and-clear
- financial visibility per unit
- externally registered payments

### 20.3 Owner–Tenant Logic
It must support the separation between:

- real financial responsible party
- operational occupant

### 20.4 ePayco Split Readiness
The financial architecture must be compatible from the start with:

- split payments
- webhook confirmations
- idempotency
- ledger
- configurable commissions
- reconciliation
- future multi-actor participation

---

## 21. ePayco Split as Strategic Requirement

ePayco Split is not a technical detail.  
It is a strategic business pillar because it supports transaction monetization.

The platform must be designed from the beginning to support:

- split of payments
- platform fee
- tenant participation when applicable
- traceability
- internal accounting
- scalability toward other ecosystem flows

This will later be detailed in the SRS and financial architecture documents.

---

## 22. PQRS and Anonymity

The system must support PQRS that are:

- identified
- anonymous

Especially for:

- complaints
- reports
- sensitive situations
- coexistence-related issues

This is important because it reduces friction and improves real usage in community contexts.

---

## 23. Pricing and Plans

The product must support plans by unit range.

### Key rule
Plans, ranges, prices, modules, and limits must be **parameterizable**.

They must not be hardcoded rigidly.

This includes the ability to modify:

- plan name
- unit range
- price
- currency
- limits
- included modules
- fair use rules
- activation/deactivation of benefits

### Official currency
Commercial values must be defined in:

- **Colombian pesos (COP)**

### Clarification
Ranges such as:

- 1–20
- 21–50
- 51–80
- 81–150
- 151–300
- 301+

may exist as the initial commercial structure, but the system must contemplate that they are configurable.

---

## 24. Notifications and Fair Use

The platform must support:

- in-app notifications
- email
- SMS
- future push and WhatsApp strategies

It must also support usage logic such as:

- SMS quota per unit
- overage
- fallback
- priority of events

This is both an operational and an economic requirement.

---

## 25. Language and UX Requirement

The platform must be:

- Spanish-first
- LATAM-aware
- Colombia-first

This implies:

- natural labels for Colombia
- correct semantics
- forms not under-modeled
- states and terms that reflect the real business context

---

## 26. Documentation and Evidence Requirements

The project must produce:

- architecture documents
- PRD
- SRS
- MVP Boundary
- backlog
- validation evidence
- decision traceability

This is part of the rigorous methodology applied to the project.

---

## 27. Official Product Priorities

### Priority 1
Correct architecture

### Priority 2
Multi-tenant isolation

### Priority 3
Commercially meaningful MVP

### Priority 4
Integrated finance and monetization

### Priority 5
UX aligned with Colombia

### Priority 6
Modularity and scalability

---

## 28. Explicit Non-Goals of the Current Phase

The project must avoid:

- accumulating features without architecture
- treating path-based routing as final truth
- forcing re-login per unit
- exposing tenants publicly
- reducing the product to administrative CRUDs
- leaving finance for later
- leaving monetization for later
- using generic UX disconnected from Colombian business logic

---

## 29. Final Statement

Sitios Urbanos is a:

- multi-tenant SaaS platform  
- operational system  
- financial system  
- economic ecosystem  

It must be:

- commercially viable  
- scalable  
- monetizable  
- architecturally solid  

All future documents must align with this definition.