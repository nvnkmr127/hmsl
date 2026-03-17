# 03 — Phase 0: Core Foundation Micro Tasks

> **Goal:** Build the complete base system before any medical module. No medical feature should be built until all Phase 0 tasks are complete and tested.

---

## 0.1 Theme System

### MT-030.1.1 — Layout Setup

- [ ] Create `resources/views/layouts/app.blade.php` — main authenticated shell
- [ ] Create `resources/views/layouts/auth.blade.php` — login/auth shell
- [ ] Create `resources/views/layouts/print.blade.php` — print-only layout (no sidebar/nav)
- [ ] Add `@livewireStyles` and `@livewireScripts` to `app.blade.php`
- [ ] Add Alpine.js CDN or install via npm
- [ ] Add Tailwind CSS via CDN or install via npm (choose one consistent approach)
- [ ] Configure CSS variables for color theme (light/dark)

---

### MT-030.1.2 — Sidebar Navigation

- [ ] Create `resources/views/components/sidebar.blade.php`
- [ ] Add links for all modules: Dashboard, Patients, OPD, IPD, Discharge, Pharmacy, Lab, Inventory, Reports, Settings, Master Data
- [ ] Highlight active menu item based on current route
- [ ] Add role-based visibility (`@can` / `@hasrole`)
- [ ] Add collapse/expand toggle (desktop)
- [ ] Add mobile overlay drawer behavior (hamburger button)

---

### MT-030.1.3 — Top Header

- [ ] Create `resources/views/components/topbar.blade.php`
- [ ] Show hospital name from settings
- [ ] Show logged-in user name and role
- [ ] Add logout button
- [ ] Add dark/light mode toggle button
- [ ] Add breadcrumb slot

---

### MT-030.1.4 — Dark/Light Mode

- [ ] Use Alpine.js `x-data` to manage theme state
- [ ] Store preference in `localStorage`
- [ ] Toggle `dark` class on `<html>` element
- [ ] Test all components in both modes

---

### MT-030.1.5 — Reusable Blade Components

Create as anonymous Blade components (`resources/views/components/`):

- [ ] `card.blade.php` — wrapper card with title slot and body slot
- [ ] `alert.blade.php` — success, error, warning, info types
- [ ] `modal.blade.php` — Alpine.js based modal with open/close
- [ ] `breadcrumb.blade.php` — takes array of links
- [ ] `badge.blade.php` — status badge with color variants
- [ ] `stat-card.blade.php` — dashboard stat display (number + label + icon)

---

### MT-030.1.6 — Form Components

Create as anonymous Blade components:

- [ ] `form/input.blade.php` — text input with label, error, help text
- [ ] `form/select.blade.php` — select dropdown with label and error
- [ ] `form/textarea.blade.php` — textarea with label
- [ ] `form/checkbox.blade.php` — checkbox with label
- [ ] `form/radio.blade.php` — radio button with label
- [ ] `form/date-picker.blade.php` — date input with flatpickr integration
- [ ] `form/search.blade.php` — search input with debounce (Livewire)

---

### MT-030.1.7 — Table Components

- [ ] `table/wrapper.blade.php` — responsive table wrapper
- [ ] `table/th.blade.php` — sortable column header
- [ ] `table/empty.blade.php` — empty state row
- [ ] `table/pagination.blade.php` — Livewire pagination links

---

### MT-030.1.8 — Notification UI

- [ ] Add toast notification system using Alpine.js
- [ ] Create `resources/views/components/toast.blade.php`
- [ ] Add `$dispatch('notify', ['type' => 'success', 'message' => '...'])` pattern
- [ ] Handle success, error, warning, info notification types
- [ ] Auto-dismiss after 3 seconds

---

## 0.2 Settings Module

### MT-030.2.1 — Settings Model & Migration

- [ ] Create migration: `create_settings_table` (key, value, group, timestamps)
- [ ] Create `app/Models/Setting.php` with scope by group
- [ ] Add static helper: `Setting::get('hospital_name', 'default')`
- [ ] Add static helper: `Setting::set('hospital_name', 'Value')`
- [ ] Cache settings with Redis; invalidate on update

---

### MT-030.2.2 — Settings Service

- [ ] Create `app/Services/SettingsService.php`
- [ ] Methods: `getGroup(string $group)`, `updateGroup(string $group, array $data)`, `get(string $key, $default)`, `set(string $key, $value)`
- [ ] Settings seeder with default values (currency, timezone, hospital name, etc.)

---

### MT-030.2.3 — Hospital Details Screen

- [ ] Create Livewire: `app/Livewire/Settings/HospitalSettings.php`
- [ ] Fields: Hospital Name, Tagline, Address, City, State, Pincode, Phone, Email, Website
- [ ] Logo upload (store in `storage/public/hospital/`)
- [ ] Save to settings table grouped as `hospital`
- [ ] Show success toast on save

---

### MT-030.2.4 — System Preferences Screen

- [ ] Create Livewire: `app/Livewire/Settings/SystemPreferences.php`
- [ ] Fields: Currency symbol, Timezone, Date format, Financial year start
- [ ] UHID prefix (e.g. `HMS-`)
- [ ] Invoice prefix (e.g. `INV-`)
- [ ] Consultation fee default
- [ ] Save all to settings table grouped as `system`

---

### MT-030.2.5 — Invoice & Print Settings Screen

- [ ] Create Livewire: `app/Livewire/Settings/InvoiceSettings.php`
- [ ] Fields: Invoice header text, Footer text, Show/hide tax, Tax %
- [ ] Prescription header (hospital name, doctor name, qualification)
- [ ] Prescription footer (signature block, disclaimer)
- [ ] Print paper size (A4, A5, letter)

---

### MT-030.2.6 — Settings Routes & Controller

- [ ] Create `routes/modules/settings.php` and include in `web.php`
- [ ] Controller: `SettingsController` to render Livewire screens
- [ ] Apply `auth` and `role:doctor_owner` middleware

---

## 0.3 Master Data Module

### MT-030.3.1 — Department Module

- [ ] Migration: `create_departments_table`
- [ ] Model: `Department.php`
- [ ] Livewire: `DepartmentList.php` — paginated list, search, active toggle
- [ ] Livewire: `DepartmentForm.php` — inline create/edit modal
- [ ] Service: `DepartmentService.php` — create, update, toggle active, delete

---

### MT-030.3.2 — Doctor Module

- [ ] Migration: `create_doctors_table`
- [ ] Model: `Doctor.php` (belongs to User, belongs to Department)
- [ ] Livewire: `DoctorList.php` — list with department filter
- [ ] Livewire: `DoctorForm.php` — form with user account creation
- [ ] Service: `DoctorService.php` — create doctor + user account + assign role

---

### MT-030.3.3 — Services/Procedures Module

- [ ] Migration: `create_services_table`
- [ ] Model: `Service.php`
- [ ] Livewire: `ServiceList.php` — paginated list
- [ ] Livewire: `ServiceForm.php` — create/edit modal
- [ ] Service: `ServiceService.php`

---

### MT-030.3.4 — Medicines Module

- [ ] Migration: `create_medicines_table`
- [ ] Model: `Medicine.php`
- [ ] Livewire: `MedicineList.php` — search by name or generic name
- [ ] Livewire: `MedicineForm.php` — create/edit with form or category
- [ ] Service: `MedicineService.php` — CRUD + search for prescription autocomplete

---

### MT-030.3.5 — Lab Tests Module

- [ ] Migration: `create_lab_tests_table`, `create_lab_test_parameters_table`
- [ ] Model: `LabTest.php`, `LabTestParameter.php`
- [ ] Livewire: `LabTestList.php`
- [ ] Livewire: `LabTestForm.php` — with repeatable parameters
- [ ] Service: `LabTestService.php`

---

### MT-030.3.6 — Ward and Bed Module

- [ ] Migration: `create_wards_table`, `create_beds_table`
- [ ] Model: `Ward.php`, `Bed.php`
- [ ] Livewire: `WardBedManager.php` — ward tree with beds, add beds inline
- [ ] Service: `BedService.php` — availability check, allocate, release

---

## 0.4 Role and Permission System

### MT-030.4.1 — Spatie Setup

- [ ] Run `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- [ ] Run migrations
- [ ] Confirm `roles`, `permissions`, `model_has_roles`, etc. tables exist

---

### MT-030.4.2 — Create Roles Seeder

- [ ] Create `RolePermissionSeeder.php`
- [ ] Define roles: `doctor_owner`, `receptionist`, `nurse`, `lab_technician`, `pharmacist`, `accountant`
- [ ] Define permissions for each module (e.g. `view patients`, `create patients`, `view billing`, etc.)
- [ ] Assign permission sets per role

---

### MT-030.4.3 — Middleware Guards

- [ ] Apply `auth` middleware to all protected routes
- [ ] Apply `role:doctor_owner` to settings and master data routes
- [ ] Apply `role:receptionist|doctor_owner` to patient and appointment routes
- [ ] Apply `role:nurse|doctor_owner` to IPD notes and vitals routes
- [ ] Apply `role:pharmacist|doctor_owner` to pharmacy routes
- [ ] Apply `role:lab_technician|doctor_owner` to lab routes

---

### MT-030.4.4 — Role-Based Dashboards

- [ ] Create Livewire `Dashboard/DoctorDashboard.php` — today's patients, pending cases
- [ ] Create Livewire `Dashboard/ReceptionDashboard.php` — queue, appointments
- [ ] Create Livewire `Dashboard/AccountantDashboard.php` — revenue, pending bills
- [ ] Route to correct dashboard based on user's role after login

---

### MT-030.4.5 — User Management

- [ ] Livewire: `UserList.php` — list all users
- [ ] Livewire: `UserForm.php` — create/edit user, assign role
- [ ] Service: `UserService.php` — create user with role, reset password, toggle active

---

## Phase 0 Checklist Summary

| Task Group | Items | Est. Hours |
|---|---|---|
| Theme Setup | 8 tasks | 12h |
| Settings Module | 6 tasks | 8h |
| Master Data | 6 tasks | 16h |
| Roles & Permissions | 5 tasks | 8h |
| **Total Phase 0** | **25 tasks** | **~44h** |
