# 📜 LOG.md: บันทึกประวัติการพัฒนาและรุ่นเวอร์ชัน (Project Changelog)

เอกสารนี้ใช้บันทึกประวัติการเปลี่ยนแปลง รายการปรับปรุงฟีเจอร์ และการแก้ไขโค้ด (Release Notes & Audit Log) ของโปรเจกต์ **SmartPort AI**

---

## 📌 [Version 1.19.8] - 2026-08-24
### ✨ Impeccable Form UX/UI & Ergonomics Enhancement

- **ยกระดับความสะดวกสบายและการเข้าถึง (Usability & Ergonomics) ของ [`pages/register.php`](file:///c:/xampp/htdocs/investment/pages/register.php):**
  - เพิ่ม **Input Prefix Vector Icons** (User, ID, Mail, Lock) สวยงาม ชัดเจน และเปลี่ยนเป็นสีทองอัตโนมัติเมื่อ Focus (`.form-input-wrapper:focus-within .input-icon-left`)
  - เพิ่ม Attribute `autocomplete` มาตรฐาน (`username`, `name`, `email`, `new-password`) อำนวยความสะดวกให้เบราว์เซอร์กรอกอัตโนมัติได้อย่างแม่นยำ
  - พัฒนาระบบแสดงผลความแข็งแรงของรหัสผ่านแบบ Real-time Text Feedback แนะนำผู้ใช้แบบละเอียด
  - เพิ่มไอคอน `mail` และ `user-check` ในโมดูล [`functions/icons.php`](file:///c:/xampp/htdocs/investment/functions/icons.php)

---

## 📌 [Version 1.19.7] - 2026-08-24
### 🎯 Streamlined Register Form Layout (Single Auth Card)

- **ปรับปรุงหน้าสมัครสมาชิก ([`pages/register.php`](file:///c:/xampp/htdocs/investment/pages/register.php)) เป็นแบบ Single Card:**
  - คงไว้เฉพาะส่วน `.auth-card.auth-form-card` กึ่งกลางหน้าจออย่างสะอาดตา สะดวกและรวดเร็วต่อการสมัครสมาชิก
  - จัดวางฟอร์ม 2 คอลัมน์สำหรับชื่อผู้ใช้/ชื่อ-นามสกุล และ รหัสผ่าน/ยืนยันรหัสผ่าน
  - ปรับขนาดความกว้าง `.auth-form-card` ให้พอดี (`max-width: 540px;`) พร้อมเงา Depth Shadow และ Dark Glassmorphism Theme

---

## 📌 [Version 1.19.6] - 2026-08-24
### 💎 Complete Auth Card Wide & Register UX/UI Redesign

- **ยกเครื่องการออกแบบ UX/UI หน้าสมัครสมาชิก (`.auth-card-wide` ใน [`pages/register.php`](file:///c:/xampp/htdocs/investment/pages/register.php)):**
  - ออกแบบการ์ดด้วยสไตล์ Luxury Dark Glassmorphism พร้อมเงา Depth Shadow และเส้นขอบประกายทอง
  - **ฝั่งซ้าย (Showcase Column):** เพิ่ม Brand Badge Pill พร้อม Live Dot Pulse, การ์ดแสดงเงินทุนจำลองเริ่มต้น ฿1,000,000 บาท, รายการฟีเจอร์เด่น 3 มิติพร้อมกล่องไอคอน และแถบการันตีความปลอดภัย
  - **ฝั่งขวา (Form Column):** จัดเรียงฟอร์มใหม่ให้กะทัดรัดและสวยงามด้วยโครงสร้าง 2 คอลัมน์ (`.auth-row-2`) สำหรับ Username/ชื่อ-นามสกุล และ รหัสผ่าน/ยืนยันรหัสผ่าน
  - เพิ่มคลาส CSS `.auth-brand-pill`, `.auth-capital-card`, `.auth-perks-list`, `.auth-form-card`, `.auth-row-2` ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css) และปรับปรุง `.register-grid` ใน [`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css)

---

## 📌 [Version 1.19.5] - 2026-08-24
### 🐞 JavaScript Syntax & Controller Consolidation

- **แก้ไขข้อผิดพลาด `Uncaught SyntaxError: Missing catch or finally after try`:**
  - จัดระเบียบลำดับฟังก์ชันและปิดบล็อก `try ... catch ... finally` ใน [`js/main.js`](file:///c:/xampp/htdocs/investment/js/main.js) ให้ถูกต้อง 100%
  - ตรวจสอบความถูกต้องของไวยากรณ์ด้วย `node -c js/main.js` ผ่านเรียบร้อย 0 errors

---

## 📌 [Version 1.19.4] - 2026-08-24
### ✨ Custom Themed Confirm Dialog & Toast Notifications

- **พัฒนาระบบแจ้งเตือนและยืนยันการทำรายการตาม Theme มืด-ทองของระบบ:**
  - สร้าง `window.showAppConfirm` (Promise-based) สำหรับกล่องข้อความยืนยันการลบ/ทำรายการ พร้อมไอคอน SVG คมชัด และปุ่มกดดีไซน์เฉพาะ
  - สร้าง `window.showAppToast` สำหรับการแจ้งเตือนความสำเร็จหรือข้อผิดพลาดแบบ Toast Notification
  - เพิ่มคลาส CSS `.app-confirm-backdrop`, `.app-confirm-card`, `.app-confirm-icon-box`, `.app-toast-container`, `.app-toast` ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)
  - นำไปใช้งานกับปุ่มลบหุ้นออกจาก Watchlist (`.btn-outline.btn-sm.text-loss`) ใน [`pages/watchlist.php`](file:///c:/xampp/htdocs/investment/pages/watchlist.php) พร้อมแอนิเมชันการยุบการ์ดที่นุ่มนวล

---

## 📌 [Version 1.19.3] - 2026-08-24
### 🎨 Modal Layout Polish & Content Expansion

- **ขยายขนาดและจัดระเบียบโครงสร้าง Modal Dialog ทั่วทั้งระบบ:**
  - ขยาย `.modal-content` เป็น `max-width: 620px;` พร้อมเงา Depth Shadow และเส้นขอบสีทองพรีเมียมใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)
  - เพิ่มสไตล์โครงสร้าง `.modal-header`, `.modal-body`, `.modal-footer` และ `.modal-grid-2`
  - ปรับปรุงหน้าต่างตั้งราคาเป้าหมาย & โน้ตใน [`pages/watchlist.php`](file:///c:/xampp/htdocs/investment/pages/watchlist.php) ให้เป้าซื้อและเป้าขายจัดวางเป็น 2 คอลัมน์อย่างสมดุล พร้อมคำอธิบายและไอคอนกำกับ
  - ซิงค์โครงสร้างหน้าต่าง [`templates/trade_modal.php`](file:///c:/xampp/htdocs/investment/templates/trade_modal.php) ให้ใช้มาตรฐานเดียวกัน

---

## 📌 [Version 1.19.2] - 2026-08-24
### 🐞 Global openTradeModal ReferenceError Fix

- **แก้ไขข้อผิดพลาด `Uncaught ReferenceError: openTradeModal is not defined`:**
  - ประกาศฟังก์ชัน `window.openTradeModal(type, symbol, price, stockId)` แบบ Global ใน [`js/main.js`](file:///c:/xampp/htdocs/investment/js/main.js) เพื่อรองรับการเรียกใช้ผ่าน Inline `onclick` และ Event Listeners
  - ปรับปรุงปุ่มส่งคำสั่งซื้อ-ขายใน [`pages/watchlist.php`](file:///c:/xampp/htdocs/investment/pages/watchlist.php) และ [`pages/compare.php`](file:///c:/xampp/htdocs/investment/pages/compare.php) ให้มีทั้งคลาส `.open-trade-modal-btn` และส่ง Data Attributes อย่างถูกต้อง

---

## 📌 [Version 1.19.1] - 2026-08-24
### 📐 Watchlist Price Box Wrap & Word-Break Prevention

- **ปรับปรุง Layout ส่วนแสดงราคาในการ์ด Watchlist (`.watchlist-price-box` / `.watchlist-price-main`):**
  - เพิ่ม `flex-shrink: 0; white-space: nowrap; word-break: keep-all;` ให้กับ `.watchlist-price-box` และ `.watchlist-price-main` ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)
  - กำหนด `flex: 1 1 auto; min-width: 0;` ให้กับ `.watchlist-stock-info` และกำหนด `max-width: 100%;` สำหรับ `.watchlist-company-name`
  - ป้องกันปัญหาตัวเลขราคาและสกุลเงิน (เช่น `34.50 THB`) ตกบรรทัดหรือฉีกคำบนทุกขนาดหน้าจออย่างสมบูรณ์แบบ
  - ปรับ `.watchlist-target-val` และ `.watchlist-target-row` ให้ไม่ฉีกคำเช่นเดียวกัน

---

## 📌 [Version 1.19.0] - 2026-08-24
### 🛡️ Error Views Layer, Documentation Sync & Project Hardening

- **เพิ่มโมดูล Error Handling Views มาตรฐาน (`errors/`):**
  - สร้าง [`errors/403.php`](file:///c:/xampp/htdocs/investment/errors/403.php): หน้าแจ้งเตือนการเข้าถึงถูกปฏิเสธ (Forbidden Access)
  - สร้าง [`errors/404.php`](file:///c:/xampp/htdocs/investment/errors/404.php): หน้าแจ้งเตือนไม่พบหน้าที่ต้องการ (Page Not Found)
  - สร้าง [`errors/500.php`](file:///c:/xampp/htdocs/investment/errors/500.php): หน้าแจ้งเตือนเซิร์ฟเวอร์ขัดข้อง (Internal Server Error)
  - ออกแบบเป็น **Standalone Full-Screen View** แสดงเฉพาะ Error Icon, รหัส Error, และข้อความอธิบาย (นำ `brand-logo` และ `error-actions` ออกทั้งหมด)
  - กำหนด HTTP Status Code ที่ถูกต้อง (403, 404, 500) และใช้ดีไซน์ Dark Glassmorphic Theme สอดคล้องกับระบบ
- **นำลิงก์ Health Check ออกจาก UI ทั่วทั้งโปรเจกต์ (`templates/footer.php`):**
  - ลบลิงก์ `pages/health_check.php` ออกจาก Footer เพื่อให้เข้าถึงได้เฉพาะการพิมพ์ URL โดยตรงบนเบราว์เซอร์เท่านั้น
- **เพิ่มสไตล์ Error Pages ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css):**
  - เพิ่มคลาส `.error-page-container`, `.error-card`, `.error-icon-wrapper`, `.error-code-number`, `.error-heading`, `.error-message` (ขยายเต็มหน้าจอ 100vh)
- **อัปเดตชุดเอกสารสถาปัตยกรรมระบบใน `markdowns/`:**
  - ซิงค์โครงสร้างโปรเจกต์ โฟลเดอร์ และตารางฐานข้อมูลใน [`markdowns/aboutProject.md`](file:///c:/xampp/htdocs/investment/markdowns/aboutProject.md)
  - ยกระดับแนวทางการดีบั๊กและ Troubleshooting Playbook ใน [`markdowns/DEBUG.md`](file:///c:/xampp/htdocs/investment/markdowns/DEBUG.md)
  - จัดระเบียบและเคลียร์ประวัติเก่าใน [`markdowns/LOG.md`](file:///c:/xampp/htdocs/investment/markdowns/LOG.md)
- **ชุดทดสอบอัตโนมัติ (`tests/run_tests.php`):**
  - ยืนยัน Strict Types ครบทั้ง 37 ไฟล์ และการทดสอบผ่าน 32/32 รายการ (100.0% Green)

---

## 📌 [Version 1.18.2] - 2026-08-24
### 📊 Live 52-Week Range & Stats Bar Real-time API Integration

- **เชื่อมต่อข้อมูล 52-Week Range เข้ากับ Live Market API จริง 100%:**
  - เพิ่มการดึงค่า `fiftyTwoWeekHigh` และ `fiftyTwoWeekLow` ใน `fetchLiveMarketQuote` ใน [`functions/market_api.php`](file:///c:/xampp/htdocs/investment/functions/market_api.php)
  - เพิ่มฟังก์ชัน `calculateStock52WeekRange(PDO $pdo, array $stock): array` ใน [`functions/stocks.php`](file:///c:/xampp/htdocs/investment/functions/stocks.php) คำนวณช่วงราคาต่ำสุด-สูงสุดรอบ 52 สัปดาห์ และสัดส่วนตำแหน่งปัจจุบันสัมพันธ์กับราคาตลาดสด
  - ปรับปรุงการแสดงผลใน [`index.php`](file:///c:/xampp/htdocs/investment/index.php) ให้รองรับสัญลักษณ์สกุลเงินแบบ Dynamic (`฿` และ `$`)
- **ตรวจสอบการทำงานของ `.stats-bar` และ `.range-52w-track`:**
  - ยืนยันข้อมูลทั้ง 7 เมตริกในแถบสถิติดึงจาก Live API และฐานข้อมูลครบถ้วนถูกต้อง 100%

---

## 📌 [Version 1.18.0 - 1.18.1] - 2026-08-24
### 💎 Full Project Refactoring, Rule 6 CSS Custom Properties & UI Polish

- **RefactorCode ตามกฎ Rule 6 (Zero Inline CSS 100%):**
  - แปลงการกำหนดความกว้าง Dynamic Progress Bars ใน [`index.php`](file:///c:/xampp/htdocs/investment/index.php) และ [`pages/portfolio.php`](file:///c:/xampp/htdocs/investment/pages/portfolio.php) ให้ส่งผ่าน CSS Custom Properties (`--progress-pct`, `--score-pct`, `--alloc-pct`)
- **Button Sizing & UI Polish:**
  - ซิงค์ขนาดปุ่ม `.btn-sm` และ `.btn-bookmark` ให้มีความสูง 36px และจัดแนวสมดุล
  - แก้ไขการฉีกคำในตารางสรุปผลกลยุทธ์ และจัดตำแหน่งหัวข้อการ์ดโมเดลสถิติ

---

## 📌 [Version 1.0.0 - 1.17.7] - สรุปภาพรวมการพัฒนาเวอร์ชันเริ่มต้น (Milestones Archive)

- **10 Core Modules:** ระบบวิเคราะห์หุ้นและกราฟเรียลไทม์ 6 Timeframes, งบการเงิน 3 ด้าน, Statistical Models (Altman Z & Piotroski F), AI Screener, Compare, DCA Simulator, Watchlist, Journal, Virtual Portfolio ฿1,000,000 และ Statement Export A4
- **Local Assets & Security:** ติดตั้ง Local Webfonts (Prompt & JetBrains Mono รวม 34 ไฟล์), SVG Vector Icons Dictionary 100%, CSRF Protection, PDO Prepared Statements, BCRYPT Cost 12 Hashing, และ Session Hardening
- **Automated Testing & QA:** ชุดทดสอบอัตโนมัติ `tests/run_tests.php` และหน้าตรวจสุขภาพระบบ 23 รายการ `pages/health_check.php`
