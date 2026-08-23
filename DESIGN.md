# 🎨 DESIGN.md: ระบบดีไซน์และแนวทางการออกแบบ (Design System & Guidelines)

เอกสารนี้กำหนดมาตรฐานการออกแบบส่วนต่อประสานผู้ใช้ (UI/UX Design System) สำหรับโปรเจกต์ **ระบบวิเคราะห์และคัดเลือกหุ้นด้วย AI พร้อมพอร์ตจำลอง (AI Stock Analysis & Simulated Portfolio)** เพื่อให้เว็บไซต์มีภาพลักษณ์ที่เป็นมืออาชีพ สวยงาม สบายตา สอดคล้องกับมาตรฐานของแพลตฟอร์มการเงินและการลงทุนระดับสากล (Financial Investment Platform Aesthetic)

---

## 📌 1. กฎการใช้สัดส่วนชุดสี 60-30-5-5 (Color Hierarchy & 60-30-10 Rule)

เพื่อสร้างมิติทางสายตา ความน่าเชื่อถือ และความชัดเจนของข้อมูลตัวเลขทางการเงิน จึงแบ่งสัดส่วนการใช้สีในหน้าเว็บออกเป็น 4 ระดับอย่างเคร่งครัด:

```
+-----------------------------------------------------------------------------+
|  Dominant Primary (60%)           | Secondary / Accent (30%) | Neg (5%)| Pos|
|  #1D1616 (Deep Charcoal Dark)      | #FFCC00 (Gold / Amber)   | #FF0000 | #03|
+-----------------------------------------------------------------------------+
```

| บทบาทการใช้งาน | รหัสสี (HEX) | สัดส่วน | วัตถุประสงค์และตำแหน่งที่ใช้ (Usage Context) |
| :--- | :--- | :---: | :--- |
| **Primary Base** | `#1D1616` | **60%** | **สีพื้นหลังหลัก (Main Canvas/Layout):** พื้นหลังหน้าเว็บ, Navigation Bar, Card Containers, Table Background, Modal Backdrop มอบความรู้สึกลึก ล้ำสมัย และลดความล้าของสายตาเมื่อดูกราฟและตัวเลขเป็นเวลานาน |
| **Secondary & Brand** | `#FFCC00` | **30%** | **สีเน้นหลักและการนำทาง (Brand & Interactive Accents):** โลโก้, ปุ่ม Action สำคัญ (Primary Buttons), แถบเมนูที่เลือก (Active Tab), กรอบตัวเลขสำคัญ, ค่าคะแนน AI Score, ไอคอนหลัก |
| **Negative / Bearish** | `#FF0000` | **5%** | **สถานะทางลบและการเตือน (Financial Loss & Risk):** ราคาหุ้นติดลบ, สัญญาณ Sell, ค่า P&L ขาดทุน, ข้อมูลความเสี่ยง/จุดเตือน (Risk Alerts), ปุ่มยืนยันการขาย |
| **Positive / Bullish** | `#03C988` | **5%** | **สถานะทางบวกและกำไร (Financial Gain & Profit):** ราคาหุ้นบวก, สัญญาณ Buy, ค่า P&L กำไร, คะแนน AI ด้านสุขภาพการเงินที่ดีเยี่ยม, ปุ่มยืนยันการซื้อ |

---

## 🔘 2. กฎการตอบสนองของปุ่มแบบเรียบนิ่ง (Subtle Flat State — No Floating, No Bloom Glow)

> [!IMPORTANT]
> **STRICT RULE: ห้ามใช้อนิเมชันปุ่มลอยตัวและห้ามใช้แสงฟุ้ง (Bloom/Glow) เด็ดขาด**
> 1. **ห้ามใช้ Floating Animation:** ห้ามใส่ `transform: translateY(-1px)` หรือ `translateY(-2px)` เมื่อนำเมาส์ไปชี้หรือกดปุ่ม
> 2. **ห้ามใช้ Bloom / Glow Box-Shadow:** ห้ามใช้ `box-shadow` แสงฟุ้งรอบปุ่มเมื่อ Hover
> 3. **พฤติกรรมที่ถูกต้อง:** เมื่อนำเมาส์ไปชี้ (Hover) หรือกด (Active) ปุ่มจะยังคงตรึงตำแหน่งเดิมอย่างมั่นคง และเกิดเพียง **"การเปลี่ยนสีให้อ่อนลงอย่างนุ่มนวล (Color Lightening / Soft Tint)"** เช่น:
>    - ปุ่มสีทอง `#FFCC00` เมื่อชี้จะเปลี่ยนเป็น `#FFE066` (สีทองอ่อนลง)
>    - ปุ่มสีเขียว `#03C988` เมื่อชี้จะเปลี่ยนเป็น `#26DCA0` (สีเขียวอ่อนลง)
>    - ปุ่มสีแดง `#FF0000` เมื่อชี้จะเปลี่ยนเป็น `#FF4D4D` (สีแดงอ่อนลง)
>    - ปุ่ม Outline เมื่อชี้จะเปลี่ยนพื้นหลังเป็นสีทองโปร่งแสงบางเบา `rgba(255, 204, 0, 0.15)`

---

## 🚫 3. กฎข้อบังคับการใช้ Icons เท่านั้น (Icons Only — Strictly No Emojis)

> [!IMPORTANT]
> **STRICT RULE: ห้ามใช้ Emoji ในส่วนต่อประสานผู้ใช้ (UI) เด็ดขาด**
> 1. ทุกส่วนที่ต้องการสื่อความหมายทางภาพ (Visual Indicators) ต้องใช้ **SVG Vector Icons** (ผ่านโมดูล `functions/icons.php` หรือ Inline SVG) เท่านั้น
> 2. ห้ามใช้ไอคอนแบบ Emoji เช่น 📈, 📊, 🤖, ⭐, 🛒, 🔍, 👤, 👁️, 🔒, ⚡
> 3. SVG Icons สามารถควบคุมขนาด ความหนาของเส้น (Stroke Width) และเปลี่ยนสีตาม CSS Custom Properties ได้อย่างสมบูรณ์แบบ

---

## 🔒 4. กฎข้อบังคับ Local Assets Only (No External CDNs)

> [!IMPORTANT]
> **STRICT RULE: ห้ามพึ่งพา CDN ภายนอกเด็ดขาด**
> 1. ทุกไลบรารี JavaScript (เช่น `js/chart.umd.min.js`), สไตล์ชีท, ฟอนต์ และสคริปต์ ต้องจัดเก็บไว้ในโปรเจกต์แบบ Local
> 2. ห้ามยิง Request ออกไปภายนอก เพื่อความเป็นส่วนตัวและรองรับการรันแบบ Offline 100%

---

## 🎨 5. CSS Design Tokens & ตัวแปรสี (CSS Custom Properties)

```css
:root {
  /* --- 60-30-5-5 Core Palette --- */
  --bg-primary: #1D1616;       /* 60% Dark Base */
  --bg-card: #231B1B;          /* Elevated Surface Card */
  --bg-card-elevated: #2A2020; /* Card Hover / Modal Surface */
  --bg-input: #1B1414;         /* Input & Field Background */
  
  --accent-gold: #FFCC00;      /* 30% Primary Accent & Brand */
  --accent-gold-hover: #FFE066;/* Subtle Lighten State on Hover */
  --accent-gold-subtle: rgba(255, 204, 0, 0.12);
  --accent-gold-border: rgba(255, 204, 0, 0.25);
  
  --color-danger: #FF0000;     /* 5% Negative / Loss */
  --color-danger-hover: #FF4D4D; /* Lighten State on Hover */
  --color-danger-subtle: rgba(255, 0, 0, 0.12);
  --color-danger-border: rgba(255, 0, 0, 0.3);
  
  --color-success: #03C988;    /* 5% Positive / Gain */
  --color-success-hover: #26DCA0; /* Lighten State on Hover */
  --color-success-subtle: rgba(3, 201, 136, 0.12);
  --color-success-border: rgba(3, 201, 136, 0.3);

  /* --- Text & Neutral Colors --- */
  --text-primary: #F0E6D0;     /* Warm White for high contrast */
  --text-secondary: #D4C9B4;   /* Body text */
  --text-muted: #8A7F70;       /* Muted labels & hints */
  --text-dark: #1D1616;        /* Dark text when placed on gold buttons */
  --border-subtle: rgba(255, 204, 0, 0.12);
  --border-focus: #FFCC00;     /* Accessible focus ring */

  /* --- Spacing & Radius Tokens --- */
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 16px;
  --radius-full: 9999px;
  
  /* --- Shadows (Flat & Stable - No Bloom Glow) --- */
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
  --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.45);
  --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.6);
}
```
