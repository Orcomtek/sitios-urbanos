# MVP Boundary — Sitios Urbanos

## 1. Purpose

This document defines the **strict boundaries of the MVP**.

Its purpose is to:

- prevent scope creep
- prioritize value delivery
- ensure fast time-to-market

---

## 2. MVP Strategy

The MVP is designed to:

- be sellable to small and medium communities
- work with or without security staff
- deliver immediate value in finance and governance

---

## 3. Included in MVP

### 3.1 Multi-Tenant Foundation
- subdomain-based tenancy
- tenant isolation via `community_id`
- multi-community user support

---

### 3.2 Authentication & Roles
- email-based login
- role selection:
  - admin
  - resident
  - guard (optional)

---

### 3.3 Core Operations
- units management
- residents and family members
- vehicles (basic)
- amenities
- reservations

---

### 3.4 Governance (CRITICAL)
- announcements board
- PQRS system
- document repository
- simple voting (basic decisions)

---

### 3.5 Financial System (CORE)
- invoices (administration + extra charges)
- payment tracking
- transaction history
- basic account statement
- paz y salvo generation (PDF)

---

### 3.6 Payment Integration
- ePayco integration
- split payments
- webhook validation
- transaction ledger

---

### 3.7 Security (Minimal Wedge)
- QR invitations
- parcel logging (manual entry)
- panic button (with SMS limits)

⚠️ Must NOT depend on guards

---

### 3.8 Dashboard System
- role-based dashboards
- modular widgets:
  - announcements
  - payments
  - reservations
  - PQRS
  - parcels

---

## 4. Explicitly Excluded from MVP

The following features are NOT allowed in MVP:

### 4.1 Marketplace
- services marketplace
- product sales
- provider onboarding

---

### 4.2 Advanced AI
- RAG assistant
- document intelligence
- automation workflows

---

### 4.3 Web Builder
- public landing generator for communities
- SEO tools

---

### 4.4 Advanced Governance
- legally binding voting
- quorum automation
- blockchain voting

---

### 4.5 IoT Integrations
- cameras
- hardware access control
- sensors

---

### 4.6 Cross-Community Ecosystem
- shared classifieds
- inter-community interactions

---

## 5. Simplifications

To accelerate MVP:

- OCR is optional (manual parcels first)
- SMS is limited (fair use)
- voting is basic (no legal complexity)
- UI is modular but not fully customizable

---

## 6. Expansion Path (Post-MVP)

Future phases may include:

- marketplace
- advanced AI
- web builder
- advanced governance
- integrations

---

## 7. Enforcement Rules

You MUST:

- reject features outside MVP
- prioritize simplicity
- avoid over-engineering

If a feature is not explicitly listed:
→ assume it is NOT part of MVP

---

## 8. Relation to PRD

This document is derived from:

→ `docs/PRD.md`