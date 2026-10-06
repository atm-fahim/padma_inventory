# Module 14 — Database, Codes & Integrations

## Primary tables (runtime)

| Table | Role |
|-------|------|
| `item_inventory` | All stock positions & SI/SO lines |
| `item_info` | Item master |
| `category`, `brand`, `supplier` | Masters |
| `branch`, `division`, `department` | Org |
| `item_transfer_logs` | Transfer audit |
| `item_swap_logs` | Swap workflow |
| `users`, `access_menu`, `backend_menu` | Auth/RBAC |

**Status convention:** `status = 9` often means deleted/inactive; active queries use `status <> 9`.

## store_type summary

| Value | Use |
|-------|-----|
| SP | Inventory package / CSV import / manual entry |
| SI | Stock in |
| SO | Stock out |

## SET_UNIQ_NO

**Class:** `App\Support\SetUniqNoFormatter`  
Format: `PBL-{ABBR}-{sequence}`  
Sequence: `maxSequenceForOrgBranch()` over existing inventory (status ≠ 9).

## Stock numbers

**Class:** `App\Support\StockReferencePrefixBuilder`  
Branch/division tags + set code → `stock_in_no` / related refs.

## HRM API

**Trait:** `CommonTraits`  
**Env:** `PADMA_HRM_API_URL`  
Login → bearer token → endpoints e.g. `/api/v3/user-info-padmaportal?emp_id=`  
Used in stock out, transfer, swap.

## Oracle vs MySQL

- Production: `yajra/laravel-oci8`, schema often `PAD_INVENTORY`  
- Local XAMPP: MySQL, run migrations + any `.sql` alters needed

## A–Z SQL scripts (repo)

| File | Description |
|------|-------------|
| [docs/sql/PAD_INVENTORY_A_TO_Z_ORACLE.sql](../sql/PAD_INVENTORY_A_TO_Z_ORACLE.sql) | Full Oracle DDL (core + logs + procurement) |
| [docs/sql/PAD_INVENTORY_A_TO_Z_MYSQL.sql](../sql/PAD_INVENTORY_A_TO_Z_MYSQL.sql) | Full MySQL DDL for local |
| [docs/sql/PAD_INVENTORY_QUERIES_A_TO_Z.sql](../sql/PAD_INVENTORY_QUERIES_A_TO_Z.sql) | Reference SELECTs (inventory, barcode, stock) |
| [docs/sql/README.md](../sql/README.md) | How to run |

**Oracle `SET_UNIQ_NO`:** may be CLOB — use `LENGTH(TRIM(set_uniq_no)) > 0` in SQL (see queries file section D–E).

## Environment checklist

```
APP_URL=
DB_CONNECTION=
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
PADMA_HRM_API_URL=
```

## Log / audit

- `stock_out_gate_pass_logs` — gate pass prints  
- Transfer/swap tables — movement history
