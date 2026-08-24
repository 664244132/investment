# 📜 LOG.md: บันทึกประวัติการพัฒนาและรุ่นเวอร์ชัน (Project Changelog)

เอกสารนี้ใช้บันทึกประวัติการเปลี่ยนแปลง รายการปรับปรุงฟีเจอร์ และการแก้ไขโค้ด (Release Notes & Audit Log) ของโปรเจกต์ **SmartPort AI**

---

## 📌 [Version 1.18.0] - 2026-08-24
### 💎 Full Project Refactoring, Rule 6 CSS Custom Properties & Architecture Docs Overhaul

- **RefactorCode ตามกฎ Rule 6 (Zero Inline CSS 100%):**
  - แปลงการกำหนดความกว้าง Dynamic Progress Bars ใน [`index.php`](file:///c:/xampp/htdocs/investment/index.php) (ช่วงราคา 52W, แถบคะแนน AI Composite 4 ด้าน, แถบคะแนน Piotroski F) และ [`pages/portfolio.php`](file:///c:/xampp/htdocs/investment/pages/portfolio.php) (Asset Allocation Bar) ให้ส่งผ่าน **CSS Custom Properties (`--progress-pct`, `--score-pct`, `--alloc-pct`)**
  - เพิ่มสไตล์ `.asset-alloc-seg`, `.asset-alloc-gold`, `.asset-alloc-gain` และอัปเดต `.range-52w-fill`, `.score-progress-bar` ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css)
- **อัปเดตสถาปัตยกรรมระบบใน [`markdowns/aboutProject.md`](file:///c:/xampp/htdocs/investment/markdowns/aboutProject.md):**
  - ปรับปรุงโครงสร้างโปรเจกต์ทั้ง 10 โมดูล, แผนผัง System Architecture, โครงสร้างโฟลเดอร์ 14 โฟลเดอร์, และตารางฐานข้อมูล 11 ตารางให้ตรงกับสภาพปัจจุบัน 100%
- **ยกระดับ Playbook สำหรับ AI Agents ใน [`markdowns/DEBUG.md`](file:///c:/xampp/htdocs/investment/markdowns/DEBUG.md):**
  - เพิ่มแนวทางการรัน Automated CLI Test Suite (32/32 Passed), การตรวจไวยากรณ์ PHP, การทดสอบ API Endpoints, ตารางตำแหน่ง Log Files ใน XAMPP, และ Troubleshooting Matrix 6 ปัญหาหลัก
- **เคลียร์และจัดระเบียบ [`markdowns/LOG.md`](file:///c:/xampp/htdocs/investment/markdowns/LOG.md):**
  - รวมประวัติรุ่นเก่าให้กระชับ ชัดเจน และอ่านง่าย

---

## 📌 [Version 1.17.7] - 2026-08-24
### 🛡️ Ignore Files Optimization (.antigravityignore & .gitignore)

- **อัปเดตไฟล์กำหนดขอบเขตการทำงานของ AI (`.antigravityignore`):**
  - จัดหมวดหมู่ 5 กลุ่มอย่างเป็นระเบียบ: Version Control, Dependencies, Temp/Logs/Scratch, Binary Dumps และ OS/IDE Files
  - เพิ่มการละเว้นโฟลเดอร์ `scratch/` และไฟล์ระบบ `.history/`, `ehthumbs.db`, `$RECYCLE.BIN/`
- **อัปเดตไฟล์ความปลอดภัยสำหรับ Git Repository (`.gitignore`):**
  - แก้ไขตัวสะกด `.antigravityignore` ให้ถูกต้อง
  - ละเว้นไฟล์ Sensitive Configurations, Error Logs, Scratch Files, Dependencies และ Local Database Dumps ป้องกันข้อมูลรั่วไหลขึ้น GitHub

---

## 📌 [Version 1.17.6] - 2026-08-24
### 📈 Performance KPI Text-Base Word Wrap Prevention

- **ปรับปรุง Layout การ์ดกำไรเฉลี่ย/ขาดทุนเฉลี่ย (`.perf-val.text-base` / `.perf-sub`):**
  - เพิ่มคลาส `.nowrap` ให้กับตัวเลขกำไรเฉลี่ย (`+฿...`) และขาดทุนเฉลี่ย (`-฿...`) ใน [`pages/journal.php`](file:///c:/xampp/htdocs/investment/pages/journal.php) เพื่อป้องกันตัวเลขและสัญลักษณ์สกุลเงินฉีกขาดจากกัน
  - ปรับ Responsive Grid ของ `.performance-kpi-grid` ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css) ให้ขยายคอลัมน์อย่างเหมาะสม (1 คอลัมน์บนมือถือเล็ก, 2 คอลัมน์บนแท็บเล็ต, 4 คอลัมน์บนเดสก์ท็อป)
  - กำหนด `word-break: keep-all;` สำหรับ `.perf-label` และ `.perf-sub` ป้องกันข้อความภาษาไทยฉีกคำอย่างสมบูรณ์แบบ

---

## 📌 [Version 1.17.5] - 2026-08-24
### 📊 Strategy Breakdown Card & Data Table Full-width Layout Enhancement

- **ปรับปรุง Layout ตารางสรุปผลกลยุทธ์การลงทุน (`.strategy-breakdown-card` / `.data-table`):**
  - กำหนดสไตล์ `.strategy-breakdown-card` และ `.data-table` ใน [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css) ให้ขยายกว้างเต็มกรอบ 100%
  - เพิ่ม Padding ให้กับเซลล์ตาราง (`th`, `td`) เป็น `0.95rem 1.25rem` และ `1rem 1.25rem` พร้อมจัดตำแหน่งตัวเลขและข้อความตรงกลาง/ชิดขวาอย่างสมดุล ไม่เบียดเสียด
  - เพิ่ม Effect การ Hover แถวตารางอย่างนุ่มนวลเพื่อให้อ่านข้อมูลสถิติได้ง่ายและสบายตา

---

## 📌 [Version 1.17.4] - 2026-08-24
### 📐 Model Card Header & Icon Alignment Fix

- **ปรับปรุง Layout ส่วนหัวการ์ดโมเดลสถิติ (`.model-card-title-group` / `.model-card-title` / `.model-card-subtitle`):**
  - จัดโครงสร้างหัวข้อใน [`index.php`](file:///c:/xampp/htdocs/investment/index.php) ให้แยกชื่อโมเดลหลัก (Altman Z-Score / Piotroski F-Score) อยู่ในบรรทัดเดียวกับไอคอน Shield / Star โดยกำหนด `white-space: nowrap` และ `flex-shrink: 0` สำหรับ SVG
  - ย้ายคำอธิบายรอง (ความเสี่ยงการล้มละลาย / คุณภาพงบการเงิน 9 ด้าน) ลงมาเป็น `.model-card-subtitle` ด้านล่างอย่างเป็นระเบียบ
  - ป้องกันปัญหาข้อความและไอคอนตกบรรทัดหรือซ้อนทับกันอย่างสมบูรณ์แบบบนทุกขนาดหน้าจอ

---

## 📌 [Version 1.17.3] - 2026-08-24
### 🧹 Clean Watchlist Bar Chips (Icons Removal)

- **นำ Icons ออกจากแถบ Quick Preset Chips (`.watchlist-bar` และ `.watchlist-bar.section-mb-md`):**
  - ลบไอคอนออกจากชิปเปรียบเทียบใน [`pages/compare.php`](file:///c:/xampp/htdocs/investment/pages/compare.php)
  - ลบไอคอนออกจากชิปแผนออมหุ้นยอดนิยมใน [`pages/dca.php`](file:///c:/xampp/htdocs/investment/pages/dca.php)
  - ลบไอคอนออกจากชิปกรองตามกลยุทธ์ใน [`pages/journal.php`](file:///c:/xampp/htdocs/investment/pages/journal.php)
  - ลบไอคอนออกจากชิปกลยุทธ์ยอดนิยมใน [`pages/screener.php`](file:///c:/xampp/htdocs/investment/pages/screener.php)
  - ทำให้ UI ดูโปร่ง สะอาด เรียบง่าย และสบายตาตามความต้องการ

---

## 📌 [Version 1.17.2] - 2026-08-24
### 🎯 Watchlist Score Color Helpers Fix & Multi-item Bookmark Rendering

- **แก้ไข Fatal Error `getScoreColorClass()` ใน Watchlist Rendering:**
  - เพิ่มฟังก์ชัน `getScoreColorClass(int|float $score): string` และ `getScoreBadgeClass(int|float $score): string` ใน [`functions/helpers.php`](file:///c:/xampp/htdocs/investment/functions/helpers.php)
  - แก้ไขปัญหา Fatal error ที่ทำให้ PHP หยุดการเรนเดอร์กลางคัน ส่งผลให้การ์ดหุ้นใน Watchlist ทุกตัวที่ผู้ใช้กด Bookmark (เช่น NVDA, AAPL และตัวอื่นๆ) แสดงผลครบถ้วนใน `.watchlist-grid` 100%
- **ขยายชุดทดสอบอัตโนมัติ (`tests/run_tests.php`):**
  - เพิ่มการทดสอบ `getScoreColorClass` และ `getScoreBadgeClass` ผ่านการทดสอบ 32/32 รายการ (100.0% Pass)

---

## 📌 [Version 1.17.0 - 1.17.1] - 2026-08-24
### 🛡️ Schema Sync, CSRF Security, Stats Bar Layout & Bookmark Query Enhancement

- **ซิงค์โครงสร้างฐานข้อมูล (`database_schema.sql` - Rule 10 Compliance):** เพิ่มตาราง `user_watchlists` และ `trading_journals` ครบถ้วน
- **ยกระดับความปลอดภัย CSRF:** เพิ่ม `getCsrfInputField()` และการตรวจสอบ `verifyCsrfToken()` ในฟอร์ม POST ทั้งหมด
- **ปรับปรุง Layout แถบสถิติสรุป KPI (`.stats-bar-4` / `.stat-card`):** จัดคอลัมน์ 4 ช่อง พร้อมป้องกันการฉีกคำ
- **เพิ่มประสิทธิภาพการดึงข้อมูล Bookmark (`getUserWatchlist`):** ใช้ Subquery เชื่อมต่อเมตริกการเงินและ AI Score

---

## 📌 [Version 1.15.0 - 1.16.2] - 2026-08-24
### 🎨 Local Webfonts Engine, UI Icon Audit & Project-Wide Hardening

- **Local Webfonts 100% (Rule 2):** ติดตั้งชุดฟอนต์ WOFF2 **Prompt** และ **JetBrains Mono** รวม 34 ไฟล์ใน [`assets/fonts/`](file:///c:/xampp/htdocs/investment/assets/fonts)
- **ตรวจสอบไอคอนซ้อนกัน (Project-wide Dual-icon Audit):** แก้ไขสถานะไอคอนเมนูมือถือ (`.mobile-toggle`) และปุ่ม Bookmark (`.btn-bookmark`) ป้องกันการแสดงผลไอคอนซ้อนกัน
- **Zero Inline CSS Overhaul (Rule 6):** สกัดสไตล์ออกจากทุกหน้าเว็บและเทมเพลตไปยัง [`css/components.css`](file:///c:/xampp/htdocs/investment/css/components.css) และ [`css/layout.css`](file:///c:/xampp/htdocs/investment/css/layout.css)

---

## 📌 [Version 1.0.0 - 1.14.5] - 2026-08-23 ถึง 2026-08-24
### 🏛️ Initial Architecture & Core Features Milestone

- **Live Market Data & Multi-Timeframe Chart:** ดึงราคาสดและกราฟ 6 ช่วงเวลา (1H, 1D, 1W, 1M, 1Y, ALL) พร้อมแคช 60 วินาทีใน MySQL
- **10 Core Investment Modules:** Dashboard หุ้นรายตัว, งบการเงิน 3 ด้าน, Statistical Models (Altman Z & Piotroski F), Screener, Compare, DCA Simulator, Watchlist, Journal, Simulated Portfolio ฿1,000,000 และ Statement Export A4
- **Automated Health Check:** ระบบตรวจสุขภาพอัตโนมัติ 23 รายการใน `pages/health_check.php`
- **Security & Standards:** Strict Types 100%, PDO Prepared Statements, De Morgan's Laws, BCRYPT Hashing, และ Session Hardening
