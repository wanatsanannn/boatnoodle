# แผนพัฒนาระบบสั่งอาหารผ่าน QR Code — ร้านก๋วยเตี๋ยวเรือชาม

---

## 1. ภาพรวมโปรเจค

| หัวข้อ | รายละเอียด |
|--------|-----------|
| ชื่อระบบ | ระบบการสั่งอาหารออนไลน์ผ่านคิวอาร์โคด ร้านก๋วยเตี๋ยวเรือชาม |
| วัตถุประสงค์ | ให้ลูกค้าสแกน QR Code ที่โต๊ะเพื่อสั่งอาหารผ่านมือถือ ส่งออเดอร์ตรงไปครัวลดขั้นตอนพนักงาน |
| เทคโนโลยี | PHP 8+, MySQL (XAMPP), HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5 CDN |
| สภาพแวดล้อม | Hosting/Domain จริง |
| แนวทาง | เรียบง่าย ใช้งานได้จริง ไม่ใช้ Framework หนักๆ |

---

## 2. ผู้ใช้งานระบบ (Actors) และสิทธิ์

| ตำแหน่ง | รหัส | สิทธิ์การเข้าถึง |
|--------|------|------------------|
| ผู้จัดการ/แอดมิน | admin | เข้าถึงได้ทุกส่วน — จัดการเมนู, โต๊ะ, ผู้ใช้, รายงาน, ดูออเดอร์ |
| หัวหน้างาน | manager | จัดการเมนู, ดูออเดอร์, ดูรายงาน (ไม่จัดการผู้ใช้) |
| พ่อครัว/แม่ครัว | chef | เห็นออเดอร์ในครัว, อัปเดตสถานะอาหาร |
| พนักงานเสิร์ฟ | waiter | เห็นรายการพร้อมเสิร์ฟ, นำอาหารไปส่ง, เปลี่ยนสถานะเป็น "เสิร์ฟแล้ว" |
| แคชเชียร์ | cashier | ดูบิลตามโต๊ะ, บันทึกการรับชำระเงิน |
| ลูกค้า | (ไม่ต้องล็อกอิน) | สแกน QR → ดูเมนู → สั่งอาหาร → ดูสถานะ |

---

## 3. โครงสร้างฐานข้อมูล (MySQL)

ชื่อฐานข้อมูล: `noodle_shop`

### 3.1 ตาราง `users` — ผู้ใช้ระบบ

| คอลัมน์ | ชนิด | คำอธิบาย |
|---------|------|----------|
| id | INT AUTO_INCREMENT PK | รหัสผู้ใช้ |
| username | VARCHAR(50) UNIQUE | ชื่อผู้ใช้เข้าสู่ระบบ |
| password | VARCHAR(255) | รหัสผ่าน (bcrypt hash) |
| fullname | VARCHAR(100) | ชื่อ-นามสกุล |
| role | ENUM('admin','manager','chef','waiter','cashier') | ตำแหน่ง |
| phone | VARCHAR(20) NULL | เบอร์โทร |
| status | ENUM('active','inactive') DEFAULT 'active' | สถานะ |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | วันที่สร้าง |

### 3.2 ตาราง `categories` — หมวดหมู่อาหาร

| คอลัมน์ | ชนิด | คำอธิบาย |
|---------|------|----------|
| id | INT AUTO_INCREMENT PK | รหัสหมวดหมู่ |
| name | VARCHAR(100) | ชื่อหมวดหมู่ (เช่น ก๋วยเตี๋ยว, เครื่องดื่ม, ของทานเล่น) |
| sort_order | INT DEFAULT 0 | ลำดับการแสดงผล |
| status | ENUM('active','inactive') DEFAULT 'active' | สถานะ |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | วันที่สร้าง |

### 3.3 ตาราง `menu_items` — รายการอาหาร

| คอลัมน์ | ชนิด | คำอธิบาย |
|---------|------|----------|
| id | INT AUTO_INCREMENT PK | รหัสเมนู |
| category_id | INT FK → categories.id | หมวดหมู่ |
| name | VARCHAR(200) | ชื่อเมนู |
| description | TEXT NULL | คำอธิบายเมนู |
| image | VARCHAR(255) NULL | ชื่อไฟล์รูปภาพ (เก็บใน uploads/menu/) |
| price_normal | DECIMAL(8,2) | ราคา |
| price_special | DECIMAL(8,2) NULL | ราคาพิเศษ (เพิ่มปริมาณ) — NULL หมายถึงไม่มีตัวเลือกพิเศษ |
| has_spice_option | TINYINT(1) DEFAULT 1 | มีตัวเลือกความเผ็ดหรือไม่ (1=มี, 0=ไม่มี) |
| status | ENUM('available','sold_out') DEFAULT 'available' | สถานะ (พร้อมขาย/ของหมด) |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | วันที่สร้าง |
| updated_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | วันที่แก้ไขล่าสุด |

### 3.4 ตาราง `tables` — โต๊ะ

| คอลัมน์ | ชนิด | คำอธิบาย |
|---------|------|----------|
| id | INT AUTO_INCREMENT PK | รหัสโต๊ะ |
| table_number | VARCHAR(10) UNIQUE | หมายเลขโต๊ะ (เช่น "1", "2", "A1") |
| seats | INT | จำนวนที่นั่ง |
| qr_code | VARCHAR(255) NULL | ชื่อไฟล์ QR Code (เก็บใน uploads/qrcodes/) |
| status | ENUM('available','occupied') DEFAULT 'available' | สถานะโต๊ะ (ว่าง/มีลูกค้า) |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | วันที่สร้าง |

### 3.5 ตาราง `orders` — คำสั่งซื้อ

| คอลัมน์ | ชนิด | คำอธิบาย |
|---------|------|----------|
| id | INT AUTO_INCREMENT PK | รหัสคำสั่งซื้อ |
| table_id | INT FK → tables.id | โต๊ะที่สั่ง |
| order_number | VARCHAR(20) UNIQUE | เลขออเดอร์ (เช่น ORD-20260226-001) |
| status | ENUM('pending','cooking','ready','served','completed','cancelled') DEFAULT 'pending' | สถานะรวม |
| total_amount | DECIMAL(10,2) DEFAULT 0 | ยอดรวม |
| note | TEXT NULL | หมายเหตุเพิ่มเติม |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | เวลาสั่ง |
| updated_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | เวลาอัปเดตล่าสุด |

**หมายเหตุสถานะ:**
- `pending` — ส่งออเดอร์แล้ว รอครัวรับ
- `cooking` — ครัวกำลังทำ
- `ready` — อาหารพร้อมเสิร์ฟ
- `served` — เสิร์ฟแล้ว
- `completed` — ชำระเงินแล้ว ปิดออเดอร์
- `cancelled` — ยกเลิก

### 3.6 ตาราง `order_items` — รายการอาหารในคำสั่งซื้อ

| คอลัมน์ | ชนิด | คำอธิบาย |
|---------|------|----------|
| id | INT AUTO_INCREMENT PK | รหัสรายการ |
| order_id | INT FK → orders.id | คำสั่งซื้อ |
| menu_item_id | INT FK → menu_items.id | เมนูที่สั่ง |
| quantity | INT DEFAULT 1 | จำนวน |
| size | ENUM('normal','special') DEFAULT 'normal' | ขนาด (ธรรมดา/พิเศษ) |
| spice_level | TINYINT DEFAULT 0 | ระดับความเผ็ด (0=ไม่เผ็ด, 1=น้อย, 2=กลาง, 3=มาก) |
| unit_price | DECIMAL(8,2) | ราคาต่อชิ้น ณ เวลาสั่ง |
| subtotal | DECIMAL(10,2) | ราคารวมรายการนี้ (unit_price × quantity) |
| status | ENUM('pending','cooking','ready','served') DEFAULT 'pending' | สถานะรายการนี้ (ครัวอัปเดต) |
| note | VARCHAR(255) NULL | หมายเหตุ (เช่น "ไม่ใส่ผัก") |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | เวลาสั่ง |

### 3.7 ตาราง `payments` — การชำระเงิน

| คอลัมน์ | ชนิด | คำอธิบาย |
|---------|------|----------|
| id | INT AUTO_INCREMENT PK | รหัสการชำระ |
| table_id | INT FK → tables.id | โต๊ะ |
| total_amount | DECIMAL(10,2) | ยอดรวมที่ชำระ |
| method | ENUM('cash','promptpay') | วิธีชำระ |
| received_by | INT FK → users.id | พนักงานที่รับชำระ |
| note | TEXT NULL | หมายเหตุ |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | เวลาชำระ |

**ความสัมพันธ์:** เมื่อชำระเงิน → อัปเดต orders ทุกรายการของโต๊ะนั้นเป็น `completed` + เปลี่ยนสถานะโต๊ะเป็น `available`

### แผนภาพความสัมพันธ์ (ER Summary)

```
users ──────────────────────────────── payments.received_by
categories ──── 1:N ──── menu_items
tables ──────── 1:N ──── orders
tables ──────── 1:N ──── payments
orders ──────── 1:N ──── order_items
menu_items ──── 1:N ──── order_items
```

---

## 4. โครงสร้างไฟล์โปรเจค

```
ก๋วยเตี๋ยวเรือชาม/
│
├── config/
│   └── db.php                      # เชื่อมต่อฐานข้อมูล (PDO)
│
├── includes/
│   ├── header.php                  # HTML head + navbar (ส่วนพนักงาน)
│   ├── footer.php                  # ปิด HTML + โหลด JS
│   ├── auth.php                    # ตรวจสอบ session + สิทธิ์
│   └── functions.php               # ฟังก์ชันกลาง (format เงิน, สร้างเลขออเดอร์ ฯลฯ)
│
├── assets/
│   ├── css/
│   │   ├── style.css               # CSS กลาง
│   │   ├── customer.css            # CSS หน้าลูกค้า (มือถือเป็นหลัก)
│   │   ├── admin.css               # CSS หน้าแอดมิน
│   │   └── kitchen.css             # CSS หน้าจอครัว (ตัวอักษรใหญ่ชัดเจน)
│   ├── js/
│   │   ├── main.js                 # JS กลาง
│   │   ├── cart.js                 # จัดการตะกร้า (localStorage)
│   │   └── kitchen.js              # polling + แจ้งเตือนเสียง
│   └── uploads/
│       ├── menu/                   # รูปภาพเมนูอาหาร
│       └── qrcodes/                # ไฟล์ QR Code ของแต่ละโต๊ะ
│
├── admin/                          # === ส่วนผู้จัดการ/แอดมิน ===
│   ├── index.php                   # แดชบอร์ด (สรุปยอดวันนี้, ออเดอร์ล่าสุด)
│   ├── login.php                   # หน้าเข้าสู่ระบบ
│   ├── logout.php                  # ออกจากระบบ
│   ├── menu.php                    # รายการเมนูทั้งหมด + ปุ่มเพิ่ม/แก้ไข/เปลี่ยนสถานะ
│   ├── menu_form.php               # ฟอร์มเพิ่ม/แก้ไขเมนู (รวมอัปโหลดรูป)
│   ├── menu_action.php             # ประมวลผลบันทึก/ลบเมนู (POST handler)
│   ├── categories.php              # จัดการหมวดหมู่ (CRUD ในหน้าเดียว)
│   ├── category_action.php         # ประมวลผลหมวดหมู่
│   ├── tables.php                  # จัดการโต๊ะ + ปุ่มสร้าง QR Code
│   ├── table_action.php            # ประมวลผลโต๊ะ + สร้าง QR
│   ├── users.php                   # จัดการผู้ใช้ (เฉพาะ admin)
│   ├── user_action.php             # ประมวลผลผู้ใช้
│   ├── orders.php                  # ดูคำสั่งซื้อทั้งหมด + กรองสถานะ
│   ├── reports_sales.php           # รายงานยอดขาย (เลือกช่วงวันที่)
│   └── reports_popular.php         # รายงานเมนูขายดี (Top 10/20)
│
├── kitchen/                        # === ส่วนครัว ===
│   ├── index.php                   # หน้าจอแสดงออเดอร์ (auto-refresh, เสียงแจ้งเตือน)
│   └── update_status.php           # API อัปเดตสถานะรายการอาหาร (AJAX)
│
├── staff/                          # === ส่วนพนักงาน ===
│   ├── index.php                   # หน้าหลัก — แสดงรายการ "พร้อมเสิร์ฟ" + แจ้งเตือน
│   ├── serve.php                   # กดยืนยันเสิร์ฟแล้ว
│   └── payment.php                 # หน้ารับชำระเงิน (เลือกโต๊ะ → ดูบิล → บันทึก)
│
├── order/                          # === ส่วนลูกค้า (ไม่ต้อง login) ===
│   ├── index.php                   # หน้าเมนูอาหาร (รับ ?table=X จาก QR)
│   ├── cart.php                    # หน้าตะกร้าสินค้า
│   ├── confirm.php                 # ยืนยันคำสั่งซื้อ → บันทึกลง DB → ส่งไปครัว
│   ├── status.php                  # หน้าตรวจสอบสถานะออเดอร์ (auto-refresh)
│   └── api.php                     # API สำหรับ AJAX (ดึงเมนู, ส่งออเดอร์, เช็คสถานะ)
│
├── api/                            # === API กลาง ===
│   ├── orders.php                  # ดึงออเดอร์ตามสถานะ/โต๊ะ
│   └── notifications.php           # เช็คออเดอร์ใหม่/สถานะเปลี่ยน (สำหรับ polling)
│
├── lib/                            # === ไลบรารีภายนอก ===
│   └── phpqrcode/                  # ไลบรารีสร้าง QR Code (PHP QR Code)
│
├── sql/
│   └── database.sql                # ไฟล์ SQL สร้างฐานข้อมูล + ข้อมูลเริ่มต้น
│
└── index.php                       # หน้าแรก → redirect ไป admin/login.php
```

---

## 5. Flow การทำงานแต่ละส่วน

### 5.1 Flow ลูกค้าสั่งอาหาร

```
สแกน QR Code ที่โต๊ะ
    │
    ▼
เข้าหน้าเมนู (order/index.php?table=5)
    │  ├── เห็นเมนูแบ่งตามหมวดหมู่ + รูป + ราคา
    │  ├── ค้นหาเมนูได้
    │  └── เมนูที่ "ของหมด" จะเป็นสีเทา กดไม่ได้
    │
    ▼
กดเลือกเมนู → แสดง popup ตัวเลือก
    │  ├── เลือกขนาด: ธรรมดา / พิเศษ (ราคาเปลี่ยนตาม)
    │  ├── เลือกความเผ็ด: ไม่เผ็ด / น้อย / กลาง / มาก
    │  ├── จำนวน: +/-
    │  └── หมายเหตุ (ถ้ามี)
    │
    ▼
กดเพิ่มลงตะกร้า → เก็บใน localStorage ของเบราว์เซอร์
    │  └── แสดงจำนวนรายการที่ไอคอนตะกร้า
    │
    ▼
เปิดตะกร้า (order/cart.php)
    │  ├── ดูรายการทั้งหมด + ราคา
    │  ├── แก้ไขจำนวน / ลบรายการได้
    │  └── เห็นยอดรวม
    │
    ▼
กดยืนยันสั่งอาหาร
    │  ├── ส่งข้อมูลไป order/confirm.php (POST)
    │  ├── บันทึก orders + order_items ลง DB
    │  ├── อัปเดตสถานะโต๊ะเป็น "occupied"
    │  ├── ล้างตะกร้า
    │  └── ได้รับเลขออเดอร์
    │
    ▼
หน้าติดตามสถานะ (order/status.php?order=ORD-xxx)
    │  ├── แสดงสถานะแต่ละรายการ (กำลังเตรียม / พร้อมเสิร์ฟ / เสิร์ฟแล้ว)
    │  ├── auto-refresh ทุก 15 วินาที (AJAX polling)
    │  └── สามารถ "สั่งเพิ่ม" ได้ (กลับไปหน้าเมนู)
```

### 5.2 Flow ครัว (พ่อครัว/แม่ครัว)

```
เข้าสู่ระบบ (admin/login.php)
    │
    ▼
หน้าจอครัว (kitchen/index.php) — ออกแบบสำหรับจอใหญ่/แท็บเล็ต
    │  ├── แสดงออเดอร์ใหม่ เรียงตามเวลา
    │  ├── แต่ละออเดอร์แสดง: หมายเลขโต๊ะ, รายการอาหาร, ตัวเลือก, จำนวน
    │  ├── มีเสียงแจ้งเตือน "ติ๊ง" เมื่อมีออเดอร์ใหม่
    │  └── auto-refresh ทุก 10 วินาที (AJAX polling)
    │
    ▼
กดปุ่ม "กำลังเตรียม" → สถานะเปลี่ยนเป็น cooking
    │
    ▼
ทำเสร็จ → กดปุ่ม "พร้อมเสิร์ฟ" → สถานะเปลี่ยนเป็น ready
    │  └── ส่งแจ้งเตือนไปหน้าพนักงานเสิร์ฟ
```

### 5.3 Flow พนักงานเสิร์ฟ

```
เข้าสู่ระบบ
    │
    ▼
หน้าพนักงาน (staff/index.php)
    │  ├── แสดงรายการ "พร้อมเสิร์ฟ" — เน้นหมายเลขโต๊ะชัดเจน
    │  ├── มีเสียงแจ้งเตือนเมื่อมีรายการพร้อมเสิร์ฟ
    │  └── auto-refresh ทุก 10 วินาที
    │
    ▼
หยิบอาหาร → กดปุ่ม "เสิร์ฟแล้ว"
    │  └── สถานะเปลี่ยนเป็น served
```

### 5.4 Flow แคชเชียร์ (รับชำระเงิน)

```
เข้าสู่ระบบ
    │
    ▼
หน้ารับชำระ (staff/payment.php)
    │  ├── เลือกโต๊ะ → ดูออเดอร์ทั้งหมดของโต๊ะนั้น
    │  ├── แสดงรายการอาหาร + ราคา + ยอดรวม
    │  └── เลือกวิธีชำระ: เงินสด / PromptPay
    │
    ▼
กดบันทึกการชำระ
    │  ├── บันทึก payments
    │  ├── อัปเดต orders ทั้งหมดของโต๊ะเป็น completed
    │  ├── เปลี่ยนสถานะโต๊ะเป็น available
    │  └── พิมพ์ใบเสร็จ (ถ้าต้องการ — ขั้นต่อไป)
```

### 5.5 Flow แอดมิน/ผู้จัดการ

```
เข้าสู่ระบบ
    │
    ▼
แดชบอร์ด (admin/index.php)
    │  ├── ยอดขายวันนี้
    │  ├── จำนวนออเดอร์วันนี้
    │  ├── จำนวนโต๊ะที่ใช้งาน
    │  └── ออเดอร์ล่าสุด 10 รายการ
    │
    ├── จัดการเมนู → เพิ่ม/แก้ไข/เปลี่ยนสถานะ (พร้อมขาย ⟷ ของหมด)
    ├── จัดการหมวดหมู่ → เพิ่ม/แก้ไข/ลำดับ
    ├── จัดการโต๊ะ → เพิ่ม/แก้ไข/สร้าง QR Code
    ├── จัดการผู้ใช้ → เพิ่ม/แก้ไข/ปิดการใช้งาน (เฉพาะ admin)
    ├── รายงานยอดขาย → เลือกช่วงวัน → ดูยอด + กราฟ
    └── รายงานเมนูขายดี → Top N เมนูที่สั่งมากที่สุด
```

---

## 6. รายละเอียดเทคนิค

### 6.1 เชื่อมต่อฐานข้อมูล
- ใช้ **PDO** (PHP Data Objects) — ปลอดภัยกว่า mysqli
- ใช้ **Prepared Statements** ทุกคำสั่ง SQL เพื่อป้องกัน SQL Injection
- ไฟล์ `config/db.php` เก็บค่าเชื่อมต่อ

### 6.2 ระบบยืนยันตัวตน (Authentication)
- เข้ารหัสรหัสผ่านด้วย `password_hash()` (bcrypt)
- ตรวจสอบด้วย `password_verify()`
- เก็บข้อมูลผู้ใช้ใน `$_SESSION`
- ไฟล์ `includes/auth.php` ตรวจสอบทุกหน้าที่ต้อง login
- ตรวจสอบ role ก่อนเข้าแต่ละส่วน

### 6.3 QR Code
- ใช้ไลบรารี **PHP QR Code** (`phpqrcode`) — ไม่ต้องพึ่ง API ภายนอก
- QR Code เก็บ URL: `https://domain.com/order/?table={table_number}`
- สร้างเป็นไฟล์ PNG เก็บใน `assets/uploads/qrcodes/`
- แอดมินสามารถพิมพ์ QR Code ออกมาวางบนโต๊ะ

### 6.4 ตะกร้าสินค้า (Cart)
- ใช้ **localStorage** ของเบราว์เซอร์ — ไม่ต้องมี session ฝั่ง server
- เก็บเป็น JSON array: `[{menu_id, name, size, spice, qty, price, note}, ...]`
- เมื่อยืนยัน → ส่งข้อมูลทั้งหมดไป server ด้วย AJAX (POST)
- ล้างตะกร้าหลังสั่งสำเร็จ

### 6.5 การแจ้งเตือน (Notifications)
- ใช้ **AJAX Polling** — ง่าย ไม่ซับซ้อน เหมาะกับระบบขนาดเล็ก
- หน้าครัว: polling ทุก 10 วินาที → เช็คออเดอร์ใหม่ (status = pending)
- หน้าเสิร์ฟ: polling ทุก 10 วินาที → เช็ครายการ ready
- หน้าลูกค้า: polling ทุก 15 วินาที → เช็คสถานะออเดอร์ตัวเอง
- เสียงแจ้งเตือน: ใช้ `Audio` API เล่นเสียง "ding" เมื่อมีรายการใหม่

### 6.6 หน้าจอ Responsive
- ใช้ **Bootstrap 5** (CDN) — กริดระบบ, ปุ่ม, ฟอร์ม, modal
- หน้าลูกค้า: **Mobile-First** — ออกแบบสำหรับมือถือเป็นหลัก
- หน้าครัว: **Tablet-First** — ตัวอักษรใหญ่, ปุ่มใหญ่, อ่านง่าย
- หน้าแอดมิน: **Desktop-First** — ตาราง, ฟอร์ม, กราฟ

### 6.7 ความปลอดภัย
- รหัสผ่าน: `password_hash()` / `password_verify()`
- SQL: Prepared Statements เท่านั้น
- XSS: `htmlspecialchars()` ทุกครั้งที่แสดงข้อมูลจาก DB
- CSRF: ใช้ token ในฟอร์ม
- อัปโหลดไฟล์: ตรวจสอบนามสกุล (jpg, png, webp) + ขนาด + rename ไฟล์
- Session: ตั้ง `session.cookie_httponly = true`

### 6.8 เลขออเดอร์
- รูปแบบ: `ORD-YYYYMMDD-XXX` (เช่น ORD-20260226-001)
- สร้างจากวันที่ + ลำดับรายวัน (นับจำนวนออเดอร์วันนั้น + 1)

---

## 7. หน้าจอหลักที่ต้องพัฒนา (จำนวน 20 หน้า)

| # | หน้า | ไฟล์ | ผู้ใช้ | คำอธิบาย |
|---|------|------|--------|----------|
| 1 | เข้าสู่ระบบ | admin/login.php | ทุกคน | ฟอร์ม username + password |
| 2 | แดชบอร์ด | admin/index.php | admin, manager | สรุปยอดวันนี้ |
| 3 | จัดการหมวดหมู่ | admin/categories.php | admin, manager | CRUD หมวดหมู่ |
| 4 | รายการเมนู | admin/menu.php | admin, manager | ตารางเมนู + สถานะ |
| 5 | ฟอร์มเมนู | admin/menu_form.php | admin, manager | เพิ่ม/แก้ไขเมนู + อัปโหลดรูป |
| 6 | จัดการโต๊ะ | admin/tables.php | admin | ตารางโต๊ะ + สร้าง QR |
| 7 | จัดการผู้ใช้ | admin/users.php | admin | CRUD ผู้ใช้ + กำหนดบทบาท |
| 8 | ดูออเดอร์ | admin/orders.php | admin, manager | รายการออเดอร์ + กรอง |
| 9 | รายงานยอดขาย | admin/reports_sales.php | admin, manager | เลือกวัน → ยอดขาย |
| 10 | รายงานเมนูขายดี | admin/reports_popular.php | admin, manager | Top N เมนู |
| 11 | หน้าจอครัว | kitchen/index.php | chef | ออเดอร์ใหม่ + ปุ่มเปลี่ยนสถานะ |
| 12 | หน้าพนักงานเสิร์ฟ | staff/index.php | waiter | รายการพร้อมเสิร์ฟ |
| 13 | หน้ารับชำระเงิน | staff/payment.php | cashier | เลือกโต๊ะ → ดูบิล → ชำระ |
| 14 | เมนูอาหาร (ลูกค้า) | order/index.php | ลูกค้า | เมนู + หมวดหมู่ + ค้นหา |
| 15 | ตะกร้าสินค้า | order/cart.php | ลูกค้า | รายการที่เลือก + ยอดรวม |
| 16 | ยืนยันคำสั่งซื้อ | order/confirm.php | ลูกค้า | สรุป → กดสั่ง |
| 17 | สถานะออเดอร์ | order/status.php | ลูกค้า | ติดตามสถานะ real-time |

API (ไม่มีหน้าจอ):
| 18 | API ลูกค้า | order/api.php | — | ดึงเมนู, ส่งออเดอร์, เช็คสถานะ |
| 19 | API ครัว | kitchen/update_status.php | — | อัปเดตสถานะรายการ |
| 20 | API แจ้งเตือน | api/notifications.php | — | polling เช็คของใหม่ |

---

## 8. ลำดับการพัฒนา (Development Phases)

### Phase 1 — ฐานระบบ (Foundation)
**ระยะเวลา: เริ่มต้น**

- [x] วางโครงสร้างโฟลเดอร์
- [ ] สร้างฐานข้อมูล + ตาราง (database.sql)
- [ ] เขียน config/db.php (เชื่อมต่อ PDO)
- [ ] เขียน includes/functions.php (ฟังก์ชันกลาง)
- [ ] เขียน includes/auth.php (ระบบตรวจสอบสิทธิ์)
- [ ] เขียน includes/header.php + footer.php
- [ ] สร้างหน้า login + logout
- [ ] ใส่ข้อมูลเริ่มต้น (admin user, หมวดหมู่ตัวอย่าง)

### Phase 2 — ส่วนแอดมิน (Admin Panel)
**ระยะเวลา: หลัง Phase 1**

- [ ] แดชบอร์ด (สรุปยอด)
- [ ] CRUD หมวดหมู่
- [ ] CRUD เมนูอาหาร + อัปโหลดรูป + เปลี่ยนสถานะ
- [ ] CRUD โต๊ะ + สร้าง QR Code
- [ ] CRUD ผู้ใช้ + กำหนดสิทธิ์ (เฉพาะ admin)
- [ ] หน้าดูออเดอร์

### Phase 3 — ส่วนลูกค้า (Customer Ordering)
**ระยะเวลา: หลัง Phase 2**

- [ ] หน้าเมนูอาหาร (mobile-first)
- [ ] กรองตามหมวดหมู่ + ค้นหา
- [ ] Popup ตัวเลือก (ขนาด/ความเผ็ด/จำนวน)
- [ ] ระบบตะกร้า (localStorage + JS)
- [ ] หน้าตะกร้า (แก้ไข/ลบ)
- [ ] ยืนยันคำสั่งซื้อ → บันทึก DB
- [ ] หน้าติดตามสถานะ (auto-refresh)

### Phase 4 — ส่วนครัวและพนักงาน (Kitchen & Staff)
**ระยะเวลา: หลัง Phase 3**

- [ ] หน้าจอครัว (tablet-first, ตัวอักษรใหญ่)
- [ ] ปุ่มเปลี่ยนสถานะ (pending → cooking → ready)
- [ ] เสียงแจ้งเตือนออเดอร์ใหม่
- [ ] หน้าพนักงานเสิร์ฟ (รายการพร้อมเสิร์ฟ + หมายเลขโต๊ะ)
- [ ] ปุ่มยืนยันเสิร์ฟแล้ว
- [ ] หน้ารับชำระเงิน (เลือกโต๊ะ → ดูบิล → บันทึก)

### Phase 5 — รายงาน (Reports)
**ระยะเวลา: หลัง Phase 4**

- [ ] รายงานยอดขาย (เลือกช่วงวันที่)
- [ ] รายงานเมนูขายดี (Top 10)

### Phase 6 — ตกแต่งและทดสอบ (Polish & Testing)
**ระยะเวลา: สุดท้าย**

- [ ] ปรับ UI/UX ให้สวยงาม
- [ ] ทดสอบ Flow ทั้งหมดตั้งแต่สแกน QR ถึงชำระเงิน
- [ ] ทดสอบบนมือถือจริง
- [ ] ทดสอบบนแท็บเล็ต (หน้าครัว)
- [ ] แก้ไขบั๊ก
- [ ] เตรียม deploy ขึ้น hosting

---

## 9. ข้อมูลเริ่มต้น (Seed Data)

### ผู้ใช้เริ่มต้น
| username | password | role | ชื่อ |
|----------|----------|------|------|
| admin | admin1234 | admin | ผู้ดูแลระบบ |

### หมวดหมู่ตัวอย่าง
1. ก๋วยเตี๋ยวเรือ (หลัก)
2. เครื่องเคียง / ของทานเล่น
3. เครื่องดื่ม
4. ของหวาน

### ตัวอย่างเมนู
| เมนู | หมวดหมู่ | ธรรมดา | พิเศษ | เผ็ด |
|------|----------|--------|-------|------|
| ก๋วยเตี๋ยวเรือหมู | ก๋วยเตี๋ยวเรือ | 35 | 50 | ✓ |
| ก๋วยเตี๋ยวเรือเนื้อ | ก๋วยเตี๋ยวเรือ | 40 | 55 | ✓ |
| ก๋วยเตี๋ยวต้มยำ | ก๋วยเตี๋ยวเรือ | 45 | 60 | ✓ |
| ลูกชิ้นทอด | เครื่องเคียง | 20 | — | ✗ |
| น้ำเปล่า | เครื่องดื่ม | 10 | — | ✗ |
| ชาเย็น | เครื่องดื่ม | 25 | — | ✗ |

---

## 10. สรุปเทคโนโลยีที่ใช้

| ส่วน | เทคโนโลยี |
|------|-----------|
| Backend | PHP 8+ (Vanilla — ไม่ใช้ Framework) |
| Database | MySQL 8+ (ผ่าน XAMPP / Hosting) |
| Frontend | HTML5 + CSS3 + JavaScript (Vanilla) |
| CSS Framework | Bootstrap 5 (CDN) |
| QR Code | PHP QR Code Library (phpqrcode) |
| Icons | Bootstrap Icons (CDN) |
| Charts (รายงาน) | Chart.js (CDN) — กราฟยอดขาย |
| Font | Google Fonts — Sarabun (ภาษาไทย) |

---

## 11. คำถาม/ข้อตกลง

- [x] PromptPay → บันทึกอย่างเดียว (ไม่ต้องสร้าง QR จ่ายเงิน)
- [x] สภาพแวดล้อม → มี Hosting/Domain จริง
- [ ] ชื่อโดเมนที่จะใช้? (ใส่ใน QR Code)
- [ ] มีโลโก้ร้านหรือไม่? (ถ้ามีส่งมาใช้ตกแต่ง)
- [ ] จำนวนโต๊ะประมาณเท่าไหร่? (เพื่อทดสอบ)
- [ ] ต้องการพิมพ์ใบเสร็จหรือไม่? (เพิ่มภายหลังได้)

---

*เอกสารนี้เป็นแผนเริ่มต้น สามารถปรับเปลี่ยนได้ตามความต้องการ*
*พร้อมเริ่มพัฒนาเมื่อได้รับการยืนยัน*
