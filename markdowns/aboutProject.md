# 📈 เกี่ยวกับโปรเจกต์: SmartPort AI (ระบบวิเคราะห์และคัดเลือกหุ้นด้วย AI พร้อมพอร์ตจำลองระดับสถาบัน)

## 1. บทนำและวัตถุประสงค์ของโปรเจกต์ (Project Overview)
**SmartPort AI** เป็นเว็บแอปพลิเคชันทางการเงินและการลงทุนยุคใหม่ พัฒนาขึ้นเพื่อผสานพลังของ **ปัญญาประดิษฐ์ (Quantitative AI)** เข้ากับการวิเคราะห์ปัจจัยพื้นฐาน (Fundamental Analysis), การวิเคราะห์เชิงสถิติ (Statistical Scoring Models เช่น Altman Z-Score & Piotroski F-Score), และการวิเคราะห์ทางเทคนิค (Technical Analysis) เพื่อช่วยให้นักลงทุน ทั้งมือใหม่และผู้มีประสบการณ์ สามารถคัดกรอง วิเคราะห์สุขภาพทางการเงิน และตัดสินใจลงทุนในหุ้นทั่วโลกได้อย่างแม่นยำ 

ระบบเชื่อมต่อกับ **Live Market Data API** ดึงราคา กราฟ และปริมาณการซื้อขายแบบเรียลไทม์ 100% พร้อมระบบ **พอร์ตการลงทุนจำลอง (Simulated Portfolio)** ที่คำนวณต้นทุนเฉลี่ยถ่วงน้ำหนัก (Weighted Average Cost), กำไรขาดทุน Realized/Unrealized, และค่าธรรมเนียมจริงตามมาตรฐานตลาดหลักทรัพย์

---

## 2. โมดูลและฟีเจอร์หลักทั้ง 10 ส่วน (10 Core Modules)

### 📊 1. ระบบวิเคราะห์หุ้นรายตัวและกราฟราคาเรียลไทม์ (`index.php`, `api/chart_data.php`)
- **Live Market Data Sync:** ดึงราคาและข้อมูลตลาดสด 100% จาก Global API พร้อมบันทึกแคชลงฐานข้อมูลอย่างชาญฉลาด (Smart 60s TTL Cache)
- **Multi-Timeframe Interactive Chart:** กราฟราคาพร้อมแท่ง Volume รองรับ 6 ช่วงเวลา:
  - `1H` (1 ชั่วโมง: แท่งเทียน 2 นาที)
  - `1D` (1 วัน: แท่งเทียน 5 นาที)
  - `1W` (1 สัปดาห์: แท่งเทียน 1 ชั่วโมง)
  - `1M` (1 เดือน: แท่งเทียน 1 วัน ย้อนหลัง 30 วันทำการ)
  - `1Y` (1 ปี: แท่งเทียน 1 วัน ย้อนหลัง 365 วัน)
  - `ALL` (ประวัติสูงสุดตั้งแต่เข้าตลาด)
- **Live Search & Auto Global Ingestion:** ค้นหาหุ้นได้ทั้งหุ้นไทย (SET/mai) และหุ้นสากลทั่วโลก (US, ASIA, Global) หากเป็นหุ้นใหม่ที่ยังไม่มีในระบบ ระบบจะดึงข้อมูลพื้นฐานและลงทะเบียนเข้าสู่ฐานข้อมูลโดยอัตโนมัติ

### 📑 2. การวิเคราะห์งบการเงินและอัตราส่วนสำคัญ (`functions/stocks.php`)
- **Key Financial Ratios:** P/E Ratio, P/BV, ROE, ROA, EPS, Dividend Yield, D/E Ratio, Current Ratio, Operating Margin, Net Margin
- **Financial Statement 3 ด้าน:**
  1. **งบแสดงฐานะการเงิน (Balance Sheet):** สินทรัพย์รวม, หนี้สินรวม, ส่วนของผู้ถือหุ้น
  2. **งบกำไรขาดทุน (Income Statement):** รายได้รวม, ต้นทุน, กำไรขั้นต้น, กำไรสุทธิ
  3. **งบกระแสเงินสด (Cash Flow Statement):** CFO, CFI, CFF, Free Cash Flow (FCF)

### 🤖 3. โมเดลประเมินคะแนนและสถิติการเงินชั้นสูง (`functions/financial_models.php`)
- **SmartPort Composite Score (0 - 100 คะแนน):**
  - **Growth Score (25%):** การเติบโตของรายได้และกำไรสุทธิ
  - **Profitability Score (25%):** อัตราผลตอบแทน ROE, Net Margin, Gross Margin
  - **Financial Health Score (25%):** อัตราส่วนหนี้สิน D/E, สภาพคล่อง Current Ratio
  - **Valuation Score (25%):** ความคุ้มค่าของราคาเทียบ P/E, P/BV
- **Altman Z-Score Model:** แบบจำลองทำนายความเสี่ยงการล้มละลายของกิจการ (Safe Zone > 2.99, Grey Zone 1.81-2.99, Distress Zone < 1.81)
- **Piotroski F-Score Model (0-9 คะแนน):** แบบจำลองประเมินความแข็งแกร่งทางการเงิน 9 มิติ

### 🔍 4. เครื่องมือคัดกรองหลักทรัพย์เชิงปริมาณ (`pages/screener.php`)
- คัดกรองหุ้นตามเงื่อนไข Sector, Max P/E, Min ROE, Min Dividend Yield, และ Min AI Composite Score
- Preset กลยุทธ์ยอดนิยม: หุ้นปันผลสูง, หุ้นเติบโตราคาถูก, หุ้นพื้นฐานแกร่ง, หุ้นสุขภาพการเงินแกร่ง
- รองรับการกด Bookmark เพิ่มหุ้นเข้า Watchlist ได้ทันที

### ⚖️ 5. ระบบเปรียบเทียบหุ้นรายคู่ / กลุ่ม (`pages/compare.php`, `functions/comparison.php`)
- เปรียบเทียบหุ้นพร้อมกันได้สูงสุด 4 บริษัท ควบคู่กับค่าเฉลี่ยกลุ่มอุตสาหกรรม (Peer Benchmark Average)
- Normalized Performance Comparison Chart (Base 100%)
- Matrix วิเคราะห์เปรียบเทียบ 5 หมวดหมู่พร้อมระบบ AI คัดเลือก Winner Badge อันดับ 1

### 📈 6. เครื่องมือจำลองการออมหุ้นแบบ DCA (`pages/dca.php`, `functions/dca.php`)
- จำลองการลงทุนแบบถัวเฉลี่ยรายเดือน (Dollar-Cost Averaging Simulation)
- รองรับการจำลอง Reinvest Dividend (DRIP: Dividend Reinvestment Plan)
- ตาราง Amortization Breakdown รายเดือนและกราฟเปรียบเทียบ DCA vs Lump-Sum

### 🔖 7. รายการติดตามหลักทรัพย์และแจ้งเตือนเป้าหมาย (`pages/watchlist.php`, `functions/watchlist.php`)
- ระบบบันทึกหุ้นโปรด (Personal Watchlist)
- กำหนดราคาเป้าหมายซื้อ (Target Buy Price) และราคาเป้าหมายทำกำไร (Target Take-Profit)
- ระบบคำนวณส่วนต่างราคาและ Badge แจ้งเตือนเมื่อราคาตลาดลงแตะเป้าหมาย

### 📔 8. บันทึกการลงทุนและการวิเคราะห์หลังเทรด (`pages/journal.php`, `functions/journal.php`)
- บันทึก Trading Journal เชื่อมโยงกับประวัติคำสั่งซื้อขายจริงในพอร์ต
- วิเคราะห์ Performance รายกลยุทธ์ (Win Rate, Profit Factor, Average Return)
- บันทึก Emotion State, Rationale และ Lessons Learned

### 💼 9. พอร์ตการลงทุนจำลองและระบบบริหารความเสี่ยง (`pages/portfolio.php`, `functions/portfolio.php`)
- เงินทุนจำลอง 1,000,000 บาท พร้อมระบบหักค่าธรรมเนียมและภาษีซื้อขายจำลอง 0.157%
- Asset Allocation Visualizer (สัดส่วนเงินสด vs ตราสารทุน)
- Real-time P&L Tracking (Unrealized & Realized P&L)
- Reusable Trade Modal Component (`templates/trade_modal.php`)

### 📑 10. ระบบออกรายงานและใบแจ้งยอดทางการ (`pages/export.php`, `functions/export.php`)
- ส่งออกข้อมูลเป็นไฟล์ CSV (Active Holdings, Transaction History, Financial Statements)
- ระบบสร้างใบแจ้งยอดพอร์ตการลงทุนอิเล็กทรอนิกส์ (Formal Account Statement) รองรับการสั่งพิมพ์ A4 / Save as PDF

---

## 3. สถาปัตยกรรมระบบ (System Architecture)

```
+-------------------------------------------------------------------------------+
|                             Presentation Layer (UI)                           |
|       - Semantic HTML5, Modular CSS3 Architecture (variables, layout,         |
|         components, responsive, print)                                        |
|       - Vanilla JavaScript (Zero External CDN Dependency)                     |
|       - Local SVG Vector Icons System (functions/icons.php)                   |
|       - Native HTML5 Canvas 2D Financial Chart Engine                         |
+---------------------------------------+---------------------------------------+
                                        | HTTP / JSON REST API
                                        v
+-------------------------------------------------------------------------------+
|                            Backend Application Layer                          |
|             (PHP 8.x - Native, Strict Types: declare(strict_types=1);)        |
|                                                                               |
|   +--------------------------+-------------------------+------------------+   |
|   | Controller / Page Views  | Core Business Logic     | API Endpoints    |   |
|   | - index.php              | - auth.php              | - chart_data.php |   |
|   | - pages/screener.php     | - stocks.php            | - search.php     |   |
|   | - pages/compare.php      | - market_api.php        | - stock_quote.php|   |
|   | - pages/dca.php          | - financial_models.php  | - watchlist_...  |   |
|   | - pages/watchlist.php    | - comparison.php        |                  |   |
|   | - pages/journal.php      | - dca.php               |                  |   |
|   | - pages/portfolio.php    | - watchlist.php         |                  |   |
|   | - pages/export.php       | - journal.php           |                  |   |
|   | - pages/health_check.php | - portfolio.php         |                  |   |
|   +--------------------------+-------------------------+------------------+   |
|                                                                               |
|   Security & Standards Layer:                                                 |
|   - Guard Clauses & Early Return Pattern                                      |
|   - De Morgan's Laws Boolean Logic (!A || !B)                                 |
|   - Output Escaping via e() helper against XSS                                |
|   - BCRYPT (Cost 12) Password Hashing                                         |
|   - CSRF Token Protection & Session Fingerprinting                            |
+---------------------------------------+---------------------------------------+
                                        | PDO Prepared Statements (Zero Raw SQL)|
                                        v
+-------------------------------------------------------------------------------+
|                            Data Storage Layer (MySQL)                         |
|   - users                     : ข้อมูลผู้ใช้และรหัสผ่าน BCRYPT                |
|   - stocks                    : ข้อมูลหุ้นพื้นฐานและสถิติราคาล่าสุด           |
|   - financial_metrics         : อัตราส่วนทางการเงินย้อนหลัง                   |
|   - financial_statements      : ข่องบดุล, งบกำไรขาดทุน, งบกระแสเงินสด         |
|   - stock_price_history       : ประวัติราคาสำหรับวิเคราะห์ทางสถิติ            |
|   - stock_api_cache           : แคชข้อมูล API ตลาดสด (60s TTL)                |
|   - portfolios                : ข้อมูลบัญชีพอร์ตการลงทุนจำลอง                 |
|   - portfolio_holdings        : หุ้นที่ถือครองและราคาเฉลี่ยถ่วงน้ำหนัก        |
|   - transactions              : ประวัติคำสั่งซื้อขายและค่าธรรมเนียม           |
|   - watchlist                 : รายการติดตามและราคาเป้าหมาย                   |
|   - trade_journal             : บันทึกการลงทุนและการทบทวนกลยุทธ์              |
+-------------------------------------------------------------------------------+
```

---

## 4. มาตรฐานความปลอดภัยและ Clean Code (Engineering Standards)
1. **PHP Strict Typing:** ทุกไฟล์ PHP เริ่มต้นด้วย `declare(strict_types=1);` และระบุ Type Hinting ทั้ง Parameters และ Return Types 100%
2. **Rule 6 (Zero Inline CSS):** ไม่มี Inline `style="..."` ใน HTML แท็ก สกัดเป็น Semantic Classes ทั้งหมดใน `css/components.css` และ `css/layout.css`
3. **Rule 1 (Zero Emojis in UI):** ใช้ไอคอน Local Vector SVG ผ่าน `icon('name')` เท่านั้น
4. **Rule 2 (Zero CDN Dependency):** ไม่พึ่งพา CDN ภายนอก ไฟล์ CSS, JS, และฟอนต์ทั้งหมดโหลดจากเครื่อง Local
5. **Rule 10 & 11 Compliance:** โค้ดเตรียมคำสั่ง SQL สำหรับให้ผู้ใช้รันเอง และไม่ทำ Git Commit/Push โดยไม่ได้รับคำสั่ง

