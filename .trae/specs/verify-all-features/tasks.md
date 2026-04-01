# Tasks
- [x] Task 1: Define verification scope and “supported modules” list
  - [x] Confirm which modules are expected to be fully working now (vs. intentionally coming soon).
  - [x] Map roles → permissions → visible navigation sections/links.

- [x] Task 2: Execute full UI screen verification pass
  - [x] Verify all navigation links resolve (no 404/500) for each role (doctor_owner, receptionist, nurse, lab_technician, pharmacist, accountant).
  - [x] Verify key screens render without runtime errors: Dashboard, Patients, OPD booking, Doctor desk, Billing, IPD admissions, Discharge, Settings, Master Data, Reports.
  - [x] Verify unfinished/placeholder pages display clear “Coming soon” messaging (not broken UI).

- [x] Task 3: Fix any blocking UI/permission issues discovered
  - [x] Broken routes or missing views
  - [x] Incorrect permission gates causing hidden/visible mismatch
  - [x] Empty nav sections or misleading labels

- [x] Task 4: Add minimal automated smoke tests for core flows
  - [x] Add feature tests that verify protected pages load for the right role and deny for the wrong role.
  - [x] Keep tests lightweight (page loads + basic assertions).

- [x] Task 5: Verification + regression safety
  - [x] Run database migrations + seeders used for verification.
  - [x] Run PHP tests and front-end build.
  - [x] Update checklist.md by checking off all completed checkpoints.

# Task Dependencies
- Task 3 depends on Task 2
- Task 4 can start after Task 1 (role/permission mapping)
- Task 5 depends on Tasks 2–4
