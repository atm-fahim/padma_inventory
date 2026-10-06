# Item Inventory CSV Upload - সম্পূর্ণ গাইড

## সংক্ষিপ্ত বিবরণ
আপনার Padma Inventory System এ এখন CSV file এর মাধ্যমে Item Inventory তে data insert করার সুবিধা যুক্ত করা হয়েছে।

## কিভাবে ব্যবহার করবেন

### ধাপ ১: CSV Upload পেজে যান
দুইটি উপায়ে যেতে পারেন:
1. সরাসরি লিংক: `http://127.0.0.1:8000/items-inventory-csv-upload`
2. Item Inventory পেজ থেকে "Excel Upload File CSV" বাটনে ক্লিক করুন

### ধাপ ২: CSV File প্রস্তুত করুন

#### আবশ্যক কলাম (Required):
- `item_name` - আইটেমের নাম
- `category` - ক্যাটাগরি নাম
- `price` - মূল্য
- `quantity` - পরিমাণ

#### ঐচ্ছিক কলাম (Optional):
- `brand` - ব্র্যান্ড নাম
- `supplier` - সাপ্লায়ার নাম
- `unit_size` - ইউনিট সাইজ (যেমন: PCS, KG, L)
- `serial_no` - সিরিয়াল নম্বর
- `condition` - অবস্থা (নতুন/ব্যবহৃত)
- `warranty` - ওয়ারেন্টি সময়কাল
- `service_warranty` - সার্ভিস ওয়ারেন্টি
- `vat` - ভ্যাট
- `tax` - ট্যাক্স
- `description` - বিবরণ
- `ram` - RAM (কম্পিউটারের জন্য)
- `storage` - Storage (কম্পিউটারের জন্য)
- `processor` - Processor
- `graphics` - Graphics Card
- `display` - Display
- `battery` - Battery
- `color` - রঙ

### ধাপ ৩: CSV File Upload করুন
1. "Download Sample CSV" বাটনে ক্লিক করে নমুনা ফাইল ডাউনলোড করুন (ঐচ্ছিক)
2. আপনার CSV file তৈরি করুন
3. "Select CSV File" এ ক্লিক করে file select করুন
4. "Upload & Preview" বাটনে ক্লিক করুন

### ধাপ ৪: Preview দেখুন এবং যাচাই করুন
- সব data একটি টেবিলে প্রদর্শিত হবে
- **সবুজ** চিহ্ন = system এ ইতিমধ্যে আছে (auto-matched)
- **হলুদ** চিহ্ন = নতুন তৈরি হবে
- প্রয়োজনে dropdown থেকে পরিবর্তন করতে পারবেন

### ধাপ ৫: Database এ Import করুন
- সব কিছু ঠিক আছে কিনা চেক করুন
- "Confirm & Import to Database" বাটনে ক্লিক করুন
- সফল হলে success message দেখাবে

## উদাহরণ CSV ফাইল

```csv
item_name,category,brand,supplier,price,quantity,unit_size,serial_no,condition,warranty,description
Dell Laptop,Laptop,Dell,Tech Supplier,55000,1,PCS,DL123456,New,1 Year,Dell Latitude 5420
HP Monitor,Monitor,HP,Tech Supplier,12000,1,PCS,HP789012,New,2 Years,HP 24 inch LED
Keyboard,Accessories,Logitech,Tech Supplier,1500,1,PCS,LK555123,New,1 Year,Logitech Wireless Keyboard
```

## বিশেষ সুবিধা

### অটো-ম্যাচিং (Auto-Matching)
- যদি আইটেম, ব্র্যান্ড, সাপ্লায়ার বা ক্যাটাগরি ইতিমধ্যে থাকে, তাহলে automatically match করবে
- না থাকলে নতুন তৈরি করে দেবে

### ইউনিক কোড জেনারেশন
- প্রতিটি inventory item এর জন্য unique code তৈরি হবে
- Format: `BR{branch_id}-{number}-CAT{category_id}`
- উদাহরণ: `BR1-5001-CAT3`

### Stock-In Number
- প্রতিটি item এর জন্য stock-in number তৈরি হবে
- Format: `SI-{set_uniq_no}`

### স্পেসিফিকেশন গ্রুপিং
একই specification এর আইটেম গুলো একই group এ থাকবে:
- একই item_id
- একই category
- একই RAM, Storage, Processor
- একই condition

এসব আইটেম একই `set_uniq_no` share করবে।

### 🖥️ Desktop/Laptop/Server Package Feature
**নতুন বৈশিষ্ট্য!** Desktop, Laptop বা Server এর সাথে RAM, Processor, Storage দিলে automatic package তৈরি হবে:

#### কিভাবে কাজ করে:
যখন আপনি CSV file এ Desktop/Laptop/Server এর সাথে RAM, Storage, Processor দেবেন:

```csv
item_name,category,price,quantity,ram,storage,processor,condition
Dell Laptop,Laptop,55000,1,8GB,512GB SSD,Intel Core i5,New
```

তখন **automatically 4টি inventory entry** তৈরি হবে:
1. **Dell Laptop** - Main item (SET_UNIQ_NO: BR1-5001-CAT3)
2. **RAM 8GB** - Separate entry (একই SET_UNIQ_NO: BR1-5001-CAT3)
3. **SSD 512GB** - Separate entry (একই SET_UNIQ_NO: BR1-5001-CAT3)
4. **Intel Core i5 Processor** - Separate entry (একই SET_UNIQ_NO: BR1-5001-CAT3)

#### সুবিধা:
- ✅ একসাথে package আকারে save হয়
- ✅ সব components একই `SET_UNIQ_NO` share করে
- ✅ Manual entry এর মতোই behavior
- ✅ Stock management সহজ হয়
- ✅ পরে আলাদা আলাদা track করা যায়

#### আরেকটি laptop যোগ করলে:
```csv
item_name,category,price,quantity,ram,storage,processor,condition
HP Laptop,Laptop,45000,1,16GB,1TB HDD,AMD Ryzen 5,New
```

এটির জন্য **নতুন SET_UNIQ_NO** হবে (যেমন: BR1-5002-CAT3) এবং 4টি নতুন entry তৈরি হবে।

#### Important Notes:
- RAM field থাকলে → RAM item (ID: 17) তৈরি হবে
- Storage field এ "SSD" থাকলে → SSD item (ID: 14) তৈরি হবে
- Storage field এ "HDD" থাকলে → HDD item (ID: 12) তৈরি হবে
- Processor field থাকলে → Processor item (ID: 41) তৈরি হবে
- সব components একই `condition`, `warranty`, `supplier` পাবে

## Important Notes

### যেসব বিষয়ে সতর্ক থাকবেন:
1. CSV file অবশ্যই comma-separated হতে হবে
2. File size সর্বোচ্চ 10MB
3. Required column গুলো অবশ্যই থাকতে হবে
4. Number field এ শুধু সংখ্যা লিখবেন

### Error হলে:
- Error message মনোযোগ সহকারে পড়ুন
- CSV format ঠিক আছে কিনা চেক করুন
- Required field গুলো fill করা আছে কিনা দেখুন
- আবার try করুন

## সাহায্য

### Sample File কোথায় পাবেন:
- Upload পেজ থেকে "Download Sample CSV" ক্লিক করুন
- অথবা `sample_inventory_upload.csv` file ব্যবহার করুন

### আরও তথ্যের জন্য:
- `CSV_UPLOAD_GUIDE.md` - বিস্তারিত ইংরেজি গাইড
- `CSV_UPLOAD_IMPLEMENTATION.md` - Technical details

## সুবিধাসমূহ

✅ দ্রুত একসাথে অনেক item add করা যায়
✅ Manual entry এর থেকে সময় বাঁচে
✅ Error কম হয়
✅ Preview দেখে নিশ্চিত হওয়া যায়
✅ Auto-matching এর ফলে duplicate কম হয়
✅ Unique code automatically তৈরি হয়
✅ Database transaction দিয়ে data safety নিশ্চিত

## সমস্যা সমাধান

### "Invalid CSV format" error
- CSV file এর header ঠিক আছে কিনা চেক করুন
- Required column গুলো আছে কিনা দেখুন

### Item match হচ্ছে না
- Spelling ঠিক আছে কিনা চেক করুন
- Name exactly same হতে হবে (case-sensitive)

### Import fail হচ্ছে
- সব required field fill করা আছে কিনা দেখুন
- Number field এ valid number আছে কিনা চেক করুন

## যোগাযোগ
আরও সাহায্যের জন্য system administrator এর সাথে যোগাযোগ করুন।
