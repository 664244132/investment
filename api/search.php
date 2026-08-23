<?php
declare(strict_types=1);

/**
 * api/search.php
 * SmartPort AI - Global Stock Search & Autocomplete API
 * ค้นหาหุ้นจากทั้งฐานข้อมูลในเครื่องและดึงหุ้นสดจากตลาดโลก
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/market_api.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim((string)($_GET['q'] ?? ''));

if ($query === '') {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

$pdo = getDbConnection();
$results = [];
$seenSymbols = [];

// 1. ค้นหาจากฐานข้อมูลภายในเครื่องก่อน (Local Database First)
$localMatches = getAllStocks($pdo, null, $query);
foreach ($localMatches as $s) {
    $sym = strtoupper($s['symbol']);
    $seenSymbols[$sym] = true;
    $results[] = [
        'symbol'        => $sym,
        'name'          => $s['name_th'] ?: $s['name_en'],
        'market'        => $s['market'],
        'current_price' => (float)$s['current_price'],
        'price_change'  => (float)$s['price_change'],
        'percent_change'=> (float)$s['percent_change'],
        'source'        => 'local'
    ];
}

// 2. หากผลลัพธ์ในเครื่องมีน้อย หรือผู้ใช้ค้นหาหุ้นสากล ให้เรียก Live Global Search
if (count($results) < 6) {
    $globalMatches = searchLiveGlobalMarket($query);
    foreach ($globalMatches as $gm) {
        $sym = strtoupper($gm['symbol']);
        if (!isset($seenSymbols[$sym])) {
            $seenSymbols[$sym] = true;
            $results[] = [
                'symbol'        => $sym,
                'name'          => $gm['name'],
                'market'        => $gm['exchange'],
                'current_price' => null,
                'price_change'  => null,
                'percent_change'=> null,
                'source'        => 'live_market'
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'query'   => $query,
    'count'   => count($results),
    'results' => array_slice($results, 0, 8)
], JSON_UNESCAPED_UNICODE);
