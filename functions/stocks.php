<?php
declare(strict_types=1);

/**
 * 📈 functions/stocks.php
 * โมดูลจัดการข้อมูลหุ้น อัตราส่วนทางการเงิน งบการเงิน และระบบคัดกรองหุ้น (Stock Screener)
 * ปฏิบัติตามมาตรฐาน PDO Prepared Statements, Strict Types และ Early Return
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * ดึงรายการหุ้นทั้งหมด พร้อมค้นหาและกรองตามกลุ่มอุตสาหกรรม
 *
 * @param PDO $pdo
 * @param string|null $sector กรองตามกลุ่มอุตสาหกรรม
 * @param string|null $search ค้นหาด้วยชื่อย่อหรือชื่อบริษัท
 * @return array
 */
function getAllStocks(PDO $pdo, ?string $sector = null, ?string $search = null): array
{
    $sql = "SELECT s.id, s.symbol, s.name_th, s.name_en, s.market, s.sector, 
                   s.current_price, s.previous_close, s.price_change, s.percent_change, 
                   s.day_high, s.day_low, s.volume, s.market_cap,
                   fm.pe_ratio, fm.eps, fm.roe, fm.dividend_yield,
                   ai.total_score, ai.recommendation
            FROM stocks s
            LEFT JOIN financial_metrics fm ON s.id = fm.stock_id AND fm.quarter = 'FY'
            LEFT JOIN ai_analyses ai ON s.id = ai.stock_id
            WHERE 1=1";

    $params = [];

    // กรองตามกลุ่มอุตสาหกรรม
    if ($sector !== null && $sector !== '' && $sector !== 'all') {
        $sql .= " AND s.sector = :sector";
        $params[':sector'] = $sector;
    }

    // ค้นหาตามชื่อย่อหุ้นหรือชื่อบริษัท
    if ($search !== null && trim($search) !== '') {
        $searchTerm = '%' . trim($search) . '%';
        $sql .= " AND (s.symbol LIKE :search_sym OR s.name_en LIKE :search_en OR s.name_th LIKE :search_th)";
        $params[':search_sym'] = $searchTerm;
        $params[':search_en']  = $searchTerm;
        $params[':search_th']  = $searchTerm;
    }

    $sql .= " ORDER BY s.market_cap DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * ดึงข้อมูลรายละเอียดหุ้นเดี่ยว พร้อมค่าสถิติทางการเงินและบทวิเคราะห์ AI
 *
 * @param PDO $pdo
 * @param string $symbol ชื่อย่อหุ้น เช่น 'NVDA', 'DELTA', 'PTT'
 * @return array|null
 */
function getStockBySymbol(PDO $pdo, string $symbol): ?array
{
    $symbol = strtoupper(trim($symbol));

    // Guard Clause 1: หากไม่มี symbol ให้ส่งคืน null ทันที
    if ($symbol === '') {
        return null;
    }

    $sql = "SELECT s.id, s.symbol, s.name_th, s.name_en, s.market, s.sector, 
                   s.current_price, s.previous_close, s.price_change, s.percent_change, 
                   s.day_high, s.day_low, s.volume, s.market_cap,
                   fm.pe_ratio, fm.eps, fm.roe, fm.roa, fm.pbv_ratio, fm.dividend_yield,
                   fm.revenue, fm.revenue_growth_yoy, fm.net_profit, fm.net_profit_margin,
                   fm.de_ratio, fm.current_ratio, fm.free_cash_flow, fm.fiscal_year,
                   ai.total_score, ai.growth_score, ai.profitability_score, 
                   ai.financial_health_score, ai.valuation_score, ai.recommendation, 
                   ai.summary_th, ai.strengths_json, ai.risks_json
            FROM stocks s
            LEFT JOIN financial_metrics fm ON s.id = fm.stock_id AND fm.quarter = 'FY'
            LEFT JOIN ai_analyses ai ON s.id = ai.stock_id
            WHERE s.symbol = :symbol
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':symbol' => $symbol]);
    $stock = $stmt->fetch();

    if (!$stock) {
        return null;
    }

    // แปลง JSON ฟิลด์ strengths และ risks ให้อยู่ในรูป Array
    $stock['strengths'] = [];
    if (!empty($stock['strengths_json'])) {
        $decoded = json_decode($stock['strengths_json'], true);
        if (is_array($decoded)) {
            $stock['strengths'] = $decoded;
        }
    }

    $stock['risks'] = [];
    if (!empty($stock['risks_json'])) {
        $decoded = json_decode($stock['risks_json'], true);
        if (is_array($decoded)) {
            $stock['risks'] = $decoded;
        }
    }

    return $stock;
}

/**
 * ดึงประวัติราคาแท่งเทียนย้อนหลัง (OHLCV) สำหรับเรนเดอร์กราฟราคา
 *
 * @param PDO $pdo
 * @param int $stockId
 * @param int $limit จำนวนวันย้อนหลัง
 * @return array
 */
function getStockPriceHistory(PDO $pdo, int $stockId, int $limit = 30): array
{
    $sql = "SELECT trade_date, open_price, high_price, low_price, close_price, volume
            FROM stock_price_history
            WHERE stock_id = :stock_id
            ORDER BY trade_date ASC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':stock_id', $stockId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * ดึงรายการกลุ่มอุตสาหกรรม (Sectors) ทั้งหมดที่มีในระบบ
 *
 * @param PDO $pdo
 * @return array
 */
function getAllSectors(PDO $pdo): array
{
    $sql = "SELECT DISTINCT sector FROM stocks WHERE sector IS NOT NULL AND sector != '' ORDER BY sector ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * คัดกรองหุ้นตามเงื่อนไขปัจจัยพื้นฐาน (Stock Screener Filter)
 *
 * @param PDO $pdo
 * @param array $filters ตัวกรอง เช่น ['max_pe' => 20, 'min_roe' => 15, 'min_yield' => 3, 'sector' => 'Technology']
 * @return array
 */
function screenStocks(PDO $pdo, array $filters = []): array
{
    $sql = "SELECT s.id, s.symbol, s.name_th, s.name_en, s.sector, s.current_price, s.percent_change,
                   fm.pe_ratio, fm.eps, fm.roe, fm.dividend_yield, fm.revenue_growth_yoy, fm.de_ratio,
                   ai.total_score, ai.recommendation
            FROM stocks s
            INNER JOIN financial_metrics fm ON s.id = fm.stock_id AND fm.quarter = 'FY'
            LEFT JOIN ai_analyses ai ON s.id = ai.stock_id
            WHERE 1=1";

    $params = [];

    // 1. P/E Ratio สูงสุด (เช่น P/E < 25)
    if (!empty($filters['max_pe']) && is_numeric($filters['max_pe'])) {
        $sql .= " AND fm.pe_ratio <= :max_pe";
        $params[':max_pe'] = (float)$filters['max_pe'];
    }

    // 2. ROE ขั้นต่ำ (เช่น ROE > 12%)
    if (!empty($filters['min_roe']) && is_numeric($filters['min_roe'])) {
        $sql .= " AND fm.roe >= :min_roe";
        $params[':min_roe'] = (float)$filters['min_roe'];
    }

    // 3. Dividend Yield ขั้นต่ำ (เช่น ปันผล > 3%)
    if (!empty($filters['min_yield']) && is_numeric($filters['min_yield'])) {
        $sql .= " AND fm.dividend_yield >= :min_yield";
        $params[':min_yield'] = (float)$filters['min_yield'];
    }

    // 4. คะแนน AI ขั้นต่ำ (เช่น AI Score >= 75)
    if (!empty($filters['min_score']) && is_numeric($filters['min_score'])) {
        $sql .= " AND ai.total_score >= :min_score";
        $params[':min_score'] = (int)$filters['min_score'];
    }

    // 5. กลุ่มอุตสาหกรรม
    if (!empty($filters['sector']) && $filters['sector'] !== 'all') {
        $sql .= " AND s.sector = :sector";
        $params[':sector'] = $filters['sector'];
    }

    $sql .= " ORDER BY ai.total_score DESC, fm.roe DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
