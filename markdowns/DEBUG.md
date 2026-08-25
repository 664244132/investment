# 🐞 DEBUG.md: คู่มือการตรวจสอบและแก้ไขข้อผิดพลาดสำหรับ AI Agents & Developers (Comprehensive Debugging Playbook)

เอกสารนี้รวบรวมแนวทางการค้นหา วิเคราะห์ และแก้ไขข้อผิดพลาด (Systematic Debugging Playbook) สำหรับระบบ **SmartPort AI** บนสภาพแวดล้อม PHP 8.x + MySQL (XAMPP) ออกแบบมาเพื่อให้ AI Agents และนักพัฒนาสามารถวินิจฉัยปัญหาได้อย่างเป็นระบบ แม่นยำ และรวดเร็ว

---

## 1. การตรวจสุขภาพและทดสอบระบบอัตโนมัติ (Automated Self-Diagnosis Suite)

ระบบมีชุดทดสอบอัตโนมัติ 2 ระดับ ครอบคลุมการทำงานทั้งผ่าน CLI และ Web UI:

### 1.1 Automated CLI Test Suite Runner (`tests/run_tests.php`)
รันการทดสอบ 44 รายการครอบคลุม 12 หมวดหมู่:
1. **Strict Types:** ตรวจสอบ `declare(strict_types=1);` ในไฟล์ PHP ทุกไฟล์ (37 ไฟล์รวม `errors/`)
2. **De Morgan's Laws:** ตรวจสอบเงื่อนไข Boolean ปราศจาก `!(A && B)` หรือ `!(A || B)`
3. **Zero External CDN:** ยืนยันว่าไม่มีการเรียกใช้งาน Assets จากภายนอก
4. **Security & Escaping:** ทดสอบฟังก์ชัน `e()`, CSRF Token Generation & Verification, `getCsrfInputField()`
5. **Financial Formatting:** ทดสอบ `formatMoney()`, `formatNumber()`, `formatPercent()`, `formatCompactNumber()`, `getScoreColorClass()`, `getScoreBadgeClass()`
6. **Statistical Models:** ทดสอบความแม่นยำของแบบจำลอง Altman Z-Score & Piotroski F-Score
7. **DCA Compounding Engine:** ทดสอบการคำนวณเงินต้น, การทบต้น (DRIP), และตาราง Amortization
8. **SVG Vector Icons:** ทดสอบการเรนเดอร์ไอคอน Vector จาก `functions/icons.php`
9. **Database Connectivity:** ตรวจสอบการเชื่อมต่อฐานข้อมูลและตารางหลัก
10. **Paper Trading Guardrails:** ทดสอบการปฏิเสธคำสั่งซื้อ-ขายที่มีจำนวนหุ้น `<= 0`, ประเภทคำสั่งไม่ถูกต้อง, หรือพอร์ต/หุ้นที่ไม่มีอยู่จริง
11. **UI Template & Markdown Design Integrity:** ตรวจสอบ Trade Modal Alert Box, ปุ่ม Quick MAX, CSS Segmented Grids, และการลบไฮไลท์สีเหลืองใน Journal
12. **Multi-Currency & FX Engine:** ตรวจสอบการจำแนกสกุลเงินหุ้นไทย (SET/MAI) และสากล (NASDAQ/NYQ), แปลงค่า USD ⇄ THB ด้วย FX Rate 35.50, และ Guardrail ตรวจสอบวงเงินซื้อในสกุลเงินบาทอย่างถูกต้อง

```powershell
# รันชุดทดสอบอัตโนมัติทั้งหมด (ผลลัพธ์ต้อง 100% Green / 44 Passed)
C:\xampp\php\php.exe tests/run_tests.php
```

---

### 1.2 การตรวจสอบไวยากรณ์ภาษา PHP ทุกไฟล์ (Syntax Check)
```powershell
# ตรวจสอบ Syntax ของทุกไฟล์ .php ในโปรเจกต์ (ต้อง 0 Errors)
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & C:\xampp\php\php.exe -l $_.FullName }
```

---

### 1.3 หน้าตรวจสุขภาพระบบอัตโนมัติ 23 รายการ (`pages/health_check.php`)
- **ผ่าน Browser:** เปิด URL `http://localhost/investment/pages/health_check.php`
- **ผ่าน Terminal:**
  ```powershell
  C:\xampp\php\php.exe pages/health_check.php
  ```
- **รายการที่ตรวจสอบ:**
  1. **PHP Runtime:** Version (>= 8.0), cURL, PDO MySQL, OpenSSL, Memory Limit, Strict Types
  2. **Database:** Connection, Table Existences (11 ตาราง), Stock Seed Data, Indexes
  3. **Local Assets (Zero CDN):** `css/variables.css`, `css/layout.css`, `css/components.css`, `css/fonts.css`, `js/main.js`, `js/chart.umd.min.js`, Favicons
  4. **Financial Calculation Engines:** Altman Z-Score, Piotroski F-Score, Composite Score, DCA Simulator, Stock Comparison
  5. **Security & Session:** CSRF Token Generator, BCRYPT Cost 12 Hashing, Session Writable, XSS Escaping

---

### 1.4 การตรวจสอบหน้าจัดการข้อผิดพลาด (Error Views Verification)
ทดสอบความสมบูรณ์และไวยากรณ์ของหน้า Error Handling Views:
```powershell
C:\xampp\php\php.exe -l errors/403.php
C:\xampp\php\php.exe -l errors/404.php
C:\xampp\php\php.exe -l errors/500.php
```

---

## 2. การตรวจสอบการเชื่อมต่อและดึงข้อมูล Live Market Data API

ระบบเชื่อมต่อกับ Global Market API เพื่อดึงราคาและข้อมูลกราฟ 100% พร้อมแคช 60 วินาทีในฐานข้อมูล (`stock_api_cache`)

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

### 2.2 ตรวจสอบ Multi-Timeframe Chart API (6 ช่วงเวลา)
```bash
# ทดสอบกราฟ 6 ช่วงเวลา
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1H"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1D"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1W"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1M"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=1Y"
curl "http://localhost/investment/api/chart_data.php?symbol=PTT&timeframe=ALL"
```
**จุดที่ต้องระวังสำหรับ Chart API:**
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

### 🔴 ปัญหา 3: Watchlist แสดงผลหุ้นไม่ครบทุกตัวที่ Bookmark ไว้
- **สาเหตุ:** เกิด Fatal Error ในลูปเรนเดอร์การ์ด (เช่น เรียกใช้ฟังก์ชันที่ไม่มีอยู่จริง) ทำให้ PHP หยุดการประมวลผลกลางคัน
- **วิธีแก้:**
  1. ตรวจสอบว่าฟังก์ชันช่วยเหลือ (เช่น `getScoreColorClass`, `formatNumber`, `formatMoney`) ถูกประกาศไว้ใน `functions/helpers.php` ครบถ้วน
  2. ตรวจสอบ SQL ใน `getUserWatchlist()` ว่าใช้ Subquery เพื่อไม่ให้เมตริกการเงินที่ยังไม่ครบถ้วนตัดแถวข้อมูลหุ้นทิ้ง

### 🔴 ปัญหา 4: กราฟไม่ขยับเมื่อเปลี่ยนปุ่ม Timeframe (1H, 1D, 1W, 1M, 1Y, ALL)
- **สาเหตุ:** Event Listener ไม่ได้ถูก bind หรือ Chart Canvas ID ไม่ตรง
- **วิธีแก้:**
  1. ตรวจสอบ Console (`F12`) ว่ามี error จาก `loadTimeframeChart()` หรือไม่
  2. ตรวจสอบว่า Element `<canvas id="priceTrendCanvas">` หรือ `<canvas id="stockPriceChart">` มีอยู่จริงใน DOM
  3. ตรวจสอบ response จาก `/api/chart_data.php?symbol=...&timeframe=...` ในแท็บ Network

### 🔴 ปัญหา 5: คำสั่งซื้อ-ขายในพอร์ตจำลองไม่ทำงาน
- **สาเหตุ:** เงินสดคงเหลือในพอร์ตไม่เพียงพอ (รวมค่าธรรมเนียม 0.157% แล้วเกิน) หรือส่งคำสั่งขายมากกว่าจำนวนหุ้นที่มีอยู่
- **วิธีแก้:**
  1. ดูข้อความแจ้งเตือน Flash Alert สีแดงบนหน้าเว็บ
  2. ตรวจสอบยอดเงินสดคงเหลือ (`cash_balance`) ในหน้าพอร์ต
  3. ตรวจสอบว่ามีการส่ง `csrf_token` ในคำขอ POST

### 🔴 ปัญหา 6: หน้าพอร์ตจำลองเกิด TypeError หรือ Undefined Key ในตารางประวัติธุรกรรม
- **สาเหตุ:** เรียกใช้ฟิลด์วันที่ผิดชื่อ (เช่น `$tx['executed_at']` แทนที่จะเป็น `$tx['created_at']`) หรือเรียกใช้ `$tx['net_amount']` ที่ไม่ได้เป็นคอลัมน์ในตาราง
- **วิธีแก้:**
  1. ในตาราง `transactions` ให้ใช้ฟิลด์ `created_at` สำหรับวันเวลาที่ทำรายการเสมอ
  2. คำนวณมูลค่าสุทธิด้วยสูตร: `($isBuy ? $total_amount + $fee : $total_amount - $fee)`

### 🔴 ปัญหา 7: ปุ่มกดยืนยันคำสั่งซื้อ (BUY) ใน Trade Modal ถูก Disabled
- **สาเหตุ:** ยอดซื้อรวมค่าคอมมิชชันเกินมูลค่าเงินสดคงเหลือ (`cash_balance`)
- **วิธีแก้:**
  1. กล่องแจ้งเตือน `#modal_trade_alert` จะแสดงยอดที่ขาดแบบ Real-time ให้ปรับลดจำนวนหุ้นลง
  2. หรือกดปุ่มลัด `MAX (สูงสุด)` เพื่อให้ระบบคำนวณจำนวนหุ้นสูงสุดที่สามารถซื้อได้ด้วยเงินสดที่มีอยู่ทันที

### 🔴 ปัญหา 8: เมื่อพิมพ์ตัวเลขจำนวนหุ้น 9999+ แล้วตัวเลขถูกปรับลดลงอัตโนมัติ
- **สาเหตุ:** ระบบ Automatic Quantity Clamping ทำงานเพื่อความปลอดภัย โดยล็อกไม่ให้ขายเกินจำนวนหุ้นที่ถือครองจริง (`Held Shares`) และล็อกไม่ให้ซื้อเกินเงินสดที่มี
- **วิธีแก้:** การทำงานนี้เป็นไปตาม Requirement ทางการเงินที่ถูกต้อง หากต้องการขายเพิ่มต้องมีหุ้นในพอร์ตเพิ่มขึ้นก่อน

### 🔴 ปัญหา 9: ซื้อหุ้นต่างประเทศ (เช่น NVDA ราคา $208.48) แล้วคำนวณเงินสดไม่ตรงกับความเป็นจริง
- **สาเหตุ:** หุ้นสากล (NASDAQ/NYQ) ซื้อขายในสกุลเงินดอลลาร์ ($ USD) แต่เงินสดในพอร์ตเป็นสกุลเงินบาท (฿ THB)
- **วิธีแก้:**
  1. ใช้ฟังก์ชัน `isThaiStock($market, $symbol)` เพื่อแยกแยะหุ้นไทยและหุ้นต่างประเทศ
  2. แปลงราคาเป็นเงินบาทด้วย `convertUsdToThb($price, DEFAULT_USD_THB_RATE)` (FX Rate 35.50 THB/USD) ก่อนหักเงินสดหรือคำนวณกำลังซื้อ (Buying Power)
  3. ในส่วนแสดงผล ให้ใช้ `formatStockPrice($price, $market, $symbol)` หรือแสดงผลแบบ Dual-Currency `$208.48 (~฿7,401.04)`

### 🔴 ปัญหา 10: กล่องแจ้งเตือน Toast Alert ไม่แสดงตรงกลาง หรือมีเส้นแถบสีเขียวหนาที่ขอบ
- **สาเหตุ:** คลาส CSS `.app-toast-container` ยังคงตั้งค่าไว้ที่มุมขวาบน หรือมีสไตล์ `border-left: 4px solid ...` หนาด้านข้าง
- **วิธีแก้:**
  1. ใน [`css/components.css`](../css/components.css) ให้กำหนดตำแหน่ง `.app-toast-container` เป็น `top: 1.5rem; left: 50%; transform: translateX(-50%);`
  2. ลบ `border-left: 4px solid ...` ออกทั้งหมด และใช้เส้นขอบบาง 1px สม่ำเสมอรอบด้าน `border: 1px solid rgba(255, 204, 0, 0.25)` บนพื้นผิวกระจก Dark Glass (`#251D1D`)

### 🔴 ปัญหา 11: ปุ่ม `.btn-outline` หรือปุ่มลบใน Watchlist แสดงผลสีไม่ถูกต้อง
- **สาเหตุ:** สับสนระหว่างปุ่ม Outline สีทอง (`.btn-outline`) กับปุ่ม Action สีแดง (`.btn-outline.text-loss`)
- **วิธีแก้:**
  1. ปุ่ม **"วิเคราะห์"** ทั่วไป ให้ใช้ `.btn-outline` (พื้นหลังสีทองคำ `linear-gradient(180deg, #FFD633 0%, #FFCC00 100%)` และตัวอักษรสีเข้ม `#1A1300 !important`)
  2. ปุ่ม **"ลบออกจาก Watchlist"** หรือ **"ออกจากระบบ"** ให้ใช้ `.btn.btn-outline.btn-sm.text-loss` (พื้นหลังสีแดงทับทิม `linear-gradient(180deg, #FF3333 0%, #D60000 100%)` และตัวอักษร/ไอคอนสีขาว `#FFFFFF !important`)

---

## 5. กฎเหล็กที่ AI Agents ต้องปฏิบัติตามเสมอ (Mandatory Rules Compliance)

1. **ห้ามรัน DDL/DML สร้างหรือแก้ไขตารางฐานข้อมูลเองโดยพลการ** (Rule 10) - ต้องจัดเตรียมโค้ด SQL ให้ผู้ใช้เป็นผู้นำไปรันผ่าน phpMyAdmin
2. **ห้าม Push หรือ Commit ขึ้น Git เด็ดขาด** (Rule 11) - ผู้ใช้จะเป็นผู้ควบคุม Version Control เอง
3. **ห้ามใช้ Emojis บน UI** (Rule 1) - ให้ใช้ฟังก์ชัน `icon('name')` จาก `functions/icons.php` เท่านั้น
4. **ห้ามใช้ External CDN** (Rule 2) - ต้องใช้ Local Assets 100%
5. **ปุ่มต้องเป็นการตอบสนองแบบ Flat สถิต** (Rule 3) - ห้ามมีอนิเมชันลอยตัว `translateY` และห้ามมีแสงฟุ้ง Box-Shadow
6. **ห้ามเขียน Inline `style="..."`** (Rule 6) - ต้องใช้ CSS Classes และ CSS Custom Properties (`--progress-pct`, `--score-pct`, `--alloc-pct`)
7. **ห้ามใช้เส้นแถบสีหัวพื้นหลังหรือขอบหนาด้านข้าง** (Rule 9) - ทุก Container, Card, Row และ Alert ต้องใช้เส้นขอบบาง 1px สม่ำเสมอรอบด้าน (Uniform 1px Border)
8. **ใช้ De Morgan's Laws เสมอ** - เขียนเงื่อนไขในรูปแบบ `!A || !B` หรือ `!A && !B` หลีกเลี่ยง `!(A && B)`
9. **Strict Types 100%** - ทุกไฟล์ PHP ต้องขึ้นต้นด้วย `declare(strict_types=1);` และระบุ Type Hinting

