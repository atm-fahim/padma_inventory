# Padma Inventory — SQL Scripts (A to Z)

| File | Purpose |
|------|---------|
| [PAD_INVENTORY_A_TO_Z_ORACLE.sql](./PAD_INVENTORY_A_TO_Z_ORACLE.sql) | **Oracle** schema: core + movement + procurement + alters |
| [PAD_INVENTORY_A_TO_Z_MYSQL.sql](./PAD_INVENTORY_A_TO_Z_MYSQL.sql) | **MySQL/XAMPP** schema (local dev) |
| [PAD_INVENTORY_QUERIES_A_TO_Z.sql](./PAD_INVENTORY_QUERIES_A_TO_Z.sql) | Reference **SELECT** queries: inventory, barcode, stock, reports |

## How to run

### Oracle (production — schema e.g. `PAD_INVENTORY`)

1. Connect as schema owner in SQL*Plus / SQL Developer.
2. Run sections **in order**; skip blocks for tables that already exist.
3. Adjust schema prefix if not `PAD_INVENTORY`.
4. Employee ID alters: see section **ALTER item_inventory** (VARCHAR2 employee columns).

### MySQL (XAMPP)

```bash
mysql -u root -p your_database < docs/sql/PAD_INVENTORY_A_TO_Z_MYSQL.sql
```

Then Laravel migrations for any tables not in the A–Z file:

```bash
php artisan migrate
```

## Related repo SQL

- `database/requisition_quotation_oracle.sql`
- `database/item_swap_logs.sql`
- `database/organization.sql`
- `database/oracle_alter_item_inventory_employee_ids.sql`
- `database/migrations/add_stock_in_no_to_item_inventory.sql`

## Oracle note (SET_UNIQ_NO)

`SET_UNIQ_NO` may be **CLOB**. Use `LENGTH(TRIM(set_uniq_no)) > 0` instead of `set_uniq_no != ''` in SQL and in application queries.
