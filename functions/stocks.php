<?php
declare(strict_types=1);

/**
 * functions/stocks.php
 * SmartPort AI - Stock Analysis & Financial Metrics Engine
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
function getStockBySymbol(PDO $pdo, string $symbol, bool $syncWithMarket = true): ?array
{
    $symbol = strtoupper(trim($symbol));

    // Guard Clause 1: หากไม่มี symbol ให้ส่งคืน null ทันที
    if ($symbol === '') {
        return null;
    }

    // เรียกใช้ Live Market API Gateway เพื่อซิงค์และดึงราคาตลาดจริงแบบ Real-Time
    if ($syncWithMarket) {
        require_once __DIR__ . '/market_api.php';
        if (function_exists('syncStockToDatabase')) {
            $liveStock = syncStockToDatabase($pdo, $symbol);
            if ($liveStock !== null) {
                return $liveStock;
            }
        }
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
        $decoded = json_decode((string)$stock['strengths_json'], true);
        if (is_array($decoded)) {
            $stock['strengths'] = $decoded;
        }
    }

    $stock['risks'] = [];
    if (!empty($stock['risks_json'])) {
        $decoded = json_decode((string)$stock['risks_json'], true);
        if (is_array($decoded)) {
            $stock['risks'] = $decoded;
        }
    }

    // หากยังไม่มีบทวิเคราะห์ AI ในฐานข้อมูล ให้สังเคราะห์บทวิเคราะห์เชิงปริมาณอัตโนมัติ
    if (empty($stock['summary_th'])) {
        $aiData = generateDynamicAiAnalysis($stock);
        $stock['total_score']            = $aiData['total_score'];
        $stock['growth_score']           = $aiData['growth_score'];
        $stock['profitability_score']    = $aiData['profitability_score'];
        $stock['financial_health_score'] = $aiData['financial_health_score'];
        $stock['valuation_score']        = $aiData['valuation_score'];
        $stock['recommendation']         = $aiData['recommendation'];
        $stock['summary_th']             = $aiData['summary_th'];
        $stock['strengths']              = $aiData['strengths'];
        $stock['risks']                  = $aiData['risks'];

        // บันทึกลงตาราง ai_analyses เพื่อเป็นแคชข้อมูล
        $chkStmt = $pdo->prepare("SELECT id FROM ai_analyses WHERE stock_id = :stock_id LIMIT 1");
        $chkStmt->execute([':stock_id' => (int)$stock['id']]);
        $hasRow = $chkStmt->fetch();

        if ($hasRow) {
            $aiUpdateSql = "UPDATE ai_analyses SET 
                              total_score = :total,
                              growth_score = :growth,
                              profitability_score = :profit,
                              financial_health_score = :health,
                              valuation_score = :val,
                              recommendation = :rec,
                              summary_th = :summary,
                              strengths_json = :str,
                              risks_json = :risk,
                              ai_model = :model,
                              analyzed_at = NOW()
                            WHERE id = :id";
            $aiStmt = $pdo->prepare($aiUpdateSql);
            $aiStmt->execute([
                ':total'    => $aiData['total_score'],
                ':growth'   => $aiData['growth_score'],
                ':profit'   => $aiData['profitability_score'],
                ':health'   => $aiData['financial_health_score'],
                ':val'      => $aiData['valuation_score'],
                ':rec'      => $aiData['recommendation'],
                ':summary'  => $aiData['summary_th'],
                ':str'      => json_encode($aiData['strengths'], JSON_UNESCAPED_UNICODE),
                ':risk'     => json_encode($aiData['risks'], JSON_UNESCAPED_UNICODE),
                ':model'    => $aiData['ai_model'],
                ':id'       => (int)$hasRow['id']
            ]);
        } else {
            $aiInsertSql = "INSERT INTO ai_analyses (stock_id, total_score, growth_score, profitability_score, financial_health_score, valuation_score, recommendation, summary_th, strengths_json, risks_json, ai_model)
                            VALUES (:stock_id, :total, :growth, :profit, :health, :val, :rec, :summary, :str, :risk, :model)";
            $aiStmt = $pdo->prepare($aiInsertSql);
            $aiStmt->execute([
                ':stock_id' => (int)$stock['id'],
                ':total'    => $aiData['total_score'],
                ':growth'   => $aiData['growth_score'],
                ':profit'   => $aiData['profitability_score'],
                ':health'   => $aiData['financial_health_score'],
                ':val'      => $aiData['valuation_score'],
                ':rec'      => $aiData['recommendation'],
                ':summary'  => $aiData['summary_th'],
                ':str'      => json_encode($aiData['strengths'], JSON_UNESCAPED_UNICODE),
                ':risk'     => json_encode($aiData['risks'], JSON_UNESCAPED_UNICODE),
                ':model'    => $aiData['ai_model']
            ]);
        }
    }

    return $stock;
}

/**
 * สังเคราะห์บทวิเคราะห์เชิงปริมาณ AI Synthesis อัตโนมัติตามปัจจัยพื้นฐานและราคาตลาดจริง
 *
 * @param array $stock ข้อมูลหุ้นและงบการเงิน
 * @return array ข้อมูลบทวิเคราะห์ AI
 */
function generateDynamicAiAnalysis(array $stock): array
{
    $symbol = (string)($stock['symbol'] ?? 'STOCK');
    $name = (string)($stock['name_th'] ?: ($stock['name_en'] ?: $symbol));
    $market = (string)($stock['market'] ?? 'SET');
    $price = (float)($stock['current_price'] ?? 0);
    $pctChange = (float)($stock['percent_change'] ?? 0);
    $pe = (float)($stock['pe_ratio'] ?? 20);
    $roe = (float)($stock['roe'] ?? 15);
    $de = (float)($stock['de_ratio'] ?? 0.8);
    $div = (float)($stock['dividend_yield'] ?? 3.0);

    // คำนวณคะแนน 4 มิติเชิงปริมาณ
    $growthScore = min(98, max(55, (int)(75 + ($pctChange * 1.5))));
    $profitScore = min(98, max(50, (int)(60 + ($roe * 1.2))));
    $healthScore = min(95, max(50, (int)(90 - ($de * 15))));
    $valScore    = min(95, max(45, (int)(95 - ($pe * 1.1) + ($div * 3))));
    
    $totalScore  = (int)(($growthScore * 0.3) + ($profitScore * 0.3) + ($healthScore * 0.2) + ($valScore * 0.2));
    $recommendation = ($totalScore >= 80) ? 'Buy' : (($totalScore >= 65) ? 'Hold' : 'Underweight');

    // สร้างบทวิเคราะห์สรุปภาษาไทยแบบ Quantitative
    $valText = ($pe > 0 && $pe < 18) ? 'ระดับ Valuation มีความน่าสนใจ (P/E ต่ำกว่าค่าเฉลี่ยอุตสาหกรรม)' : 'มีระดับมูลค่าพรีเมียมตามความคาดหวังการเติบโต';
    $profText = ($roe >= 15) ? 'ความสามารถในการทำกำไรโดดเด่น (ROE สูงกว่า 15%)' : 'ความสามารถในการสร้างผลตอบแทนอยู่ในเกณฑ์มาตรฐาน';
    $debtText = ($de <= 1.0) ? 'โครงสร้างทางการเงินมีความแข็งแกร่งด้วยอัตราส่วนหนี้สินต่ำ' : 'มีภาระหนี้สินในระดับปานกลางที่ต้องบริหารต้นทุนดอกเบี้ย';
    $trendText = ($pctChange >= 0) ? 'ทิศทางราคาตลาดล่าสุดเคลื่อนไหวในแดนบวกสะท้อนแรงซื้อสะสม' : 'ราคาตลาดล่าสุดพักตัวตามสภาวะตลาด';

    $summary = sprintf(
        '%s (%s) ในตลาด %s มีราคาซื้อขายล่าสุดที่ %s (%s%.2f%%) %s ควบคู่กับ%s และ%s ภาพรวมการประเมินเชิงปริมาณจัดอยู่ในกลุ่ม "%s" ด้วยคะแนนรวม %d/100',
        $name,
        $symbol,
        $market,
        number_format($price, 2),
        ($pctChange >= 0 ? '+' : ''),
        $pctChange,
        $trendText,
        $profText,
        $debtText,
        $recommendation,
        $totalScore
    );

    // สร้างจุดแข็ง (Catalysts)
    $strengths = [];
    if ($roe >= 12) {
        $strengths[] = sprintf('อัตราผลตอบแทนต่อส่วนของผู้ถือหุ้น (ROE) แข็งแกร่งที่ %.2f%%', $roe);
    }
    if ($div >= 2.0) {
        $strengths[] = sprintf('อัตราเงินปันผลตอบแทนสม่ำเสมอ %.2f%% ต่อปี', $div);
    }
    if ($de <= 1.2) {
        $strengths[] = sprintf('โครงสร้างเงินทุนมั่นคง อัตราส่วนหนี้สินต่อทุน (D/E) ต่ำเพียง %.2fx', $de);
    }
    if (empty($strengths)) {
        $strengths[] = 'มีสภาพคล่องในการซื้อขายจริงในตลาดหลักทรัพย์สากล';
        $strengths[] = 'ราคาตลาดสะท้อนข้อมูลปัจจัยพื้นฐานตามรอบบัญชีล่าสุด';
    }

    // สร้างปัจจัยเสี่ยง (Risks)
    $risks = [];
    if ($pe >= 25) {
        $risks[] = sprintf('ระดับ P/E Ratio อยู่ที่ %.2fx ซึ่งสะท้อนความคาดหวังการเติบโตสูง หากผลประกอบการชะลอตัวอาจเกิดแรงเทขาย', $pe);
    }
    if ($pctChange <= -3.0) {
        $risks[] = sprintf('ความผันผวนของราคาในระยะสั้น (ราคาปรับตัว %.2f%%) จากแรงกดดันทางเศรษฐกิจมหภาค', $pctChange);
    }
    if ($de > 1.2) {
        $risks[] = sprintf('อัตราส่วนหนี้สินต่อทุน %.2fx ควรติดตามการบริหารกระแสเงินสดสำหรับชำระหนี้', $de);
    }
    if (empty($risks)) {
        $risks[] = 'ความเสี่ยงจากความผันผวนของสภาวะเศรษฐกิจมหภาคและอัตราดอกเบี้ยนโยบาย';
        $risks[] = 'ปัจจัยเสี่ยงด้านการแข่งขันในอุตสาหกรรมและต้นทุนการดำเนินงาน';
    }

    return [
        'total_score'            => $totalScore,
        'growth_score'           => $growthScore,
        'profitability_score'    => $profitScore,
        'financial_health_score' => $healthScore,
        'valuation_score'        => $valScore,
        'recommendation'         => $recommendation,
        'summary_th'             => $summary,
        'strengths'              => $strengths,
        'risks'                  => $risks,
        'ai_model'               => 'SmartPort AI Quantitative Engine v2.5'
    ];
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
            FROM (
                SELECT trade_date, open_price, high_price, low_price, close_price, volume
                FROM stock_price_history
                WHERE stock_id = :stock_id
                ORDER BY trade_date DESC
                LIMIT :limit
            ) sub
            ORDER BY trade_date ASC";

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
    $sql = "SELECT s.id, s.symbol, s.name_th, s.name_en, s.market, s.sector, s.current_price, s.percent_change,
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

/**
 * คำนวณช่วงราคา 52 สัปดาห์ (52-Week Range) และตำแหน่งเปอร์เซ็นต์ปัจจุบัน
 * โดยเชื่อมโยงกับ API สด, ประวัติราคาในฐานข้อมูล, หรือประมาณการทางสถิติ
 *
 * @param PDO $pdo
 * @param array $stock ข้อมูลหุ้น
 * @return array ['low_52w' => float, 'high_52w' => float, 'progress_pct' => float]
 */
function calculateStock52WeekRange(PDO $pdo, array $stock): array
{
    $currentPrice = (float)($stock['current_price'] ?? 0);
    $low52w = (float)($stock['fifty_two_week_low'] ?? 0);
    $high52w = (float)($stock['fifty_two_week_high'] ?? 0);

    // หากไม่มีในตัวแปร stock ให้ลองดึงจากประวัติราคาในฐานข้อมูล 1 ปีย้อนหลัง
    if ($low52w <= 0.0 || $high52w <= $low52w) {
        $stockId = (int)($stock['id'] ?? 0);
        if ($stockId > 0) {
            $stmt = $pdo->prepare("SELECT MIN(low_price) AS min_low, MAX(high_price) AS max_high 
                                   FROM stock_price_history 
                                   WHERE stock_id = :id AND trade_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)");
            $stmt->execute([':id' => $stockId]);
            $row = $stmt->fetch();
            if ($row && $row['min_low'] !== null && $row['max_high'] !== null && (float)$row['max_high'] > (float)$row['min_low']) {
                $low52w = (float)$row['min_low'];
                $high52w = (float)$row['max_high'];
            }
        }
    }

    // หากยังไม่มี ให้ลองดึงสดจาก Live Market API
    if ($low52w <= 0.0 || $high52w <= $low52w) {
        require_once __DIR__ . '/market_api.php';
        $symbol = (string)($stock['symbol'] ?? '');
        if ($symbol !== '' && function_exists('fetchLiveMarketQuote')) {
            $quote = fetchLiveMarketQuote($symbol);
            if ($quote && !empty($quote['fifty_two_week_high']) && !empty($quote['fifty_two_week_low']) && (float)$quote['fifty_two_week_high'] > (float)$quote['fifty_two_week_low']) {
                $low52w = (float)$quote['fifty_two_week_low'];
                $high52w = (float)$quote['fifty_two_week_high'];
            }
        }
    }

    // Fallback: หากยังไม่มี ให้ประมาณการทางสถิติ (-28% และ +28% จากราคาปัจจุบัน)
    if ($low52w <= 0.0 || $high52w <= $low52w) {
        $low52w = $currentPrice * 0.72;
        $high52w = $currentPrice * 1.28;
    }

    $progressPct = 50.0;
    if ($high52w > $low52w && $currentPrice > 0.0) {
        $progressPct = min(100.0, max(0.0, (($currentPrice - $low52w) / ($high52w - $low52w)) * 100.0));
    }

    return [
        'low_52w'      => $low52w,
        'high_52w'     => $high52w,
        'progress_pct' => $progressPct
    ];
}

