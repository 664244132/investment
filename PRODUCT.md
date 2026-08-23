# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **นักลงทุนรายบุคคล (Retail Investors & Value Investors):** นักลงทุนที่ต้องการศึกษาและคัดกรองหุ้นคุณค่า (VI) หรือหุ้นเติบโต (Growth) โดยไม่ต้องเสียเวลาแกะงบการเงินหลายร้อยหน้า
- **ผู้เริ่มต้นศึกษาการลงทุน (Beginner Investors):** ผู้ที่ต้องการทดลองซื้อ-ขายหุ้นจริงแบบเรียลไทม์ผ่านพอร์ตจำลอง (Simulated Portfolio) ก่อนลงสนามจริงด้วยเงินจริง
- **นักวิเคราะห์อิสระ (Independent Financial Analysts):** ผู้ที่ต้องการตัวช่วยสรุปภาพรวมงบการเงิน อัตราส่วนสถิติ และการประเมินมูลค่าหุ้นอย่างรวดเร็ว

## Product Purpose

SmartPort AI คือแพลตฟอร์มวิเคราะห์หุ้นและพอร์ตจำลองอัจฉริยะ (Intelligent Portfolio Simulator & Financial Analytics) ที่เปลี่ยนงบการเงินที่ซับซ้อนให้กลายเป็นข้อมูลเชิงลึกที่เข้าใจง่ายและนำไปใช้ตัดสินใจลงทุนได้ทันที พร้อมระบบพอร์ตจำลองที่คำนวณผลตอบแทนและ P&L แบบ Real-time

## Positioning

แพลตฟอร์มวิเคราะห์หุ้นสไตล์ Financial Terminal (Bloomberg / Morningstar-grade) สำหรับตลาดหุ้นไทยและต่างประเทศ ที่ผสาน **การคำนวณอัตราส่วนการเงินเชิงสถิติจริง (Deterministic Fundamentals: P/E, EPS, ROE, D/E)** เข้ากับ **โมเดลประเมินคะแนนคุณภาพหุ้น 4 มิติ (SmartPort Composite Rating)** และ **ระบบพอร์ตจำลองเงินทุน 1,000,000 บาท** ที่ทำงานเร็ว ลื่นไหล บนสถาปัตยกรรม Native 100% (Zero External CDN, Zero Bloat)

## Operating Context

- เว็บแอปพลิเคชันสำหรับใช้งานบน Desktop Terminal (หน้าจอ 1280px+) และ Mobile Responsive (375px+)
- ทำงานบน XAMPP (PHP 8.x + MySQL + Vanilla JS + Modern CSS)
- ใช้คู่กับการเปิดดูข้อมูลตลาดหลักทรัพย์ (SET / NASDAQ) และการจำลองเทรดจริง

## Capabilities and Constraints

### Capabilities
- **Stock Analysis Dashboard:** แสดงราคาตลาด Real-time, กราฟราคาปรับ Timeframe ได้ (1M, 3M, 6M, 1Y), ตัวชี้วัดสำคัญ (P/E, EPS, ROE, Revenue), และงบการเงินสรุป 3 ด้าน
- **SmartPort Composite Rating (0-100):** ให้คะแนนความน่าสนใจรอบด้าน 4 มิติ (Growth, Quality, Solvency, Valuation) พร้อมบทสรุปจุดแข็งและความเสี่ยง
- **Smart Stock Screener:** ระบบคัดกรองหุ้นตามเงื่อนไข P/E, ROE, Dividend Yield, Composite Rating, และ Sector พร้อม Quick Strategy Presets
- **Real-time Virtual Portfolio:** จำลองการซื้อ-ขายหุ้นเสมือนจริง คิดค่าคอมมิชชัน 0.157% คำนวณต้นทุนเฉลี่ย (Average Cost), Asset Allocation Bar, และกำไร/ขาดทุน Realized & Unrealized P&L
- **Authentication & Security:** ระบบสมาชิกพร้อมสร้างพอร์ต 1,000,000 บาทอัตโนมัติ, Session Hardening, PDO Prepared Statements, และ XSS Escaping

### Constraints
- **Zero External CDN:** ทุก Assets ต้องโหลดจาก Local ภายในโปรเจกต์ 100%
- **Icons Only:** ใช้เฉพาะ SVG Vector Icons ห้ามใช้ Emojis ใน UI
- **Flat Subtle State:** ปุ่มต้องนิ่ง ห้ามใช้อนิเมชันลอยตัว (No `translateY`) และห้ามใช้แสงฟุ้ง (No Bloom/Glow)
- **Clean Rounded Rectangles:** ป้ายกำกับ Badges และคอมโพเนนต์ใช้กรอบสี่เหลี่ยมมุมมน (`border-radius: 4px - 6px`) ไม่ใช้ทรงแคปซูล
- **Unified Typography:** ใช้ Font Sans-serif ชุดเดียวกันทั้งระบบ ไม่ใช้ฟอนต์ขัดแย้งกัน
- **Strict Rule 10 & 11:** ผู้ใช้เป็นผู้รัน Database DDL ใน phpMyAdmin และเป็นผู้ commit/push ขึ้น Git เอง

## Brand Commitments

- **ชื่อแบรนด์:** SmartPort AI
- **ความรู้สึก (Aesthetic):** Financial Terminal Dark Theme (60% `#1D1616`, 30% `#FFCC00`, 5% `#FF0000`, 5% `#03C988`)
- **Typography:** Unified Modern Sans-serif (`Prompt`, `Kanit`, `Inter`, `system-ui`) สำหรับ UI และเนื้อหาทั้งหมด, Monospace เฉพาะตัวเลขการเงิน (`JetBrains Mono`, `Consolas`)

## Product Principles

1. **Deterministic Data First:** ตัวเลขทางการเงินทุกตัวต้องคำนวณจริงจากงบการเงิน ไม่กุตัวเลขหลอก
2. **Institutional Craft:** ออกแบบให้ดูน่าเชื่อถือแบบมืออาชีพ สไตล์ Bloomberg Terminal หลีกเลี่ยงภาษาการตลาดโอ้อวดหรือ AI Slop
3. **Frictionless Usability:** หน้าเว็บต้องโหลดเร็ว ตอบสนองฉับไว ตัวหนังสืออ่านง่าย คมชัด และตัวเลขในตารางต้องตรงแนวกันอย่างสมบูรณ์แบบ
