# 📐 คู่มือแนวทางการพัฒนาและรีแฟคเตอร์โค้ด (Refactoring & Development Guide)

**คุณคือ Senior Software Engineer — บังคับต้องอ่านและปฏิบัติตามกฎในเอกสารนี้อย่างเคร่งครัดเสมอ**

---

## 1. กฎระเบียบสูงสุดในการทำงาน (Strict Operational Rules)

### 🔴 กฎข้อ 1: การใช้ Icons เท่านั้น (Icons Only — Strictly No Emojis)
- **ห้ามใช้ Emojis ในส่วนติดต่อผู้ใช้งาน (UI) หรือคอมโพเนนต์ใดๆ ของโปรเจกต์เด็ดขาด**
- ให้ใช้เฉพาะ **SVG Vector Icons** (ผ่านโมดูล `functions/icons.php` หรือ Inline SVG) เพื่อให้เว็บไซต์มีภาพลักษณ์ที่เป็นมืออาชีพ สวยงาม คมชัด และสอดคล้องกับมาตรฐานของแพลตฟอร์มการเงินระดับสากล

### 🔴 กฎข้อ 2: ห้ามใช้ CDN ภายนอก (Local Assets Only — No External CDNs)
- **ห้ามเรียกใช้งานไลบรารี สคริปต์ สไตล์ชีท หรือฟอนต์จาก CDN ภายนอกเด็ดขาด**
- ทุกไฟล์ Assets (เช่น `chart.umd.min.js`, สไตล์ชีท, JavaScript, และ SVG Icons) ต้องถูกดาวน์โหลดมาจัดเก็บไว้ในโฟลเดอร์ของโปรเจกต์ (`assets/`, `css/`, `js/`) และเรียกใช้งานแบบ Local 100%

### 🔴 กฎข้อ 3: การตอบสนองของปุ่มแบบเรียบนิ่ง (Subtle Flat Button Interactions — No Floating, No Bloom Glow)
- **ห้ามใช้อนิเมชันปุ่มที่กดหรือชี้แล้วลอยตัว (`transform: translateY(...)`) เด็ดขาด**
- **ห้ามใช้เอฟเฟกต์แสงฟุ้ง (Bloom / Glow Box-Shadow) เมื่อชี้หรือกดปุ่มเด็ดขาด**
- **การตอบสนองที่ถูกต้อง:** เมื่อนำเมาส์ไปชี้ (Hover) หรือกด (Active) ปุ่มจะต้องคงตำแหน่งเดิมอย่างมั่นคง ไม่ขยับเขยื้อน และให้เกิดเพียงการ **"เปลี่ยนสีให้อ่อนลงอย่างนุ่มนวล (Color Lightening / Soft Tint)"** เพื่อให้ UI ดูนิ่ง สบายตา และมีความเป็นมืออาชีพทางการเงินสูง

### 🔴 กฎข้อ 10: การจัดการฐานข้อมูล (Database Protocol)
- **ห้ามนำข้อมูลเข้า หรือสร้าง table database เองโดยอัตโนมัติเด็ดขาด**
- ต้องจัดเตรียมไฟล์คำสั่ง SQL (เช่น `database_schema.sql`) และแจ้งผู้ใช้ทราบ พร้อมส่งมอบโค้ดให้ผู้ใช้เป็นผู้นำไปกดรันผ่าน phpMyAdmin บนเครื่องของผู้ใช้เองเท่านั้น

### 🔴 กฎข้อ 11: การใช้งาน Git (Git Policy)
- **ห้ามสั่ง `git push` หรือ `git commit` ขึ้น GitHub เด็ดขาด**
- ผู้ใช้จะเป็นผู้ดำเนินการตรวจสอบและ commit / push โค้ดขึ้น GitHub ด้วยตนเองเท่านั้น

---

## 2. หลักการและมาตรฐานการเขียนโค้ด (Coding Standards Checklist)

### 2.1 กฎ De Morgan's Laws (Boolean Simplification)
- **ห้าม** เขียนเงื่อนไขที่มีการปฏิเสธซ้อนวงเล็บ `!(A && B)` หรือ `!(A || B)`
- ต้องแปลงเป็น `!A || !B` และ `!A && !B` พร้อมกลับเครื่องหมายเปรียบเทียบเสมอ ตามที่ระบุใน [`DeMorgansLaws.md`](./DeMorgansLaws.md)

### 2.2 การใช้ Guard Clauses & Early Return Pattern
- **ห้าม** เขียนโค้ดที่มี Nested If ซ้อนกันลึกเป็นรูปหัวลูกศร (Arrow Anti-Pattern)
- ให้ดักจับเงื่อนไขข้อผิดพลาด (Error / Edge cases) แล้วสั่ง `return` หรือ `exit` ออกจากฟังก์ชันทันที เพื่อให้โครงสร้างโค้ดแบนราบ (Flat Code) และเห็นเส้นทาง Happy Path ชัดเจน

### 2.3 การประกาศประเภทข้อมูลเข้มงวด (Strict Types)
- ไฟล์ PHP ทุกไฟล์ต้องมี `declare(strict_types=1);` ที่บรรทัดแรก
- ต้องระบุ Type Hinting ให้กับ Parameter และ Return Type ของทุกฟังก์ชันอย่างชัดเจน

### 2.4 การป้องกันความปลอดภัยระดับสูง (Security Best Practices)
1. **SQL Injection:** คำสั่ง SQL ทั้งหมดต้องใช้ **PDO Prepared Statements** พร้อม Bind ค่าพารามิเตอร์เสมอ ห้ามต่อสตริงตัวแปรเด็ดขาด
2. **Cross-Site Scripting (XSS):** ข้อมูลทุกตัวที่เรนเดอร์ลงใน HTML ต้องผ่านฟังก์ชัน Escaping `e()` หรือ `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
3. **Password Security:** ใช้ `password_hash($pw, PASSWORD_DEFAULT)` และ `password_verify()`
4. **Session Fixation:** เรียก `session_regenerate_id(true)` ทันทีเมื่อมีการเปลี่ยนสถานะการเข้าสู่ระบบ

---

## 3. Checklist การตรวจสอบก่อนส่งมอบงาน (Pre-Delivery Checklist)

- [x] **No Floating / No Bloom Glow:** ตรวจสอบว่าไม่มี `translateY` หรือแสงฟุ้งในปุ่มและคอมโพเนนต์
- [x] **Zero Emojis:** ตรวจสอบทั่วโปรเจกต์ว่าไม่มี Emoji ปรากฏใน UI (ใช้ SVG Icons 100%)
- [x] **Zero External CDN:** ตรวจสอบว่าไม่มีลิงก์ CDN ภายนอก ทุก Assets เป็น Local 100%
- [x] **PHP Syntax Check:** ตรวจสอบ Syntax ภาษา PHP ทุกไฟล์ด้วย `php -l` ผ่าน 0 Errors
- [x] **Color Palette 60-30-5-5:** สีและคอมโพเนนต์เป็นไปตามทฤษฎีใน [`DESIGN.md`](../DESIGN.md)
- [x] **No Auto Git/DB:** ไม่มีคำสั่ง git commit/push (Rule 11) และไม่มีการรัน DDL เข้า Database อัตโนมัติ (Rule 10)