# Module 04 — Master Data (Category, Brand, Supplier, Unit)

## Category

| Route | Controller |
|-------|------------|
| `/category` | `CategoryController` |
| `/category-import-excel` | Excel import |

**Table:** `category` — fields `category_title`, `category`, `org_id`, `status`  
**Repo:** `CategoryRepo` / `CategoryRepoImpl`

**Branch CSV default category:** `IT Equipment` (see module 07).

## Brand

| Route | `/brand` — `BrandController`  
**Repo:** `BrandRepo` / `BrandRepoImpl`

## Supplier

| Route | `/supplier` — `SupplierController`  
**Repo:** `SupplierRepo` / `SupplierRepoImpl`

## Unit (size)

| Route | `/size-unit` — `UnitController`  
**Repo:** `UnitRepo` / `UnitRepoImpl`

## Views

```
resources/views/backend/category/category.blade.php
resources/views/backend/brand/brand.blade.php
resources/views/backend/supplier/supplier.blade.php
resources/views/backend/unit/unit.blade.php
```

## Typical CRUD pattern

All masters follow: index → POST save-update → GET edit/delete → active/inactive (status ≠ 9).
