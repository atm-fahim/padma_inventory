# Padma Inventory — Project Structure

## 1. High-level layers

```
┌─────────────────────────────────────────────────────────────┐
│  Browser (Blade + jQuery/Bootstrap)                         │
└───────────────────────────┬─────────────────────────────────┘
                            │ HTTP
┌───────────────────────────▼─────────────────────────────────┐
│  routes/web.php  →  Middleware (auth, CSRF)                 │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│  app/Http/Controllers/Backend/*Controller.php               │
│  Traits: UserAccessTraits, CommonTraits, FileUploadTraits   │
└───────────────────────────┬─────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
┌───────────────┐  ┌───────────────┐  ┌───────────────────┐
│ Interfaces/   │  │ app/Support/  │  │ DB:: / Eloquent   │
│ Repo/Backend  │  │ SetUniqNo,    │  │ item_inventory,   │
│               │  │ CSV Mapper    │  │ item_info, …      │
└───────┬───────┘  └───────────────┘  └───────────────────┘
        ▼
┌─────────────────────────────────────────────────────────────┐
│  app/Repository/Backend/*RepoImpl.php                       │
└───────────────────────────┬─────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────┐
│  MySQL (local) / Oracle PAD_INVENTORY (production)          │
└─────────────────────────────────────────────────────────────┘
```

## 2. Repository directory tree (core)

```
app/
├── Console/
├── Exceptions/
├── Exports/                    # Maatwebsite Excel export classes
├── Helper/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/               # Login, password
│   │   └── Backend/            # Inventory & admin modules
│   ├── Middleware/
│   └── Kernel.php
├── Interfaces/
│   ├── EloquentRepositoryInterface.php
│   └── Repo/Backend/           # *Repo.php contracts
├── Models/                     # Eloquent (partial table coverage)
├── Providers/
│   ├── AppServiceProvider.php
│   └── RepositoryServiceProvider.php   # Interface → Impl bindings
├── Repository/Backend/         # *RepoImpl.php
├── Support/
│   ├── InventorySpreadsheetImportMapper.php
│   ├── SetUniqNoFormatter.php
│   ├── StockReferencePrefixBuilder.php
│   └── WarrantyPeriodFormatter.php
└── Traits/
    ├── CommonTraits.php        # HRM API
    ├── UserAccessTraits.php    # Menu permission
    ├── FileUploadTraits.php
    └── ImageUploadTraits.php
```

## 3. Backend controllers (module map)

| Controller | Module |
|------------|--------|
| `HomeController`, `DashboardLogController` | Dashboard |
| `UsersController`, `UsersTypeController`, `UserPermissionController`, `MenuController` | Auth & RBAC |
| `OrganizationController`, `BranchController`, `DivisionController`, `DepartmentController` | Organization |
| `CategoryController`, `BrandController`, `SupplierController`, `UnitController` | Master data |
| `ItemController` | Item master |
| `ItemsInventoryController` | Manual inventory |
| `ItemsCsvUploadController`, `ItemsCsvReceiveController` | Bulk upload |
| `StockInController` | Stock in |
| `StockOutController` | Stock out, transfer, return, receive |
| `ItemSwapController` | Swap |
| `InventoryReportController` | Reports |
| `RequisitionController`, `QuotationController`, `DeliveryChallanController`, `ProductReturnController` | Procurement |
| `WarehouseController` | Warehouse |

## 4. Views structure

```
resources/views/
├── layouts/                    # app layout, sidebar
├── auth/                       # login
└── backend/
    ├── branch/, category/, brand/, supplier/, unit/
    ├── division/, department/, organization/
    ├── menu/, menu_permission/, user/
    ├── items_csv_upload/       # upload.blade.php, preview.blade.php
    ├── items_csv_receive/
    ├── item/                   # inventory, stock_in/out, swap, reports, …
    └── quotation/
```

## 5. Routes entry

- **Single file:** `routes/web.php` (all backend routes; no separate `api.php` inventory group for main UI).

## 6. Database artifacts

```
database/
├── migrations/                 # Laravel migrations + item_swap, transfer, quotation, …
├── migrations/*.sql            # Oracle ALTER scripts (e.g. stock_in_no)
└── seeders/
```

## 7. Config (inventory-relevant)

| File | Purpose |
|------|---------|
| `config/database.php` | mysql / oracle connections |
| `config/oracle.php` | OCI8 |
| `.env` | `DB_*`, `PADMA_HRM_API_URL`, `APP_URL` |

## 8. Public assets

```
public/
├── backend/assets/             # Admin theme, flags, plugins
├── frontend/assets/
└── tinymce/
```

## 9. Request flow (typical CRUD module)

```
GET /category
  → CategoryController@index
  → CategoryRepo::getCategory()
  → view backend/category/category.blade.php
  → userAccessFunction() for button visibility

POST /category-save-update
  → validate → CategoryRepo::saveUpdate()
  → redirect + flash message
```

## 10. Inventory data flow (simplified)

```
item_info (master)
       │
       ▼
item_inventory (store_type = SP)  ← manual entry / CSV import
       │
       ├── stock out (SO) + employee fields
       ├── stock in (SI)
       ├── transfer → item_transfer_logs
       └── swap → item_swap_logs → receive
```

See module docs under `docs/modules/` for each area in detail.

## SQL scripts (`docs/sql/`)

| File | Role |
|------|------|
| `PAD_INVENTORY_A_TO_Z_ORACLE.sql` | Oracle DDL (organization → procurement) |
| `PAD_INVENTORY_A_TO_Z_MYSQL.sql` | MySQL bootstrap for XAMPP |
| `PAD_INVENTORY_QUERIES_A_TO_Z.sql` | Reference SELECT queries |

See [sql/README.md](./sql/README.md).
