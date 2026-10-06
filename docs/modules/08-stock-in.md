# Module 08 — Stock In

## Routes

| Route | Action |
|-------|--------|
| `/stock-in` | Entry form |
| `/stock-in-save-update` | Save lines |
| `/stock-in-list` | List |
| `/stock-in-invoice/{stockInNo}` | Invoice view |
| POST | `/stock-in-get-inventory-by-item-id`, `/stock-in-get-items-by-set-uniq-no` | AJAX |

## Controller / repo

- **Controller:** `StockInController`  
- **Repo:** `StockInRepo` / `StockInRepoImpl`

## store_type

New lines use **`store_type = SI`**.

## stock_in_no

Requires column on Oracle (see `database/migrations/add_stock_in_no_to_item_inventory.sql`).  
Built with `StockReferencePrefixBuilder`.

## Views

- `stock_in.blade.php`  
- `stock_in_list.blade.php`  
- `stock_in_invoice.blade.php`

## Reference doc

`STOCK_IN_OUT_IMPLEMENTATION.md`
