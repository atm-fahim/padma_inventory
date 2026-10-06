# Inventory Table Schema Recommendations

## Current Schema Analysis

Your current `item_inventory` table already has a good structure for managing both stock_in and stock_out in one table using the `STORE_TYPE` field.

## ✅ Recommended Approach: Single Table for Stock In/Out

**YES, managing stock_in and stock_out in one table is a GOOD approach** because:

1. **Unified Data Model**: All inventory transactions in one place
2. **Easier Tracking**: Can see complete item lifecycle
3. **Simpler Queries**: Filter by `STORE_TYPE` ('SI' for Stock In, 'SO' for Stock Out)
4. **Less Complexity**: No need to join multiple tables
5. **Better Reporting**: Can generate reports on all transactions easily

## Current Fields Status

### ✅ Already Present (Good):
- `STORE_TYPE` - Differentiates Stock In ('SI') vs Stock Out ('SO')
- `STOCK_OUT_NO` - For stock out tracking
- `FROM_EMPLOYEE_ID`, `TO_EMPLOYEE_ID` - Employee tracking
- `FROM_BRANCH_ID`, `TO_BRANCH_ID` - Branch tracking
- `WAREHOUSE` - Warehouse location
- `STATUS` - Active/Inactive status
- `CREATED_DATE`, `CREATED_AT`, `UPDATED_AT` - Timestamps

## 🔧 Suggested Additional Fields (Optional)

### For Better Stock In Tracking:
```sql
STOCK_IN_NO VARCHAR2(100 BYTE)  -- Similar to STOCK_OUT_NO for stock in
PURCHASE_ORDER_NO VARCHAR2(100 BYTE)  -- Link to purchase orders
INVOICE_NO VARCHAR2(100 BYTE)  -- Supplier invoice number
RECEIVED_DATE DATE  -- When items were physically received
RECEIVED_BY VARCHAR2(100 BYTE)  -- Person who received the items
```

### For Better Stock Out Tracking:
```sql
GATE_PASS_CODE VARCHAR2(100 BYTE)  -- Gate pass reference (currently generated)
DELIVERY_DATE DATE  -- When items were delivered
RECEIVE_BY VARCHAR2(100 BYTE)  -- Person receiving (courier/employee name)
MOBILE VARCHAR2(20 BYTE)  -- Contact number for delivery
```

### For Audit & Tracking:
```sql
REFERENCE_NO VARCHAR2(100 BYTE)  -- General reference number
APPROVED_BY NUMBER  -- User who approved the transaction
APPROVED_DATE DATE  -- When transaction was approved
REJECTED_BY NUMBER  -- If transaction was rejected
REJECTED_DATE DATE  -- When transaction was rejected
REJECTION_REASON CLOB  -- Reason for rejection
```

### For Financial Tracking:
```sql
TOTAL_AMOUNT NUMBER(10,2)  -- Total transaction amount
DISCOUNT NUMBER(10,2)  -- Discount applied
FINAL_AMOUNT NUMBER(10,2)  -- Final amount after discount
PAYMENT_STATUS VARCHAR2(20 BYTE)  -- Paid, Pending, Partial
```

## 📋 Recommended Minimum Additions

If you want to keep it simple, add these **3 fields**:

1. **STOCK_IN_NO** - For stock in tracking (similar to STOCK_OUT_NO)
2. **GATE_PASS_CODE** - Store the gate pass code in database (currently generated)
3. **RECEIVED_DATE** - Physical receipt date for stock in

## Example Usage Pattern

```sql
-- Stock In Entry
INSERT INTO item_inventory (
    STORE_TYPE = 'SI',
    STOCK_IN_NO = 'SI-20260104-001',
    ITEM_ID = 123,
    QUANTITY = 10,
    FROM_BRANCH_ID = NULL,  -- Supplier
    TO_BRANCH_ID = 1,  -- Receiving branch
    ...
);

-- Stock Out Entry
INSERT INTO item_inventory (
    STORE_TYPE = 'SO',
    STOCK_OUT_NO = 'SO-20260104-001',
    ITEM_ID = 123,
    QUANTITY = 5,
    FROM_BRANCH_ID = 1,  -- Sending branch
    TO_BRANCH_ID = 2,  -- Receiving branch
    ...
);
```

## Query Examples

```sql
-- Get all stock in transactions
SELECT * FROM item_inventory WHERE STORE_TYPE = 'SI';

-- Get all stock out transactions
SELECT * FROM item_inventory WHERE STORE_TYPE = 'SO';

-- Get current stock (stock in - stock out)
SELECT 
    ITEM_ID,
    SUM(CASE WHEN STORE_TYPE = 'SI' THEN QUANTITY ELSE 0 END) as STOCK_IN,
    SUM(CASE WHEN STORE_TYPE = 'SO' THEN QUANTITY ELSE 0 END) as STOCK_OUT,
    SUM(CASE WHEN STORE_TYPE = 'SI' THEN QUANTITY ELSE -QUANTITY END) as CURRENT_STOCK
FROM item_inventory
WHERE STATUS = 1
GROUP BY ITEM_ID;
```

## Conclusion

Your current schema is **well-designed** for managing both stock_in and stock_out. The only additions I'd strongly recommend are:
- `STOCK_IN_NO` (for consistency with STOCK_OUT_NO)
- `GATE_PASS_CODE` (to store generated codes)
- `RECEIVED_DATE` (for stock in tracking)

All other fields are optional and depend on your business requirements.
