# Module 06 — Item Inventory (Manual Entry)

## Routes

| Route | Action |
|-------|--------|
| `/items-inventory` | Entry form |
| `/items-inventory-list` | List |
| `/items-inventory-save-update` | Create package |
| `/items-inventory-edit/{id}` | Edit |
| `/items-inventory-update/{id}` | Update |
| `/items-inventory-delete/{id}` | Soft delete (status) |

## Controller / repo

- **Controller:** `ItemsInventoryController`  
- **Repo:** `ItemsInventoryRepo` / `ItemsInventoryRepoImpl`  
- **Model:** `ItemsInventoryModel`

## Package model

One logical device = **one `set_uniq_no`** with multiple rows:

| Row | Typical content |
|-----|-----------------|
| Main | Desktop/Laptop item, full specs on main row |
| Child | RAM, Storage, Processor as separate `item_inventory` rows |

All use **`store_type = SP`**, same `set_uniq_no`.

## SET_UNIQ_NO

Generated via `SetUniqNoFormatter` — format `PBL-{abbr}-{seq}`.

## Edit specifications

- `getSetUniqNoSpecifications` loads all rows for a set (status ≠ 9).  
- Modal JS pre-selects RAM/Storage/Processor; update syncs component rows.

## UI behaviour

- Select item → auto category from `item_info.category_id`  
- Default condition: **good**

## Views

- `item_inventory.blade.php`  
- `item_inventory_edit.blade.php`  
- `item_inventory_list.blade.php`

## Stock in number

`StockReferencePrefixBuilder::formatStockInNumber()` on save where applicable.
