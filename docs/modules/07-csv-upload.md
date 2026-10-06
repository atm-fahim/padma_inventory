# Module 07 — Branch CSV / Excel Upload

## Routes

| Method | Route | Name |
|--------|-------|------|
| GET | `/items-inventory-csv-upload` | Upload form + branch |
| GET | `/items-inventory-csv-sample/{format?}` | Sample download |
| POST | `/items-inventory-csv-upload-process` | Parse → preview |
| POST | `/items-inventory-csv-store` | Import |
| GET | `/items-inventory-csv-cancel` | Clear session |
| GET | `/items-inventory-csv-export-result` | Result Excel |

## Controller

`ItemsCsvUploadController`  
**Repo:** `ItemsCsvUploadRepo` / `ItemsCsvUploadRepoImpl`

## Parser

`App\Support\InventorySpreadsheetImportMapper`

| Format | Detection |
|--------|-----------|
| `branch_inventory` | Header row + column aliases |
| `wide_asset_matrix` | Group headers (Laptop/Monitor) + Brand/Serial subheaders |

## Session keys

| Key | Content |
|-----|---------|
| `csv_preview_data` | Rows for preview grid |
| `csv_upload_branch_id` | Selected branch |
| `csv_import_meta` | format, header row, warnings |
| `csv_upload_export_rows` | Post-import export |

## Category rule

**All branch import rows → category `IT Equipment`** (match DB category id when exists).  
Item master match still applies for **item** dropdown only.

## SET_UNIQ_NO

- Assigned in preview via `assignPreviewSetUniqNumbers()`  
- Import: `resolveSetUniqNoForImport()` + conflict checks  
- Formatter: `SetUniqNoFormatter`

## Specs from CSV

Optional columns / description parsing → RAM, Storage, Processor; `processSpecsFromCsv()` creates SP rows.

## Views

- `resources/views/backend/items_csv_upload/upload.blade.php`  
- `resources/views/backend/items_csv_upload/preview.blade.php` (editable grid)

## Related

`ItemsCsvReceiveController` — alternate receive flow (`/items-inventory-csv-receive`).

See also: `CSV_UPLOAD_GUIDE.md`, `CSV_UPLOAD_IMPLEMENTATION.md`.
