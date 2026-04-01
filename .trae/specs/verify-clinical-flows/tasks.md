# Tasks
- [x] Task 1: Inventory current clinical flow coverage (what exists vs missing)
  - [x] OPD token creation + print slip route verification
  - [x] Doctor appointments/queue verification
  - [x] IPD admission + discharge verification
  - [x] Billing + receipt/print verification
  - [x] Discharge summary screens/print outputs inventory

- [x] Task 2: Define role/permission matrix for these flows and align routes
  - [x] Confirm required permissions for each screen/route
  - [x] Fix mismatched permission names or missing permissions
  - [x] Ensure navigation matches route access (no dead links)

- [x] Task 3: Implement or finalize Discharge Summary + Receipt surfaces (if missing)
  - [x] Discharge summary view (screen) for discharged admissions
  - [x] Discharge summary print view route
  - [x] Receipt/print view route coverage for bills

- [x] Task 4: Add automated flow tests (beyond page-load smoke)
  - [x] OPD: create Consultation/token via service and verify print view loads
  - [x] Billing: create Bill with items and verify receipt view loads
  - [x] IPD: admit + discharge via IpdManager and verify bed availability toggles
  - [x] Permission assertions for key routes per role

- [x] Task 5: Verification run + checklist completion
  - [x] Run migrations/seed to support flows (including any new tables)
  - [x] Run `php artisan test` and ensure tests pass
  - [x] Run `npm run build`
  - [x] Check off all items in checklist.md

# Task Dependencies
- Task 2 depends on Task 1
- Task 3 depends on Task 1 (gap finding)
- Task 4 depends on Tasks 2–3
- Task 5 depends on Tasks 2–4
