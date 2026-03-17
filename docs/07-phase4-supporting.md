# 07 — Phase 4: Supporting Modules Micro Tasks

> **Prerequisite:** Phase 0 must be complete. Phases 1–3 can be in progress.  
> **Goal:** Build Pharmacy, Laboratory, Inventory, and Reports as independent supporting modules.

---

## 4.1 Pharmacy Module

### MT-070.1.1 — Pharmacy Dashboard Livewire

- [ ] Create `app/Livewire/Pharmacy/PharmacyDashboard.php`
- [ ] Show: Today's pending prescriptions (from OPD + IPD)
- [ ] Show: Low stock alerts
- [ ] Show: Expiry alerts (next 30 days)
- [ ] Quick links: Dispense Prescription, Manage Stock, Sales

---

### MT-070.1.2 — Prescription Dispensing Livewire

- [ ] Create `app/Livewire/Pharmacy/PrescriptionDispense.php`
- [ ] Search pending prescriptions by patient name or UHID
- [ ] Load prescription items
- [ ] For each item: link to inventory (medicine lookup), auto-fill available stock
- [ ] Allow partial dispense (if stock insufficient)
- [ ] On dispense: create `Dispense` + `DispenseItems`, deduct from inventory via `StockService`
- [ ] Generate pharmacy bill (invoice type = `pharmacy`)

---

### MT-070.1.3 — Pharmacy Billing Livewire

- [ ] Create `app/Livewire/Pharmacy/PharmacyBilling.php`
- [ ] Based on dispensed items, auto-calculate total
- [ ] Show unit price from inventory, quantity dispensed, total
- [ ] Payment entry
- [ ] Issue invoice and print

---

### MT-070.1.4 — Dispense Migration & Models

- [ ] Create migration: `create_dispenses_table`
- [ ] Create migration: `create_dispense_items_table`
- [ ] Model: `Dispense.php` → `belongsTo(Prescription)`, `hasMany(DispenseItem)`, `belongsTo(User, 'dispensed_by')`
- [ ] Model: `DispenseItem.php` → `belongsTo(Dispense)`, `belongsTo(InventoryItem)`, `belongsTo(PrescriptionItem)`

---

### MT-070.1.5 — Pharmacy Service

- [ ] Create `app/Services/PharmacyService.php`
- [ ] Methods: `dispense(Prescription $prescription, array $items, User $by)`, `getPendingPrescriptions()`, `getDispenseHistory(Patient $patient)`
- [ ] In `dispense()`: validate stock sufficiency, call `StockService::deduct()`, create dispense record, mark prescription as `is_dispensed`

---

## 4.2 Laboratory Module

### MT-070.2.1 — Lab Dashboard Livewire

- [ ] Create `app/Livewire/Lab/LabDashboard.php`
- [ ] Show: Pending orders, Processing orders, Completed today
- [ ] Filters: by test type, by date

---

### MT-070.2.2 — Lab Order List Livewire

- [ ] Create `app/Livewire/Lab/LabOrderList.php`
- [ ] List all lab orders with status
- [ ] Filter: pending / sample collected / processing / completed
- [ ] Actions: Mark Sample Collected, Enter Results, Download Report

---

### MT-070.2.3 — Sample Collection Livewire

- [ ] Create `app/Livewire/Lab/SampleCollection.php`
- [ ] Select lab order → view ordered tests
- [ ] Mark sample collected (with timestamp)
- [ ] Update status to `sample_collected`
- [ ] Print sample label (patient name, test names, date/time, order number)

---

### MT-070.2.4 — Result Entry Livewire

- [ ] Create `app/Livewire/Lab/ResultEntry.php`
- [ ] Load lab order → all ordered tests → each test's parameters
- [ ] For each parameter: input result value
- [ ] Auto-flag abnormal: compare with `normal_range` in lab test parameter
- [ ] Highlight abnormal values in red
- [ ] Save results → update item status to `completed`
- [ ] When all items complete → update order status to `completed`
- [ ] Migration: `create_lab_results_table`
- [ ] Model: `LabResult.php`

---

### MT-070.2.5 — Lab Report Print View

- [ ] Create `resources/views/print/lab-report.blade.php`
- [ ] Hospital header
- [ ] Patient details, order number, date
- [ ] For each test: test name, parameters, result, reference range, abnormal flag
- [ ] Doctor and technician signature blocks
- [ ] PDF generation via `PdfService`

---

### MT-070.2.6 — Lab Service

- [ ] Create `app/Services/LabResultService.php`
- [ ] Methods: `enterResults(LabOrderItem $item, array $results)`, `isAbnormal(LabTestParameter $param, string $value)`, `getReport(LabOrder $order)`, `markSampleCollected(LabOrder $order, DateTime $collectedAt)`

---

## 4.3 Inventory Module

### MT-070.3.1 — Inventory Migration & Models

- [ ] Create migration: `create_suppliers_table`
- [ ] Create migration: `create_inventory_items_table`
- [ ] Create migration: `create_purchase_orders_table`
- [ ] Create migration: `create_purchase_order_items_table`
- [ ] Create migration: `create_stock_transactions_table`
- [ ] Models: `Supplier.php`, `InventoryItem.php`, `PurchaseOrder.php`, `PurchaseOrderItem.php`, `StockTransaction.php`

---

### MT-070.3.2 — Inventory List Livewire

- [ ] Create `app/Livewire/Inventory/InventoryList.php`
- [ ] Table: Item name, Category, Batch, Expiry, Current Qty, Unit, Reorder Level
- [ ] Color: red if qty ≤ reorder level
- [ ] Color: orange if expiry ≤ 30 days
- [ ] Filters: category, expiry alert, low stock alert
- [ ] Actions: Edit, Add Stock (manual adjustment), View transactions

---

### MT-070.3.3 — Stock Adjustment Livewire

- [ ] Create `app/Livewire/Inventory/StockAdjustment.php`
- [ ] Add new batch (in transaction)
- [ ] Adjust quantity (in/out/adjustment) with reason/notes
- [ ] On each transaction: create `StockTransaction` record, update `inventory_items.quantity`

---

### MT-070.3.4 — Purchase Order Livewire

- [ ] Create `app/Livewire/Inventory/PurchaseOrderForm.php`
- [ ] Select supplier
- [ ] Add items: search from inventory, qty ordered, unit price
- [ ] Save as draft
- [ ] Mark as ordered → send (notification or print PO)
- [ ] Mark as received → update stock automatically via `StockService`

---

### MT-070.3.5 — Supplier Management Livewire

- [ ] Create a simple CRUD Livewire for suppliers
- [ ] Fields: Name, Contact Person, Phone, Email, Address, GSTIN
- [ ] Linked to purchase orders

---

### MT-070.3.6 — Stock Service

- [ ] Create `app/Services/StockService.php`
- [ ] Methods: `addStock(InventoryItem $item, float $qty, string $reference)`, `deductStock(InventoryItem $item, float $qty, string $reference)`, `adjustStock(InventoryItem $item, float $qty, string $type, string $notes)`, `getLowStockItems()`, `getExpiryAlerts(int $daysAhead = 30)`, `receiveOrder(PurchaseOrder $po)`

---

### MT-070.3.7 — Expiry Alert System

- [ ] Create Laravel scheduled command: `php artisan inventory:expiry-alert`
- [ ] Run daily via scheduler
- [ ] Log expiry alerts to notifications table (or log file)
- [ ] Show expiry alerts on Inventory dashboard and relevant role dashboards

---

## 4.4 Reports Module

### MT-070.4.1 — Patient Report Livewire

- [ ] Create `app/Livewire/Reports/PatientReport.php`
- [ ] Filters: date range
- [ ] Output:
  - Total registrations
  - OPD visits count
  - IPD admissions count
  - New vs returning patients
- [ ] Export as PDF or printable view

---

### MT-070.4.2 — Revenue Report Livewire

- [ ] Create `app/Livewire/Reports/RevenueReport.php`
- [ ] Filters: date range, invoice type (OPD / IPD / Pharmacy / Lab)
- [ ] Output:
  - Total revenue
  - Revenue by type (OPD, IPD, Pharmacy, Lab)
  - Collections (paid amount)
  - Outstanding (balance)
  - Revenue by payment method
- [ ] Chart (Bar or Line using Chart.js)

---

### MT-070.4.3 — Pharmacy Report Livewire

- [ ] Create `app/Livewire/Reports/PharmacyReport.php`
- [ ] Filters: date range
- [ ] Output:
  - Total dispensed
  - Revenue from pharmacy
  - Top 10 medicines dispensed
  - Stock valuation

---

### MT-070.4.4 — Lab Report Summary Livewire

- [ ] Create `app/Livewire/Reports/LabReportSummary.php`
- [ ] Filters: date range
- [ ] Output:
  - Total lab orders
  - Revenue from lab
  - Most common tests ordered
  - Pending vs completed orders

---

### MT-070.4.5 — Report Service

- [ ] Create `app/Services/ReportService.php`
- [ ] Methods: `getPatientStats(Carbon $from, Carbon $to)`, `getRevenueStats(Carbon $from, Carbon $to)`, `getPharmacyStats(Carbon $from, Carbon $to)`, `getLabStats(Carbon $from, Carbon $to)`
- [ ] Use query builder with grouping; avoid N+1 with eager loading

---

## Phase 4 Checklist Summary

| Task Group | Items | Est. Hours |
|---|---|---|
| Pharmacy Module | 5 tasks | 18h |
| Laboratory Module | 6 tasks | 16h |
| Inventory Module | 7 tasks | 20h |
| Reports Module | 5 tasks | 12h |
| **Total Phase 4** | **23 tasks** | **~66h** |
