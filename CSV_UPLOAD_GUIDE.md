# Item Inventory CSV Upload Guide

## Overview
This guide explains how to upload item inventory data using CSV files in the Padma Inventory System.

## Access the CSV Upload Feature
Navigate to: `http://127.0.0.1:8000/items-inventory-csv-upload`

Or click the "Excel Upload File CSV" button on the Item Inventory page.

## CSV File Format

### Required Columns
Your CSV file **must** include these columns:
- `item_name` - Name of the item (Required)
- `category` - Category name (Required)
- `price` - Price per unit (Required)
- `quantity` - Available quantity (Required)

### Optional Columns
You can also include these columns for additional information:
- `brand` - Brand name
- `supplier` - Supplier name
- `unit_size` - Unit size/measurement (e.g., PCS, KG, L)
- `serial_no` - Serial number
- `condition` - Item condition (e.g., New, Used, Refurbished)
- `warranty` - Warranty period (e.g., 1 Year, 2 Years)
- `service_warranty` - Service warranty period
- `vat` - VAT amount
- `tax` - Tax amount
- `description` - Item description
- `ram` - RAM specification (for computers)
- `storage` - Storage specification (for computers)
- `processor` - Processor specification
- `graphics` - Graphics card specification
- `display` - Display specification
- `battery` - Battery specification
- `color` - Item color

## Sample CSV Format

```csv
item_name,category,brand,supplier,price,quantity,unit_size,serial_no,condition,warranty,service_warranty,vat,tax,description,ram,storage,processor,graphics,display,battery,color
Dell Laptop,Laptop,Dell,Tech Supplier,55000,1,PCS,DL123456,New,1 Year,1 Year,0,0,Dell Latitude 5420,8GB,512GB SSD,Intel Core i5,Intel UHD,14 inch,40Wh,Black
HP Monitor,Monitor,HP,Tech Supplier,12000,1,PCS,HP789012,New,2 Years,1 Year,0,0,HP 24 inch LED Monitor,,,,,24 inch,,Black
Keyboard,Accessories,Logitech,Tech Supplier,1500,1,PCS,LK555123,New,1 Year,,0,0,Logitech Wireless Keyboard,,,,,,,,Black
```

## How It Works

### Step 1: Upload CSV File
1. Go to the CSV upload page
2. Click "Download Sample CSV" to get a template (optional)
3. Prepare your CSV file with the required columns
4. Click "Choose File" and select your CSV file
5. Click "Upload & Preview"

### Step 2: Preview and Verify
- The system will show a preview of all items from your CSV
- Auto-matched items will be highlighted in **green** (items that already exist in the system)
- New items will be highlighted in **yellow** (items that will be created)
- You can manually change any dropdown selection before importing

### Step 3: Import to Database
- Review all the data carefully
- Click "Confirm & Import to Database" to complete the import
- The system will:
  - Create new items if they don't exist
  - Create new brands/suppliers/categories if they don't exist
  - Generate unique inventory codes automatically
  - Add all items to the inventory with proper tracking

## Important Notes

### Automatic Matching
- If an item with the same name exists in your organization, it will be automatically matched
- If a brand/supplier/category exists, it will be matched; otherwise, a new one will be created
- Items are matched based on: item name + category + RAM + storage + processor + condition

### Unique Codes
- Each inventory item gets a unique code (`set_uniq_no`) automatically generated
- Format: `BR{branch_id}-{number}-CAT{category_id}`
- Items with identical specifications will share the same `set_uniq_no`

### Stock In Number
- Each item also gets a `stock_in_no` in the format: `SI-{set_uniq_no}`

### 🖥️ Desktop/Laptop/Server Package Feature (NEW!)
When you upload a Desktop, Laptop, or Server with RAM, Processor, and Storage specifications, the system automatically creates a **package**:

#### How it works:
If your CSV has:
```csv
item_name,category,price,quantity,ram,storage,processor,condition
Dell Laptop,Laptop,55000,1,8GB,512GB SSD,Intel Core i5,New
```

The system will create **4 separate inventory entries** with the **same SET_UNIQ_NO**:
1. **Dell Laptop** - Main item (SET_UNIQ_NO: BR1-5001-CAT3)
2. **RAM 8GB** - Separate entry (same SET_UNIQ_NO: BR1-5001-CAT3)
3. **SSD 512GB** - Separate entry (same SET_UNIQ_NO: BR1-5001-CAT3)
4. **Intel Core i5 Processor** - Separate entry (same SET_UNIQ_NO: BR1-5001-CAT3)

#### Benefits:
- ✅ All components grouped together by `SET_UNIQ_NO`
- ✅ Behaves exactly like manual entry
- ✅ Easy stock management
- ✅ Components can be tracked individually
- ✅ Each new computer gets a unique `SET_UNIQ_NO`

#### Item IDs:
- RAM → Item ID: 17
- Processor → Item ID: 41
- HDD → Item ID: 12
- SSD → Item ID: 14 (auto-detected if storage contains "SSD")

### Error Handling
- Rows with missing required fields will be skipped
- Error messages will be displayed if any issues occur
- The import uses database transactions, so if any error occurs, no data will be saved

## Tips for Success

1. **Use the Sample CSV**: Download and use the sample CSV as a template to ensure correct formatting
2. **Required Fields First**: Make sure all required fields (item_name, category, price, quantity) are filled
3. **Consistent Naming**: Use consistent names for brands/suppliers/categories to avoid duplicates
4. **Check Preview**: Always review the preview before confirming the import
5. **Small Batches**: For large imports, consider uploading in smaller batches to make verification easier

## Troubleshooting

### "Invalid CSV format" error
- Make sure your file has the required column headers
- Check that the file is saved as CSV (comma-separated values)

### Items not matching automatically
- Check the spelling of item names, brands, suppliers, and categories
- Names are case-sensitive and must match exactly

### Import fails
- Check that all required fields are filled
- Verify that numeric fields (price, quantity, vat, tax) contain valid numbers
- Make sure the CSV file is not corrupted

## Support
For additional help or questions, please contact the system administrator.
