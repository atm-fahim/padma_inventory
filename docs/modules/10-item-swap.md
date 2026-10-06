# Module 10 — Item Swap

## Purpose

Same branch/division: hand off device from one employee to another with **pending → receive** workflow.

## Routes

| Route | Action |
|-------|--------|
| GET `/swap-items` | Search employee, pick device |
| POST `/swap-items-save` | Create swap (pending) |
| GET `/receive-swap-items/{swapNo?}` | Receiver UI |
| POST `/receive-swap-items-save` | Confirm with remarks |

## Controller / repo

- **Controller:** `ItemSwapController`  
- **Repo:** `ItemSwapRepo` / `ItemSwapRepoImpl`  
- **Model:** `ItemSwapLogModel`

## Table: item_swap_logs

| status | Meaning |
|--------|---------|
| 1 | Pending receive |
| 2 | Received |
| 3 | Cancelled |

Fields include: `swap_no`, `item_inventory_id`, `from_employee_id`, `to_employee_id`, branch/division ids, `remarks`, `receive_remarks`, `received_at`.

## Inventory

Initiate swap marks related inventory as pending; receive completes assignment update.

## Views

- `swap_items.blade.php`  
- `receive_swap_items.blade.php`

## Migration

`database/migrations/2026_03_21_000000_create_item_swap_logs_table.php`
