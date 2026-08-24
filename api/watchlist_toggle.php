<?php
declare(strict_types=1);

/**
 * api/watchlist_toggle.php
 * SmartPort AI - Watchlist AJAX Action Endpoint
 * รับส่งคำสั่ง Toggle Bookmark, Update Target Price และ Remove หุ้นจาก Watchlist
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/watchlist.php';

startAppSession();

// Header สำหรับ JSON Response
header('Content-Type: application/json; charset=UTF-8');

// Guard Clause 1: ต้องเป็นคำขอแบบ POST เท่านั้น
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method Not Allowed'], 405);
}

// Guard Clause 2: ผู้ใช้ต้องเข้าสู่ระบบแล้ว
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อนใช้งาน Watchlist', 'require_login' => true], 401);
}

$user = getCurrentUser();
$userId = (int)($user['id'] ?? 0);
if ($userId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้งาน'], 401);
}

$pdo = getDbConnection();

// อ่านข้อมูล JSON Payload หรือ Form Post
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

$action  = (string)($data['action'] ?? 'toggle');
$stockId = (int)($data['stock_id'] ?? 0);

// Guard Clause 3: ตรวจสอบ stock_id
if ($stockId <= 0) {
    jsonResponse(['success' => false, 'message' => 'รหัสหุ้นไม่ถูกต้อง'], 400);
}

// 1. จัดการคำสั่ง Toggle Bookmark (เพิ่ม/ลบ)
if ($action === 'toggle') {
    $result = toggleWatchlist($pdo, $userId, $stockId);
    $statusCode = $result['success'] ? 200 : 400;
    jsonResponse($result, $statusCode);
}

// 2. จัดการคำสั่ง Update Target Price & Notes
if ($action === 'update_target') {
    $targetBuy  = isset($data['target_buy']) && $data['target_buy'] !== '' ? (float)$data['target_buy'] : null;
    $targetSell = isset($data['target_sell']) && $data['target_sell'] !== '' ? (float)$data['target_sell'] : null;
    $notes      = isset($data['notes']) ? (string)$data['notes'] : null;

    $success = updateWatchlistTarget($pdo, $userId, $stockId, $targetBuy, $targetSell, $notes);
    if (!$success) {
        jsonResponse(['success' => false, 'message' => 'บันทึกราคาเป้าหมายไม่สำเร็จ'], 500);
    }

    jsonResponse([
        'success' => true,
        'message' => 'บันทึกราคาเป้าหมายและโน้ตเรียบร้อยแล้ว',
        'target_buy' => $targetBuy,
        'target_sell' => $targetSell,
        'notes' => $notes
    ]);
}

// 3. จัดการคำสั่ง Remove หุ้นออกจาก Watchlist
if ($action === 'remove') {
    $success = removeFromWatchlist($pdo, $userId, $stockId);
    if (!$success) {
        jsonResponse(['success' => false, 'message' => 'ลบหุ้นออกจาก Watchlist ไม่สำเร็จ'], 500);
    }

    jsonResponse([
        'success' => true,
        'message' => 'นำหุ้นออกจาก Watchlist เรียบร้อยแล้ว',
        'stock_id' => $stockId
    ]);
}

// Action อื่นๆ ที่ไม่รองรับ
jsonResponse(['success' => false, 'message' => 'Action ไม่ถูกต้อง'], 400);
