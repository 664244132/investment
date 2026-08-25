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
  --radius-xs: 4px;
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 14px;
  --radius-xl: 18px;
  
  /* --- Shadows (Flat & Stable - No Bloom Glow) --- */
  --shadow-sm: 0 2px 6px rgba(0, 0, 0, 0.35);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.45);
  --shadow-lg: 0 12px 36px rgba(0, 0, 0, 0.65);
}
```

---

## 📋 6. ระบบแบบฟอร์มและการควบคุมค่า (Custom Form Controls & Steppers)

### 6.1 Custom Bespoke Dropdown System
- ปิดการทำงานของ Native Browser Select UI ด้วย `appearance: none;`
- ใช้ Trigger Button ที่มีกรอบโค้งมนนุ่มนวล (`border-radius: 10px`) พื้นหลังไล่เฉด Dark Glass (`#2A2020` -> `#1E1616`)
- ไอคอนลูกศร SVG Chevron สีทอง `#FFCC00` ที่หมุน 180° เมื่อเปิดเมนู
- เมนูป๊อปอัปแบบ Glassmorphism `backdrop-filter: blur(24px);` พร้อมไอคอน Checkmark แสดงตัวเลือกที่เลือกอยู่

### 6.2 Custom Dark-Gold Number Steppers
- ซ่อนปุ่ม Spin Button สี่เหลี่ยมสีขาวเดิมของเบราว์เซอร์ (`input[type="number"]::-webkit-inner-spin-button`)
- ติดตั้งปุ่มเพิ่ม-ลดจำนวน (▲ / ▼) สไตล์ Dark Gold คัสตอมที่ขอบขวาของช่องกรอกตัวเลข
- เชื่อมต่อการเพิ่ม-ลดค่าตาม `step`, `min`, `max` พร้อมยิง Event `input` / `change` สำหรับคำนวณแบบ Real-time ทันที

---

## ⚡ 7. ระบบปุ่มซื้อ-ขายระดับสถาบัน และ Glassmorphic Badges (Trading Buttons & Badges)

### 7.1 ปุ่มคำสั่งซื้อ `.btn-buy` (Buy / Long Action)
- พื้นหลัง: `linear-gradient(180deg, #03D38E 0%, #029E6B 100%)`
- เส้นขอบและ Inner Highlight: `border: 1px solid #03E298; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35), 0 2px 8px rgba(3, 201, 136, 0.25);`
- ตัวอักษรและไอคอน: สีเข้มจัด `#062316` ตัวหนา 700
- Hover State: สว่างขึ้นเป็นสีมินต์ `#25E5A5` (ไม่ลอยตัว)

### 7.2 ปุ่มคำสั่งขาย `.btn-sell` (Sell / Short Action)
- พื้นหลัง: `linear-gradient(180deg, #FF3333 0%, #D60000 100%)`
- เส้นขอบและ Inner Highlight: `border: 1px solid #FF4D4D; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3), 0 2px 8px rgba(255, 0, 0, 0.25);`
- ตัวอักษรและไอคอน: สีขาวบริสุทธิ์ `#FFFFFF` ตัวหนา 700 พร้อม Text Shadow
- Hover State: สว่างขึ้นเป็นสีแดงสด `#FF5C5C` (ไม่ลอยตัว)

### 7.3 Glassmorphic Financial Badges (`.badge`)
- โครงสร้าง: `display: inline-flex; align-items: center; gap: 0.38rem; padding: 0.32rem 0.75rem; border-radius: var(--radius-sm);`
- เอฟเฟกต์กระจก: `backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);`
- เงาขนาดเล็ก: `box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);`
- เฉดสี:
  - 🟢 **`.badge-gain`:** พื้นหลังเขียวมรกตโปร่งแสง ขอบสีเขียวสด `#1AE29E`
  - 🔴 **`.badge-loss`:** พื้นหลังแดงทับทิมโปร่งแสง ขอบสีแดง `#FF6666`
  - 🟡 **`.badge-gold`:** พื้นหลังทองคำโปร่งแสง ขอบสีทอง `#FFD633`
  - ⚪ **`.badge-neutral`:** พื้นหลังเงินสว่างโปร่งแสง ขอบ `#D4C9B4`

---

## 🔲 8. กฎการใช้เส้นขอบและพื้นหลังสะอาดตา (Uniform 1px Borders — No Accent Stripe Lines)

> [!IMPORTANT]
> **STRICT RULE: ห้ามใช้เส้นแถบสีหัวพื้นหลังหรือขอบด้านข้างหนาเด็ดขาด**
> 1. ห้ามใส่เส้นแถบสีหนาที่ขอบด้านข้างหรือด้านบนของ Card, Row, Container, Box หรือ Toast Alert (เช่น `border-left: 3px/4px solid`, `border-top: 3px/4px solid`)
> 2. ทุกองค์ประกอบต้องใช้ **เส้นขอบบาง 1px สม่ำเสมอรอบด้าน (Uniform 1px Border)** เช่น `border: 1px solid var(--border-subtle)` หรือเส้นขอบโปร่งแสง 1px เพื่อรักษาความสะอาดตา ความเรียบหรูระดับ Luxury Dark Glass และความคงเส้นคงวาของ UI ทั้งระบบ


