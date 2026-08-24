# 🛠️ TECHSTACK.md: สถาปัตยกรรมและเทคโนโลยีที่ใช้ในโปรเจกต์ (Technical Architecture & Stack)

เอกสารนี้ระบุรายละเอียดโครงสร้างเทคโนโลยี สถาปัตยกรรมระบบ โครงสร้างโฟลเดอร์ และมาตรฐานความปลอดภัยสำหรับโปรเจกต์ **ระบบวิเคราะห์และคัดเลือกหุ้นด้วย AI พร้อมพอร์ตจำลอง (AI Stock Analysis & Simulated Portfolio)**

---

## 1. ข้อมูลเทคโนโลยีหลัก (Core Technology Stack)

| เลเยอร์ (Layer) | เทคโนโลยีที่เลือกใช้ | รายละเอียดและเวอร์ชัน | นโยบายการใช้งาน (Policy) |
| :--- | :--- | :--- | :--- |
| **Frontend UI** | **HTML5 Semantic** | HTML5 Standard | โครงสร้างหน้าเว็บเชิงความหมาย รองรับ Accessibility (a11y) |
| **Styling** | **CSS3 / Modern CSS** | CSS Custom Properties | ธีมการเงิน 60-30-5-5, Responsive Grid/Flex, **Local Stylesheets Only** |
| **UI Icons** | **SVG Vector Icons** | Clean Vector Graphics | **Icons Only — ห้ามใช้ Emojis** โหลดผ่าน `functions/icons.php` |
| **Client-Side Logic** | **JavaScript (ES6+)** | Vanilla JS | จัดการ UI, Event Listeners, Trade Modal, **Local JS Only** |
| **Charting Engine** | **Chart.js v4.4.1 (Local)** | Local UMD Bundle | จัดเก็บที่ `js/chart.umd.min.js` (**Zero External CDN**) |
| **Backend Engine** | **PHP 8.x (Native)** | PHP 8.x + Strict Types | Native OOP/Modular, PDO Prepared Statements, Guard Clauses |
| **Database Server** | **MySQL / MariaDB** | InnoDB Engine (UTF-8 mb4) | จัดเก็บข้อมูลหุ้น, งบการเงิน, ผู้ใช้, พอร์ตจำลอง |
| **Local Environment** | **XAMPP (Apache + MySQL)** | PHP 8.x + phpMyAdmin | จัดการฐานข้อมูลผ่าน Web Interface |

---

## 2. กฎระเบียบข้อบังคับถาวร (Mandatory Architectural Rules)

1. **Icons Only Standard (Strictly No Emojis):**
   - ใช้เฉพาะ **SVG Vector Icons** เท่านั้น ห้ามใช้ Emoji ในคอมโพเนนต์หรือ UI
2. **Local Assets Only (Strictly No External CDNs):**
   - ห้ามใช้ CDN ภายนอก (Zero External CDNs) ทุกไฟล์ Assets ต้องถูกบันทึกและโหลดจากภายในโปรเจกต์ (`css/`, `js/`, `assets/`) 100%
3. **Database Import Policy (Rule 10):**
   - ห้ามรันคำสั่ง DDL/DML สร้างตารางเข้าสู่ฐานข้อมูลอัตโนมัติ ต้องจัดเตรียมไฟล์ SQL ให้ผู้ใช้รันผ่าน phpMyAdmin เอง
4. **Git Policy (Rule 11):**
   - ห้ามสั่ง `git push` หรือ `git commit` อัตโนมัติ ผู้ใช้จะเป็นผู้ดำเนินการเอง

---

## 3. โครงสร้างโฟลเดอร์ของโปรเจกต์ (Project Directory Structure)

```
investment/
├── assets/                  # ไฟล์ภาพ, ไอคอน, ฟอนต์ Local
├── config/                  # การตั้งค่าระบบและการเชื่อมต่อฐานข้อมูล
│   ├── db.php               # PDO Database Connection (Singleton)
│   └── constants.php        # ค่าคงที่ระบบและ Config
├── css/                     # สไตล์ชีท Local 100%
│   ├── variables.css        # CSS Custom Properties (Theme Colors & Design Tokens)
│   ├── layout.css           # โครงสร้าง Grid/Flex, Navigation, Sidebar
│   └── components.css       # สไตล์ปุ่ม, การ์ดหุ้น, ตารางข้อมูล, Modal
├── js/                      # สคริปต์การทำงานฝั่งไคลเอนต์ Local 100%
│   ├── chart.umd.min.js     # ไลบรารี Chart.js Bundle Local (Zero CDN)
│   ├── chart_engine.js      # ตัวจัดการและเรนเดอร์กราฟหุ้น (Interactive Chart)
│   └── main.js              # สคริปต์การทำงานทั่วไปและการโต้ตอบ UI
├── functions/               # โมดูลฟังก์ชันและ Business Logic ฝั่ง Backend (PHP)
│   ├── icons.php            # โมดูล SVG Icons Vector สำหรับ UI (Icons Only)
│   ├── helpers.php          # ฟังก์ชันช่วยเหลือทั่วไป, Format ตัวเลข, Output Escaping
│   ├── auth.php             # ระบบสมาชิก, ตรวจสอบสิทธิ์, Session Management
│   ├── stocks.php           # ฟังก์ชันดึงข้อมูลหุ้น, คำนวณ P/E, EPS, ROE, รายได้
│   └── portfolio.php        # ระบบจัดการพอร์ตจำลอง, คำนวณ P&L สด
├── pages/                   # หน้าเว็บและมุมมองแสดงผล (Views)
│   ├── login.php            # หน้าเข้าสู่ระบบ
│   ├── register.php         # หน้าสมัครสมาชิก
│   ├── portfolio.php        # หน้าพอร์ตการลงทุนจำลองและบันทึกการซื้อ-ขาย
│   ├── screener.php         # หน้าคัดกรองหุ้นตามเงื่อนไขปัจจัยพื้นฐาน
│   └── logout.php           # ตัวจัดการออกจากระบบ
├── markdowns/               # คู่มือการพัฒนาและข้อกำหนดโปรเจกต์
├── DESIGN.md                # ระบบดีไซน์ สัดส่วนสี 60-30-5-5 & Icons Only
├── TECHSTACK.md             # สถาปัตยกรรมและเทคโนโลยี (ไฟล์นี้)
├── database_schema.sql      # สคริปต์โครงสร้างฐานข้อมูลและ Seed Data
└── index.php                # Entry Point หลักของเว็บแอปพลิเคชัน
```
