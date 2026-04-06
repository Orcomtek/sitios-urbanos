# 04-05 — Financial Core and Payments (ePayco Split)  
## System Requirements Specification (SRS)  
### Sitios Urbanos

---

## 1. Purpose

This document defines:

- payment handling within the community  
- integration with payment gateway (ePayco)  
- split payment logic  
- commission model  
- differentiation between platform and external payments  
- financial configuration per community  
- support for marketplace, services, and P2P ecosystem  

This module defines how the platform generates revenue without breaking commercial logic or adoption.

---

## 2. Core Principle

The platform MUST only charge commission when it generates direct value in the transaction.

---

## 3. Payment Types in the System

The system MUST clearly differentiate three types of payments:

---

## 3.1 Community Payments

Payments directly related to community obligations or services.

### Examples:

- administration fees  
- fines  
- extraordinary fees  
- paid reservations of common areas or amenities  

---

## 3.2 Payments Between Users and Providers / Ecosystem Commerce

Payments that occur within the extended Sitios Urbanos ecosystem.

### Examples:

- technical services (plumbing, electrical, etc.)  
- cleaning  
- gardening  
- maintenance  
- local store purchases  
- marketplace transactions  

---

## 3.3 Community-Based P2P Payments

Payments between residents within a community or across communities in the Sitios Urbanos network.

### Examples:

- neighbor-to-neighbor sales  
- rentals of goods  
- small services between residents  
- internal classified listings  

---

## 4. Critical Commission Rule

---

## 4.1 Community Payments

Commission MUST ONLY be charged if the payment is processed within the platform.

---

### Scenarios

#### Platform Payment (via ePayco)
Commission applies ✔

#### External Payment
- bank transfer  
- cash  
- manual deposit  

Commission does NOT apply ❌

---

### Key Rule

The system MUST NOT enforce commission on payments that occur outside the platform.

This is critical to avoid commercial friction.

---

## 5. ePayco Split Integration

---

## 5.1 Definition

The system uses ePayco Split Payments to automatically distribute funds.

---

## 5.2 Payment Flow

1. user initiates payment  
2. system creates transaction  
3. ePayco processes payment  
4. ePayco splits funds:

- percentage to the community  
- percentage to Sitios Urbanos  

---

## 5.3 Benefits

- full automation  
- transparency  
- no manual reconciliation  
- reduced operational errors  

---

## 6. Commission Configuration

---

## 6.1 Mandatory Rule

Commissions MUST be fully configurable.

---

## 6.2 Configuration Levels

### Global
- system default

---

### Per Community
- tenant-specific override

---

### Per Transaction Type
- administration  
- providers  
- marketplace  
- P2P  

---

## 6.3 Example

- administration → 3%  
- providers → 8%  
- marketplace → 5%  
- P2P → configurable  

---

## 7. External Payments

---

## 7.1 Mandatory Rule

The system MUST support registering payments made outside the platform.

---

## 7.2 Examples

- bank transfers  
- cash payments  
- manual deposits  

---

## 7.3 Purpose

- maintain accurate accounting  
- reflect real financial state  
- avoid dependency on gateway-only flows  

---

## 7.4 Critical Rule

External payments MUST NOT generate platform commission.

---

## 8. Marketplace, Services, and Commercial Ecosystem

The system MUST support an expanded economic ecosystem composed of three main layers:

---

## 8.1 Professional Services Marketplace

A network of verified providers with:

- profiles  
- reviews  
- reputation  
- service requests  
- order management  
- in-platform payments  

### Examples:

- plumbers  
- electricians  
- technicians  
- specialized services  

---

## 8.2 Mini-Market Express

A local commerce layer for fast purchases and nearby services.

### Examples:

- local stores  
- allied businesses  
- express orders  

---

### Monetization Options

- membership (monthly / yearly)  
- commissions  
- hybrid models  

---

## 8.3 P2P Ecosystem (Strategic Layer)

A core and disruptive component of the platform.

The system MUST support classified listings and transactions between residents.

---

### Examples:

- selling items between neighbors  
- renting goods  
- small peer services  
- internal classifieds  

---

### Future Evolution

- cross-community marketplace  
- shared Sitios Urbanos network  

---

### Strategic Value

This model leverages:

- trust between neighbors  
- proximity  
- increased perception of safety  
- local economy dynamics  

---

### Monetization Options

- transaction commissions  
- featured listings  
- premium memberships  
- priority visibility  

---

## 8.4 Community Revenue Participation

The system MUST allow the community to participate in transaction revenue.

---

### Example

A transaction may split commission between:

- platform  
- community  

---

## 9. Advanced Configuration

The system MUST allow configuration of:

- commission existence  
- commission value  
- revenue distribution  
- rules per transaction type  

---

## 10. Common Area Reservations

---

## 10.1 Core Rule

Reservations belong to the community financial flow, not to SaaS-only monetization logic.

---

## 10.2 Commission Rule

Commission only applies if:

- a payment exists  
- and it is processed through the platform  

---

## 11. Transaction Records

---

## 11.1 Required Fields

Each transaction MUST include:

- payment type  
- origin (platform or external)  
- total amount  
- commission  
- distribution  
- status  

---

## 11.2 Statuses

- pending  
- completed  
- failed  
- refunded  

---

## 12. Financial Security

---

## 12.1 The system MUST NOT:

- allow arbitrary amount manipulation  
- allow manual override of split logic  
- trust frontend inputs  

---

## 12.2 Required Validations

- amount integrity  
- commission consistency  
- configuration validation  

---

## 13. Accounting Rules

---

## 13.1 Fund Separation

The system MUST clearly separate:

- community funds  
- platform revenue  

---

## 13.2 Traceability

Every transaction MUST be traceable:

- source  
- destination  
- commission  

---

## 14. Testing Requirements

The system MUST validate:

- split payments  
- zero-commission scenarios  
- external payments  
- per-community configuration  
- marketplace flows  
- P2P flows  
- revenue distribution  

---

## 15. Expected Outcome

The system guarantees:

- flexible monetization  
- low commercial friction  
- financial traceability  
- robust ePayco integration  
- support for multiple business models  

---

## 16. Strategic Importance

This module defines:

- platform sustainability  
- SaaS monetization model  
- scalability  

---

### If implemented incorrectly:

- adoption decreases  
- commercial friction increases  
- business model weakens  

---

### If implemented correctly:

The platform becomes financially sustainable from the MVP stage.