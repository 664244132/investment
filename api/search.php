<?php
declare(strict_types=1);

/**
 * api/search.php
 * SmartPort AI - Global Stock Search & Autocomplete API (100% Live Market Connected)
 * ค้นหาหุ้นได้ทั่วโลก ทั้งตลาดไทย (SET) และตลาดสากล (NASDAQ, NYSE, S&P 500, Global Exchanges)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/market_api.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=30');

$query = trim((string)($_GET['q'] ?? ''));

if ($query === '') {
    echo json_encode(['success' => true, 'results' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = getDbConnection();
$results = [];
$seenSymbols = [];

// 1. ค้นหาจาก Live Global Market Search API แบบ Real-Time
$globalMatches = searchLiveGlobalMarket($query);

// รวบรวมสัญลักษณ์ทั้งหมดเพื่อดึงข้อมูลจาก DB ใน Batch Query เดียว (O(1) Roundtrip)
$symbolsToFetch = [];
foreach ($globalMatches as $gm) {
    $symbolsToFetch[] = strtoupper($gm['symbol']);
}

$localStockMap = [];
if (!empty($symbolsToFetch)) {
    $placeholders = implode(',', array_fill(0, count($symbolsToFetch), '?'));
    $bStmt = $pdo->prepare("SELECT symbol, current_price, price_change, percent_change, market FROM stocks WHERE symbol IN ($placeholders)");
    $bStmt->execute($symbolsToFetch);
    while ($row = $bStmt->fetch()) {
        $localStockMap[strtoupper((string)$row['symbol'])] = $row;
    }
}

foreach ($globalMatches as $gm) {
    $sym = strtoupper($gm['symbol']);
    if (!isset($seenSymbols[$sym])) {
        $seenSymbols[$sym] = true;
        $localInfo = $localStockMap[$sym] ?? null;

        $results[] = [
            'symbol'        => $sym,
            'name'          => $gm['name'],
            'market'        => $localInfo['market'] ?? $gm['exchange'],
            'sector'        => $gm['sector'] ?? 'General',
            'current_price' => $localInfo ? (float)$localInfo['current_price'] : null,
            'price_change'  => $localInfo ? (float)$localInfo['price_change'] : null,
            'percent_change'=> $localInfo ? (float)$localInfo['percent_change'] : null,
            'source'        => 'live_global_api'
        ];
    }
}

// 2. ค้นหาเพิ่มเติมจากฐานข้อมูลภายในเครื่อง (สำหรับชื่อภาษาไทย เช่น ปตท, เดลต้า)
$localMatches = getAllStocks($pdo, null, $query);
foreach ($localMatches as $s) {
    $sym = strtoupper($s['symbol']);
    if (!isset($seenSymbols[$sym])) {
        $seenSymbols[$sym] = true;
        $results[] = [
            'symbol'        => $sym,
            'name'          => $s['name_th'] ?: $s['name_en'],
            'market'        => $s['market'],
            'sector'        => $s['sector'] ?? 'General',
            'current_price' => (float)$s['current_price'],
            'price_change'  => (float)$s['price_change'],
            'percent_change'=> (float)$s['percent_change'],
            'source'        => 'local'
        ];
    }
}

echo json_encode([
    'success' => true,
    'query'   => $query,
    'count'   => count($results),
    'results' => array_slice($results, 0, 10)
], JSON_UNESCAPED_UNICODE);

