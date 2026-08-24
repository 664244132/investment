<?php
declare(strict_types=1);

/**
 * api/stock_quote.php
 * SmartPort AI - Live Real-Time Stock Quote API Endpoint
 * ดึงราคาตลาดสดของหุ้นรายตัวแบบ Real-Time จาก Live Market Gateway
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/market_api.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=30');

$symbol = strtoupper(trim((string)($_GET['symbol'] ?? '')));

if ($symbol === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing stock symbol'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = getDbConnection();
$stock = getStockBySymbol($pdo, $symbol, true);

if (!$stock) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Stock not found'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success'        => true,
    'symbol'         => $stock['symbol'],
    'name_th'        => $stock['name_th'],
    'name_en'        => $stock['name_en'],
    'market'         => $stock['market'],
    'sector'         => $stock['sector'],
    'current_price'  => (float)$stock['current_price'],
    'previous_close' => (float)$stock['previous_close'],
    'price_change'   => (float)$stock['price_change'],
    'percent_change' => (float)$stock['percent_change'],
    'day_high'       => (float)$stock['day_high'],
    'day_low'        => (float)$stock['day_low'],
    'volume'         => (int)$stock['volume'],
    'timestamp'      => time(),
    'updated_at'     => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
