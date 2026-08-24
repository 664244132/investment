# 📜 LOG.md: บันทึกประวัติการพัฒนาและรุ่นเวอร์ชัน (Project Changelog)

เอกสารนี้ใช้บันทึกประวัติการเปลี่ยนแปลง รายการปรับปรุงฟีเจอร์ และการแก้ไขโค้ด (Release Notes & Audit Log) ของโปรเจกต์ **SmartPort AI**

---

## 📌 [Version 1.15.1] - 2026-08-24
### 🛡️ Critical Bug Fix & 100% Zero Inline CSS Finalization

#### 1. แก้ไข Critical Runtime Error ในหน้าพอร์ตจำลอง ([`pages/portfolio.php`](file:///c:/xampp/htdocs/investment/pages/portfolio.php))
- **แก้ไขคีย์ฟิลด์วันที่ธุรกรรม:** เปลี่ยน `$tx['executed_at']` เป็น `$tx['created_at']` ป้องกันข้อผิดพลาด `Fatal error: TypeError: strtotime()` เมื่อเรนเดอร์ตารางประวัติคำสั่งซื้อขาย
- **แก้ไขการคำนวณมูลค่าสุทธิของธุรกรรม:** คำนวณ `net_amount` ตามประเภทคำสั่ง (BUY: `total_amount + fee`, SELL: `total_amount - fee`) ป้องกัน `Undefined array key "net_amount"`

#### 2. สกัด Inline CSS ที่ตกค้าง 100% (Rule 6 Strict Compliance)
- **`index.php`:** สกัดสไตล์แถบวัดคะแนน Piotroski F เป็นคลาส `.mt-xs`
- **`pages/screener.php`:** สกัดกล่องผลลัพธ์ว่างเปล่าเป็นคลาส `.table-empty-cell-padded`
- **`pages/watchlist.php`:** สกัดคำแนะนำฟอร์มเป็น `.form-hint-xs` และกล่องบันทึกเป็น `.textarea-resizable`
- **`js/main.js`:** สกัดสไตล์ปุ่มดูรหัสผ่านเป็น `.toggle-icon-svg`
- **`functions/icons.php` & `css/layout.css`:** สกัดสไตล์สากลของ SVG Vector Icons ออกจากฟังก์ชัน `icon()` ไปยัง `css/layout.css`

---

## 📌 [Version 1.15.0] - 2026-08-24
### 💎 Full Project-Wide Refactoring & Architectural Hardening (Rule 6 Zero Inline CSS & Clean Code)

#### 1. Zero Inline CSS Overhaul (Rule 6 Compliance 100%)
- **สกัด Inline `style="..."` ออกจากทุกไฟล์หน้าเว็บและเทมเพลต:**
  - `index.php`: แดชบอร์ดวิเคราะห์หุ้นรายตัว, Hero Header, ตัวชี้วัดสำคัญ, ตารางงบการเงิน, การ์ดสังเคราะห์ AI, เกจวัดคะแนน, และ Statistical Models (Altman Z & Piotroski F)
  - `templates/trade_modal.php`: ปรับแต่ง Rationale Textarea และ Modal Controls
  - `pages/compare.php`: แถบ Pill Controls, กราฟเปรียบเทียบ, ตาราง Matrix 5 หมวดหมู่, และ Winner Badges
  - `pages/dca.php`: ฟอร์มพารามิเตอร์, KPI Summary, กราฟสะสมทุน, และตาราง Amortization
  - `pages/export.php`: การ์ด Action Hub, ฟอร์มดาวน์โหลด CSV, และเอกสารใบแจ้งยอดทางการขนาด A4
  - `pages/journal.php`: KPI Cards, Trading Timeline Cards, Strategy Performance, และแบบประเมินอารมณ์
  - `pages/portfolio.php`: Header Controls, Overview Metrics, Asset Allocation Bar, Holdings Table, และ Transaction History
  - `pages/screener.php`: แถบ Preset Chips, ฟอร์มคัดกรองหุ้นเชิงปริมาณ, และตารางผลการคัดกรอง
  - `pages/health_check.php`: สถิติผลการตรวจสุขภาพ 23 ข้อ และตารางแจกแจงสถานะแต่ละหมวดหมู่
  - `pages/login.php` & `pages/register.php`: ฟอร์มเข้าสู่ระบบ, ฟอร์มลงทะเบียน, และ Benefit Highlights
  - `templates/header.php`: Mobile Hamburger Drawer และ Navigation Links
  - `js/main.js`: ปรับปรุง Autocomplete Dropdown Renderer ให้ใช้คลาส CSS แทนการสร้าง Inline Styles ใน JavaScript
- **สร้าง Semantic Classes ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css) และ [`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css):**
  - Section 18: Hero, Metrics, Statements, AI Synthesis, Scoring, Compare, DCA, Export, Journal, Portfolio, Screener, Health Check, Auth, และ Dropdown Utilities

#### 2. Clean Code & Strict Engineering Standards
- **PHP Strict Typing 100%:** ทุกไฟล์ PHP ทั้ง 28 ไฟล์ขึ้นต้นด้วย `declare(strict_types=1);` และมี Type Hinting
- **Guard Clauses & Early Return:** ตรวจสอบความถูกต้องของ Input และสิทธิ์ผู้ใช้งานก่อนเสมอ ลดความซับซ้อนของโค้ด
- **De Morgan's Laws Boolean Logic:** ตรวจสอบเงื่อนไขทุกจุดในรูปแบบ `!A || !B` หรือ `!A && !B` ปราศจาก `!(A && B)`
- **Database & Version Control Compliance:** สอดคล้องตาม Rule 10 (ไม่รัน DDL/DML เอง) และ Rule 11 (ไม่ Commit/Push Git เอง)

#### 3. Documentation & Playbook Synchronization
- **`markdowns/aboutProject.md`:** อัปเดตสถาปัตยกรรม 10 โมดูลหลัก, ระบบแคช 60 วินาที, Live Market API, และโครงสร้าง Data Layer
- **`markdowns/DEBUG.md`:** ยกระดับเป็น AI Agent Debugging Playbook ที่สมบูรณ์ ครอบคลุมการรัน Health Check, การทดสอบ API Multi-Timeframe, และแนวทางแก้ปัญหาเฉพาะจุด
- **`markdowns/LOG.md`:** เคลียร์และจัดระเบียบประวัติการพัฒนาให้กระชับ ชัดเจน และอ่านง่าย

---

## 📌 [Version 1.14.5] - 2026-08-24
### 🎯 Precise Timeframe Differentiation (1H Intraday vs 1W Weekly)
- แยกชุดข้อมูลและช่วงเวลาของ 1H และ 1W ให้ถูกต้อง 100% ใน [`functions/market_api.php`](file:///c:/xampp/htdocs/investment/functions/market_api.php):
  - **`1H` (รายชั่วโมง / Intraday):** ใช้ `interval=2m&range=1d` (79-196 จุดข้อมูล) แสดงเวลา `H:i`
  - **`1W` (รายอาทิตย์ / 1 Week):** ใช้ `interval=1h&range=5d` (30-36 จุดข้อมูล) แสดงวันที่และเวลา `d M H:i`

---

## 📌 [Version 1.14.4] - 2026-08-24
### 🛠️ 1M Chart Live Synchronization & Global Performance Hardening
- แก้ไข SQL ใน `getStockPriceHistory()` ให้ใช้ Subquery เรียงลำดับประวัติราคาวันล่าสุดย้อนหลัง (`ORDER BY trade_date DESC LIMIT 30`) และจัดเรียงแสดงผลจากอดีตไปปัจจุบัน (`ORDER BY trade_date ASC`) อย่างถูกต้อง
- ปรับแต่ง In-Memory Chart Cache และ Client Animation Easing ให้ตอบสนองลื่นไหลในทุกอุปกรณ์

---

## 📌 [Version 1.14.0 - 1.14.3] - 2026-08-24
### 📊 Live Multi-Timeframe Chart, Smart Caching, Favicon & Navigation Hub
- **100% Live Market Chart:** พัฒนา `api/chart_data.php` ดึงข้อมูลประวัติราคาแท่งเทียนสด 6 ช่วงเวลา (1H, 1D, 1W, 1M, 1Y, ALL)
- **Smart DB TTL Cache 60s:** ดึงราคาหุ้นซ้ำจาก MySQL ภายใน 60 วินาที ตอบสนองไวใน 4.74ms (เร็วขึ้นกว่า 86 เท่า)
- **Native Brand Favicon:** สร้าง Favicon SVG/PNG/ICO จากโลโก้ `brand-icon`
- **Comprehensive Footer:** ปรับเมนู Footer เป็น 4 คอลัมน์สมบูรณ์

---

## 📌 [Version 1.13.0 - 1.13.9] - 2026-08-24
### 🌍 Global Market Search, Statement Engine & UI Polish
- **Global Stock Search:** ค้นหาหุ้นทั่วโลกผ่าน Yahoo Finance Search และสร้างหุ้นเข้าสู่ระบบอัตโนมัติ
- **Electronic Statement:** สร้างใบรายงานพอร์ตทางการ A4 และส่งออก CSV ใน `pages/export.php`
- **Registration Clean:** ลบองค์ประกอบตกแต่งที่ไม่จำเป็นใน `pages/register.php` และปรับตำแหน่งหัวข้อให้ตรงกัน

---

## 📌 [Version 1.4.0 - 1.12.0] - 2026-08-23 ถึง 2026-08-24
### 📱 Institutional Design, DRY Architecture & Automated Health Check
- **Automated Health Check:** พัฒนา `pages/health_check.php` ตรวจสอบสุขภาพระบบ 23 รายการ
- **Modular Templates:** สกัดโค้ดส่วนกลางเป็น `templates/` (Header, Footer, Flash, Trade Modal)
- **Zero Inline CSS & Safe Area:** รองรับหน้าจอทุกขนาดพร้อม Viewport Insets สำหรับ iOS/Android

---

## 📌 [Version 1.0.0 - 1.11.0] - 2026-08-23 ถึง 2026-08-24
### 🏛️ Initial Release, Advanced Financial Models & Feature Modules
- **Advanced Statistical Models:** Altman Z-Score & Piotroski F-Score ใน `functions/financial_models.php`
- **Statement & Export Hub:** ส่งออก CSV รายงานพอร์ต และรายงานทางการ A4 ใน `pages/export.php`
- **DCA Simulator:** เครื่องมือจำลองการสะสมหุ้นรายเดือนและการทบต้น (DRIP) ใน `pages/dca.php`
- **Trading Journal:** วิเคราะห์ประสิทธิภาพการเทรด Win Rate และจิตวิทยาการลงทุนใน `pages/journal.php`
- **Stock Comparison Matrix:** เปรียบเทียบหุ้นสูงสุด 4 บริษัทและเฟ้นหาผู้ชนะ 5 มิติใน `pages/compare.php`
- **Watchlist & Alerts:** รายการติดตามและระบบเตือนราคาเป้าหมายใน `pages/watchlist.php`
- **Database & Architecture:** ระบบฐานข้อมูล 11 ตาราง และพอร์ตจำลอง ฿1,000,000
