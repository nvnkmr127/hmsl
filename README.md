# 🏥 Hospital Management System (HMS)

> **Single Doctor Owned Hospital — Full Digital Workflow**  
> Built with Laravel 11 · PHP 8.3 · MySQL · Livewire · Redis

---

## Overview

A complete, modular, production-ready Hospital Management System designed to replace all manual operations in a single-doctor owned hospital. The system covers OPD, IPD, Discharge Summary, Pharmacy, Laboratory, Billing, and Master Data — with full role-based access control and a clean UI theme.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11, PHP 8.3 |
| Database | MySQL 8 |
| Frontend | Laravel Blade + Livewire 3 |
| Cache & Queue | Redis |
| Auth | Laravel Sanctum / Fortify |
| File Storage | Laravel Storage (local/S3) |
| PDF | Barryvdh Laravel DomPDF |
| Roles & Permissions | Spatie Laravel Permission |

---

## System Phases

| Phase | Module | Status |
|---|---|---|
| Phase 0 | Core Foundation (Theme, Settings, Master Data, Roles) | 📋 Planned |
| Phase 1 | OPD System | 📋 Planned |
| Phase 2 | IPD System | 📋 Planned |
| Phase 3 | Discharge Summary | 📋 Planned |
| Phase 4 | Supporting Modules (Pharmacy, Lab, Inventory, Reports) | 📋 Planned |

---

## Project Plan Files

```
docs/
├── 00-project-setup.md          → Laravel setup, environment, packages
├── 01-database-schema.md        → Full database schema (all tables)
├── 02-folder-structure.md       → Module wise folder structure
├── 03-phase0-foundation.md      → Phase 0 micro tasks
├── 04-phase1-opd.md             → Phase 1 OPD micro tasks
├── 05-phase2-ipd.md             → Phase 2 IPD micro tasks
├── 06-phase3-discharge.md       → Phase 3 Discharge micro tasks
├── 07-phase4-supporting.md      → Phase 4 Supporting modules
├── 08-api-structure.md          → API endpoint structure
├── 09-ui-screens.md             → UI screen list
└── 10-task-master.md            → Master task list (all phases)
```

---

## Development Rules

- Each visit creates a **new record** — no overwriting medical data
- **Service Layer** handles all business logic — fat controllers are forbidden
- Modules are **loosely coupled** — new modules can be added without breaking existing ones
- All interactions via **Livewire** — zero page reloads on core workflows
- System is **API-ready** — even UI is built on Livewire, the Services expose clean interfaces
- **Configuration-driven** behavior via the Settings module

---

## Roles

| Role | Access |
|---|---|
| Doctor Owner | Full access to all modules |
| Receptionist | Patient, OPD, Appointments, Billing |
| Nurse | IPD notes, Vitals, Medication chart |
| Lab Technician | Lab orders, Results, Reports |
| Pharmacist | Pharmacy, Dispensing, Inventory |
| Accountant | Billing, Payments, Reports |

---

## Quick Start

> See `docs/00-project-setup.md` for full installation steps.

```bash
composer create-project laravel/laravel hms
cd hms
# Follow steps in docs/00-project-setup.md
```

---

*Plan version 1.0 | Created: March 2026*
