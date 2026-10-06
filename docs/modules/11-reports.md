# Module 11 — Inventory Reports

## Controller / repo

- **Controller:** `InventoryReportController`  
- **Repo:** `InventoryReportRepo` / `InventoryReportRepoImpl`  
- **Exports:** `app/Exports/Inventory*ReportExport.php`

## Routes

| Route | Report |
|-------|--------|
| `/inventory-report` | Summary in/out |
| `/inventory-report-details` | AJAX details |
| `/inventory-detail-report` | Detail grid |
| `/inventory-summary` | Division/branch summary |
| `/inventory-employee-report` | By employee |
| `/inventory-detail-export-excel` | Excel |
| `/inventory-detail-export-pdf` | PDF |
| `/inventory-summary-export-excel` | Summary Excel |
| `/inventory-summary-export-pdf` | Summary PDF |
| `/inventory-employee-report-export-excel` | Employee Excel |
| `/inventory-employee-report-export-pdf` | Employee PDF |

## Views

- `inventory_report.blade.php`  
- `inventory_detail_report.blade.php` / `_pdf.blade.php`  
- `inventory_division_branch_summary.blade.php`  
- `inventory_employee_report.blade.php` / `_pdf.blade.php`  
- `inventory_summary_report_pdf.blade.php`

## Filters

Typically org, branch, division, date range, store_type — see controller methods for exact query params.
