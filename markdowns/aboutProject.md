# 📈 เกี่ยวกับโปรเจกต์: ระบบวิเคราะห์และคัดเลือกหุ้นด้วย AI พร้อมพอร์ตจำลอง (AI Stock Analysis & Simulated Portfolio)

## 1. บทนำและวัตถุประสงค์ของโปรเจกต์ (Project Overview)
โปรเจกต์นี้เป็นเว็บแอปพลิเคชันทางการเงินและการลงทุนยุคใหม่ ที่ผสานพลังของ **ปัญญาประดิษฐ์ (AI)** เข้ากับการวิเคราะห์ปัจจัยพื้นฐาน (Fundamental Analysis) และการวิเคราะห์ทางเทคนิค (Technical Analysis) เพื่อช่วยให้นักลงทุน ทั้งมือใหม่และผู้มีประสบการณ์ สามารถคัดกรอง วิเคราะห์สุขภาพทางการเงิน และตัดสินใจลงทุนในหุ้นได้อย่างมีประสิทธิภาพและแม่นยำ พร้อมทั้งมีระบบ **พอร์ตการลงทุนจำลอง (Simulated Portfolio)** ที่เชื่อมโยงกับราคาและข้อมูลหุ้นจริงแบบ Real-time เพื่อให้ผู้ใช้สามารถฝึกฝนกลยุทธ์การลงทุนได้โดยไม่ต้องเสี่ยงกับเงินจริง

---

## 2. ฟีเจอร์หลักของระบบ (Core Features)

### 📊 2.1 ระบบแสดงกราฟและข้อมูลราคาหุ้นแบบ Interactive (Stock Chart & Live Data)
- **Interactive Candlestick & Line Chart:** กราฟแท่งเทียนและกราฟเส้นปรับแต่งได้ รองรับช่วงเวลาหลากหลาย (1D, 1W, 1M, 1Y, 5Y, MAX)
- **Technical Indicators พื้นฐาน:** เช่น Moving Average (SMA/EMA), Volume, RSI, MACD
- **Real-time Price & Market Stats:** ราคาปัจจุบัน, การเปลี่ยนแปลง (% Change), จุดสูงสุด-ต่ำสุดของวัน (High/Low), มูลค่าการซื้อขาย (Trading Volume)

### 📑 2.2 การวิเคราะห์งบการเงินและอัตราส่วนสำคัญ (Financial Metrics & Statement Analysis)
- **อัตราส่วนทางการเงินหลัก (Key Valuation & Financial Ratios):**
  - **P/E (Price to Earnings Ratio):** ประเมินความถูกแพงของราคาหุ้นเทียบกับกำไรต่อหุ้น
  - **EPS (Earnings Per Share):** กำไรสุทธิต่อหุ้นเพื่อดูการเติบโตของผลการดำเนินงาน
  - **ROE (Return on Equity):** อัตราส่วนผลตอบแทนต่อส่วนของผู้ถือหุ้น เพื่อวัดความสามารถในการสร้างกำไร
  - **Revenue & Net Profit:** รายได้รวมและกำไรสุทธิย้อนหลัง แสดงแนวโน้มการเติบโต
  - **P/BV & Dividend Yield:** อัตราส่วนราคาต่อมูลค่าทางบัญชี และอัตราผลตอบแทนจากเงินปันผล
- **การวิเคราะห์งบการเงิน 3 ด้านหลัก:**
  1. **งบแสดงฐานะการเงิน (Balance Sheet):** สินทรัพย์รวม, หนี้สิน, ส่วนของผู้ถือหุ้น, D/E Ratio
  2. **งบกำไรขาดทุนเบ็ดเสร็จ (Income Statement):** รายได้, ต้นทุน, กำไรขั้นต้น (Gross Profit Margin), กำไรจากการดำเนินงาน, กำไรสุทธิ
  3. **งบกระแสเงินสด (Cash Flow Statement):** กระแสเงินสดจากการดำเนินงาน (CFO), การลงทุน (CFI), การจัดหาเงิน (CFF) และ Free Cash Flow (FCF)

### 🤖 2.3 ระบบ AI สรุปหุ้นและประเมินคะแนน (AI Summary & Stock Scoring Engine)
- **AI Fundamental Summary:** AI สรุปบทวิเคราะห์จุดแข็ง (Strengths), จุดอ่อน/ความเสี่ยง (Risks), และทิศทางธุรกิจในภาษาที่เข้าใจง่าย (Executive Summary)
- **AI Stock Score (0 - 100 คะแนน):** ระบบคำนวณคะแนนหุ้นแบบองค์รวม แบ่งเป็น:
  - **Growth Score (คะแนนการเติบโต):** พิจารณาการเติบโตของรายได้และ EPS
  - **Profitability Score (คะแนนความสามารถในการทำกำไร):** พิจารณา ROE, Net Margin, Gross Margin
  - **Financial Health Score (คะแนนสุขภาพทางการเงิน):** พิจารณาหนี้สินต่อทุน (D/E), Current Ratio, กระแสเงินสด
  - **Valuation Score (คะแนนความคุ้มค่าของราคา):** พิจารณา P/E, P/BV เทียบกับค่าเฉลี่ยอุตสาหกรรม
- **AI Recommendation Tag:** ให้คำแนะนำเชิงวิเคราะห์ เช่น `Strong Buy`, `Accumulate`, `Hold`, `Wait & See`, `High Risk`

### 💼 2.4 ระบบพอร์ตการลงทุนจำลองแบบเรียลไทม์ (Simulated Portfolio Tracker)
- **จำลองการซื้อ-ขายหุ้นเสมือนจริง (Virtual Trading Simulation):**
  - กำหนดเงินทุนเริ่มต้นจำลอง (เช่น 1,000,000 บาท)
  - ส่งคำสั่งซื้อ (Buy) / ขาย (Sell) ในราคาตลาดจริงแบบ Real-time
- **การคำนวณกำไร-ขาดทุนแบบสด (Real-time P&L Calculation):**
  - คำนวณราคาต้นทุนเฉลี่ย (Average Cost)
  - กำไร/ขาดทุนที่ยังไม่เกิดขึ้นจริง (Unrealized Profit/Loss) อัปเดตตามราคาตลาดสด
  - กำไร/ขาดทุนที่เกิดขึ้นจริง (Realized Profit/Loss) หลังปิดสถานะขาย
  - แสดงผลตอบแทนรวมของพอร์ต (% Portfolio Return) เทียบกับดัชนีตลาด
- **ประวัติการทำธุรกรรม (Transaction History):** บันทึกประวัติการซื้อ-ขายอย่างละเอียด

---

## 3. สถาปัตยกรรมระบบ (System Architecture)

```
+-------------------------------------------------------------------+
|                        Web Presentation Layer                      |
|       (HTML5 Semantic, CSS3 / Custom Properties, JavaScript)      |
|               - Chart Engine (Lightweight / Chart.js)             |
|               - Financial Dashboard & Screener UI                 |
+---------------------------------+---------------------------------+
                                  | HTTP / JSON API
                                  v
+-------------------------------------------------------------------+
|                        Backend Logic Layer                        |
|             (PHP 8.x - Native, Modular, Strict Types)             |
|   - Auth & Session Manager (functions/auth.php)                   |
|   - Stock Analysis & Metrics Calculator (functions/stocks.php)    |
|   - AI Summary & Scoring Engine (functions/ai_engine.php)         |
|   - Portfolio Simulator Engine (functions/portfolio.php)         |
|   - Security & Validation Layer (functions/helpers.php)           |
+---------------------------------+---------------------------------+
                                  | PDO Prepared Statements
                                  v
+-------------------------------------------------------------------+
|                         Data Storage Layer                        |
|                   (MySQL / MariaDB via XAMPP)                     |
|   - Users, Stocks, Financial Metrics, Statements, Portfolios      |
+-------------------------------------------------------------------+
```

---

## 4. ผู้ใช้งานและกรณีการใช้งานหลัก (User Personas & Use Cases)

1. **นักลงทุนมือใหม่ (Beginner Investors):**
   - ศึกษางบการเงินและทำความเข้าใจอัตราส่วน P/E, EPS, ROE ผ่านคำอธิบายที่เข้าใจง่าย
   - อ่านบทวิเคราะห์สรุปหุ้นจาก AI และดู AI Score เพื่อประกอบการศึกษา
   - ทดลองซื้อหุ้นเข้าพอร์ตจำลองเพื่อดูผลตอบแทนจริงก่อนลงสนามจริง
2. **นักลงทุนสายปัจจัยพื้นฐาน (Value / Growth Investors):**
   - คัดกรองหุ้นตามเกณฑ์อัตราส่วนการเงิน (Stock Screener)
   - วิเคราะห์งบการเงินย้อนหลังหลายปีเพื่อประเมินความแข็งแกร่งของกิจการ
3. **นักลงทุนทั่วไปที่ต้องการติดตามพอร์ต (Active Traders):**
   - ติดตามราคาและกราฟหุ้นแบบ Real-time
   - ทดลองจัดสรรพอร์ตการลงทุน (Portfolio Allocation) ในรูปแบบต่างๆ

---

## 5. มาตรฐานและกฎการพัฒนา (Development Guidelines)
- ปฏิบัติตาม **Coding Guides** ทั้งหมดในโฟลเดอร์ `markdowns/`
- ใช้หลักการ **De Morgan's Laws** และ **Early Return** ในการจัดการเงื่อนไขทุกฟังก์ชัน
- รักษาความปลอดภัยของข้อมูลด้วย **PDO Prepared Statements**, **Output Escaping**, และ **Password Hashing**
- การจัดการฐานข้อมูลต้องจัดเตรียมสคริปต์ SQL ให้ผู้ใช้เป็นผู้ Import ผ่าน phpMyAdmin เองตามกฎข้อ 10
