# 📜 LOG.md: บันทึกประวัติการพัฒนาและรุ่นเวอร์ชัน (Project Changelog)

เอกสารนี้ใช้บันทึกประวัติการเปลี่ยนแปลง รายการปรับปรุงฟีเจอร์ และการแก้ไขโค้ด (Release Notes & Audit Log) ของโปรเจกต์ **SmartPort AI**

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
- ปรับปรุง [`functions/stocks.php`](file:///c:/xampp/htdocs/investment/functions/stocks.php) ให้มีระบบ **Live Auto-Sync Fallback** เมื่อผู้ใช้ค้นหาหุ้นตัวใดในโลกที่ยังไม่มีในระบบ:
  1. ระบบจะวิ่งไปดึงข้อมูลสดจากตลาดจริงทันที
  2. สร้างรายการหุ้นในฐานข้อมูล MySQL อัตโนมัติ พร้อมคำนวณตัวชี้วัดทางการเงินและบทวิเคราะห์ AI Score
  3. บันทึกแท่งเทียนย้อนหลังสำหรับเรนเดอร์กราฟ Chart.js ทันที พร้อมให้นำไปทดลองซื้อขายในพอร์ตจำลองได้ทันที

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
