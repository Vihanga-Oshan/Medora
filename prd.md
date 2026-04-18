# Medora - Product Requirements Document (PRD)

> Version: 2.0
> Date: 2026-04-09
> Product: Medora Medication Management and Pharmacy Coordination System
> Primary Stack: PHP (vanilla), MySQL 8, Apache (XAMPP)
> Reference Module: Legacy Java module kept locally in `java/` for reference only

---

## 1. Product Overview

Medora is a web platform that connects patients, guardians, pharmacists, and admins to support safe medication use, prescription handling, adherence tracking, and pharmacy operations.

The system focuses on:
- prescription intake and review
- medication scheduling and adherence tracking
- pharmacy inventory and dispensing workflows
- patient to pharmacy assignment
- role-based operational dashboards

## 1.1 User Roles

| Role | Description |
|------|-------------|
| Patient | Registers, uploads prescriptions, receives schedules, tracks adherence, orders medicine, views notifications/messages. |
| Guardian | Manages linked patients, monitors key updates, and supports patient care actions. |
| Pharmacist | Reviews prescriptions, creates medication plans/schedules, manages inventory, handles dispensing and communication. |
| Admin | Manages platform settings, pharmacies, pharmacists, assignments, and approval/operational oversight. |

## 1.2 Product Goals

1. Reduce missed doses and medication misuse.
2. Enable efficient pharmacist review and scheduling workflows.
3. Allow guardians to support medication safety for dependents.
4. Provide admin controls for multi-pharmacy operations.
5. Keep data structured, auditable, and role-restricted.

---

## 2. Core Modules

## 2.1 Authentication and Access

- Role-specific login flows for admin, patient, guardian, and pharmacist.
- Session-based authentication and route protection.
- Logout and onboarding flows available.

## 2.2 Patient Module

- Dashboard overview of medication and prescription status.
- Prescription upload and status tracking.
- Medication list and mark-as-taken behavior.
- Adherence/history views.
- Notifications and messages.
- Profile management.
- Pharmacy selection workflow.
- Shop and cart/order flow for medicine purchasing.

## 2.3 Guardian Module

- Guardian dashboard.
- Add/remove linked patients.
- View patient-related alerts.
- Profile and account management.

## 2.4 Pharmacist Module

- Dashboard for work queue and operational visibility.
- Prescription review, validation, and approval handling.
- Medication plan creation and schedule management.
- Inventory management (add/edit/delete medicine).
- Dispensing workflow and patient list access.
- Messages and settings.

## 2.5 Admin Module

- Admin dashboard and system settings.
- Pharmacy management.
- Pharmacist account management.
- Pharmacist request/approval management.
- Pharmacy assignment controls.

---

## 3. Key Functional Flows

## 3.1 Prescription to Schedule Flow

1. Patient uploads prescription.
2. Pharmacist reviews and validates/rejects.
3. If approved, pharmacist creates medication schedule/plan.
4. Patient receives medication timing and starts adherence tracking.
5. Patient marks doses as taken; logs are stored for adherence reporting.

## 3.2 Patient to Pharmacy Assignment

1. Admin defines pharmacies.
2. Pharmacists are assigned to pharmacies.
3. Patient selects or is mapped to a pharmacy.
4. Prescription/inventory operations are scoped by pharmacy context.

## 3.3 Inventory and Dispensing Flow

1. Pharmacist maintains stock and medicine metadata.
2. Approved prescriptions are processed for dispensing.
3. Inventory is updated based on dispense activity.
4. Patient receives relevant status updates.

## 3.4 Guardian Support Flow

1. Guardian links patient profile(s).
2. Guardian monitors alerts and key patient updates.
3. Guardian can coordinate support actions when adherence issues appear.

---

## 4. Data and Database Requirements

A single canonical SQL setup file exists for team setup:
- `database/medora_database_setup.sql`

This script must be treated as the source of truth for schema initialization.

## 4.1 Expected Database Scope

The database supports entities for:
- user/auth and role-specific profiles (patient, guardian, pharmacist, admin)
- prescriptions and prescription status lifecycle
- medication master and inventory
- medication schedules and logs
- pharmacy and assignment mappings
- notifications/messages
- shop/cart/orders

## 4.2 Environment Configuration

Application DB connection is configured via `.env` and used by `config/database.php`:
- `DB_HOST`
- `DB_PORT`
- `DB_USER`
- `DB_PASS`
- `DB_NAME` (default: `medoradb`)

---

## 5. Non-Functional Requirements

| Area | Requirement |
|------|-------------|
| Security | Password hashing, CSRF protection, input validation, SQL injection prevention via safe query patterns. |
| Performance | Main dashboard and list views should load in practical response times for standard clinic usage. |
| Reliability | Core flows (prescription review, schedule updates, medication logs) must fail cleanly and avoid partial writes where practical. |
| Usability | Role-specific navigation and clear status indicators for operational actions. |
| Maintainability | Modular page/controller/model layout and a single canonical DB setup script. |

---

## 6. Current Repository Alignment

This PRD aligns with active PHP modules under:
- `pages/admin/*`
- `pages/auth/*`
- `pages/patient/*`
- `pages/guardian/*`
- `pages/pharmacist/*`

and shared infrastructure under:
- `config/*`
- `core/*`
- `public/*`

The `java/` folder remains excluded from git but intentionally retained locally for reference.

---

## 7. Release Priorities

## Phase 1 (Core Operations)

1. Stable auth and role-based routing.
2. Prescription upload/review lifecycle.
3. Medication schedule creation and adherence logging.
4. Pharmacy and pharmacist assignment baseline.

## Phase 2 (Operational Expansion)

1. Inventory and dispensing refinements.
2. Notifications/messages hardening.
3. Guardian monitoring improvements.
4. Reporting and admin oversight enhancements.

## Phase 3 (Commercial and Scalability)

1. Shop and order workflow optimization.
2. Stronger auditing and analytics.
3. Multi-pharmacy scaling and performance improvements.

---

## 8. Out of Scope (For This PRD Version)

- Native mobile apps (iOS/Android)
- AI-generated medication advice
- Third-party telemedicine/video consultation stack
- Full cloud-native deployment specification

---

## 9. Team Onboarding Note

For any new developer:

1. Clone repository and configure `.env`.
2. Create database using:
   - `database/medora_database_setup.sql`
3. Run under XAMPP Apache + MySQL.
4. Use role-specific flows to validate end-to-end behavior.

This PRD should be updated whenever core flows or schema ownership change.
