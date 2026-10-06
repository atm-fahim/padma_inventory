# Padma Inventory — Documentation Index

সম্পূর্ণ সিস্টেম **module-wise** ও **structure** সহ নিচের ফাইলগুলোতে ভাগ করা আছে।

## দ্রুত লিংক

| # | Document | বিষয় |
|---|----------|--------|
| — | [STRUCTURE.md](./STRUCTURE.md) | **Project folder structure**, layer diagram, request flow |
| — | [TECHNICAL_SYSTEM_DOCUMENTATION.md](./TECHNICAL_SYSTEM_DOCUMENTATION.md) | একীভূত technical overview |
| — | [sql/README.md](./sql/README.md) | **A–Z SQL scripts** (Oracle + MySQL + queries) |
| — | [word/Padma_Inventory_Module_Documentation.doc](./word/Padma_Inventory_Module_Documentation.doc) | Microsoft Word-এ খোলার জন্য (HTML-based) |

## Module documents (`docs/modules/`)

| Module | File |
|--------|------|
| Overview | [modules/00-overview.md](./modules/00-overview.md) |
| Architecture & layers | [modules/01-architecture.md](./modules/01-architecture.md) |
| Login, users, permission | [modules/02-auth-rbac.md](./modules/02-auth-rbac.md) |
| Organization, branch, division | [modules/03-organization.md](./modules/03-organization.md) |
| Category, brand, supplier, unit | [modules/04-master-data.md](./modules/04-master-data.md) |
| Item master (`item_info`) | [modules/05-item-master.md](./modules/05-item-master.md) |
| Item inventory (manual entry) | [modules/06-item-inventory.md](./modules/06-item-inventory.md) |
| Branch CSV/Excel upload | [modules/07-csv-upload.md](./modules/07-csv-upload.md) |
| Stock In | [modules/08-stock-in.md](./modules/08-stock-in.md) |
| Stock Out, receive, transfer, return | [modules/09-stock-out.md](./modules/09-stock-out.md) |
| Item swap | [modules/10-item-swap.md](./modules/10-item-swap.md) |
| Inventory reports | [modules/11-reports.md](./modules/11-reports.md) |
| Requisition, quotation, challan, repair | [modules/12-procurement.md](./modules/12-procurement.md) |
| Warehouse & dashboard | [modules/13-warehouse-dashboard.md](./modules/13-warehouse-dashboard.md) |
| Database, `SET_UNIQ_NO`, APIs | [modules/14-data-codes-integration.md](./modules/14-data-codes-integration.md) |
| Inventory barcode print | [modules/15-inventory-barcode.md](./modules/15-inventory-barcode.md) |

## Legacy / supplementary (repo root)

- `STOCK_IN_OUT_IMPLEMENTATION.md`
- `CSV_UPLOAD_IMPLEMENTATION.md`, `CSV_UPLOAD_GUIDE.md`
- `SCHEMA_RECOMMENDATIONS.md`

## কীভাবে Word-এ convert করবেন

1. `docs/word/Padma_Inventory_Module_Documentation.doc` সরাসরি Word দিয়ে open করুন, **অথবা**
2. যেকোনো `docs/modules/*.md` ফাইল Word-এ open করে Save As `.docx` করুন, **অথবা**
3. [Pandoc](https://pandoc.org/) থাকলে:  
   `pandoc docs/modules/07-csv-upload.md -o csv-upload.docx`
