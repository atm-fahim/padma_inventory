# Module 00 — System Overview

## Purpose

**Padma Inventory** (`padma_inventory`) is Padma Bank Limited’s IT asset inventory system: register devices, assign to employees/branches, stock in/out, branch bulk upload, swap/transfer, and reporting.

## Users

- IT / branch inventory staff  
- Admin (users, menus, permissions)  
- Procurement (requisition, quotation — partial workflow)

## Technology

- **Backend:** Laravel 12, PHP 8.2+  
- **UI:** Blade, Bootstrap, jQuery  
- **DB:** MySQL (dev), Oracle (production typical)  
- **Integrations:** Padma HRM API (employee lookup)

## Core concept

Almost all quantity and assignment state lives in **`item_inventory`**, split by **`store_type`**:

| Code | Meaning |
|------|---------|
| `SP` | Stock position / package (main inventory row + spec rows) |
| `SI` | Stock in document line |
| `SO` | Stock out document line |

Device identity: **`set_uniq_no`** (e.g. `PBL-DK-5007`), **`uniq_serial_no`**.

## Documentation map

See [docs/README.md](../README.md) for all module files and [STRUCTURE.md](../STRUCTURE.md) for folder layout.
