<?php
declare(strict_types=1);

/**
 * functions/comparison.php
 * SmartPort AI - Stock Peer Comparison & Benchmark Matrix Engine
 * เปรียบเทียบตัวชี้วัดทางการเงินและคะแนน AI ระหว่างหุ้น 2-4 ตัวพร้อมกัน
 * ปฏิบัติตามมาตรฐาน PDO Prepared Statements, Strict Types และ Early Return Pattern
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/stocks.php';

/**
 * ดึงข้อมูลหุ้นหลายตัวสำหรับนำมาเปรียบเทียบ (สูงสุด 4 ตัว)
 *
 * @param PDO $pdo
 * @param array $symbols รายชื่อย่อหุ้น เช่น ['DELTA', 'PTT', 'NVDA']
 * @return array
 */
function getStocksForComparison(PDO $pdo, array $symbols): array
{
    if (empty($symbols)) {
        return [];
    }

    // ทำความสะอาดและจำกัดไม่เกิน 4 ตัว
    $cleanedSymbols = [];
    foreach ($symbols as $sym) {
        $trimmed = strtoupper(trim((string)$sym));
        if ($trimmed !== '' && !in_array($trimmed, $cleanedSymbols, true)) {
            $cleanedSymbols[] = $trimmed;
        }
        if (count($cleanedSymbols) >= 4) {
            break;
        }
    }

    if (empty($cleanedSymbols)) {
        return [];
    }

    $result = [];
    foreach ($cleanedSymbols as $sym) {
        $stock = getStockBySymbol($pdo, $sym);
        if ($stock) {
            $result[] = $stock;
        }
    }

    return $result;
}

/**
 * ระบุหุ้นที่ชนะเลิศในแต่ละตัวชี้วัดทางการเงิน (Winning Metric Identification)
 *
 * @param array $stocks รายการหุ้นที่นำมาเปรียบเทียบ
 * @return array Map [metric_name => [winning_symbol, best_value]]
 */
function identifyWinningMetrics(array $stocks): array
{
    if (count($stocks) < 2) {
        return [];
    }

    $winners = [];

    // 1. P/E Ratio (ต่ำกว่าดีกว่า เมื่อ > 0)
    $bestPeSym = null;
    $bestPeVal = PHP_FLOAT_MAX;
    foreach ($stocks as $s) {
        $pe = $s['pe_ratio'] !== null ? (float)$s['pe_ratio'] : null;
        if ($pe !== null && $pe > 0 && $pe < $bestPeVal) {
            $bestPeVal = $pe;
            $bestPeSym = (string)$s['symbol'];
        }
    }
    if ($bestPeSym !== null && $bestPeVal < PHP_FLOAT_MAX) {
        $winners['pe_ratio'] = $bestPeSym;
    }

    // 2. P/BV Ratio (ต่ำกว่าดีกว่า เมื่อ > 0)
    $bestPbvSym = null;
    $bestPbvVal = PHP_FLOAT_MAX;
    foreach ($stocks as $s) {
        $pbv = $s['pbv_ratio'] !== null ? (float)$s['pbv_ratio'] : null;
        if ($pbv !== null && $pbv > 0 && $pbv < $bestPbvVal) {
            $bestPbvVal = $pbv;
            $bestPbvSym = (string)$s['symbol'];
        }
    }
    if ($bestPbvSym !== null && $bestPbvVal < PHP_FLOAT_MAX) {
        $winners['pbv_ratio'] = $bestPbvSym;
    }

    // 3. ROE (สูงกว่าดีกว่า)
    $bestRoeSym = null;
    $bestRoeVal = -PHP_FLOAT_MAX;
    foreach ($stocks as $s) {
        $roe = $s['roe'] !== null ? (float)$s['roe'] : null;
        if ($roe !== null && $roe > $bestRoeVal) {
            $bestRoeVal = $roe;
            $bestRoeSym = (string)$s['symbol'];
        }
    }
    if ($bestRoeSym !== null && $bestRoeVal > -PHP_FLOAT_MAX) {
        $winners['roe'] = $bestRoeSym;
    }

    // 4. Dividend Yield (สูงกว่าดีกว่า)
    $bestYieldSym = null;
    $bestYieldVal = -PHP_FLOAT_MAX;
    foreach ($stocks as $s) {
        $yield = $s['dividend_yield'] !== null ? (float)$s['dividend_yield'] : null;
        if ($yield !== null && $yield > $bestYieldVal) {
            $bestYieldVal = $yield;
            $bestYieldSym = (string)$s['symbol'];
        }
    }
    if ($bestYieldSym !== null && $bestYieldVal > 0) {
        $winners['dividend_yield'] = $bestYieldSym;
    }

    // 5. Net Profit Margin (สูงกว่าดีกว่า)
    $bestNpmSym = null;
    $bestNpmVal = -PHP_FLOAT_MAX;
    foreach ($stocks as $s) {
        $npm = $s['net_profit_margin'] !== null ? (float)$s['net_profit_margin'] : null;
        if ($npm !== null && $npm > $bestNpmVal) {
            $bestNpmVal = $npm;
            $bestNpmSym = (string)$s['symbol'];
        }
    }
    if ($bestNpmSym !== null && $bestNpmVal > -PHP_FLOAT_MAX) {
        $winners['net_profit_margin'] = $bestNpmSym;
    }

    // 6. D/E Ratio (ต่ำกว่าดีกว่า - หนี้สินต่ำ)
    $bestDeSym = null;
    $bestDeVal = PHP_FLOAT_MAX;
    foreach ($stocks as $s) {
        $de = $s['de_ratio'] !== null ? (float)$s['de_ratio'] : null;
        if ($de !== null && $de >= 0 && $de < $bestDeVal) {
            $bestDeVal = $de;
            $bestDeSym = (string)$s['symbol'];
        }
    }
    if ($bestDeSym !== null && $bestDeVal < PHP_FLOAT_MAX) {
        $winners['de_ratio'] = $bestDeSym;
    }

    // 7. Revenue Growth YoY (สูงกว่าดีกว่า)
    $bestRevGrowthSym = null;
    $bestRevGrowthVal = -PHP_FLOAT_MAX;
    foreach ($stocks as $s) {
        $growth = $s['revenue_growth_yoy'] !== null ? (float)$s['revenue_growth_yoy'] : null;
        if ($growth !== null && $growth > $bestRevGrowthVal) {
            $bestRevGrowthVal = $growth;
            $bestRevGrowthSym = (string)$s['symbol'];
        }
    }
    if ($bestRevGrowthSym !== null && $bestRevGrowthVal > -PHP_FLOAT_MAX) {
        $winners['revenue_growth_yoy'] = $bestRevGrowthSym;
    }

    // 8. AI Total Composite Score (สูงกว่าดีกว่า)
    $bestScoreSym = null;
    $bestScoreVal = -1;
    foreach ($stocks as $s) {
        $score = (int)($s['total_score'] ?? 0);
        if ($score > $bestScoreVal) {
            $bestScoreVal = $score;
            $bestScoreSym = (string)$s['symbol'];
        }
    }
    if ($bestScoreSym !== null && $bestScoreVal > 0) {
        $winners['total_score'] = $bestScoreSym;
    }

    // 9. Market Cap (ขนาดมูลค่ากิจการสูงสุด)
    $bestCapSym = null;
    $bestCapVal = -1.0;
    foreach ($stocks as $s) {
        $cap = (float)($s['market_cap'] ?? 0);
        if ($cap > $bestCapVal) {
            $bestCapVal = $cap;
            $bestCapSym = (string)$s['symbol'];
        }
    }
    if ($bestCapSym !== null && $bestCapVal > 0) {
        $winners['market_cap'] = $bestCapSym;
    }

    return $winners;
}

/**
 * คำนวณค่าเฉลี่ยของกลุ่มหุ้นที่นำมาเปรียบเทียบ (Peer Group Average Benchmark)
 *
 * @param array $stocks
 * @return array
 */
function calculatePeerAverages(array $stocks): array
{
    $count = count($stocks);
    if ($count === 0) {
        return [];
    }

    $sumPe = 0.0; $cntPe = 0;
    $sumPbv = 0.0; $cntPbv = 0;
    $sumRoe = 0.0; $cntRoe = 0;
    $sumYield = 0.0; $cntYield = 0;
    $sumNpm = 0.0; $cntNpm = 0;
    $sumDe = 0.0; $cntDe = 0;
    $sumGrowth = 0.0; $cntGrowth = 0;
    $sumScore = 0; $cntScore = 0;

    foreach ($stocks as $s) {
        if ($s['pe_ratio'] !== null) { $sumPe += (float)$s['pe_ratio']; $cntPe++; }
        if ($s['pbv_ratio'] !== null) { $sumPbv += (float)$s['pbv_ratio']; $cntPbv++; }
        if ($s['roe'] !== null) { $sumRoe += (float)$s['roe']; $cntRoe++; }
        if ($s['dividend_yield'] !== null) { $sumYield += (float)$s['dividend_yield']; $cntYield++; }
        if ($s['net_profit_margin'] !== null) { $sumNpm += (float)$s['net_profit_margin']; $cntNpm++; }
        if ($s['de_ratio'] !== null) { $sumDe += (float)$s['de_ratio']; $cntDe++; }
        if ($s['revenue_growth_yoy'] !== null) { $sumGrowth += (float)$s['revenue_growth_yoy']; $cntGrowth++; }
        if (!empty($s['total_score'])) { $sumScore += (int)$s['total_score']; $cntScore++; }
    }

    return [
        'pe_ratio'           => $cntPe > 0 ? $sumPe / $cntPe : null,
        'pbv_ratio'          => $cntPbv > 0 ? $sumPbv / $cntPbv : null,
        'roe'                => $cntRoe > 0 ? $sumRoe / $cntRoe : null,
        'dividend_yield'     => $cntYield > 0 ? $sumYield / $cntYield : null,
        'net_profit_margin'  => $cntNpm > 0 ? $sumNpm / $cntNpm : null,
        'de_ratio'           => $cntDe > 0 ? $sumDe / $cntDe : null,
        'revenue_growth_yoy' => $cntGrowth > 0 ? $sumGrowth / $cntGrowth : null,
        'total_score'        => $cntScore > 0 ? (int)round($sumScore / $cntScore) : null,
    ];
}
