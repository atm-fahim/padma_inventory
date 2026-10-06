# Module 15 — Inventory Barcode (SET_UNIQ_NO)

## Routes

| Method | URL | Name |
|--------|-----|------|
| GET | `/inventory-barcode`, `/barcode-generate` | `inventory-barcode`, `barcode-generate` |
| POST | `/inventory-barcode-generate`, `/barcode-generate-by-item-id` | `inventory-barcode-generate` |

## Flow

1. User selects **Division/Branch** (required unless Item chosen) and optional **Item**.
2. **QTY**, **Page Layout**, optional **Price** / **Brand** on label.
3. **Generate** → same page shows **vertical barcode preview** (one label under another, thermal-style).
4. **Print** → one label per print page (`@page` size from Page Layout).

## Layers

| Layer | Class |
|--------|--------|
| Controller | `InventoryBarcodeController` |
| Interface | `InventoryBarcodeRepo` |
| Repository | `InventoryBarcodeRepoImpl` |
| Label sizes | `BarcodeLabelSizeCatalog` |
| Views | `resources/views/backend/item/inventory_barcode/` |
| CSS | `public/backend/assets/css/barcode.css` |

## Data rules

- Barcode value: **`SET_UNIQ_NO`** (CODE128 via `picqer/php-barcode-generator`).
- Main row per set: `MAX(item_inventory.id)` grouped by `set_uniq_no`; exclude spec `item_id` **17, 41, 12, 14** (RAM, processor, HDD, SSD).
- Active rows: `status <> 9`.
- **Oracle:** filter non-empty set with `LENGTH(TRIM(set_uniq_no)) > 0` (not `!= ''`).
- Branch filter: dropdown value `branch:{hr_branch_id}`; inventory matched via `branch.id` / `hr_branch_id` for that branch row only.

## SQL reference

See [docs/sql/PAD_INVENTORY_QUERIES_A_TO_Z.sql](../sql/PAD_INVENTORY_QUERIES_A_TO_Z.sql) sections **D, E, F**.
