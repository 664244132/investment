# 📜 LOG.md: บันทึกประวัติการพัฒนาและรุ่นเวอร์ชัน (Project Changelog)

เอกสารนี้ใช้บันทึกประวัติการเปลี่ยนแปลง รายการปรับปรุงฟีเจอร์ และการแก้ไขโค้ด (Release Notes & Audit Log) ของโปรเจกต์ **AI Stock Analysis & Simulated Portfolio**

---

## 📌 [Version 1.0.0] - 2026-08-23
### 🎉 Initial Core Release & Full-Stack Architecture

#### 1. สถาปัตยกรรมเอกสารและข้อกำหนด (Specifications & Documentation)
- จัดทำ `markdowns/aboutProject.md`: บันทึกขอบเขตฟีเจอร์หลัก (กราฟราคา, งบการเงิน, AI Score, พอร์ตจำลอง)
- จัดทำ `DESIGN.md`: กำหนดระบบชุดสี 60-30-5-5 (`#1D1616`, `#FFCC00`, `#FF0000`, `#03C988`), Typography, และ Component Tokens
- จัดทำ `TECHSTACK.md`: กำหนดสถาปัตยกรรม PHP 8.x + MySQL + HTML5/CSS3/JS, มาตรฐานความปลอดภัย PDO Prepared Statements, และกฎข้อบังคับโปรเจกต์
- จัดทำ `markdowns/DEBUG.md`: คู่มือการตรวจสอบข้อผิดพลาดบน XAMPP
- จัดทำ `markdowns/REFACTORCODE.md`: มาตรฐานการเขียนโค้ดและการรีแฟคเตอร์

#### 2. โครงสร้างฐานข้อมูล (Database Schema & Seed Data)
- สร้างไฟล์ `database_schema.sql` ประกอบด้วย 9 ตารางหลัก:
  - `users`: จัดเก็บสมาชิกและรหัสผ่าน (`password_hash`)
  - `stocks`: จัดเก็บรายชื่อหุ้นและราคาตลาด Real-time
  - `stock_price_history`: จัดเก็บแท่งเทียนราคา OHLCV ย้อนหลัง
  - `financial_metrics`: จัดเก็บอัตราส่วน P/E, EPS, ROE, P/BV, Dividend Yield, รายได้, กำไรสุทธิ, D/E
  - `financial_statements`: จัดเก็บงบดุล, งบกำไรขาดทุน, งบกระแสเงินสด
  - `ai_analyses`: จัดเก็บบทวิเคราะห์และคะแนน AI Score (0-100)
  - `portfolios` & `portfolio_items`: จัดเก็บพอร์ตจำลองและหุ้นที่ถือครอง
  - `transactions`: บันทึกประวัติการซื้อ-ขายจำลอง (BUY/SELL)
- ใส่ Seed Data ตัวอย่างสำหรับหุ้นชั้นนำ: DELTA, PTT, CPALL, BDMS, AOT, SCB, NVDA, AAPL

#### 3. ฝั่ง Backend Logic & Security Core (PHP 8.x)
- `config/constants.php`: ค่าคงที่ระบบและ Base URLs
- `config/db.php`: เชื่อมต่อ PDO MySQL แบบ Singleton ปิด Emulated Prepares ป้องกัน SQL Injection
- `functions/helpers.php`: ฟังก์ชัน Escape XSS `e()`, จัดรูปแบบตัวเลขทางการเงิน `formatMoney()`, `formatPercent()`, `formatCompactNumber()`
- `functions/auth.php`: ระบบยืนยันตัวตน, Session Hardening, และระบบสร้างพอร์ตจำลองเงินทุน 1,000,000 บาท อัตโนมัติเมื่อสมัครสมาชิก
- `functions/stocks.php`: ดึงข้อมูลหุ้น, คัดกรองหุ้น (Screener Logic), และประวัติราคาแท่งเทียน
- `functions/portfolio.php`: ระบบพอร์ตจำลอง คำนวณต้นทุนเฉลี่ย, Unrealized P&L, Realized P&L, และส่งคำสั่งซื้อ-ขายจำลอง

#### 4. ส่วนติดต่อผู้ใช้งาน (Frontend Presentation Layer)
- `css/variables.css`, `css/layout.css`, `css/components.css`: ระบบสไตล์ชีท 60-30-5-5 Dark Mode สมบูรณ์แบบ
- `js/chart_engine.js`: ตัวเรนเดอร์กราฟ Interactive ด้วย Chart.js พร้อม Timeframe Switcher (1M, 3M, 6M, 1Y)
- `js/main.js`: ตัวจัดการ UI, ค้นหาหุ้น, สลับดูรหัสผ่าน, ตัววัดความแข็งแรงของรหัสผ่าน และ Trade Modal
- `index.php`: หน้า Dashboard หลักแสดงภาพรวมหุ้น, กราฟ, งบการเงิน, AI Score Radial Gauge, และส่งคำสั่งซื้อ-ขาย
- `pages/login.php`: หน้าเข้าสู่ระบบ สไตล์ Glassmorphism
- `pages/register.php`: หน้าสมัครสมาชิกพร้อมรายการสิทธิประโยชน์
- `pages/portfolio.php`: หน้าพอร์ตจำลองและประวัติธุรกรรม
- `pages/screener.php`: หน้าคัดกรองหุ้นตามเงื่อนไขปัจจัยพื้นฐาน
- `pages/logout.php`: ตัวจัดการออกจากระบบ
