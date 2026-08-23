# 🐞 DEBUG.md: คู่มือการตรวจสอบและแก้ไขข้อผิดพลาด (Debugging Playbook)

เอกสารนี้รวบรวมแนวทางการค้นหา วิเคราะห์ และแก้ไขข้อผิดพลาด (Debugging Guide) สำหรับระบบ **AI Stock Analysis & Simulated Portfolio** บนสภาพแวดล้อม XAMPP (Apache + PHP 8.x + MySQL)

---

## 1. ตำแหน่งไฟล์บันทึกข้อผิดพลาด (Log File Locations in XAMPP)

เมื่อเกิดปัญหา หน้าจอว่างเปล่า (White Screen) หรือระบบแสดง Error 500 ให้ตรวจสอบไฟล์ Log ตามพาธต่อไปนี้เป็นอันดับแรก:

| ระบบที่เกี่ยวข้อง | ตำแหน่งไฟล์ Log (XAMPP บน Windows) | สิ่งที่ต้องค้นหา |
| :--- | :--- | :--- |
| **PHP Errors** | `C:\xampp\php\logs\php_error_log` | Fatal errors, Type errors, Uncaught Exceptions |
| **Apache Web Server** | `C:\xampp\apache\logs\error.log` | ข้อผิดพลาดการโหลดโมดูล, `.htaccess` rewrite errors, 500 Internal Server Error |
| **MySQL Database** | `C:\xampp\mysql\data\mysql_error.log` | ข้อผิดพลาด Port 3306 ชน, Table corrupted, Access denied |
| **Application Log** | ดูผ่านฟังก์ชัน `error_log()` ของ PHP | บันทึกการเชื่อมต่อฐานข้อมูลล้มเหลว หรือ Transaction ล้มเหลว |

---

## 2. การเปิดโหมดแสดง Error ระหว่างการพัฒนา (Development Error Reporting)

ในสภาพแวดล้อม Local (XAMPP) สามารถเปิดการแสดงข้อผิดพลาดบนหน้าจอได้โดยใส่โค้ดนี้ที่บรรทัดบนสุดของไฟล์ PHP ที่ต้องการทดสอบ:

```php
<?php
declare(strict_types=1);

// เปิดแสดง Error ทั้งหมดสำหรับการพัฒนา (ห้ามเปิดบน Production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_log_mode: E_ALL;
error_reporting(E_ALL);
```

---

## 3. ปัญหาที่พบบ่อยและแนวทางแก้ไข (Troubleshooting Common Issues)

### 🔴 ปัญหา 1: ไม่สามารถเชื่อมต่อฐานข้อมูลได้ (`Database Connection Error`)
- **สาเหตุที่เป็นไปได้:**
  1. MySQL ใน XAMPP ยังไม่ได้กด **"Start"**
  2. ยังไม่ได้สร้างฐานข้อมูล `investment_db` หรือยังไม่ได้ Import ไฟล์ `database_schema.sql`
  3. รหัสผ่านฐานข้อมูลใน `config/constants.php` ไม่ตรงกับ XAMPP
- **วิธีแก้ไข:**
  1. เปิด **XAMPP Control Panel** -> กดปุ่ม **Start** ที่โมดูล **MySQL** (สังเกตสถานะเป็นสีเขียวและมี Port 3306)
  2. เปิดเบราว์เซอร์ไปที่ `http://localhost/phpmyadmin` แล้วตรวจสอบว่ามีฐานข้อมูลชื่อ `investment_db` และมีตารางครบ 9 ตารางหรือไม่
  3. ตรวจสอบไฟล์ `config/constants.php` ว่าค่า `DB_USER` คือ `'root'` และ `DB_PASS` คือ `''` (ค่าเริ่มต้นของ XAMPP)

---

### 🔴 ปัญหา 2: Session หลุดหรือไม่สามารถเข้าสู่ระบบได้
- **สาเหตุที่เป็นไปได้:**
  1. โฟลเดอร์ Temp เก็บ Session ของ PHP เต็ม หรือไม่มีสิทธิ์เขียนไฟล์
  2. คุกกี้ในเบราว์เซอร์ถูกบล็อก
- **วิธีแก้ไข:**
  1. ตรวจสอบว่าในไฟล์มีการเรียก `startAppSession()` ก่อนส่ง Output ใดๆ ออกมา
  2. ล้าง Cookie ของ `localhost` ในเบราว์เซอร์ (กด `F12` -> Application -> Cookies -> Clear)

---

### 🔴 ปัญหา 3: กราฟราคาไม่แสดงผลบนหน้าจอ (Chart is blank)
- **สาเหตุที่เป็นไปได้:**
  1. ไฟล์ไลบรารี `Chart.js` โหลดไม่สำเร็จ (ปัญหาการเชื่อมต่ออินเทอร์เน็ต CDN)
  2. ไม่มีข้อมูลในตาราง `stock_price_history` สำหรับหุ้นตัวนั้น
  3. มีข้อผิดพลาดใน JavaScript Console
- **วิธีแก้ไข:**
  1. กด `F12` ในเบราว์เซอร์ -> ไปที่แท็บ **Console** เพื่อดูข้อความสีแดง
  2. ตรวจสอบว่าในตัวแปร `window.stockPriceHistory` มีข้อมูล JSON อยู่หรือไม่ โดยพิมพ์ `console.log(window.stockPriceHistory)` ในคอนโซล
  3. หากไม่มีข้อมูล ให้รันสคริปต์ Seed Data ใน `database_schema.sql` อีกครั้ง

---

### 🔴 ปัญหา 4: คำสั่งซื้อ-ขายในพอร์ตจำลองไม่ทำงาน
- **สาเหตุที่เป็นไปได้:**
  1. เงินสดคงเหลือในพอร์ตไม่เพียงพอ (หักรวมค่าคอมมิชชัน 0.157% แล้วเกินยอดเงินสด)
  2. ส่งคำสั่งขาย (SELL) ด้วยจำนวนหุ้นที่มากกว่าที่มีอยู่จริงใน `portfolio_items`
- **วิธีแก้ไข:**
  1. ดูข้อความแจ้งเตือน Flash Alert สีแดงบนหน้าเว็บ
  2. ตรวจสอบยอดเงินสดคงเหลือ (`cash_balance`) ในหน้าพอร์ต

---

## 4. เทคนิคการตรวจสอบตัวแปร (Inspection Techniques)

ใช้ฟังก์ชัน `var_dump()` ร่วมกับแท็ก `<pre>` หรือ `jsonResponse()` เพื่อดูค่าตัวแปร:

```php
// ตัวอย่างการ Dump ข้อมูลดูโครงสร้าง Array
echo '<pre style="background: #231B1B; color: #FFCC00; padding: 1rem; border-radius: 8px;">';
var_dump($stock);
echo '</pre>';
exit;
```
