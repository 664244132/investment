-- ==============================================================================
-- 📈 Database Schema: AI Stock Analysis & Simulated Portfolio (investment_db)
-- สคริปต์โครงสร้างฐานข้อมูลและข้อมูลตั้งต้นสำหรับระบบวิเคราะห์หุ้นด้วย AI และพอร์ตจำลอง
-- ==============================================================================

-- 1. สร้างฐานข้อมูล (ถ้ายังไม่มี) และกำหนด Collation รองรับภาษาไทย UTF-8
CREATE DATABASE IF NOT EXISTS `investment_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `investment_db`;

-- ปิดการตรวจสอบ Foreign Key ชั่วคราวระหว่างสร้างตาราง
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 2. ตารางผู้ใช้งาน (users)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'รหัสผ่านที่ผ่านการ hash ด้วย password_hash()',
    `full_name` VARCHAR(100) NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บข้อมูลผู้ใช้งาน';

-- ------------------------------------------------------------------------------
-- 3. ตารางข้อมูลหุ้นและราคาตลาด (stocks)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `stocks`;
CREATE TABLE `stocks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `symbol` VARCHAR(20) NOT NULL UNIQUE COMMENT 'ชื่อย่อหุ้น เช่น PTT, CPALL, DELTA, NVDA',
    `name_th` VARCHAR(150) NULL COMMENT 'ชื่อบริษัทภาษาไทย',
    `name_en` VARCHAR(150) NOT NULL COMMENT 'ชื่อบริษัทภาษาอังกฤษ',
    `market` VARCHAR(20) NOT NULL DEFAULT 'SET' COMMENT 'ตลาดหลักทรัพย์ เช่น SET, mai, NASDAQ',
    `sector` VARCHAR(50) NOT NULL COMMENT 'กลุ่มอุตสาหกรรม',
    `current_price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาล่าสุด',
    `previous_close` DECIMAL(10,2) NOT NULL COMMENT 'ราคาปิดวันก่อนหน้า',
    `price_change` DECIMAL(10,2) NOT NULL COMMENT 'ราคาที่เปลี่ยนแปลง (+/-)',
    `percent_change` DECIMAL(6,2) NOT NULL COMMENT 'เปอร์เซ็นต์ที่เปลี่ยนแปลง (+/- %)',
    `day_high` DECIMAL(10,2) NOT NULL COMMENT 'ราคาสูงสุดของวัน',
    `day_low` DECIMAL(10,2) NOT NULL COMMENT 'ราคาต่ำสุดของวัน',
    `volume` BIGINT NOT NULL DEFAULT 0 COMMENT 'ปริมาณการซื้อขาย',
    `market_cap` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'มูลค่าหลักทรัพย์ตามราคาตลาด (ล้านบาท/ดอลลาร์)',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_stock_symbol` (`symbol`),
    INDEX `idx_stock_sector` (`sector`),
    INDEX `idx_stock_market` (`market`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางข้อมูลหุ้นและราคาตลาด';

-- ------------------------------------------------------------------------------
-- 4. ตารางประวัติราคาหุ้นสำหรับกราฟ (stock_price_history) - Candlestick OHLCV
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `stock_price_history`;
CREATE TABLE `stock_price_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stock_id` INT NOT NULL,
    `trade_date` DATE NOT NULL COMMENT 'วันที่ซื้อขาย',
    `open_price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาเปิด (Open)',
    `high_price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาสูงสุด (High)',
    `low_price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาต่ำสุด (Low)',
    `close_price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาปิด (Close)',
    `volume` BIGINT NOT NULL DEFAULT 0 COMMENT 'ปริมาณซื้อขาย (Volume)',
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_stock_date` (`stock_id`, `trade_date`),
    INDEX `idx_history_stock_date` (`stock_id`, `trade_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บแท่งเทียนราคาย้อนหลังสำหรับเรนเดอร์กราฟ';

-- ------------------------------------------------------------------------------
-- 5. ตารางอัตราส่วนทางการเงินหลักและสถิติ (financial_metrics)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `financial_metrics`;
CREATE TABLE `financial_metrics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stock_id` INT NOT NULL,
    `pe_ratio` DECIMAL(8,2) NULL COMMENT 'Price to Earnings Ratio',
    `eps` DECIMAL(8,2) NULL COMMENT 'Earnings Per Share (กำไรต่อหุ้น)',
    `roe` DECIMAL(6,2) NULL COMMENT 'Return on Equity (% ผลตอบแทนต่อส่วนของผู้ถือหุ้น)',
    `roa` DECIMAL(6,2) NULL COMMENT 'Return on Assets (% ผลตอบแทนต่อสินทรัพย์รวม)',
    `pbv_ratio` DECIMAL(8,2) NULL COMMENT 'Price to Book Value',
    `dividend_yield` DECIMAL(6,2) NULL COMMENT 'Dividend Yield (% อัตราปันผลตอบแทน)',
    `revenue` DECIMAL(18,2) NULL COMMENT 'รายได้รวม (Total Revenue)',
    `revenue_growth_yoy` DECIMAL(6,2) NULL COMMENT 'การเติบโตของรายได้เทียบปีก่อน (% YoY)',
    `net_profit` DECIMAL(18,2) NULL COMMENT 'กำไรสุทธิ (Net Profit)',
    `net_profit_margin` DECIMAL(6,2) NULL COMMENT 'อัตรากำไรสุทธิ (% Net Margin)',
    `de_ratio` DECIMAL(6,2) NULL COMMENT 'Debt to Equity (หนี้สินต่อทุน)',
    `current_ratio` DECIMAL(6,2) NULL COMMENT 'Current Ratio (อัตราส่วนสภาพคล่อง)',
    `free_cash_flow` DECIMAL(18,2) NULL COMMENT 'กระแสเงินสดอิสระ (Free Cash Flow)',
    `fiscal_year` INT NOT NULL COMMENT 'ปีงบการเงิน',
    `quarter` ENUM('Q1', 'Q2', 'Q3', 'Q4', 'FY') NOT NULL DEFAULT 'FY' COMMENT 'ไตรมาส',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_stock_fiscal` (`stock_id`, `fiscal_year`, `quarter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บอัตราส่วนทางการเงินและตัวชี้วัดสำคัญ';

-- ------------------------------------------------------------------------------
-- 6. ตารางข้อมูลงบการเงินแบบละเอียด (financial_statements)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `financial_statements`;
CREATE TABLE `financial_statements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stock_id` INT NOT NULL,
    `statement_type` ENUM('balance_sheet', 'income_statement', 'cash_flow') NOT NULL COMMENT 'ประเภทงบ: งบดุล, งบกำไรขาดทุน, งบกระแสเงินสด',
    `fiscal_year` INT NOT NULL COMMENT 'ปีงบการเงิน',
    `quarter` ENUM('Q1', 'Q2', 'Q3', 'Q4', 'FY') NOT NULL DEFAULT 'FY',
    `data_json` JSON NOT NULL COMMENT 'ข้อมูลรายการงบการเงินในรูปแบบ JSON',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    INDEX `idx_statement_stock` (`stock_id`, `statement_type`, `fiscal_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางจัดเก็บงบการเงิน 3 ด้านแบบละเอียด';

-- ------------------------------------------------------------------------------
-- 7. ตารางผลการวิเคราะห์และคะแนน AI (ai_analyses)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `ai_analyses`;
CREATE TABLE `ai_analyses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stock_id` INT NOT NULL,
    `total_score` INT NOT NULL COMMENT 'คะแนนภาพรวม AI (0 - 100)',
    `growth_score` INT NOT NULL COMMENT 'คะแนนด้านการเติบโต (0 - 100)',
    `profitability_score` INT NOT NULL COMMENT 'คะแนนความสามารถทำกำไร (0 - 100)',
    `financial_health_score` INT NOT NULL COMMENT 'คะแนนสุขภาพทางการเงิน (0 - 100)',
    `valuation_score` INT NOT NULL COMMENT 'คะแนนความคุ้มค่าของราคา (0 - 100)',
    `recommendation` ENUM('Strong Buy', 'Buy', 'Hold', 'Wait & See', 'Sell') NOT NULL DEFAULT 'Hold' COMMENT 'คำแนะนำจาก AI',
    `summary_th` TEXT NOT NULL COMMENT 'บทสรุปภาพรวมผู้บริหารภาษาไทย',
    `strengths_json` JSON NOT NULL COMMENT 'รายการจุดเด่น/โอกาส (Array of strings)',
    `risks_json` JSON NOT NULL COMMENT 'รายการความเสี่ยง/จุดควรระวัง (Array of strings)',
    `ai_model` VARCHAR(50) DEFAULT 'Gemini Financial Analyst Engine',
    `analyzed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    INDEX `idx_ai_stock` (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางผลการประเมินและให้คะแนนหุ้นด้วย AI';

-- ------------------------------------------------------------------------------
-- 8. ตารางพอร์ตการลงทุนจำลอง (portfolios)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `portfolios`;
CREATE TABLE `portfolios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `portfolio_name` VARCHAR(100) NOT NULL DEFAULT 'พอร์ตจำลองหลัก',
    `initial_cash` DECIMAL(15,2) NOT NULL DEFAULT 1000000.00 COMMENT 'เงินทุนตั้งต้นจำลอง',
    `cash_balance` DECIMAL(15,2) NOT NULL DEFAULT 1000000.00 COMMENT 'เงินสดคงเหลือในพอร์ต',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางพอร์ตการลงทุนจำลองของผู้ใช้';

-- ------------------------------------------------------------------------------
-- 9. ตารางหุ้นที่ถือครองในพอร์ตจำลอง (portfolio_items)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `portfolio_items`;
CREATE TABLE `portfolio_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `portfolio_id` INT NOT NULL,
    `stock_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 0 COMMENT 'จำนวนหุ้นที่ถือครอง',
    `average_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ราคาต้นทุนเฉลี่ยต่อหุ้น',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_portfolio_stock` (`portfolio_id`, `stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บรายการหุ้นที่ถือครองในพอร์ต';

-- ------------------------------------------------------------------------------
-- 10. ตารางประวัติธุรกรรมการซื้อ-ขายจำลอง (transactions)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `portfolio_id` INT NOT NULL,
    `stock_id` INT NOT NULL,
    `transaction_type` ENUM('BUY', 'SELL') NOT NULL COMMENT 'ประเภทคำสั่ง ซื้อ/ขาย',
    `quantity` INT NOT NULL COMMENT 'จำนวนหุ้น',
    `price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาต่อหุ้นที่ทำรายการ',
    `total_amount` DECIMAL(15,2) NOT NULL COMMENT 'มูลค่ารวม (quantity * price)',
    `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ค่าธรรมเนียมจำลอง (0.157%)',
    `realized_pnl` DECIMAL(15,2) NULL COMMENT 'กำไร/ขาดทุนที่รับรู้จริง (กรณีขาย)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    INDEX `idx_trx_portfolio` (`portfolio_id`),
    INDEX `idx_trx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางบันทึกประวัติการซื้อขายพอร์ตจำลอง';

-- ------------------------------------------------------------------------------
-- 11. ตารางรายการหุ้นที่ติดตามและราคาเป้าหมาย (user_watchlists)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_watchlists`;
CREATE TABLE `user_watchlists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `stock_id` INT NOT NULL,
    `target_buy_price` DECIMAL(10,2) NULL COMMENT 'ราคาเป้าหมายซื้อ',
    `target_sell_price` DECIMAL(10,2) NULL COMMENT 'ราคาเป้าหมายขายทำกำไร',
    `notes` TEXT NULL COMMENT 'บันทึกส่วนตัว',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_user_stock` (`user_id`, `stock_id`),
    INDEX `idx_watchlist_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บรายการหุ้นที่ติดตามและราคาเป้าหมาย';

-- ------------------------------------------------------------------------------
-- 12. ตารางบันทึกการเทรดและวิเคราะห์กลยุทธ์ (trading_journals)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `trading_journals`;
CREATE TABLE `trading_journals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `strategy_tag` VARCHAR(50) NOT NULL DEFAULT 'Value Investing' COMMENT 'แท็กกลยุทธ์ เช่น Value Investing, Growth Stock',
    `rationale` TEXT NULL COMMENT 'เหตุผลในการตัดสินใจเข้าเทรด',
    `lessons_learned` TEXT NULL COMMENT 'บทเรียนและข้อคิดที่ได้รับหลังปิดออเดอร์',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_journal_user` (`user_id`),
    INDEX `idx_journal_strategy` (`strategy_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางบันทึกไดอารี่การเทรดและบทเรียน';

-- เปิดการตรวจสอบ Foreign Key กลับคืน
SET FOREIGN_KEY_CHECKS = 1;


-- ==============================================================================
-- 🚀 SEED DATA: ข้อมูลตัวอย่างเริ่มต้นสำหรับทดสอบระบบทันที
-- ==============================================================================

-- 1. เพิ่มผู้ใช้งานทดสอบ (รหัสผ่านคือ: password123)
-- Hash สร้างจาก password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`) VALUES
(1, 'investor_demo', 'demo@investment.local', '$2y$10$a6R0bmx9CyBzTVayaVAOpuUXbfqgKUSbKH8A4SNo/iB3RzU3vHjP.', 'คุณนักลงทุน ตัวอย่าง', 'user'),
(2, 'admin_master', 'admin@investment.local', '$2y$10$a6R0bmx9CyBzTVayaVAOpuUXbfqgKUSbKH8A4SNo/iB3RzU3vHjP.', 'ผู้ดูแลระบบ สูงสุด', 'admin');

-- 2. เพิ่มข้อมูลหุ้นชั้นนำในตลาด (SET & Global Tech)
INSERT INTO `stocks` (`id`, `symbol`, `name_th`, `name_en`, `market`, `sector`, `current_price`, `previous_close`, `price_change`, `percent_change`, `day_high`, `day_low`, `volume`, `market_cap`) VALUES
(1, 'DELTA', 'บริษัท เดลต้า อีเลคโทรนิคส์ (ประเทศไทย) จำกัด (มหาชน)', 'Delta Electronics (Thailand) PCL', 'SET', 'Technology', 148.50, 142.00, 6.50, 4.58, 151.00, 141.50, 18520000, 1852500000000.00),
(2, 'PTT', 'บริษัท ปตท. จำกัด (มหาชน)', 'PTT Public Company Limited', 'SET', 'Energy & Utilities', 33.75, 33.50, 0.25, 0.75, 34.00, 33.50, 42150000, 964000000000.00),
(3, 'CPALL', 'บริษัท ซีพี ออลล์ จำกัด (มหาชน)', 'CP ALL Public Company Limited', 'SET', 'Commerce', 62.50, 63.50, -1.00, -1.57, 63.75, 62.25, 24500000, 561400000000.00),
(4, 'BDMS', 'บริษัท กรุงเทพดุสิตเวชการ จำกัด (มหาชน)', 'Bangkok Dusit Medical Services PCL', 'SET', 'Healthcare', 28.00, 27.50, 0.50, 1.82, 28.25, 27.50, 16800000, 444900000000.00),
(5, 'AOT', 'บริษัท ท่าอากาศยานไทย จำกัด (มหาชน)', 'Airports of Thailand PCL', 'SET', 'Transportation', 59.25, 60.00, -0.75, -1.25, 60.50, 59.00, 21300000, 846400000000.00),
(6, 'SCB', 'เอสซีบี เอกซ์ จำกัด (มหาชน)', 'SCB X Public Company Limited', 'SET', 'Banking', 114.50, 113.00, 1.50, 1.33, 115.00, 112.50, 12900000, 385500000000.00),
(7, 'NVDA', 'NVIDIA Corporation', 'NVIDIA Corporation', 'NASDAQ', 'Technology', 128.50, 124.20, 4.30, 3.46, 130.00, 123.80, 54200000, 3160000000000.00),
(8, 'AAPL', 'Apple Inc.', 'Apple Inc.', 'NASDAQ', 'Technology', 224.20, 222.80, 1.40, 0.63, 225.50, 221.90, 48300000, 3420000000000.00);

-- 3. เพิ่มอัตราส่วนทางการเงิน (Financial Metrics)
INSERT INTO `financial_metrics` (`stock_id`, `pe_ratio`, `eps`, `roe`, `roa`, `pbv_ratio`, `dividend_yield`, `revenue`, `revenue_growth_yoy`, `net_profit`, `net_profit_margin`, `de_ratio`, `current_ratio`, `free_cash_flow`, `fiscal_year`, `quarter`) VALUES
(1, 65.40, 2.27, 24.80, 16.50, 14.20, 0.65, 146371000000.00, 23.50, 18412000000.00, 12.58, 0.42, 2.15, 15200000000.00, 2025, 'FY'),
(2, 9.80, 3.44, 9.50, 6.20, 0.85, 5.92, 3144552000000.00, 4.20, 98300000000.00, 3.13, 0.85, 1.65, 112400000000.00, 2025, 'FY'),
(3, 27.30, 2.29, 17.80, 8.40, 4.60, 2.24, 921470000000.00, 8.70, 20560000000.00, 2.23, 1.75, 0.95, 24800000000.00, 2025, 'FY'),
(4, 31.20, 0.90, 15.60, 11.20, 4.50, 2.68, 102110000000.00, 11.40, 14280000000.00, 13.98, 0.48, 1.85, 16300000000.00, 2025, 'FY'),
(5, 42.50, 1.39, 13.20, 8.90, 5.10, 1.85, 68420000000.00, 32.80, 19850000000.00, 29.01, 0.72, 2.40, 22100000000.00, 2025, 'FY'),
(6, 9.20, 12.45, 9.80, 1.35, 0.82, 7.85, 182300000000.00, 6.10, 41800000000.00, 22.93, 6.20, 1.10, 38500000000.00, 2025, 'FY'),
(7, 45.20, 2.84, 52.40, 38.20, 28.50, 0.15, 120400000000.00, 122.00, 62800000000.00, 52.16, 0.35, 3.80, 56000000000.00, 2025, 'FY'),
(8, 33.80, 6.63, 147.20, 28.50, 48.20, 0.45, 385700000000.00, 2.10, 100370000000.00, 26.02, 1.45, 0.98, 108000000000.00, 2025, 'FY');

-- 4. เพิ่มผลการวิเคราะห์และคะแนน AI (AI Analysis & Scoring)
INSERT INTO `ai_analyses` (`stock_id`, `total_score`, `growth_score`, `profitability_score`, `financial_health_score`, `valuation_score`, `recommendation`, `summary_th`, `strengths_json`, `risks_json`, `ai_model`) VALUES
(1, 88, 95, 89, 92, 62, 'Strong Buy', 'DELTA เป็นผู้นำด้านชิ้นส่วนอิเล็กทรอนิกส์และ Power Management สำหรับ Data Center และ EV ที่ได้ประโยชน์สูงสุดจากกระแส AI ทั่วโลก งบการเงินแข็งแกร่ง หนี้สินต่ำมาก แต่ระดับราคา (P/E 65x) สะท้อนความคาดหวังการเติบโตไว้ค่อนข้างสูง', '["เติบโตสูงตามอุตสาหกรรม AI Data Center และชิ้นส่วนยานยนต์ไฟฟ้า", "อัตราผลตอบแทนต่อส่วนของผู้ถือหุ้น (ROE) สูงถึง 24.8%", "ฐานะการเงินแข็งแกร่ง มีหนี้สินที่มีภาระดอกเบี้ยต่ำมาก (D/E 0.42)"]', '["P/E ค่อนข้างพรีเมียม (65.4x) มีความผันผวนสูงหากการเติบโตชะลอตัว", "ความเสี่ยงด้านการพึ่งพาลูกค้ากลุ่มเทคโนโลยีรายใหญ่"]', 'Gemini Financial Analyst v2.5'),
(2, 76, 68, 72, 85, 90, 'Buy', 'PTT มีกระแสเงินสดจากการดำเนินงานที่สม่ำเสมอและมั่นคงสูง จ่ายเงินปันผลสูงสม่ำเสมอ (Dividend Yield เกือบ 6%) ระดับ Valuation ค่อนข้างถูก (P/E ต่ำกว่า 10x และ P/BV ต่ำกว่า 1 เท่า) เหมาะสำหรับพอร์ตลงทุนเน้นคุณค่าและรับเงินปันผล', '["จ่ายเงินปันผลสูงสม่ำเสมอ Yield ประมาณ 5.92%", "Valuation ต่ำมาก P/E เพียง 9.8 เท่า และเทรดต่ำกว่ามูลค่าทางบัญชี (P/BV 0.85)", "กระแสเงินสดแข็งแกร่ง เป็นเสาหลักด้านพลังงานของประเทศ"]', '["การเติบโตของรายได้ค่อนข้างอิงกับวัฏจักรราคาน้ำมันและก๊าซธรรมชาติโลก", "ความท้าทายในการเปลี่ยนผ่านสู่พลังงานสะอาดในระยะยาว"]', 'Gemini Financial Analyst v2.5'),
(3, 82, 78, 85, 80, 84, 'Buy', 'CPALL มีความได้เปรียบในการแข่งขันอย่างยั่งยืน (Economic Moat) จากเครือข่าย 7-Eleven ทั่วประเทศ ยอดขายและกำไรเติบโตต่อเนื่องตามการฟื้นตัวของการบริโภคและการท่องเที่ยว อัตรากำไรสุทธิมีแนวโน้มขยายตัว', '["เครือข่ายสาขาครอบคลุมทั่วประเทศ มีความแข็งแกร่งด้าน Brand และทำเล", "ROE สม่ำเสมอที่ระดับ 17.8%", "ได้รับประโยชน์โดยตรงจากการท่องเที่ยวและมาตรการกระตุ้นเศรษฐกิจ"]', '["ภาระดอกเบี้ยจ่ายจากหนี้สินในการเข้าซื้อกิจการในอดีต (D/E 1.75)", "การแข่งขันของกลุ่มร้านสะดวกซื้อและช่องทาง Delivery"]', 'Gemini Financial Analyst v2.5'),
(7, 94, 98, 96, 95, 75, 'Strong Buy', 'NVDA เป็นผู้นำเบ็ดเสร็จในตลาดชิป AI GPU ทั่วโลก มีอัตรากำไรสุทธิ (Net Margin) สูงเกิน 50% และการเติบโตของรายได้แบบก้าวกระโดด ถือเป็นหัวใจหลักของโครงสร้างพื้นฐาน AI แห่งอนาคต', '["ส่วนแบ่งการตลาด AI Accelerator สูงกว่า 80% ทั่วโลก", "กำไรสุทธิเติบโตมากกว่า 100% YoY พร้อม Net Margin สูงถึง 52%", "ระบบนิเวศซอฟต์แวร์ CUDA เป็นปราการป้องกันคู่แข่งที่แข็งแกร่ง"]', '["ความเสี่ยงด้านมาตรการคว่ำบาตรการส่งออกชิปขั้นสูงไปยังบางประเทศ", "ความคาดหวังของตลาดสูงมากในทุกรอบผลประกอบการ"]', 'Gemini Financial Analyst v2.5');

-- 5. เพิ่มประวัติราคาแท่งเทียนย้อนหลังสำหรับกราฟ (ตัวอย่าง 10 วันทำการล่าสุดสำหรับ DELTA และ PTT)
INSERT INTO `stock_price_history` (`stock_id`, `trade_date`, `open_price`, `high_price`, `low_price`, `close_price`, `volume`) VALUES
(1, '2026-08-10', 138.00, 140.50, 137.50, 140.00, 14200000),
(1, '2026-08-11', 140.00, 142.00, 139.00, 141.50, 15100000),
(1, '2026-08-12', 141.50, 144.00, 140.50, 143.00, 16800000),
(1, '2026-08-15', 143.00, 145.50, 142.00, 144.00, 15900000),
(1, '2026-08-16', 144.00, 146.00, 143.50, 145.00, 17200000),
(1, '2026-08-17', 145.00, 147.00, 144.00, 146.50, 18400000),
(1, '2026-08-18', 146.50, 148.00, 145.00, 145.50, 16200000),
(1, '2026-08-19', 145.50, 146.50, 143.00, 144.00, 15300000),
(1, '2026-08-22', 144.00, 145.00, 141.50, 142.00, 14800000),
(1, '2026-08-23', 142.00, 151.00, 141.50, 148.50, 18520000),
(2, '2026-08-10', 33.00, 33.50, 32.75, 33.25, 35000000),
(2, '2026-08-11', 33.25, 33.75, 33.00, 33.50, 38000000),
(2, '2026-08-12', 33.50, 33.75, 33.25, 33.50, 32000000),
(2, '2026-08-15', 33.50, 34.00, 33.25, 33.75, 41000000),
(2, '2026-08-16', 33.75, 34.00, 33.50, 33.50, 29000000),
(2, '2026-08-17', 33.50, 33.75, 33.25, 33.25, 31000000),
(2, '2026-08-18', 33.25, 33.50, 33.00, 33.25, 33000000),
(2, '2026-08-19', 33.25, 33.75, 33.25, 33.50, 36000000),
(2, '2026-08-22', 33.50, 33.75, 33.25, 33.50, 28000000),
(2, '2026-08-23', 33.50, 34.00, 33.50, 33.75, 42150000);

-- 6. เพิ่มพอร์ตจำลองเริ่มต้นสำหรับ User 1
INSERT INTO `portfolios` (`id`, `user_id`, `portfolio_name`, `initial_cash`, `cash_balance`) VALUES
(1, 1, 'พอร์ตจำลอง AI Top Picks', 1000000.00, 545225.00);

-- 7. เพิ่มรายการถือครองหุ้นจำลอง (DELTA 2,000 หุ้น, PTT 5,000 หุ้น)
INSERT INTO `portfolio_items` (`portfolio_id`, `stock_id`, `quantity`, `average_price`) VALUES
(1, 1, 2000, 142.50), -- ซื้อ DELTA 2,000 หุ้น ต้นทุนเฉลี่ย 142.50
(1, 2, 5000, 33.25);  -- ซื้อ PTT 5,000 หุ้น ต้นทุนเฉลี่ย 33.25

-- 8. บันทึกประวัติการซื้อ-ขายจำลอง
INSERT INTO `transactions` (`portfolio_id`, `stock_id`, `transaction_type`, `quantity`, `price`, `total_amount`, `fee`, `realized_pnl`) VALUES
(1, 1, 'BUY', 2000, 142.50, 285000.00, 447.45, NULL),
(1, 2, 'BUY', 5000, 33.25, 166250.00, 261.01, NULL);

-- ==============================================================================
-- สิ้นสุดสคริปต์ SQL
-- ==============================================================================
