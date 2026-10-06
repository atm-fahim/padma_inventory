# Module 13 — Warehouse & Dashboard

## Dashboard

| Route | Controller |
|-------|------------|
| `/dashboard` | `HomeController` |
| `/dashboard-total-count-log` | `DashboardLogController` |
| `/dashboard-category-details` | Category drill-down |
| `/dashboard-item-details` | Item drill-down |
| `/dashboard-supplier-details` | Supplier drill-down |

**Repo:** `DashBoardLogRepo` / `DashBoardLogRepoImpl`

## Warehouse

| Route | Controller |
|-------|------------|
| `/add-warehouse` | `WarehouseController` |
| `/warehouse-save`, `/warehouse-update` | |
| `/warehouse-invoice-details/{invoiceNo}` | |
| `/warehouse-edit/{invoiceNo}` | |
| `/warehouse-invoice-cancel` | |

Manages warehouse invoice-style entries (legacy/supplier warehouse flow).

## Barcode (if enabled in menu)

Views under `resources/views/backend/item/barcode*.blade.php` — uses barcode packages from `composer.json`.
