# 📜 LOG.md: บันทึกประวัติการพัฒนาและรุ่นเวอร์ชัน (Project Changelog)

เอกสารนี้ใช้บันทึกประวัติการเปลี่ยนแปลง รายการปรับปรุงฟีเจอร์ และการแก้ไขโค้ด (Release Notes & Audit Log) ของโปรเจกต์ **SmartPort AI**

---

## 📌 [Version 1.13.5] - 2026-08-24
### 📄 Electronic Statement Standardization (No Signature Required)

- **ปรับรูปแบบเอกสารเป็นรายงานอิเล็กทรอนิกส์สมบูรณ์ ([`pages/export.php`](file:///c:/xampp/htdocs/investment/pages/export.php)):**
  - ลบช่องลงนาม (Signature Box) ออกทั้งหมดตามมาตรฐานรายงานอิเล็กทรอนิกส์อัตโนมัติ
  - เพิ่มแถบรับรองเอกสารระบบอิเล็กทรอนิกส์ (Electronic Statement Notice) ระบุว่าเอกสารประมวลผลจากฐานข้อมูลระบบอัตโนมัติ จึงมีความสมบูรณ์ถูกต้องโดยไม่ต้องลงลายมือชื่อ
  - ปรับปรุงข้อความท้ายเอกสารเป็น `หน้า ๑ จาก ๑ · เอกสารทางการอิเล็กทรอนิกส์ (ไม่ต้องลงลายมือชื่อ)`
- **จัดระเบียบ CSS สไตล์ชีท ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)):** ลบโค้ด CSS ของกล่องลายเซ็นและจัดระเบียบส่วน `@media print` ให้พิมพ์กระดาษ A4 ได้คมชัด สะอาดตา

---

## 📌 [Version 1.13.4] - 2026-08-24
### 🏛️ Official Institutional Standard & Certified Signature Ledger

- **ปรับแต่งหัวเอกสารและโครงสร้างข้อมูลทางการ ([`pages/export.php`](file:///c:/xampp/htdocs/investment/pages/export.php)):**
  - หัวรายงาน: **ใบรายงานสรุปสถานะพอร์ตการลงทุนและบัญชีหลักทรัพย์**
  - กล่องข้อมูลทางการ: ระบุเลขที่บัญชีหลักทรัพย์, ชื่อผู้ถือบัญชี, ประเภทบัญชี (Cash Balance), และสกุลเงินอ้างอิง
- **ปรับตารางบัญชีเป็นภาษาไทยทางการ (ตารางที่ ๑ และ ตารางที่ ๒):**
  - เพิ่มคอลัมน์ลำดับที่, รหัสหลักทรัพย์, ชื่อบริษัท/กิจการ, ราคาปิดล่าสุด, อัตราผลตอบแทน
  - ปรับการระบุประเภทคำสั่งเป็นภาษาไทยควบคู่สากล: `ซื้อ (BUY)` และ `ขาย (SELL)`
- **เพิ่มช่องลงนามรับรองรายงานทางการ (Certification & Signature Grid):**
  - เพิ่มส่วนลงนามรับรองความถูกต้องโดยผู้ตรวจสอบข้อมูลฝ่ายปฏิบัติการ และช่องลงนามรับทราบโดยผู้ถือบัญชี
  - เพิ่ม CSS สไตล์ราชการ/สถาบันการเงิน รองรับการพิมพ์ A4 โดยที่ลายเซ็นไม่หลุดหน้ากระดาษ ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css))

---

## 📌 [Version 1.13.3] - 2026-08-24
### 🇹🇭 Localization & Official Thai Statement Branding

- **เพิ่มฟังก์ชัน `formatThaiDate()` ([`functions/helpers.php`](file:///c:/xampp/htdocs/investment/functions/helpers.php)):** แปลง Timestamp หรือวันที่สากลให้อยู่ในรูปแบบภาษาไทย พุทธศักราช (พ.ศ.) อย่างเป็นทางการ เช่น `24 สิงหาคม 2569 เวลา 09:46:05 น.`
- **ปรับหัวเอกสารและป้ายกำกับเป็นภาษาไทยทางการ ([`pages/export.php`](file:///c:/xampp/htdocs/investment/pages/export.php)):**
  - หัวเอกสาร: **ใบรายงานสถานะพอร์ตการลงทุนและรายการซื้อขายหลักทรัพย์**
  - ชื่อระบบ: **ระบบจำลองการลงทุนและวิเคราะห์หลักทรัพย์เชิงปริมาณ SmartPort AI**
  - ตารางที่ 1: **1. รายการหลักทรัพย์ที่ถือครองในพอร์ตการลงทุน**
  - ตารางที่ 2: **2. รายการบันทึกประวัติการซื้อขายหลักทรัพย์**
  - แสดงผลวันที่ในตารางประวัติธุรกรรมด้วยรูปแบบวันที่ไทย พ.ศ. ครบถ้วน

---

## 📌 [Version 1.13.2] - 2026-08-24
### 📄 Print & Statement Engine Polish: Official Institutional Ledger

- **แก้ไข Warning unrealized_pnl_pct:** เพิ่มคีย์ `unrealized_pnl_pct` ใน `functions/portfolio.php` (`getPortfolioHoldings()`) และเพิ่ม Fallback ใน [`pages/export.php`](file:///c:/xampp/htdocs/investment/pages/export.php) ป้องกันข้อผิดพลาด 100%
- **จัดรูปแบบข้อมูลบัญชีพอร์ตชิดซ้าย (Left-Aligned Meta):** ปรับแก้ `.statement-meta-box` ให้แสดงผลข้อมูลเลขที่บัญชี ชื่อผู้ถือ วันที่ และสกุลเงิน ชิดซ้ายอย่างเป็นระเบียบชัดเจน
- **ลบกรอบครอบประเภทธุรกรรม BUY/SELL:** เปลี่ยนเป็นข้อความ Plain Text หนาสีเขียว/แดง (`text-gain` / `text-loss`) สะอาดตา สไตล์รายงานทางการ
- **Typography & A4 Print Layout:** เพิ่มกฎ `word-break: normal;` และ `@media print` จัดเส้นตารางคมชัด ไม่มีการฉีกคำภาษาไทย หรือข้อความซ้อนทับกัน

---

## 📌 [Version 1.13.1] - 2026-08-24
### 🐛 Bug Fix: Portfolio Statement Array Keys & P&L Alignment

- **`functions/portfolio.php`:** เพิ่มคีย์ `total_pnl` และ `total_pnl_percent` ในฟังก์ชัน `getUserPortfolio()` ควบคู่กับ `total_profit_loss` และ `total_return_percent` เพื่อป้องกันปัญหา Undefined Array Key
- **`pages/export.php`:** ปรับปรุงการเข้าถึงตัวแปร `$portfolio['total_pnl']` และ `$portfolio['total_pnl_percent']` พร้อมระบบ Fallback รองรับตัวแปรทุกรูปแบบ ป้องกัน PHP Warning 100%

---

## 📌 [Version 1.13.0] - 2026-08-24
### 🎨 UI Refinement: Clean Badge Removal

- ลบ `<span class="badge badge-gold">` (Market Tag) ใน Hero Section ([`index.php`](file:///c:/xampp/htdocs/investment/index.php))
- ลบ `<span class="badge badge-neutral">` (Sector Tag) ใน Hero Section ([`index.php`](file:///c:/xampp/htdocs/investment/index.php))
- ลบ `<span class="badge badge-gain">` (Ready for analysis Tag) ใน Hero Section ([`index.php`](file:///c:/xampp/htdocs/investment/index.php))
- ลบ `<span class="badge badge-gold">` (Fundamental model Tag) ใน Composite Rating Card ([`index.php`](file:///c:/xampp/htdocs/investment/index.php))
- ลบ `<span class="badge badge-gold">` (Decorative Header Tags) ใน [`pages/screener.php`](file:///c:/xampp/htdocs/investment/pages/screener.php) และ [`pages/portfolio.php`](file:///c:/xampp/htdocs/investment/pages/portfolio.php))

---

## 📌 [Version 1.12.0] - 2026-08-24
### 🩺 Step 1: System Health & Responsive Header Optimization

#### 1. พัฒนาระบบตรวจสอบสุขภาพระบบอัตโนมัติ ([`pages/health_check.php`](file:///c:/xampp/htdocs/investment/pages/health_check.php))
- **Environment & Runtime Suite:** ตรวจสอบความเข้ากันได้ของ PHP Runtime และ Extensions สำคัญ (`pdo`, `pdo_mysql`, `mbstring`, `json`, `session`, `curl`)
- **Database & Schema Integrity Suite:** ตรวจสอบการเชื่อมต่อและนับจำนวนแถวในตารางฐานข้อมูลทั้ง 10 ตาราง
- **Static Assets & CDN-Free Suite:** ตรวจสอบความพร้อมของไฟล์สไตล์ชีท CSS (`variables.css`, `layout.css`, `components.css`) และไลบรารี JavaScript ในเครื่อง (Local 100%)
- **Financial Engines Validation:** ตรวจสอบความถูกต้องของโมดูลคำนวณราคาหุ้น, Altman Z-Score, Piotroski F-Score และ DCA Simulator
- **Security & Session Suite:** ตรวจสอบ Session Lifecycle, Timing-Attack Safe CSRF Protection และ Bcrypt Password Hashing

#### 2. จัดระเบียบ Responsive Navbar Header ไม่ให้ล้นจอ ([`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css) & [`templates/header.php`](file:///c:/xampp/htdocs/investment/templates/header.php))
- ปรับ Breakpoint แสดงผล Desktop Nav จาก `992px` เป็น `1140px` พร้อมกลไกสลับแสดงผล Mobile Drawer Hamburger บนหน้าจอแท็บเล็ตและหน้าต่างขนาดกลาง
- เพิ่มกฎขนาดกะทัดรัด (Compact Rules) บนหน้าจอขนาดกลาง (1140px - 1380px) ปรับลด Padding, Font Size และขนาด Search Box ป้องกันการทะลุออกนอกจอ 100%
- ปรับข้อความป้ายเมนู Desktop ให้กระชับ ได้ใจความชัดเจน

#### 3. เสริมสร้างความปลอดภัยส่วนกลาง ([`functions/helpers.php`](file:///c:/xampp/htdocs/investment/functions/helpers.php))
- เพิ่มฟังก์ชัน `generateCsrfToken()` สร้าง Token ปลอดภัยด้วย `random_bytes(32)`
- เพิ่มฟังก์ชัน `verifyCsrfToken()` ตรวจสอบ Token แบบ Timing Attack Safe ด้วย `hash_equals()`

---

## 📌 [Version 1.11.0] - 2026-08-24
### 🛡️ Step 6: Advanced Statistical Financial Health Models (Altman Z & Piotroski F)

#### 1. พัฒนาโมดูล Financial Models Engine ([`functions/financial_models.php`](file:///c:/xampp/htdocs/investment/functions/financial_models.php))
- **`calculateAltmanZScore()`:** คำนวณแบบจำลองสถิติ Altman Z-Score ทำนายความเสี่ยงการล้มละลายของกิจการ จำแนกออกเป็น 3 โซน (Safe Zone \(Z \ge 2.99\), Grey Zone \(1.81 \le Z < 2.99\), และ Distress Zone \(Z < 1.81\))
- **`calculatePiotroskiFScore()`:** คำนวณเกณฑ์ประเมินความแข็งแกร่งของงบการเงิน 9 จุด (Piotroski 9-Point Quality Score) ครอบคลุม 3 มิติหลัก: Profitability (4 คะแนน), Leverage & Liquidity (3 คะแนน), และ Operating Efficiency (2 คะแนน)

#### 2. บูรณาการและเชื่อมโยงทั่วทั้งระบบ
- **[`index.php`](file:///c:/xampp/htdocs/investment/index.php):** ติดตั้งแผงแสดงผล Altman Z-Score Segmented Meter และ Piotroski F-Score Progress Bar พร้อมกล่อง Dropdown ตรวจสอบเช็คลิสต์ 9 มิติอย่างละเอียด (Fundamental Checklist)
- **[`pages/compare.php`](file:///c:/xampp/htdocs/investment/pages/compare.php):** เพิ่มแถว Altman Z-Score และ Piotroski F-Score ลงในตาราง Comparison Matrix Section 5 (Solvency & Financial Health)
- **[`pages/screener.php`](file:///c:/xampp/htdocs/investment/pages/screener.php):** เพิ่มปุ่มชิปตัวกรองกลยุทธ์ยอดนิยม *หุ้นสุขภาพการเงินแกร่ง (Altman Safe & High F-Score)*
- **[`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css):** เพิ่มคลาสสำหรับ Models Grid, Model Card, Z-Score 3-Segmented Meter, และ F-Score Checklist Items

---

## 📌 [Version 1.10.0] - 2026-08-24
### 📑 Step 5: Portfolio Statement & Transaction Report Export Hub

#### 1. พัฒนาโมดูล Export Engine ([`functions/export.php`](file:///c:/xampp/htdocs/investment/functions/export.php))
- **`exportHoldingsToCsv()`:** ส่งออกรายการหุ้นที่ถือครองในพอร์ตเป็นไฟล์ CSV เข้ารหัส UTF-8 พร้อม Byte Order Mark (BOM `\xEF\xBB\xBF`) เพื่อเปิดภาษาไทยใน Microsoft Excel ได้ถูกต้องสมบูรณ์
- **`exportTransactionsToCsv()`:** ส่งออกประวัติธุรกรรมซื้อ-ขาย พร้อมตัวกรองช่วงวันที่ (Date Range Filter), ค่าธรรมเนียม, กำไรรับรู้, และบันทึก Trading Rationale
- **`getStatementData()`:** รวบรวมข้อมูลสถานะพอร์ตการเงิน เลขที่บัญชี และประวัติคำสั่งเพื่อใช้สร้างใบแจ้งยอดทางการ

#### 2. พัฒนาหน้าจอ Export Hub & Official Statement ([`pages/export.php`](file:///c:/xampp/htdocs/investment/pages/export.php))
- **Export Action Hub Cards:** การ์ดศูนย์ดาวน์โหลด 3 รูปแบบ (Holdings CSV, Transactions CSV พร้อมเลือกช่วงวันที่, และ Official Statement)
- **Printable A4 Brokerage Statement:** ใบแจ้งยอดพอร์ตสไตล์สถาบันการเงิน จัดพิมพ์ขนาด A4 อัตโนมัติ พร้อมตราประทับดิจิทัล
- **Print Media Engine (`@media print`):** ปรับแต่ง CSS ซ่อนปุ่มและ Navbar ทั้งหมด แปลงธีมเป็นโหมดเอกสารทางการสีขาว-ดำ สะอาดตา

#### 3. บูรณาการและเชื่อมโยงทั่วทั้งระบบ
- **[`pages/portfolio.php`](file:///c:/xampp/htdocs/investment/pages/portfolio.php):** เพิ่มปุ่มทางลัด `ส่งออกรายงาน (Export)` บนแถบ Header ของหน้าพอร์ตการลงทุน
- **[`functions/icons.php`](file:///c:/xampp/htdocs/investment/functions/icons.php):** เพิ่ม SVG Icons `download`, `printer`, `file-text`
- **[`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css):** เพิ่มคลาสสำหรับ Export Hub Grid, Statement Paper, และสไตล์ `@media print`

---

## 📌 [Version 1.9.0] - 2026-08-24
### 📈 Step 4: Dollar-Cost Averaging (DCA) Simulator & Compounding Engine

#### 1. พัฒนาโมดูล DCA Engine ([`functions/dca.php`](file:///c:/xampp/htdocs/investment/functions/dca.php))
- **`simulateDcaGrowth()`:** จำลองการสะสมหุ้นรายเดือนแบบทบต้น (Month-by-Month Compounding), คำนวณเงินต้นสะสม, มูลค่าพอร์ตสุทธิ, กำไรส่วนต่างราคา (Capital Gains), ปันผลสะสม (Dividends), ตัวเลือกนำปันผลมาซื้อซ้ำ (DRIP), และเปรียบเทียบ Benchmark กับเงินฝากประจำ 1.5% APY และดัชนีตลาด SET Index 7.0% APY

#### 2. พัฒนาหน้าจอ DCA Simulator ([`pages/dca.php`](file:///c:/xampp/htdocs/investment/pages/dca.php))
- **Preset Strategy Chips:** ทางลัดแผนออมหุ้นยอดนิยม (*มือใหม่เริ่มออม 3,000฿/ด. 5 ปี*, *สร้างอิสรภาพทางการเงิน 5,000฿/ด. 10 ปี*, *เกษียณมั่งคั่ง 10,000฿/ด. 15 ปี*)
- **Parameter Configuration Form:** ฟอร์มปรับเปลี่ยนจำนวนเงิน, ระยะเวลา (1-30 ปี), อัตราเติบโตคาดการณ์, อัตราเงินปันผล และสวิตช์ DRIP
- **Financial KPI Summary:** สรุป 4 มิติ (เงินต้นสะสม, มูลค่าพอร์ตสุทธิ, กำไรส่วนต่างราคา, เงินปันผลสะสม)
- **Benchmark Comparative Cards:** เปรียบเทียบผลตอบแทนสุทธิ พอร์ต DCA vs ดัชนีตลาด vs เงินฝากประจำ
- **Multi-Line Growth Trajectory Chart:** แผนภูมิกราฟเส้นแสดงทิศทางการเติบโตของความมั่งคั่งเปรียบเทียบ 4 เส้น (Chart.js Local 100%)
- **Year-by-Year Amortization Schedule Table:** ตารางแจกแจงเงินต้น, ปันผลประจำปี, กำไรสะสม, และมูลค่าพอร์ตสุทธิในแต่ละสิ้นปี

#### 3. บูรณาการและเชื่อมโยงทั่วทั้งระบบ
- **[`templates/header.php`](file:///c:/xampp/htdocs/investment/templates/header.php):** เพิ่มเมนู `จำลองออมหุ้น (DCA)` ใน Header ทั้ง Desktop และ Mobile Navigation Drawer
- **[`functions/icons.php`](file:///c:/xampp/htdocs/investment/functions/icons.php):** เพิ่ม SVG Icons `calculator`, `repeat`, `coins`
- **[`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css):** เพิ่มคลาสสำหรับ DCA Calculator Grid, Form Cards, Summary Cards, และ Benchmark Cards

---

## 📌 [Version 1.8.0] - 2026-08-24
### 📖 Step 3: Trading Journal & Performance Analytics System

#### 1. พัฒนาโมดูล Trading Journal Engine ([`functions/journal.php`](file:///c:/xampp/htdocs/investment/functions/journal.php))
- **`getUserJournalEntries()`:** ดึงประวัติการบันทึกการเทรด พร้อมกรองตามกลยุทธ์ (Value, Growth, Dividend, Technical Breakout, AI Recommendation)
- **`updateTradeJournalLesson()`:** อัปเดตบทเรียนที่ได้รับ (Lessons Learned / Post-trade Reflection)
- **`calculateTradingPerformanceMetrics()`:** อัลกอริทึมคำนวณ Win Rate (%), Profit Factor, Net Realized P&L, Best/Worst Trade, Average Win vs Average Loss และสถิติแจกแจงแยกตามกลยุทธ์ (Strategy Breakdown)

#### 2. พัฒนาหน้าจอ Trading Journal Dashboard ([`pages/journal.php`](file:///c:/xampp/htdocs/investment/pages/journal.php))
- **Performance KPI Cards:** แผงสถิติ 4 มิติ (Win Rate %, Profit Factor, Net Realized P&L, Average Win / Average Loss)
- **Strategy Breakdown Table:** ตารางสรุปจำนวนออเดอร์, Win, Loss, Win Rate และกำไรสะสมของแต่ละกลยุทธ์
- **Strategy Filter Bar:** ชิปตัวกรองแยกตามกลยุทธ์การลงทุน 5 รูปแบบ
- **Journal Timeline Cards:** แสดงรายละเอียดคำสั่งซื้อ-ขาย, กล่องเหตุผลการตัดสินใจ (`journal-rationale-box`), กล่องบทเรียนที่ได้รับ (`journal-lesson-box`), และแบบฟอร์ม Inline สำหรับเขียน/แก้ไขข้อคิดหลังปิดออเดอร์

#### 3. ปรับปรุง Trade Modal & Portfolio Engine
- **[`templates/trade_modal.php`](file:///c:/xampp/htdocs/investment/templates/trade_modal.php):** เพิ่มช่องเลือก Strategy Tag และกล่องกรอก Trading Rationale ในขั้นตอนการส่งคำสั่งซื้อ-ขาย
- **[`functions/portfolio.php`](file:///c:/xampp/htdocs/investment/functions/portfolio.php):** เชื่อมโยง `executeSimulatedTrade()` ให้บันทึก Transaction ID, Strategy Tag, และ Rationale ลงในตาราง `trading_journals` แบบ Atomic Transaction
- **[`pages/portfolio.php`](file:///c:/xampp/htdocs/investment/pages/portfolio.php):** ส่งต่อค่า Strategy Tag และ Rationale จากฟอร์มไปยังฟังก์ชันการเทรด
- **[`templates/header.php`](file:///c:/xampp/htdocs/investment/templates/header.php):** เพิ่มเมนู `บันทึกการเทรด (Journal)` ใน Header ทั้ง Desktop และ Mobile Navigation Drawer
- **[`functions/icons.php`](file:///c:/xampp/htdocs/investment/functions/icons.php):** เพิ่ม SVG Icons `book-open`, `target`, `pie-chart`
- **[`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css):** เพิ่มสไตล์สำหรับ Performance KPI Grid, Journal Cards, Rationale Boxes, และ Strategy Breakdown Table

---

## 📌 [Version 1.7.0] - 2026-08-24
### ⚖️ Step 2: Stock Comparison & Peer Analysis Matrix System

#### 1. พัฒนาโมดูล Comparison Engine ([`functions/comparison.php`](file:///c:/xampp/htdocs/investment/functions/comparison.php))
- **`getStocksForComparison()`:** ดึงข้อมูลหุ้นพร้อมกัน 2-4 ตัว พร้อมอัตราส่วนงบการเงินและคะแนน AI
- **`identifyWinningMetrics()`:** อัลกอริทึมวิเคราะห์หาหุ้นที่เป็นผู้ชนะในแต่ละมิติ (P/E ต่ำสุด, P/BV ต่ำสุด, ROE สูงสุด, Net Margin สูงสุด, D/E ต่ำสุด, Yield สูงสุด, Revenue Growth สูงสุด, AI Score สูงสุด)
- **`calculatePeerAverages()`:** คำนวณค่าเฉลี่ยของกลุ่ม (Peer Average Benchmark) เพื่อใช้เปรียบเทียบ

#### 2. พัฒนาหน้าจอเปรียบเทียบระดับสถาบัน ([`pages/compare.php`](file:///c:/xampp/htdocs/investment/pages/compare.php))
- **Preset Comparison Strategies:** ชิปทางลัดเปรียบเทียบกลุ่มยอดนิยม (*Tech AI Giants: DELTA vs NVDA vs AAPL*, *Thai Blue Chips & Dividend*, *Domestic Consumption & Recovery*)
- **Multi-Stock Pills & Dropdown:** ระบบเลือกหุ้นเปรียบเทียบแบบยืดหยุ่น เพิ่ม/ลบได้สูงสุด 4 บริษัท
- **Champion Spotlight Cards:** การ์ดสรุปผู้ชนะ 4 หมวดหลัก (AI Score Champion, Valuation Champion, ROE Champion, Dividend Champion)
- **Multi-Bar Comparison Chart:** แผนภูมิเปรียบเทียบคะแนน AI 4 มิติ (Chart.js Local 100%)
- **Financial Matrix Table:** ตารางเปรียบเทียบ 5 หมวดหมู่ใหญ่ (AI Score, Valuation, Profitability, Growth & Dividend, Solvency & Health) พร้อมไฮไลท์เซลล์ผู้ชนะด้วยสีทองและป้าย `badge-winner-tag`
- **One-Click Trading Action:** ปุ่มส่งคำสั่งซื้อ-ขายจำลองตรงจากตารางเปรียบเทียบ

#### 3. บูรณาการและเชื่อมโยงทั่วทั้งระบบ
- **[`templates/header.php`](file:///c:/xampp/htdocs/investment/templates/header.php):** เพิ่มเมนู `เปรียบเทียบหุ้น (Compare)` ใน Header ทั้งบน Desktop และ Mobile Navigation Drawer
- **[`index.php`](file:///c:/xampp/htdocs/investment/index.php):** เพิ่มปุ่มทางลัด `เปรียบเทียบคู่แข่ง` ข้างชื่อหุ้นใน Stock Hero
- **[`functions/icons.php`](file:///c:/xampp/htdocs/investment/functions/icons.php):** เพิ่ม SVG Icons `scale`, `award`, `check-circle`
- **[`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css):** เพิ่ม CSS Classes สำหรับ Matrix Table, Champion Cards, Winner Highlights, และ Comparison Pills

---

## 📌 [Version 1.6.0] - 2026-08-24
### ⭐ Step 1: Watchlist & Price Target Alerts System

#### 1. พัฒนาโมดูล Watchlist Engine ([`functions/watchlist.php`](file:///c:/xampp/htdocs/investment/functions/watchlist.php))
- **`getUserWatchlist()`:** ดึงรายการหุ้นที่ผู้ใช้บันทึกไว้ พร้อมเชื่อมโยงอัตราส่วนการเงินและคะแนน AI พร้อมคำนวณ Proximity Alerts (แตะเป้าซื้อ/ขาย, ใกล้เป้าซื้อ ≤ +3%, ใกล้เป้าขาย ≥ -3%)
- **`toggleWatchlist()`:** สลับสถานะเพิ่ม/ลบหุ้นออกจาก Watchlist แบบ atomic transaction
- **`updateWatchlistTarget()`:** บันทึกและปรับปรุงราคาเป้าหมายซื้อ, เป้าหมายขาย, และข้อความบันทึกส่วนตัว
- **`removeFromWatchlist()` & `isStockInWatchlist()`:** ตรวจสอบและจัดการข้อมูลอย่างปลอดภัยด้วย PDO Prepared Statements

#### 2. สร้าง AJAX API Endpoint ([`api/watchlist_toggle.php`](file:///c:/xampp/htdocs/investment/api/watchlist_toggle.php))
- รองรับ Action `toggle`, `update_target`, และ `remove` ตอบสนองแบบ Asynchronous JSON
- มีการตรวจสอบสิทธิ์การเข้าสู่ระบบและ Guard Clauses ตามมาตรฐาน De Morgan's Laws & Early Return

#### 3. พัฒนาหน้าจัดการ Watchlist สไตล์ Financial Terminal ([`pages/watchlist.php`](file:///c:/xampp/htdocs/investment/pages/watchlist.php))
- แถบสรุป KPI 4 มิติ: จำนวนหุ้นใน Watchlist, หุ้นที่แตะเป้าซื้อ, หุ้นที่ใกล้เข้าเขตซื้อ, หุ้นคุณภาพสูง (AI Score 80+)
- การ์ดหุ้น Watchlist แสดงราคา Real-time, อัตราส่วน P/E, ROE, AI Score, ป้าย Proximity Alerts (`badge-target-hit`, `badge-target-near`), และโน้ตส่วนตัว
- ปุ่มเทรดด่วนเชื่อมกับ `trade_modal.php`, ปุ่มเปิด Modal ตั้งราคาเป้าหมาย และปุ่มลบหุ้น
- Empty State สะอาดตาพร้อมเชิญชวนให้คัดกรองหุ้นหรือเข้าสู่ระบบ

#### 4. บูรณาการ UI ทั่วทั้งระบบ
- **[`templates/header.php`](file:///c:/xampp/htdocs/investment/templates/header.php):** เพิ่มเมนู `รายการติดตาม (Watchlist)` ทั้งใน Desktop Bar และ Mobile Navigation Drawer
- **[`index.php`](file:///c:/xampp/htdocs/investment/index.php):** ติดตั้งปุ่ม Quick Bookmark Icon สไตล์ Dark Gold ข้างชื่อย่อหุ้นใน Stock Hero
- **[`pages/screener.php`](file:///c:/xampp/htdocs/investment/pages/screener.php):** ติดตั้งปุ่ม Quick Bookmark ในตารางผลการคัดกรองหุ้น
- **[`functions/icons.php`](file:///c:/xampp/htdocs/investment/functions/icons.php):** เพิ่ม SVG Icons `bookmark`, `bookmark-filled`, `crosshair`, `trash`, `edit`, `external-link` (100% Vector ไร้ Emoji)
- **[`js/main.js`](file:///c:/xampp/htdocs/investment/js/main.js):** สร้าง `window.toggleWatchlistStock()` สากล รองรับการคลิก Bookmark ได้จากทุกหน้าเพจ
- **[`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css):** เพิ่ม CSS Classes สำหรับ Watchlist Grid, Cards, Badges, และปุ่ม Bookmark ตามทฤษฎี Subtle Flat Hover State

---

## 📌 [Version 1.5.4] - 2026-08-24
### 🧹 Removal of Quick Demo Account Box & Placeholder Cleanup

#### 1. นำข้อมูลบัญชีทดลองใช้ออกจากหน้าเข้าสู่ระบบ ([`pages/login.php`](file:///c:/xampp/htdocs/investment/pages/login.php))
- **ลบ Demo Box:** ลบส่วน *บัญชีทดสอบในระบบ* และ *บัญชี Demo สำหรับทดลองใช้ (User: investor_demo | Pass: password123)* ออกจากแบบฟอร์มเข้าสู่ระบบทั้งหมด
- **ปรับ Placeholder:** ปรับข้อความตัวอย่างในช่องกรอกชื่อผู้ใช้/อีเมลเป็น `กรอกชื่อผู้ใช้ หรือ อีเมลของคุณ` และช่องรหัสผ่านเป็น `กรอกรหัสผ่านของคุณ` ให้สะอาดตา เป็นมืออาชีพ พร้อมสำหรับการใช้งานจริง

---

## 📌 [Version 1.5.3] - 2026-08-24
### 👁️ Native Browser Password Reveal Icon Reset & Input Wrapper Isolation

#### 1. ซ่อนไอคอนดวงตาเริ่มต้นของเว็บเบราว์เซอร์ ([`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css))
- **Edge / IE Pseudo-elements:** ซ่อน `::-ms-reveal` และ `::-ms-clear` บน `input[type="password"]` แบบ `display: none !important;` ขจัดไอคอนดวงตาของ Microsoft Edge ที่แสดงซ้อนกับปุ่ม Custom SVG
- **WebKit / Blink Elements:** ซ่อน `::-webkit-contacts-auto-fill-button`, `::-webkit-credentials-auto-fill-button`, และ `::-webkit-caps-lock-indicator`
- **Padding Isolation:** กำหนด `padding-right: 2.75rem;` บน `.form-input-wrapper input[type="password"]` ป้องกันไม่ให้ข้อความรหัสผ่านที่พิมพ์ทับซ้อนกับปุ่มดวงตา Custom SVG (`.input-toggle-btn`)
- **Luxury Toggle Button:** ปรับแต่ง `.input-toggle-btn` ให้มี `z-index: 2`, รองรับ Focus State และเปลี่ยนสีเป็น `var(--accent-gold)` เมื่อ Hover อย่างราบรื่น

---

## 📌 [Version 1.5.2] - 2026-08-23
### 📐 Comprehensive Footer Responsive Layout & Proportional Grid System

#### 1. ปรับระบบ Layout และ Grid ของ Footer ให้สมดุลทุกขนาดหน้าจอ ([`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css))
- **Mobile (< 640px):** แสดงผลแบบ 1 Column แนวตั้ง พร้อมระยะห่าง `gap: 2rem;` ที่อ่านง่าย สบายตา
- **Tablet (640px - 991px):** กำหนดให้คอลัมน์แบรนด์ขยายเต็มความกว้าง (`grid-column: 1 / -1;`) และให้ 2 คอลัมน์เมนูนำทาง (`ระบบการลงทุน` และ `บัญชีผู้ใช้งาน`) วางเรียงข้างกันอย่างสมมาตร (`1fr 1fr`) ขจัดปัญหาช่องว่างว่างเปล่า
- **Desktop (>= 992px):** ปรับสัดส่วน Grid เป็น `1.85fr 1fr 1fr;` พร้อมระยะห่าง `gap: clamp(2.5rem, 4vw, 4rem);` และควบคุม `max-width: 440px;` ของคำบรรยายให้อ่านสบายตา
- **Typography & Links:** ปรับระยะบรรทัด `line-height: 1.65;`, ระยะห่างไอคอน `gap: 8px;`, และจัดแถบ Copyright กึ่งกลางอย่างประณีต

---

## 📌 [Version 1.5.1] - 2026-08-23
### 🏷️ Complete Removal of Brand Badge (PRO) across Header & Footer

#### 1. ลบ `class="brand-badge"` ทั่วทั้งระบบ
- **[`templates/header.php`](file:///c:/xampp/htdocs/investment/templates/header.php):** ลบ `<span class="brand-badge">PRO</span>` ออกจากโลโก้ในแถบ Header
- **[`templates/footer.php`](file:///c:/xampp/htdocs/investment/templates/footer.php):** ลบ `<span class="brand-badge">PRO</span>` ออกจากโลโก้ในส่วนท้ายเว็บไซต์
- **[`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css):** ลบคลาส CSS `.brand-badge` และ Media Query ที่เกี่ยวข้องออกทั้งหมด
- โลโก้แบรนด์แสดงผลเรียบง่าย คมชัด และสม่ำเสมอในรูปแบบ `SmartPort AI`

---

## 📌 [Version 1.5.0] - 2026-08-23
### 🌟 3-Column Minimalist Luxury Footer Refinement

#### 1. ปรับโครงสร้าง Footer สู่รูปแบบ 3 คอลัมน์สมดุลสะอาดตา ([`templates/footer.php`](file:///c:/xampp/htdocs/investment/templates/footer.php))
- **ลบคอลัมน์โมเดลวิเคราะห์ออก:** ลบหัวข้อ *การวิเคราะห์และโมเดล AI* พร้อมรายการย่อย 4 รายการ (`Composite Score`, `งบการเงิน 3 ด้าน`, `อัตราส่วนสำคัญ`, `AI Summary`)
- **ลบ Trust Chips:** ลบ `.footer-trust-chips` ออกจากคอลัมน์แบรนด์
- **คงเหลือ 3 คอลัมน์หลักระดับพรีเมียม:**
  1. `แบรนด์และคำบรรยายระบบ` (SmartPort AI PRO Logo & Mission Statement)
  2. `ระบบการลงทุน` (วิเคราะห์หุ้น, คัดกรองหุ้น Screener, พอร์ตจำลอง ฿1,000,000)
  3. `บัญชีผู้ใช้งาน` (เข้าสู่ระบบ / สมัครสมาชิก หรือ พอร์ตจำลอง / ออกจากระบบ)
- **ปรับแต่ง Desktop Layout ([`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css)):** เปลี่ยน `.footer-grid` ให้แสดงผลแบบ `grid-template-columns: 2fr 1fr 1fr;` บนหน้าจอ Desktop (`>= 992px`) และจัดความกว้าง `max-width: 420px;` สำหรับคำบรรยายให้อ่านง่าย สวยงาม ไม่หนาแน่นจนเกินไป

---

## 📌 [Version 1.4.9] - 2026-08-23
### ✨ Clean & Streamlined Footer Optimization

#### 1. ตัดส่วนที่ไม่จำเป็นออกตามคำสั่งของผู้ใช้ ([`templates/footer.php`](file:///c:/xampp/htdocs/investment/templates/footer.php))
- **ลบ Technical Meta Bar:** นำข้อมูล Stack (`PHP 8.x + MySQL + Vanilla JS`), Security (`PDO Prepared & BCRYPT`), และ Version ออกจาก Footer ด้านล่าง
- **ลบ Status Card:** นำ `.footer-status-card` ออกจากคอลัมน์ที่ 4 เพื่อให้รายการเมนูบัญชีผู้ใช้แสดงผลอย่างสะอาดตาและเป็นระเบียบ
- **ลบ Disclaimer Box:** นำ `.footer-disclaimer-box` ออก และรวมข้อความแจ้งเตือนสั้นๆ ลงในแถบ Copyright ด้านล่าง
- **ทำความสะอาด CSS:** ลบ CSS Rules ที่ไม่ได้ใช้งานใน [`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css) และจัดตำแหน่ง Copyright ให้อยู่กึ่งกลางอย่างสมดุล

---

## 📌 [Version 1.4.8] - 2026-08-23
### 🎯 Footer Content Alignment with Core Project Specifications

#### 1. ปรับเนื้อหาส่วนท้ายเว็บไซต์ (`class="app-footer"`) ให้ตรงกับโปรเจกต์ 100%
- **Col 1 (พันธกิจและแบรนด์):** ระบุวัตถุประสงค์หลักของ SmartPort AI ในการวิเคราะห์ปัจจัยพื้นฐานหุ้นไทย/สากล, AI Composite Score 4 มิติ, และพอร์ตจำลอง ฿1,000,000 บนสถาปัตยกรรม Native Local 100%
- **Col 2 (ระบบหลัก):** ลิงก์ตรงสู่ 3 ระบบสำคัญของโปรเจกต์ (วิเคราะห์หุ้นรายตัว & กราฟราคา, คัดกรองหุ้นคุณค่า AI Screener, และพอร์ตการลงทุนจำลอง ฿1,000,000)
- **Col 3 (การวิเคราะห์ & ตัวชี้วัด):** ปรับหัวข้อให้ตรงกับฟังก์ชันจริง (`Composite Score 0-100`, `งบการเงิน 3 ด้าน`, `อัตราส่วนสำคัญ P/E, EPS, ROE, D/E`, `AI Summary & Risk Assessment`)
- **Col 4 (จำลองพอร์ตและสมาชิก):** ระบุข้อมูลเงินทุนตั้งต้น ฿1,000,000 และค่าคอมมิชชัน 0.157% ตรงตาม `constants.php` และ `PRODUCT.md`
- **Financial Disclaimer:** ปรับข้อความคำเตือนความเสี่ยงให้ครอบคลุมการศึกษาและการจำลองพอร์ตลงทุนเสมือนจริง
- **Footer Bottom Meta:** ระบุ Stack (`PHP 8.x + MySQL + Vanilla JS`) และความปลอดภัย (`PDO Prepared & BCRYPT`)

---

## 📌 [Version 1.4.7] - 2026-08-23
### 🏛️ Institutional Luxury Footer Redesign & Trust Framework

#### 1. ปรับปรุงดีไซน์และโครงสร้างส่วนท้ายเว็บไซต์ ([`templates/footer.php`](file:///c:/xampp/htdocs/investment/templates/footer.php))
- **Brand & Trust Framework (Col 1):** โลโก้แบรนด์ `SmartPort AI PRO`, คำอธิบายแพลตฟอร์มเชิงสถาบัน, และป้าย Trust Chips 3 รายการ (`100% Local Intelligence`, `Zero-Lag Architecture`, `256-Bit Security`)
- **ระบบการลงทุน (Col 2):** เมนูนำทางสู่ระบบหลักพร้อมไอคอน `arrow-up-right`
- **โมเดล AI เชิงลึก (Col 3):** สรุปโมเดลการคำนวณและประเมินมูลค่าหุ้น (`Composite Rating`, `Intrinsic Value`, `Z-Score`, `Asset Allocation Optimizer`)
- **สถานะและการเข้าถึง (Col 4):** การ์ดแสดงสถานะระบบแบบ Real-time Pulse Dot (`Core Engine Active`, `Architecture: 100% Local`) พร้อมปุ่มเข้าถึงบัญชีผู้ใช้
- **Financial Disclaimer Box:** กล่องแจ้งเตือนความรับผิดชอบทางการเงินแบบ Glassmorphic พร้อมไอคอน `alert-triangle` สีทอง
- **Footer Bottom Meta:** ข้อมูลเครื่องยนต์ (`PHP 8.2 Strict Types`), เวอร์ชันระบบ (`APP_VERSION`), และค่า Latency (`< 5ms`)

#### 2. เพิ่มเติม SVG Icons ใน [`functions/icons.php`](file:///c:/xampp/htdocs/investment/functions/icons.php)
- เพิ่มไอคอน `activity`, `globe`, `cpu`, `layers` แบบ Vector คมชัด 100% ปราศจาก Emoji

#### 3. ปรับปรุง CSS สไตล์หรูหราใน [`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css)
- กำหนดเฉดสีพื้นหลังแบบ Dark Ambient Gradient (`#181111` ถึง `#0F0B0B`)
- เพิ่มเส้นขอบบนแบบ Accent Gold Ambient Line
- ปรับ Grid และ Flex ให้รองรับ Responsive สมบูรณ์แบบทุกอุปกรณ์

---

## 📌 [Version 1.4.6] - 2026-08-23
### 🚀 Direct Inline Mobile Drawer Controller & Cache Busting (v1.4.5 / v1.4.6)

#### 1. ระบบควบคุมการเปิด-ปิดเมนูแบบ Direct Inline Invocation
- ผูกฟังก์ชัน [**`window.toggleMobileDrawer(event)`**](file:///c:/xampp/htdocs/investment/templates/header.php) ผ่านแอตทริบิวต์ `onclick` โดยตรงบนแท็ก `<button id="mobileMenuBtn">`
- ทำงานทันทีโดยตรงในตัว Header ปราศจากการพึ่งพาการโหลดสคริปต์ภายนอกหรือความล่าช้าของ DOM Ready
- ซิงก์การสลับไอคอนระหว่าง `menu` และ `close` SVG อย่างแม่นยำ

#### 2. ล้างแคชเบราว์เซอร์อัตโนมัติ (Asset Cache Busting)
- อัปเกรดค่าคงที่ [**`APP_VERSION`**](file:///c:/xampp/htdocs/investment/config/constants.php) และแนบพารามิเตอร์ `?v=<?= e(APP_VERSION) ?>` ต่อท้ายไฟล์ CSS ทุกไฟล์ (`variables.css`, `layout.css`, `components.css`) และไฟล์ JavaScript (`main.js`)
- ป้องกันไม่ให้เบราว์เซอร์บนมือถือจำสคริปต์เวอร์ชันเก่าค้างอยู่ในแคช

---

## 📌 [Version 1.4.5] - 2026-08-23
### 🛡️ Fail-safe Mobile Hamburger Touch Controller & Zero-Overflow Architecture

#### 1. สถาปัตยกรรมแถบหัวเว็บแบบแยกเดี่ยว (Direct Child Isolation)
- ปรับโครงสร้าง [**`templates/header.php`**](file:///c:/xampp/htdocs/investment/templates/header.php) ให้ปุ่ม Hamburger Menu (`.mobile-toggle#mobileMenuBtn`) เป็น **Direct Child** ของ `.header-inner` แยกขาดจาก `.header-actions`
- ซ่อน `.header-actions` แบบ `display: none !important;` บนหน้าจอขนาด `< 992px` เพื่อขจัดองค์ประกอบที่ไม่เกี่ยวข้องออกจาก DOM layout
- ทำให้แถบหัวเว็บบนมือถือมีเพียง 2 ส่วนเท่านั้น คือ โลโก้ทางซ้าย และปุ่ม Hamburger ทางขวา โดยไม่มีสิ่งใดเบียดดันกัน

#### 2. ระบบ Event Listeners ป้องกัน Race Condition (Fail-safe Bootstrap)
- ปรับปรุง [**`js/main.js`**](file:///c:/xampp/htdocs/investment/js/main.js) ให้ฟังก์ชัน `initSmartPort()` ตรวจสอบ `document.readyState` ทันที ป้องกันปัญหา Event `DOMContentLoaded` ข้ามผ่านไปก่อนที่สคริปต์จะถูกโหลด
- เพิ่ม `pointer-events: none;` บนองค์ประกอบย่อยภายใน `.mobile-toggle *` และตั้งค่า `touch-action: manipulation;` พร้อม `pointer-events: auto;` บนตัวปุ่ม เพื่อให้จับสัญญาณแตะ/คลิกบนมือถือ (iOS/Android) ได้ 100% ไร้การหน่วง

#### 3. ฝัง `window.BASE_URL` Global Variable
- ฝัง `window.BASE_URL` ใน `<head>` ผ่าน JSON encode เพื่อให้การร้องขอ API Autocomplete และ Navigation บนมือถือทำงานได้อย่างถูกต้อง

---

## 📌 [Version 1.4.4] - 2026-08-23
### 📱 Pure Hamburger Header Mode on Mobile/Tablet (< 992px)

#### 1. สถาปัตยกรรมแถบเมนูหัวเว็บแบบ Pure Hamburger Mode บนหน้าจอมือถือและแท็บเล็ต
- บนหน้าจอขนาด `< 992px`: ซ่อนปุ่ม Action, Search Box และ Main Nav ทั้งหมดออกจากแถบหัวเว็บด้านบนอย่างเด็ดขาด คงเหลือไว้เฉพาะ **โลโก้ทางซ้าย (`.brand-logo`)** และ **ปุ่ม Hamburger Menu ทางขวา (`.mobile-toggle`)**
- ป้องกันปัญหาการเบียดดันของ Search Input หรือปุ่มสมัครสมาชิกที่เคยทำให้ Header ล้นขอบจอไปทางขวา 100%
- รวมศูนย์การค้นหาหุ้น (`#mobileStockSearchInput`), เมนูนำทาง (`.mobile-nav-links`), และปุ่มโปรไฟล์/เข้าสู่ระบบ/สมัครสมาชิก (`.mobile-auth-section`) ไว้ภายใน Mobile Drawer ที่กดเปิด-ปิดได้สะดวก

#### 2. การควบคุมความกว้างและ Layout Containment
- ปรับแต่ง `.app-header` ให้มี `width: 100%; max-width: 100%; box-sizing: border-box;` สอดรับกับ Safe Area Insets ทุกอุปกรณ์
- ปรับการทำงานของ Desktop Components ให้แสดงผลเฉพาะบนหน้าจอขนาดใหญ่ `>= 992px` อย่างเป็นระเบียบ

---

## 📌 [Version 1.4.3] - 2026-08-23
### 📱 Mobile Navigation Drawer & Hamburger Menu Touch-Optimized Upgrade

#### 1. อัปเกรดปุ่ม Hamburger Menu (`.mobile-toggle`)
- เพิ่มมาตรฐาน Touch Target 44x44px ตามหลัก **Apple Human Interface Guidelines (HIG)** พร้อม `z-index: 105`
- เพิ่มการสลับไอคอนแอนิเมชัน SVG ระหว่าง `menu` (เมื่อปิด) และ `close` (เมื่อเปิด)
- รองรับการกดและปิดเมื่อคลิกที่ใดก็ได้นอกเมนู หรือกดปุ่ม `Escape`

#### 2. พัฒนา Luxury Slide-down Mobile Drawer ([`templates/header.php`](file:///c:/xampp/htdocs/investment/templates/header.php))
- เพิ่ม **Mobile Quick Search Box** ในแถบเมนูมือถือ รองรับการค้นหาหุ้นทั้งไทยและเทศสดแบบ Real-time
- จัดระเบียบรายการเมนูนำทางพร้อม SVG Icons (`chart-bar`, `filter`, `wallet`, `arrow-up-right`)
- เพิ่มส่วนข้อมูลผู้ใช้แบบ Mobile Card (`.mobile-user-info`) แสดงชื่อ อีเมล และปุ่มออกจากระบบ หรือปุ่มเข้าสู่ระบบ/สมัครสมาชิก

#### 3. ปรับปรุง CSS Layout และ Touch Momentum ([`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css))
- ใช้ `animation: drawerSlideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1)` มอบสัมผัสที่นุ่มนวล
- ป้องกันการหลุดจอหรือเมนูหายเมื่อย่อหน้าจอหรือใช้งานบนสมาร์ตโฟนทุกขนาด

---

## 📌 [Version 1.4.2] - 2026-08-23
### 🛡️ Header Viewport Overflow Fix, Rules 6-8 Standardization & System Integrity Audit

#### 1. บันทึกและขยายกฎระเบียบการพัฒนาใน [`markdowns/REFACTORCODE.md`](file:///c:/xampp/htdocs/investment/markdowns/REFACTORCODE.md)
- **Rule 6 (Zero Inline CSS Extraction):** ห้ามเขียน CSS ในแอตทริบิวต์ `style="..."` หรือ `<style>` ภายในหน้าเพจ ให้สกัดไปยัง `css/variables.css`, `css/layout.css`, `css/components.css`
- **Rule 7 (DRY Modular Template Architecture):** รวมศูนย์โค้ดส่วนหัว ส่วนท้าย กล่องแจ้งเตือน และหน้าต่างเทรดไว้ในโฟลเดอร์ `templates/`
- **Rule 8 (Local Assets & Vector Icons Dictionary):** บังคับใช้ Assets ในโปรเจกต์ 100% ปราศจาก External CDN พร้อมลงทะเบียน SVG Icons ใน `functions/icons.php`

#### 2. แก้ไขปัญหา Header ล้นขอบจอไปทางขวา (`.app-header` Viewport Overflow Fix)
- เพิ่ม `overflow-x: hidden; max-width: 100vw;` บน `html, body` และ `max-width: 100vw; overflow-x: clip;` บน `.app-header`
- ปรับขนาด `.search-box` ใน Header ให้เป็นแบบ Fluid Responsive (`width: clamp(140px, 14vw, 220px);` ขยายได้ถึง `clamp(180px, 18vw, 260px);`)
- ปรับปรุงการจัดตำแหน่งของเมนูป๊อปอัปค้นหาหุ้นสด [**`.search-dropdown`**](file:///c:/xampp/htdocs/investment/css/components.css) ให้จัดชิดขวา (`right: 0; left: auto;`) พร้อมจำกัดขนาด `max-width: min(340px, calc(100vw - 2rem));` ป้องกันการล้นขอบจอ
- ปรับการแสดงผลปุ่ม Action ใน Header บนหน้าจอมือถือ/แท็บเล็ต โดยซ่อนปุ่มรองที่ไม่จำเป็นและให้เรียกใช้งานผ่าน Mobile Drawer แทน

#### 3. การตรวจสอบความสมบูรณ์และกฎระเบียบทั่วทั้งระบบ (Full System Audit)
- ตรวจสอบไวยากรณ์ PHP ทุกไฟล์ผ่านคำสั่ง `php -l`: **100% PASS (0 Errors / 0 Warnings)**
- ตรวจสอบ De Morgan's Laws, Strict Types, Output Escaping, No-Emoji UI, No-CDN, และ Flat Button Hover: ผ่านเกณฑ์มาตรฐานระดับสากลครบถ้วน

---

## 📌 [Version 1.4.1] - 2026-08-23
### 🏗️ Project-Wide DRY Modularization, CSS Extraction & 100% Local Asset Refactoring

#### 1. สถาปัตยกรรมเทมเพลตแบบโมดูลาร์ (DRY Template Components Architecture)
- สกัดโค้ดส่วนหัวและเนวิเกชันบาร์รวมศูนย์ไว้ที่ [**`templates/header.php`**](file:///c:/xampp/htdocs/investment/templates/header.php) รองรับ Active Route Highlighting, Mobile Drawer, และ Head Meta Tag (`viewport-fit=cover`)
- รวมศูนย์ส่วนท้ายเว็บไซต์และสคริปต์สากลไว้ที่ [**`templates/footer.php`**](file:///c:/xampp/htdocs/investment/templates/footer.php) รองรับการแทรกสคริปต์เฉพาะหน้า (`$extraScripts`)
- สร้างคอมโพเนนต์การแจ้งเตือนสากล [**`templates/flash.php`**](file:///c:/xampp/htdocs/investment/templates/flash.php) รองรับสถานะ `success`, `danger`, `warning`
- สกัดหน้าต่างส่งคำสั่งซื้อ-ขายจำลองเป็นคอมโพเนนต์ใช้ซ้ำ [**`templates/trade_modal.php`**](file:///c:/xampp/htdocs/investment/templates/trade_modal.php) รองรับการซื้อขายทั้งแบบเลือกจาก Universe หุ้นทั้งหมด หรือระบุหุ้นรายตัว

#### 2. การสกัด CSS เป็นคลาสมาตรฐาน (Zero Inline CSS Extraction)
- ย้าย CSS ที่เขียนแทรกอยู่ในหน้าเว็บ (Inline/Internal `<style>`) ออกไปยัง [**`css/components.css`**](file:///c:/xampp/htdocs/investment/css/components.css) และ [**`css/layout.css`**](file:///c:/xampp/htdocs/investment/css/layout.css)
- กำหนด Semantic CSS Classes ใหม่ เช่น `.watchlist-bar`, `.watchlist-chip`, `.range-52w-card`, `.analysis-grid`, `.asset-allocation-card`, `.asset-allocation-bar`, `.pw-strength-bar` เป็นต้น

#### 3. ปรับโครงสร้างโค้ดหน้าเว็บทุกหน้า (Full Refactoring Across All Pages)
- **`index.php`**: ลดขนาดโค้ดจาก 769 บรรทัดเหลือ ~300 บรรทัด สะอาด เป็นระเบียบ บำรุงรักษาง่าย
- **`pages/portfolio.php`**: เชื่อมต่อ `templates/header.php`, `templates/trade_modal.php`, `templates/flash.php`, `templates/footer.php`
- **`pages/screener.php`**: ใช้งาน `templates/header.php`, `templates/footer.php` และ Quick Strategy Presets ผ่านคลาส `.watchlist-chip`
- **`pages/login.php` & `pages/register.php`**: ยุบรวมโครงสร้างและฟอร์ม ใช้เทมเพลตมาตรฐานร่วมกัน 100%

#### 4. สอดคล้องตามกฎระเบียบ 100% Local Assets & Icon Dictionary
- ขยาย SVG Icons ใน [**`functions/icons.php`**](file:///c:/xampp/htdocs/investment/functions/icons.php) เพิ่ม `alert-triangle`, `clock` ให้ครอบคลุมทุกจุด
- ตรวจสอบความถูกต้องของ Syntax PHP ทุกไฟล์ (`php -l`) ผ่านการทดสอบ 100% 0 Errors

---

## 📌 [Version 1.4.0] - 2026-08-23
### 📱 Cross-Platform Responsive Architecture & High-DPI Display Adaptation (iOS, Android, Laptop & Desktop)

#### 1. รากฐาน Safe Area Insets & Viewport Meta (`viewport-fit=cover`)
- ปรับปรุง `<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">` ครบทั้ง 5 ไฟล์หน้าเว็บ
- กำหนด Safe Area Inset Tokens ใน [`css/variables.css`](file:///c:/xampp/htdocs/investment/css/variables.css) (`env(safe-area-inset-*)`) รองรับ iPhone Dynamic Island, Notch และ Android Gesture Navigation Bar

#### 2. การปรับแต่ง Responsive Hero, Stats Bar & 52-Week Range
- ปรับแต่ง `.stock-hero` ให้แสดงผลแบบ Fluid Stacking บนหน้าจอมือถือเล็ก (320px - 414px) พร้อมปุ่มคำสั่งเทรดที่ขยายเต็มพื้นที่อย่างลงตัว
- อัปเกรด `.stats-bar` แสดงผล 2 คอลัมน์บนมือถือ, 3-4 คอลัมน์บนแท็บเล็ต, และ 7-8 คอลัมน์บนเดสก์ท็อป

#### 3. ความคมชัดสูงบนจอ High-DPI & Retina Displays (Chart.js Configuration)
- เปิดใช้งาน `devicePixelRatio: window.devicePixelRatio || 1` ใน [`js/chart_engine.js`](file:///c:/xampp/htdocs/investment/js/chart_engine.js) ให้เส้นกราฟคมชัด 100% บนจอ Retina, iPad Pro, MacBook, และ 4K Displays
- ปรับ Chart Canvas Container ให้มีความสูงแบบ Fluid Aspect Ratio (`clamp(240px, 42vh, 380px)`)

#### 4. ตารางทางการเงินแบบ Touch-Friendly & Responsive Trade Modal
- อัปเกรด `.table-responsive` รองรับ Smooth Touch Momentum Scrolling บนอุปกรณ์สัมผัส
- ปรับแต่ง `#tradeModal` ให้มีขนาดพอดีกับหน้าจอมือถือ (`max-height: calc(100dvh - 2rem)`) และรองรับคีย์บอร์ดเสมือน

---

## 📌 [Version 1.3.0] - 2026-08-23
### 🌐 Live Real-Time Market Data Gateway & Global Stock Search Engine

#### 1. ระบบดึงราคาตลาดจริงและสถิติซื้อขายสด (Live Real-Time Market Data)
- สร้างโมดูล [`functions/market_api.php`](file:///c:/xampp/htdocs/investment/functions/market_api.php) ทำหน้าที่เป็น **Financial Data Gateway** เชื่อมต่อ Live Market Feeds
- รองรับการดึงข้อมูลสดทั้ง **หุ้นไทย (SET / mai เช่น PTT, DELTA, KBANK, OR)** และ **หุ้นสากลทั่วโลก (NASDAQ, NYSE, S&P500 เช่น TSLA, AAPL, NVDA, MSFT, GOOGL)**
- ดึงราคาตลาด Real-time, ราคาปิดวันก่อนหน้า, การเปลี่ยนแปลงราคาและเปอร์เซ็นต์, ราคาสูงสุด/ต่ำสุดของวัน (Day High/Low), ปริมาณการซื้อขาย (Volume), และแท่งเทียนย้อนหลัง (OHLCV)

#### 2. ระบบค้นหาหุ้นทั่วโลกแบบสด (Global Stock Search & Autocomplete)
- สร้าง API Endpoint [**`api/search.php`**](file:///c:/xampp/htdocs/investment/api/search.php) สำหรับค้นหาหุ้นทั้งจากฐานข้อมูลในเครื่องและตลาดโลกแบบไดนามิก
- พัฒนา UI Autocomplete Dropdown ใน [**`js/main.js`**](file:///c:/xampp/htdocs/investment/js/main.js) แสดงผลการค้นหาแบบ Real-time พร้อมชื่อบริษัท, ตลาดหลักทรัพย์, และราคาตลาด

#### 3. ระบบนำเข้าและวิเคราะห์อัตโนมัติ (On-Demand Auto-Sync & AI Synthesis)
- ปรับปรุง [`functions/stocks.php`](file:///c:/xampp/htdocs/investment/functions/stocks.php) ให้มีระบบ **Live Auto-Sync Fallback** เมื่อค้นหาหุ้นทั่วโลก
- พัฒนาระบบประเมินบทวิเคราะห์ AI เชิงปริมาณ (Quantitative Executive Summary), จุดแข็ง (Catalysts) และจุดเสี่ยง (Risks) อัตโนมัติ

#### 4. ระบบฟอร์มและการควบคุมค่า (Custom Dropdowns, Number Steppers & Rule 4)
- เพิ่ม **Rule 4 ใน `markdowns/REFACTORCODE.md` และ `DESIGN.md`**: กำหนดให้ใช้ Custom Dropdown โค้งมน (`10px`), Backdrop Blur Glassmorphism, และ Custom Dark-Gold Number Steppers (▲ / ▼) ทั่วโปรเจกต์
- ซ่อนปุ่มสี่เหลี่ยมสีขาวเดิมของเบราว์เซอร์ใน `<input type="number">` ป้องกันการทำลายธีมมืด
- แก้ไขปัญหาข้อความขัดแย้งในส่วน AI Synthesis Card (`analysis-grid`) แสดงผลตรงกับความจริง 100%

---

## 📌 [Version 1.2.0] - 2026-08-23
### 🎨 SmartPort AI Branding & UX/UI Polish (Rounded Rectangles, Non-breaking Thai Typography & Unified Fonts)

#### 1. รีแบรนด์และปรับชื่อระบบอย่างเป็นทางการ (Official Rebranding)
- เปลี่ยนชื่อโปรเจกต์และแบรนด์เป็น **SmartPort AI** (*ระบบวิเคราะห์หุ้นและพอร์ตจำลองอัจฉริยะ*)
- อัปเดต Title, Logo, Header, และ Footer ทั่วทุกหน้าเว็บ (`index.php`, `pages/login.php`, `pages/register.php`, `pages/portfolio.php`, `pages/screener.php`, `config/constants.php`, `PRODUCT.md`)

#### 2. เปลี่ยนกรอบแคปซูลเป็นกรอบสี่เหลี่ยมมุมมน (Clean Rounded Rectangles)
- ปรับปรุง CSS Tokens ใน `css/variables.css`: กำหนด `--radius-sm: 4px;` และ `--radius-md: 6px;`
- ปรับเปลี่ยน `.badge`, ปุ่ม Timeframe, Search box, Watchlist pills และ Asset allocation bars จากทรงแคปซูลมนกลม (`9999px`) เป็น **สี่เหลี่ยมมุมมน 4px - 6px** สไตล์ Financial Terminal ระดับสากล

#### 3. รวมระบบ Fonts ให้เป็นหนึ่งเดียว (Unified Typography Stack)
- รวม Font Family ทั่วทั้งโปรเจกต์ให้ใช้ **Single Modern Sans-serif Stack** (`system-ui, -apple-system, 'Segoe UI', Roboto, 'Noto Sans Thai', 'Prompt', 'Kanit', sans-serif`)
- ลบสไตล์ Serif ที่ขัดแย้งกันในหัวข้อ ทำให้ตัวอักษรทุกจุดมีความคมชัด กลมกลืน และอ่านง่ายสบายตา
- คง Monospace (`JetBrains Mono`, `Consolas`) ไว้เฉพาะตัวเลขทางการเงินและอัตราส่วนสถิติ พร้อมเปิดใช้งาน `tabular-nums`

#### 4. แก้ไขปัญหาคำฉีก (Thai Word-breaking & Line Wrapping Fixes)
- ลบแท็ก `<br>` ที่ตัดคำผิดจังหวะกลางประโยคในภาษาไทย
- เพิ่มกฎ CSS `white-space: nowrap;` ให้กับ Badges, ตัวย่อหุ้น, ปุ่ม, ตัวเลขทางการเงิน, และหัวตาราง
- เพิ่ม `text-wrap: balance;` และ `word-break: normal;` ให้กับหัวข้อและเนื้อหาเพื่อการจัดวางที่สวยงาม

#### 5. แก้ไขข้อผิดพลาด Native PDO Prepared Statements
- แก้ไขข้อผิดพลาด `SQLSTATE[HY093]: Invalid parameter number` ใน `functions/auth.php` และ `functions/stocks.php`
- แยกชื่อ Parameter Placeholder ให้ไม่ซ้ำกัน รองรับ `PDO::ATTR_EMULATE_PREPARES => false` อย่างสมบูรณ์แบบ
- อัปเดตรหัสผ่าน Bcrypt Hash ที่ถูกต้องของบัญชีทดสอบใน `database_schema.sql`

---

## 📌 [Version 1.1.0] - 2026-08-23
### 🚀 Institutional-Grade UI/UX Overhaul & Strict Quality Refactoring

#### 1. สถาปัตยกรรมเอกสารและมาตรฐานการพัฒนา (Impeccable & Standard Guides)
- จัดทำ `PRODUCT.md` ตามมาตรฐาน `impeccable:product-schema 1` กำหนด Positioning, กลุ่มผู้ใช้, ขอบเขตความสามารถ, และ Product Principles
- อัปเดต `markdowns/REFACTORCODE.md`: กำหนดกฎระเบียบสูงสุด 5 ข้อ (Rule 1: Icons Only, Rule 2: Zero External CDN, Rule 3: Flat Button States, Rule 10: Database Protocol, Rule 11: Git Policy)
- สร้างและกำหนดค่า `.gitignore` และ `.antigravityignore` เพื่อจัดการคัดแยก Dependencies และ AI Harnesses (`.agents/`, `.codex/`) อย่างปลอดภัย

#### 2. กำจัด AI Slop & ยกระดับงานดีไซน์ (Anti-AI Slop & Craft Elevation)
- **กำจัด Fake Testimonial & Cliché Copy:**
  - ลบรีวิวตัวละครปลอมใน `pages/register.php` เปลี่ยนเป็น *"กล่องจุดเด่นสถาปัตยกรรมระดับสถาบัน (Institutional Architecture & Security Highlights)"*
  - เปลี่ยนภาษาโปรโมททั่วไปใน `index.php` เป็นคำศัพท์ทางการเงินเชิงสถิติจริง เช่น *"Deterministic Valuation & Quantitative AI Synthesis"*
- **ยกระดับแดชบอร์ดหลัก (`index.php`):**
  - เพิ่ม **แถบช่วงราคา 52 สัปดาห์ (52-Week Range Visual Bar)** แสดงตำแหน่งราคาปัจจุบันเทียบจุดต่ำสุด/สูงสุด
  - เพิ่มคำอธิบายและ Benchmark ในการ์ดตัวชี้วัดสำคัญ (P/E, EPS, ROE, Revenue)
  - จัดระเบียบตารางงบการเงินแบบ Audited พร้อมระบุหน่วยทางการเงินชัดเจน (*"หน่วย: ล้านบาท / THB Million"*) และจัดแนวตัวเลขชิดขวาแบบ `tabular-nums`
  - ยกระดับเกจวัดคะแนนและแถบวัดคะแนน 4 มิติ (การเติบโต, ความสามารถทำกำไร, สุขภาพการเงิน, ความคุ้มค่าราคา)
- **ยกระดับหน้าพอร์ตจำลอง (`pages/portfolio.php`):**
  - เพิ่ม **แถบสัดส่วนสินทรัพย์ (Asset Allocation Progress Bar)** แสดงสัดส่วนเงินสดเทียบมูลค่าหุ้นที่ถือครอง
  - ตารางถือครองหุ้น (Holdings) แสดงต้นทุนเฉลี่ยถ่วงน้ำหนัก, ราคาตลาด, มูลค่ารวม, และ P&L แบบ Real-time
  - ตารางประวัติคำสั่ง (Order Audit) บันทึกประวัติพร้อมแสดงค่าคอมมิชชันและกำไร/ขาดทุนรับรู้จริง
- **ยกระดับหน้าคัดกรองหุ้น (`pages/screener.php`):**
  - เพิ่ม **Quick Strategy Presets** (หุ้นปันผลสูง Yield > 4%, หุ้นเติบโตราคาถูก P/E < 20 & ROE > 15%, หุ้นพื้นฐานแกร่ง Composite Rating > 80)
  - เพิ่มปุ่ม *"ล้างตัวกรองทั้งหมด"* เพื่อรีเซ็ตเงื่อนไข

#### 3. ความปลอดภัยและมาตรฐานทางเทคนิค (Strict Standards Compliance)
- **Icons Only 100%:** ใช้งาน SVG Vector Icons จาก `functions/icons.php` ครบทุกจุด ไม่มี Emoji ใน UI
- **Zero External CDN 100%:** ทุก Assets รวมถึง Chart.js v4.4.1 bundle โหลดจาก Local
- **Subtle Flat Button Interactions:** ลบ `transform: translateY(...)` และ `box-shadow` แสงฟุ้งทั้งหมด ปุ่มเปลี่ยนเฉพาะสีพื้นหลังให้อ่อนลงอย่างนุ่มนวล
- **De Morgan's Laws & Guard Clauses:** ทุกฟังก์ชันในระบบผ่านการตรวจสอบเงื่อนไขแบบแบนราบ ไม่มี Nested If ซ้อนลึก
- **PHP Syntax Validation:** ผ่านการทดสอบ `php -l` ทุกไฟล์ในระบบ **(0 Errors ใน 13 ไฟล์)**

---

## 📌 [Version 1.0.0] - 2026-08-23
### 🎉 Initial Core Release & Full-Stack Architecture
- จัดทำเอกสารและฐานข้อมูลเริ่มต้น 9 ตาราง พร้อมระบบจำลองพอร์ต 1,000,000 บาท
