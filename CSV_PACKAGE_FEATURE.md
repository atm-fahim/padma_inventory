# Desktop/Laptop/Server Package Feature - CSV Upload

## 🎯 নতুন Feature যুক্ত হয়েছে!

এখন CSV upload এ Desktop, Laptop, বা Server এর সাথে RAM, Processor, Storage দিলে **automatic package** তৈরি হবে - ঠিক যেমন manual entry তে হয়!

## 🖥️ কিভাবে কাজ করে?

### উদাহরণ ১: Dell Laptop Package

CSV file এ এইভাবে লিখুন:
```csv
item_name,category,brand,supplier,price,quantity,ram,storage,processor,condition,warranty
Dell Laptop,Laptop,Dell,Tech Supplier,55000,1,8GB,512GB SSD,Intel Core i5,New,1 Year
```

### ফলাফল:
System **automatically 4টি inventory entry** তৈরি করবে:

| Entry | Item | Category | SET_UNIQ_NO | Stock-In No |
|-------|------|----------|-------------|-------------|
| 1 | Dell Laptop | Laptop | BR1-5001-CAT3 | SI-BR1-5001-CAT3 |
| 2 | RAM 8GB | RAM | BR1-5001-CAT3 | SI-BR1-5001-CAT3 |
| 3 | SSD 512GB | SSD | BR1-5001-CAT3 | SI-BR1-5001-CAT3 |
| 4 | Intel Core i5 | Processor | BR1-5001-CAT3 | SI-BR1-5001-CAT3 |

**সব entries একই `SET_UNIQ_NO` share করবে!** এটাই package।

### উদাহরণ ২: আরেকটি Laptop যোগ করুন

CSV file এ আরেকটি laptop add করুন:
```csv
item_name,category,brand,supplier,price,quantity,ram,storage,processor,condition,warranty
Dell Laptop,Laptop,Dell,Tech Supplier,55000,1,8GB,512GB SSD,Intel Core i5,New,1 Year
HP Laptop,Laptop,HP,Tech Supplier,45000,1,16GB,1TB HDD,AMD Ryzen 5,New,1 Year
```

### ফলাফল:
এখন **8টি entries** তৈরি হবে (প্রতিটি laptop এর জন্য 4টি করে):

**First Package (Dell):**
| Entry | Item | SET_UNIQ_NO |
|-------|------|-------------|
| 1 | Dell Laptop | BR1-5001-CAT3 |
| 2 | RAM 8GB | BR1-5001-CAT3 |
| 3 | SSD 512GB | BR1-5001-CAT3 |
| 4 | Intel Core i5 | BR1-5001-CAT3 |

**Second Package (HP) - নতুন SET_UNIQ_NO:**
| Entry | Item | SET_UNIQ_NO |
|-------|------|-------------|
| 5 | HP Laptop | BR1-5002-CAT3 |
| 6 | RAM 16GB | BR1-5002-CAT3 |
| 7 | HDD 1TB | BR1-5002-CAT3 |
| 8 | AMD Ryzen 5 | BR1-5002-CAT3 |

## 📋 Component Item IDs

System এই Item IDs use করবে:
- **RAM** → Item ID: `17`
- **Processor** → Item ID: `41`
- **HDD** → Item ID: `12`
- **SSD** → Item ID: `14`

### Storage Type Detection:
- যদি storage field এ **"SSD"** থাকে → SSD item (ID: 14) তৈরি হবে
- যদি storage field এ **"HDD"** থাকে → HDD item (ID: 12) তৈরি হবে
- Default → HDD

## ✅ সুবিধাসমূহ

1. **Package Management**: সব components একসাথে grouped
2. **Stock Tracking**: প্রতিটি component আলাদাভাবে track করা যায়
3. **Same Behavior**: Manual entry এর মতোই কাজ করে
4. **Automatic**: কোন extra step লাগে না
5. **Unique Grouping**: প্রতিটি computer এর unique SET_UNIQ_NO

## 🎨 Sample CSV

```csv
item_name,category,brand,supplier,price,quantity,unit_size,serial_no,condition,warranty,ram,storage,processor
Dell Laptop,Laptop,Dell,Tech Supplier,55000,1,PCS,DL123456,New,1 Year,8GB,512GB SSD,Intel Core i5
HP Desktop,Desktop,HP,Tech Supplier,45000,1,PCS,HP998877,New,1 Year,16GB,1TB HDD,Intel Core i7
Lenovo Server,Server,Lenovo,Tech Supplier,85000,1,PCS,LN445566,New,3 Years,32GB,2TB HDD,Intel Xeon
```

## 📊 Database Entries Example

### Dell Laptop থেকে যা তৈরি হবে:

**Entry 1: Main Laptop**
```
item_id: [Laptop Item ID]
category_id: [Laptop Category]
set_uniq_no: BR1-5001-CAT3
ram: 8GB
storage: 512GB SSD
processor: Intel Core i5
price: 55000
```

**Entry 2: RAM**
```
item_id: 17
category_id: [RAM Category]
set_uniq_no: BR1-5001-CAT3
ram: 8GB
description: 8GB
price: 0
```

**Entry 3: SSD**
```
item_id: 14
category_id: [SSD Category]
set_uniq_no: BR1-5001-CAT3
storage: 512GB SSD
description: 512GB SSD
price: 0
```

**Entry 4: Processor**
```
item_id: 41
category_id: [Processor Category]
set_uniq_no: BR1-5001-CAT3
processor: Intel Core i5
description: Intel Core i5
price: 0
```

## 🔍 কখন Package তৈরি হবে?

Package তৈরি হবে যদি:
- ✅ Item একটি Desktop/Laptop/Server হয়
- ✅ CSV file এ `ram` অথবা `storage` অথবা `processor` field fill করা থাকে
- ✅ Field গুলো empty না হয়

## ⚠️ Important Notes

1. **Main Item Price**: শুধুমাত্র main item (laptop/desktop/server) এর price দিন
2. **Component Price**: RAM, Storage, Processor এর price automatically 0 হবে
3. **Condition**: Main item এর condition সব components এ apply হবে
4. **Warranty**: Main item এর warranty সব components এ apply হবে
5. **Supplier**: Main item এর supplier সব components এ apply হবে

## 🚀 এখনই Test করুন!

1. Navigate: `http://127.0.0.1:8000/items-inventory-csv-upload`
2. Download sample CSV
3. Dell Laptop example দিয়ে test করুন
4. Database check করুন - 4টি entries দেখতে পাবেন একই SET_UNIQ_NO সহ

## 📚 Related Documentation

- **QUICK_START_CSV_UPLOAD.md** - দ্রুত শুরু করার গাইড
- **README_CSV_UPLOAD_BANGLA.md** - সম্পূর্ণ বাংলা গাইড
- **CSV_UPLOAD_GUIDE.md** - English guide
- **sample_inventory_upload.csv** - Sample file with examples

---

**Feature Status**: ✅ Ready and Tested
**Date**: January 19, 2026
**Project**: Padma Inventory System
