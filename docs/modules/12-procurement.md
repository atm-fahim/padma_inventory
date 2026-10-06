# Module 12 — Procurement (Requisition, Quotation, Challan, Repair)

## Requisition

| Route | Controller |
|-------|------------|
| `/create-requisition`, `/requisition-store` | `RequisitionController` |
| `/requisition-list`, `/requisition-show/{id}` | |

**Repo:** `RequisitionRepo` / `RequisitionRepoImpl`  
**Tables:** `requisitions`, `requisition_items`

## Quotation

| Route | Controller |
|-------|------------|
| `/create-quotation`, `/quotation-store` | `QuotationController` |
| `/quotation-list`, `/quotation-show/{id}` | |
| `/quotation-approve/{id}` | Approval |

**Tables:** `quotations`, `quotation_items`, `quotation_approvals`

## Delivery challan

| Route | `/delivery-challan`, POST store, show  
**Controller:** `DeliveryChallanController`  
**Tables:** `delivery_challans`, `delivery_challan_items`

## Product repair (return)

| Route | `/product-repair`, list, show  
**Controller:** `ProductReturnController`  
**Tables:** `product_returns`, `product_return_items`

## Views

```
resources/views/backend/quotation/
resources/views/backend/item/delivery_challan*.blade.php
resources/views/backend/item/product_return*.blade.php
resources/views/backend/item/quotation*.blade.php
```

## Models

`RequisitionModel`, `QuotationModel`, `DeliveryChallanModel`, `ProductReturnModel`, etc.
