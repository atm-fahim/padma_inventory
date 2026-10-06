# Module 05 — Item Master

## Purpose

Define catalog items (title, category, brand, supplier, image) used by inventory rows.

## Routes

| Route | Action |
|-------|--------|
| `/items` | List / form |
| `/item-save-update` | Save |
| `/items-by-category/{categoryId}` | JSON/list by category |
| `/item-edit/{id}`, `/item-delete/{id}` | CRUD |
| `/item-active/{id}`, `/item-in-active/{id}` | Status |

## Controller / repo

- **Controller:** `ItemController`  
- **Repo:** `ItemsRepo` / `ItemsRepoImpl`  
- **Table:** `item_info` (primary in code; migrations may reference `items`)

## Item code generation

`ItemController` uses supplier/brand/category context and `UserAccessTraits::generateRandomItemCode()` for codes when not supplied.

## View

`resources/views/backend/item/item.blade.php`

## Links to inventory

- `item_inventory.item_id` → `item_info.id`  
- Item dropdown on inventory form uses `data-category-id` for UI auto-category (manual entry only; CSV uses IT Equipment rule).
