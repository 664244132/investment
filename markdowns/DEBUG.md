# 🐞 DEBUG.md: คู่มือการตรวจสอบและแก้ไขข้อผิดพลาดสำหรับ AI Agents & Developers (Comprehensive Debugging Playbook)

เอกสารนี้รวบรวมแนวทางการค้นหา วิเคราะห์ และแก้ไขข้อผิดพลาด (Systematic Debugging Playbook) สำหรับระบบ **SmartPort AI** บนสภาพแวดล้อม PHP 8.x + MySQL (XAMPP) ออกแบบมาเพื่อให้ AI Agents และนักพัฒนาสามารถวินิจฉัยปัญหาได้อย่างเป็นระบบและรวดเร็ว

---

## 1. การตรวจสุขภาพระบบอัตโนมัติ (Automated Self-Diagnosis Suite)

ระบบมีหน้าตรวจสอบสุขภาพอัตโนมัติ 23 รายการครอบคลุม 5 หมวดหมู่ สามารถรันได้ทั้งผ่าน Web UI และ CLI:

### 1.1 รันผ่าน Web Browser
- เปิด URL: `http://localhost/investment/pages/health_check.php`
- ระบบจะตรวจสอบ:
  1. **PHP Runtime:** Version (>= 8.0), cURL extension, PDO MySQL, OpenSSL, Memory Limit, Strict Types
  2. **Database:** Connection, Table Existences (11 ตาราง), Stock Seed Data, Index Check
  3. **Local Assets (Zero CDN):** `css/variables.css`, `css/layout.css`, `css/components.css`, `js/main.js`, Favicons
  4. **Financial Calculation Engines:** Altman Z-Score, Piotroski F-Score, Composite Score (0-100), DCA Amortization, Comparison Engine
  5. **Security & Session:** CSRF Token Generator, BCRYPT Cost 12 Hashing, Session Path Writable, XSS Escaping

### 1.2 รันผ่าน Terminal / CLI
```powershell
# ตรวจสอบ Syntax ของทุกไฟล์ในโปรเจกต์
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & C:\xampp\php\php.exe -l $_.FullName }

# ตรวจสอบการทำงานของ Health Check ผ่าน CLI
C:\xampp\php\php.exe pages/health_check.php
```

---

## 2. การตรวจสอบการเชื่อมต่อและดึงข้อมูล Live Market Data API

ระบบเชื่อมต่อกับ Global Market API เพื่อดึงราคาและข้อมูลกราฟ 100% พร้อมแคช 60 วินาทีในฐานข้อมูล

### 2.1 ตรวจสอบ API Quote Endpoint
```bash
# ทดสอบดึงราคาหุ้นปัจจุบัน
curl "http://localhost/investment/api/stock_quote.php?symbol=PTT"
curl "http://localhost/investment/api/stock_quote.php?symbol=NVDA"
```
**Expected Response:**
```json
{"success":true,"data":{"symbol":"NVDA","current_price":128.5,"percent_change":2.34,"source":"live_api"}}
```

### 2.2 ตรวจสอบ Multi-Timeframe Chart API
```bash
# ทดสอบกราฟ 6 ช่วงเวลา
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1H"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1D"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1W"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1M"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1Y"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=ALL"
```
**Troubleshooting Chart API:**
- หาก `1M` ส่งข้อมูลว่าง ให้ตรวจเช็ค `getStockPriceHistory()` ใน `functions/stocks.php` ว่าใช้ Subquery เรียงลำดับ `trade_date DESC LIMIT 30` ซ้อนด้วย `ORDER BY trade_date ASC`
- หาก `1H` และ `1W` กราฟเหมือนกัน ให้ตรวจเช็ค `fetchRealtimeStockPriceHistory()` ใน `functions/market_api.php` ว่าส่ง `interval=2m&range=1d` สำหรับ 1H และ `interval=1h&range=5d` สำหรับ 1W

### 2.3 ตรวจสอบ Live Search & Global Stock Ingestion
```bash
curl "http://localhost/investment/api/search.php?q=AAPL"
```
**Expected Response:** รายการหุ้น Apple Inc. พร้อมราคาตลาดสด และเพิ่มหุ้นเข้าตาราง `stocks` อัตโนมัติหากเป็นหุ้นสากลตัวใหม่

---

## 3. ตำแหน่งไฟล์บันทึกข้อผิดพลาด (Log File Locations in XAMPP)

| ระบบที่เกี่ยวข้อง | ตำแหน่งไฟล์ Log (XAMPP บน Windows) | สิ่งที่ต้องค้นหา |
| :--- | :--- | :--- |
| **PHP Errors** | `C:\xampp\php\logs\php_error_log` | Fatal errors, Type errors, Uncaught Exceptions |
| **Apache Web Server** | `C:\xampp\apache\logs\error.log` | ข้อผิดพลาดการโหลดโมดูล, `.htaccess` rewrite errors, 500 Internal Server Error |
| **MySQL Database** | `C:\xampp\mysql\data\mysql_error.log` | ข้อผิดพลาด Port 3306 ชน, Table corrupted, Access denied |

---

## 4. ปัญหาที่พบบ่อยและแนวทางแก้ไข (Troubleshooting Matrix)

### 🔴 ปัญหา 1: ไม่สามารถเชื่อมต่อฐานข้อมูลได้ (`Database Connection Error`)
- **สาเหตุ:** MySQL ยังไม่รัน หรือค่ารหัสผ่านใน `config/constants.php` ไม่ตรงกับ MySQL ใน XAMPP
- **วิธีแก้:**
  1. เปิด **XAMPP Control Panel** -> กดปุ่ม **Start** ที่ MySQL (สถานะเป็นสีเขียว Port 3306)
  2. ตรวจสอบ `config/constants.php` ค่า `DB_HOST`, `DB_USER`, `DB_NAME`
  3. ตรวจสอบสิทธิ์การเชื่อมต่อผ่าน `pages/health_check.php`

### 🔴 ปัญหา 2: Session หลุดหรือ CSRF Token Mismatch
- **สาเหตุ:** Session cookie โดนบล็อก หรือไม่ได้ส่ง token ในฟอร์ม POST
- **วิธีแก้:**
  1. ตรวจสอบว่าไฟล์มีการเรียก `require_once __DIR__ . '/../functions/auth.php'`
  2. ฟอร์ม POST ทุกฟอร์มต้องมี `<?= getCsrfInputField() ?>`
  3. ใน API AJAX ต้องส่ง header `X-CSRF-Token` หรือ query param `csrf_token`

### 🔴 ปัญหา 3: กราฟไม่ขยับเมื่อเปลี่ยนปุ่ม Timeframe (1H, 1D, 1W, 1M, 1Y, ALL)
- **สาเหตุ:** Event Listener ไม่ได้ถูก bind หรือ Chart Canvas ID ไม่ตรง
- **วิธีแก้:**
  1. ตรวจสอบ Console (`F12`) ว่ามี error จาก `loadTimeframeChart()` หรือไม่
  2. ตรวจสอบว่า Element `<canvas id="priceTrendCanvas">` มีอยู่จริงใน DOM
  3. ตรวจสอบ response จาก `/api/chart_data.php?symbol=...&timeframe=...` ในแท็บ Network

### 🔴 ปัญหา 4: คำสั่งซื้อ-ขายในพอร์ตจำลองไม่ทำงาน
- **สาเหตุ:** เงินสดคงเหลือในพอร์ตไม่เพียงพอ (รวมค่าธรรมเนียม 0.157% แล้วเกิน) หรือส่งคำสั่งขายมากกว่าจำนวนหุ้นที่มีอยู่
- **วิธีแก้:**
  1. ดูข้อความแจ้งเตือน Flash Alert สีแดงบนหน้าเว็บ
  2. ตรวจสอบยอดเงินสดคงเหลือ (`cash_balance`) ในหน้าพอร์ต

### 🔴 ปัญหา 5: หน้าพอร์ตจำลองเกิด TypeError หรือ Undefined Key ในตารางประวัติธุรกรรม
- **สาเหตุ:** เรียกใช้ฟิลด์วันที่ผิดชื่อ (เช่น `$tx['executed_at']` แทนที่จะเป็น `$tx['created_at']`) หรือเรียกใช้ `$tx['net_amount']` ที่ไม่ได้เป็นคอลัมน์ในตาราง
- **วิธีแก้:**
  1. ในตาราง `transactions` ให้ใช้ฟิลด์ `created_at` สำหรับวันเวลาที่ทำรายการเสมอ
  2. คำนวณมูลค่าสุทธิด้วยสูตร: `($isBuy ? $total_amount + $fee : $total_amount - $fee)`

---

## 5. กฎเหล็กที่ AI Agents ต้องปฏิบัติตามเสมอ (Mandatory Rules Compliance)
1. **ห้ามรัน DDL/DML สร้างหรือแก้ไขตารางฐานข้อมูลเองโดยพลการ** (Rule 10) - ต้องจัดเตรียมโค้ด SQL ให้ผู้ใช้เป็นผู้ Import
2. **ห้าม Push หรือ Commit ขึ้น Git เด็ดขาด** (Rule 11) - ผู้ใช้จะเป็นผู้ควบคุม Version Control เอง
3. **ห้ามใช้ Emojis บน UI** (Rule 1) - ให้ใช้ฟังก์ชัน `icon('name')` จาก `functions/icons.php` เท่านั้น
4. **ห้ามใช้ External CDN** (Rule 2) - ต้องใช้ Local Assets 100%
5. **ห้ามเขียน Inline `style="..."`** (Rule 6) - ต้องใช้ CSS Classes จาก `css/components.css` และ `css/layout.css`
6. **ใช้ De Morgan's Laws เสมอ** - เขียนเงื่อนไขในรูปแบบ `!A || !B` หรือ `!A && !B` หลีกเลี่ยง `!(A && B)`

