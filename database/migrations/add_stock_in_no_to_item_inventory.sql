-- Add STOCK_IN_NO column to ITEM_INVENTORY table
-- Run this SQL script to add the STOCK_IN_NO field for stock in tracking

ALTER TABLE PAD_INVENTORY.ITEM_INVENTORY
ADD STOCK_IN_NO VARCHAR2(100 BYTE);

-- Add comment to column
COMMENT ON COLUMN PAD_INVENTORY.ITEM_INVENTORY.STOCK_IN_NO IS 'Stock In Number for tracking stock in transactions';

-- Optional: Create index for better query performance
CREATE INDEX IDX_ITEM_INVENTORY_STOCK_IN_NO ON PAD_INVENTORY.ITEM_INVENTORY(STOCK_IN_NO);

-- Optional: Create index for STOCK_OUT_NO if not exists
CREATE INDEX IDX_ITEM_INVENTORY_STOCK_OUT_NO ON PAD_INVENTORY.ITEM_INVENTORY(STOCK_OUT_NO);

-- Optional: Create composite index for store_type and stock numbers
CREATE INDEX IDX_ITEM_INVENTORY_STORE_TYPE ON PAD_INVENTORY.ITEM_INVENTORY(STORE_TYPE, STOCK_IN_NO, STOCK_OUT_NO);
