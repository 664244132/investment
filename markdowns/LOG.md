# 📜 LOG.md: บันทึกประวัติการพัฒนาและรุ่นเวอร์ชัน (Project Changelog)

เอกสารนี้ใช้บันทึกประวัติการเปลี่ยนแปลง รายการปรับปรุงฟีเจอร์ และการแก้ไขโค้ด (Release Notes & Audit Log) ของโปรเจกต์ **SmartPort AI**

---

## 📌 [Version 1.22.3] - 2026-08-25
### 🏷️ Icon-Only Action Button Styling Matching .btn-bookmark.bookmarked

- **1. ปรับปรุง UI และ CSS สำหรับ `.btn-outline.btn-sm` ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)):**
  - ออกแบบปุ่ม `.btn-outline.btn-sm` ให้เป็น **ปุ่มไอคอนล้วน (Icon-Only Compact Button)** ขนาด 32x32px สไตล์เดียวกับ `.btn-bookmark.bookmarked`
  - ใช้พื้นหลังสีทองโปร่งแสง `var(--accent-gold-subtle)` (`rgba(255, 204, 0, 0.12)`), ขอบสีทอง 1px `var(--accent-gold)` (`#FFCC00`), และไอคอน SVG สีทอง `stroke: var(--accent-gold)`
  - สำหรับปุ่มลบ `.btn-outline.btn-sm.text-loss` ใช้สไตล์สีแดงโปร่งแสง `rgba(255, 92, 92, 0.12)` พร้อมขอบสีแดง `#FF5C5C`
- **2. ปรับปรุงเทมเพลตให้เป็นไอคอนล้วนพร้อม Tooltip:**
  - [`pages/portfolio.php`](file:///c:/xampp/htdocs/investment/pages/portfolio.php) & [`pages/screener.php`](file:///c:/xampp/htdocs/investment/pages/screener.php): เปลี่ยนปุ่ม "วิเคราะห์" เป็นไอคอนบอท AI (`icon('bot')`) พร้อม `title="วิเคราะห์ [SYMBOL]"`
  - [`pages/watchlist.php`](file:///c:/xampp/htdocs/investment/pages/watchlist.php): เปลี่ยนปุ่ม "ตั้งเป้าหมาย" เป็นไอคอนดินสอ (`icon('edit')`) พร้อม `title="แก้ไขราคาเป้าหมายและโน้ต"`
  - [`index.php`](file:///c:/xampp/htdocs/investment/index.php): เปลี่ยนปุ่มเปรียบเทียบในส่วน Hero เป็นไอคอนตาชั่ง (`icon('scale')`) เคียงข้างปุ่ม Bookmark
- **3. ผลการทดสอบ:** ชุดทดสอบอัตโนมัติ 44/44 ผ่านฉลุย 100%

---

## 📌 [Version 1.22.2] - 2026-08-25
### ⚫ High-Contrast Dark Text for Gold Outline & Header Action Buttons

- **1. ปรับปรุงสีตัวอักษรและไอคอนเป็นสีดำ ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css) & [`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css)):**
  - กำหนดให้ `.btn-outline` (ปุ่ม "วิเคราะห์"), `.btn-header-portfolio` (ปุ่ม "พอร์ตของฉัน"), และ `.btn-header-logout` (ปุ่ม "ออกจากระบบ") ใช้ **ตัวอักษรสีดำเข้ม (`#1A1300 !important`)** และเส้นขอบไอคอน SVG สีดำ (`stroke: #1A1300 !important`)
  - มอบ Contrast สูงสุด คมชัด อ่านง่าย บนพื้นหลังสีเหลืองทองทรงคุณค่า
- **2. ยืนยันผลการทดสอบ:** ชุดทดสอบอัตโนมัติ 44/44 ผ่านฉลุย 100%

---

## 📌 [Version 1.22.1] - 2026-08-25
### ⚪ Crisp White Text Color Styling for Action & Trading Buttons

- **1. ปรับปรุงสีตัวอักษรและไอคอนเป็นสีขาว ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css) & [`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css)):**
  - กำหนดให้ `.btn-outline` (ปุ่ม "วิเคราะห์"), `.btn-buy` (ปุ่ม "ซื้อ"), `.btn-header-portfolio` (ปุ่ม "พอร์ตของฉัน"), และ `.btn-header-logout` (ปุ่ม "ออกจากระบบ") ใช้ **ตัวอักษรสีขาวบริสุทธิ์ (`#FFFFFF !important`)** และเส้นขอบไอคอน SVG สีขาว (`stroke: #FFFFFF !important`)
  - เพิ่มมิติด้วย Subtle Text Shadow `0 1px 2px rgba(0, 0, 0, 0.45)` เพื่อให้อ่านง่าย คมชัด บนพื้นหลังสีทองและสีเขียว
- **2. ยืนยันผลการทดสอบ:** ชุดทดสอบอัตโนมัติ 44/44 ผ่านฉลุย 100%

---

## 📌 [Version 1.22.0] - 2026-08-25
### 🔍 Paper Trade Stock Universe Searchbar & Live Autocomplete

- **1. ปรับปรุง UI Trade Modal ([`templates/trade_modal.php`](file:///c:/xampp/htdocs/investment/templates/trade_modal.php)):**
  - เปลี่ยนส่วนเลือกหลักทรัพย์ (Stock Universe) จาก `<select>` Dropdown เดิมเป็น **Searchbar สำหรับพิมพ์ค้นหาหุ้นแบบเรียลไทม์**
  - เพิ่มไอคอนค้นหา SVG, ปุ่มล้างคำค้นหา (Clear Button), และเมนูดรอปดาวน์ Autocomplete รายการหุ้นทั้งไทย (SET/mai) และสากล (NASDAQ/NYQ)
  - แสดงผลสัญลักษณ์หุ้น, ตลาด, ชื่อบริษัท, ราคาตลาดสด (Dual-Currency ฿/$), และ Badge หุ้นที่ถือครอง
- **2. ออกแบบ CSS สไตล์ Dark Glass ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)):**
  - เพิ่มคลาส `.trade-stock-search-group`, `.trade-stock-search-input`, `.trade-stock-dropdown`, `.trade-stock-option` พร้อมขอบ 1px รอบด้านสม่ำเสมอตามกฎ Rule 9 (Clean Uniform 1px Borders)
- **3. พัฒนาการควบคุมและการนำทางด้วยแป้นพิมพ์ ([`js/main.js`](file:///c:/xampp/htdocs/investment/js/main.js)):**
  - กรองคำค้นหาแบบ Real-time ตาม Symbol, Name, Market
  - รองรับการกดแป้นพิมพ์ Arrow Up/Down, Enter เพื่อเลือกหุ้น, และ Esc เพื่อปิดเมนู
  - เชื่อมโยงกับฟังก์ชัน `window.openTradeModal` เมื่อเปิด Modal มาจากหน้าอื่นจะใส่ชื่อหุ้นลงใน Searchbar ให้อัตโนมัติ
- **4. ผลการทดสอบ:** ชุดทดสอบอัตโนมัติ 44/44 ผ่านฉลุย 100%

---

## 📌 [Version 1.21.4] - 2026-08-25
### 🔲 Clean Uniform Border Policy (Elimination of Accent Border Stripes)

- **1. ปรับปรุงสไตล์ Watchlist Target Rows ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)):**
  - ลบเส้นแถบสีหัวพื้นหลังหนา 3px (`border-left: 3px solid ...`) ออกจาก `.watchlist-target-row`, `.target-buy-row`, และ `.target-sell-row`
  - ปรับเป็นเส้นขอบบาง 1px สม่ำเสมอรอบด้าน (Uniform 1px Border) บนพื้นผิว Dark Glass สะอาดตา
  - ปรับปรุง `.journal-lesson-box` ให้ใช้เส้นขอบ 1px รอบด้านเช่นเดียวกัน
- **2. บันทึกและบัญญัติกฎระเบียบการออกแบบใหม่ (Design Rules Update):**
  - เพิ่ม **"กฎข้อ 9: ห้ามใช้เส้นแถบสีหัวพื้นหลังหรือเส้นหนาด้านข้าง (No Accent Border Stripes / No Header Indicator Lines on Backgrounds)"** ลงใน [`markdowns/REFACTORCODE.md`](file:///c:/xampp/htdocs/investment/markdowns/REFACTORCODE.md)
  - เพิ่มหมวดหมู่ **"8. Uniform 1px Borders — No Accent Stripe Lines"** ลงใน [`DESIGN.md`](file:///c:/xampp/htdocs/investment/DESIGN.md)
- **3. ยืนยันผลการทดสอบ:** ชุดทดสอบอัตโนมัติ 44/44 ผ่านฉลุย 100%

---

## 📌 [Version 1.21.3] - 2026-08-25
### 🔴 Red Action Variant: `.btn-outline.text-loss` Button Styling

- **1. ปรับแต่งสไตล์เฉพาะสำหรับปุ่ม `.btn-outline.text-loss` ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)):**
  - กำหนด **สีพื้นหลังเป็นสีแดงทับทิม (`linear-gradient(180deg, #FF3333 0%, #D60000 100%)`)** พร้อมเส้นขอบสีแดงสว่าง `#FF4D4D`
  - กำหนด **สีตัวอักษรและเส้นขอบไอคอน SVG เป็นสีขาวบริสุทธิ์ (`#FFFFFF !important`)** ตัวหนา 700
  - กำหนดพฤติกรรม Flat Interaction เมื่อ Hover ให้สว่างขึ้นนุ่มนวลเป็น `#FF5C5C` และเมื่อ Active เป็น `#B30000` โดยไม่มีการลอยตัวตามกฎระเบียบ Design System
  - ส่งผลให้ปุ่ม *"ลบออกจาก Watchlist"* (ไอคอนถังขยะ) และปุ่ม *"ออกจากระบบ"* ใน Mobile Drawer มีภาพลักษณ์ที่ชัดเจน สื่อความหมายถึง Action สำคัญได้อย่างโดดเด่น
- **2. ยืนยันผลการทดสอบ:** ผ่านการทดสอบ 44/44 Tests (100% Green)

---

## 📌 [Version 1.21.2] - 2026-08-25
### 🔔 Toast Notification Positioning & Clean Glass Design Alignment

- **1. ปรับปรุงตำแหน่งการแสดงผล Toast แจ้งเตือน ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)):**
  - ย้ายตำแหน่งจากมุมขวาบน (`top: 1.5rem; right: 1.5rem`) ไปเป็น **"กึ่งกลางด้านบนของหน้าจอ (Top-Center)"** ด้วย `top: 1.5rem; left: 50%; transform: translateX(-50%)` เพื่อให้อยู่ในระดับสายตาของผู้ใช้พอดี
  - จัดการ Responsive บนมือถือให้อยู่ตรงกลางหน้าจอกว้างพอดีขอบ `width: calc(100% - 2rem)`
- **2. ลบเส้นสีเขียวที่ขอบซ้าย (Clean Unified Glass Border):**
  - ลบเส้นแถบสีเขียวหนา 4px (`border-left: 4px solid ...`) และเส้นแถบสีแดงหนา 4px ออกจาก Toast ทุกประเภท
  - ปรับให้ใช้เส้นขอบบางประณีต 1px รอบด้าน (`border: 1px solid rgba(255, 204, 0, 0.25)`) บนพื้นผิวกระจกมืดหรูหรา Dark Glass (`#251D1D` + `backdrop-filter: blur(16px)`)
- **3. รวมศูนย์การทำงานแจ้งเตือนทุกจุดในระบบ ([`js/main.js`](file:///c:/xampp/htdocs/investment/js/main.js)):**
  - อัปเกรด `window.showAppToast(message, type)` ให้รองรับทั้งการแจ้งเตือน Watchlist ("บันทึกหุ้นลงใน Watchlist เรียบร้อยแล้ว", "นำหุ้นออกจาก Watchlist เรียบร้อยแล้ว"), การบันทึกราคาเป้าหมาย & โน้ต, และข้อความระบบทุกชนิด โดยแสดงผลตรงกลางด้านบนด้วยสไตล์เดียวกัน 100%
- **4. ผลการทดสอบ:** ชุดทดสอบอัตโนมัติ 44/44 ผ่านฉลุย 100%

---

## 📌 [Version 1.21.1] - 2026-08-25
### 🎨 UI Design Refinement: Solid Gold Inversion for `.btn-outline` Buttons

- **1. ปรับปรุงดีไซน์สไตล์ปุ่ม `.btn-outline` ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)):**
  - สลับการแสดงผลให้ใช้ **สีพื้นหลังเป็นสีเหลืองทองทรงคุณค่า (`linear-gradient(180deg, #FFD633 0%, #FFCC00 100%)`)** 
  - สลับ **สีตัวอักษรและเส้นขอบไอคอน SVG (`stroke`) เป็นสีเข้มของพื้นหลัง (`#1A1300 !important`)** ตัวหนา 600 พร้อม Inner Highlight `inset 0 1px 0 rgba(255, 255, 255, 0.4)`
  - กำหนดการโต้ตอบเมื่อ Hover/Focus ให้สว่างขึ้นอย่างนุ่มนวลเป็น `#FFE466` และเมื่อกด Active เป็น `#CCA300` โดยไม่มีการลอยตัว `translateY` และไม่มีแสงฟุ้งตามกฎ Design System อย่างเคร่งครัด
  - ส่งผลให้ปุ่ม *"วิเคราะห์"* ในหน้า Portfolio, Screener, Compare, DCA, Watchlist มีความโดดเด่น คมชัด อ่านง่าย และเข้ากับ Theme แบรนด์หลักทันที
- **2. ยืนยันผลการทดสอบ:** ชุดทดสอบอัตโนมัติผ่านสมบูรณ์ **44/44 Passed (100% Green)** และ 0 Runtime Warnings/Errors

---

## 📌 [Version 1.21.0] - 2026-08-25
### 🌐 Multi-Currency & Foreign Exchange (FX) Engine Integration (USD ⇄ THB)

- **1. วิเคราะห์และค้นพบปัญหาความคลาดเคลื่อนของสกุลเงิน (Multi-Currency Discrepancy Discovery):**
  - ตรวจพบว่าระบบมีหุ้นทั้งตลาดไทย (SET/mai 8 ตัว สกุล ฿ THB) และตลาดสากล (NASDAQ/NYQ 19 ตัว สกุล $ USD เช่น NVDA ราคา $208.48, AAPL, TSLA, MSFT)
  - เดิมระบบคำนวณราคา $208.48 เป็น 208.48 บาททันที ทำให้ตัดเงินสดน้อยกว่าความเป็นจริงประมาณ 35.5 เท่า
- **2. พัฒนา Core FX Engine & Helper Functions ([`functions/helpers.php`](file:///c:/xampp/htdocs/investment/functions/helpers.php) & [`config/constants.php`](file:///c:/xampp/htdocs/investment/config/constants.php)):**
  - กำหนดค่าคงที่ `DEFAULT_USD_THB_RATE = 35.50`
  - เพิ่มฟังก์ชัน `isThaiStock($market, $symbol)` เพื่อจำแนกหุ้นไทยและหุ้นต่างประเทศอย่างแม่นยำ
  - เพิ่มฟังก์ชัน `getStockCurrency()`, `getStockCurrencySymbol()`, `convertUsdToThb()`, `convertThbToUsd()`
  - เพิ่มฟังก์ชัน `formatStockPrice($price, $market, $symbol)` แสดงผล `$208.48` สำหรับหุ้นสากล และ `฿40.75` สำหรับหุ้นไทย
- **3. อัปเกรดระบบพอร์ตจำลอง & การทำรายการเทรด ([`functions/portfolio.php`](file:///c:/xampp/htdocs/investment/functions/portfolio.php)):**
  - ใน `executeSimulatedTrade()`: คำนวณตัดเงินสดตามมูลค่าเทียบเท่าเงินบาท (`$price * $fxRate`) พร้อมคำนวณค่าคอมมิชชันในสกุลเงินบาท และบันทึกราคาต้นทุนเฉลี่ยในสกุลเงินจริง
  - ใน `getPortfolioHoldings()` & `getUserPortfolio()`: แปลงมูลค่าตลาดและต้นทุนเป็นเงินบาทเพื่อรวมเป็นมูลค่าสินทรัพย์สุทธิ (Total Portfolio Value) ของผู้ใช้อย่างถูกต้อง 100%
- **4. ปรับปรุง Trade Modal & การตรวจสอบวงเงินสดแบบ Dual-Currency ([`templates/trade_modal.php`](file:///c:/xampp/htdocs/investment/templates/trade_modal.php) & [`js/main.js`](file:///c:/xampp/htdocs/investment/js/main.js)):**
  - แสดงผลราคาและยอดรวมทั้งสองสกุลเงินคู่กัน เช่น `$208.48 (≈ ฿7,401.04)`
  - ปรับการคำนวณปุ่ม `MAX` และระบบแจ้งเตือนวงเงินสดคงเหลือ (Buying Power Guardrail) ให้เทียบกับมูลค่าในสกุลเงินบาทแบบ Real-time
- **5. ปรับปรุงการแสดงผลทุกหน้าในระบบ (Screener, Compare, Watchlist, Portfolio, Export):**
  - แทนที่สัญลักษณ์ `฿` ที่ฮาร์ดโค้ดไว้ด้วยฟังก์ชันไดนามิกตามสกุลเงินจริงของหลักทรัพย์
- **6. ขยายชุดทดสอบอัตโนมัติ Section 12 ([`tests/run_tests.php`](file:///c:/xampp/htdocs/investment/tests/run_tests.php)):**
  - เพิ่มการทดสอบการจำแนกสกุลเงิน, การแปลงอัตราแลกเปลี่ยน, การจัดรูปแบบราคา, และ Guardrail การซื้อหุ้นสากล
  - ผลการรันชุดทดสอบผ่านสมบูรณ์ **44/44 Passed (100% Green)**
- **7. ซิงค์เอกสารคู่มือระบบ ([`markdowns/aboutProject.md`](file:///c:/xampp/htdocs/investment/markdowns/aboutProject.md) & [`markdowns/DEBUG.md`](file:///c:/xampp/htdocs/investment/markdowns/DEBUG.md)):**
  - บันทึกสถาปัตยกรรม Multi-Currency และคู่มือการ Debugging สำหรับ AI Agents ครบถ้วน

---

## 📌 [Version 1.20.0] - 2026-08-25
### 🚀 Simulated Trading Guardrails & Institutional Grid System Alignment

- **1. Buying Power Guardrail:** ระบบแจ้งเตือน Real-time บน Trade Modal ทันทีที่ยอดซื้อรวมเกินเงินสดคงเหลือ พร้อมล็อกปุ่มยืนยันคำสั่งซื้อ
- **2. Stepper Clamping & Quick Quantity:** ล็อกค่าขั้นต่ำ 1 หุ้น, คำสั่งขายสูงสุดไม่เกินหุ้นที่มี, และเพิ่มปุ่มลัด `+100`, `+500`, `+1,000`, `MAX`
- **3. Institutional Segmented Grids:** ยกระดับดีไซน์ 6 Grid Classes ให้ใช้เลย์เอาต์กรอบคั่น 1px สไตล์โมเดิร์น
- **4. Dark Glass Journal:** ลบไฮไลท์สีเหลืองใน `.journal-rationale-box` เปลี่ยนเป็น Dark Glass เรียบหรู
- **5. ขยายชุดทดสอบอัตโนมัติ:** เพิ่มการทดสอบ Guardrails และ UI Rules ครบถ้วน

---

## 📌 [Version 1.0.0 - 1.19.8] - สรุปภาพรวมการพัฒนาระบบเริ่มต้น (Core System Archive)
- **10 Core Financial Modules:** พัฒนาระบบวิเคราะห์หุ้นรายตัว, กราฟราคาเรียลไทม์ 6 Timeframes, งบการเงิน 3 ด้าน, แบบจำลอง Altman Z-Score & Piotroski F-Score, Quantitative Screener, Peer Comparison Matrix, DCA Growth Simulator, Personal Watchlist, Trading Journal และ Account Statement Export
- **Security & Performance Standards:** ติดตั้ง Local Fonts 34 ไฟล์ (Zero CDN), พจนานุกรม SVG Vector Icons 100% (Zero Emojis), CSRF Protection, PDO Prepared Statements, BCRYPT Cost 12 Hashing, และ Session Hardening
- **Automated QA Suite:** ชุดทดสอบอัตโนมัติ `tests/run_tests.php` และหน้าตรวจสุขภาพระบบ `pages/health_check.php`
