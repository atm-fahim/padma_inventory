# Module 09 — Stock Out, Receive, Transfer, Return

## Stock out

| Route | Action |
|-------|--------|
| `/stock-out` | Issue form |
| `/stock-out-save-update` | Save |
| `/stock-out-list` | List |
| `/stock-out-invoice/{stockOutNo}` | Invoice |
| `/stock-out-gate-pass-print-log` | Gate pass log |

**store_type:** `SO`  
**Controller:** `StockOutController`  
**Repo:** `StockOutRepo` / `StockOutRepoImpl`

## Receive stock

| Route | `/receive-stock`, POST `/receive-stock-save` |

Return / receive flow for issued items.

## Transfer items

| Route | `/transfer-items`, POST `/transfer-items-save` |

- Moves assignment between employees (same branch context).  
- **Remark** field saved on inventory + **`item_transfer_logs`**.  
- View: `transfer_items.blade.php`

## Return to division

| Route | `/return-to-division`, POST `/return-to-division-save`  
View: `return_to_division.blade.php`

## AJAX helpers

- `/get-employee-by-id` — HRM employee  
- `/get-inventory-by-item-id`  
- `/get-items-by-set-uniq-no`  
- `/stock-out-branch-set-uniq-list`

## HRM

`CommonTraits::employeeInfo()` — env `PADMA_HRM_API_URL`

## Views

`stock_out.blade.php`, `stock_out_list.blade.php`, `stock_out_invoice.blade.php`, `receive_stock.blade.php`
