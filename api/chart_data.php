<?php
declare(strict_types=1);

/**
 * api/chart_data.php
 * SmartPort AI - Live Market Chart Historical & Intraday Data Endpoint (100% Real-Time API)
 * รองรับ Timeframe: 1H (รายชั่วโมง), 1D (รายวัน), 1W (รายอาทิตย์), 1M (1 เดือน), 1Y (1 ปี), 5Y (5 ปี)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/market_api.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=30');

$symbol = strtoupper(trim((string)($_GET['symbol'] ?? '')));
$timeframe = strtoupper(trim((string)($_GET['timeframe'] ?? '1M')));

if ($symbol === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing stock symbol'], JSON_UNESCAPED_UNICODE);
    exit;
}

$chartData = fetchLiveChartData($symbol, $timeframe);

// Fallback to local DB if API returned empty
if (empty($chartData)) {
    $pdo = getDbConnection();
    $stock = getStockBySymbol($pdo, $symbol, false);
    if ($stock) {
        $localHistory = getStockPriceHistory($pdo, (int)$stock['id'], 30);
        foreach ($localHistory as $lh) {
            $chartData[] = [
                'timestamp'    => strtotime($lh['trade_date']),
                'date'         => $lh['trade_date'],
                'display_date' => date('d M', strtotime($lh['trade_date'])),
                'open'         => (float)$lh['open_price'],
                'high'         => (float)$lh['high_price'],
                'low'          => (float)$lh['low_price'],
                'close'        => (float)$lh['close_price'],
                'volume'       => (int)$lh['volume']
            ];
        }
    }
}

echo json_encode([
    'success'   => true,
    'symbol'    => $symbol,
    'timeframe' => $timeframe,
    'count'     => count($chartData),
    'data'      => $chartData
], JSON_UNESCAPED_UNICODE);
