<?php
declare(strict_types=1);

/**
 * functions/watchlist.php
 * SmartPort AI - Watchlist & Price Target Alert Engine
 * จัดการข้อมูลรายการหุ้นโปรด การแจ้งเตือนราคาเป้าหมาย และบันทึกโน้ตส่วนตัว
 * ปฏิบัติตามมาตรฐาน PDO Prepared Statements, Strict Types และ Early Return Pattern
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * ดึงรายการหุ้นทั้งหมดใน Watchlist ของผู้ใช้ พร้อมอัตราส่วนและสถานะราคาเป้าหมาย
 *
 * @param PDO $pdo
 * @param int $userId
 * @return array
 */
function getUserWatchlist(PDO $pdo, int $userId): array
{
    // Guard Clause 1: หาก userId ไม่ถูกต้อง ให้ส่งคืน array ว่างทันที
    if ($userId <= 0) {
        return [];
    }

    $sql = "SELECT w.id AS watchlist_id, w.user_id, w.stock_id, 
                   w.target_buy_price, w.target_sell_price, w.notes, w.created_at AS bookmarked_at,
                   s.symbol, s.name_th, s.name_en, s.market, s.sector, 
                   s.current_price, s.previous_close, s.price_change, s.percent_change,
                   s.day_high, s.day_low, s.volume, s.market_cap,
                   fm.pe_ratio, fm.eps, fm.roe, fm.dividend_yield,
                   ai.total_score, ai.recommendation, ai.summary_th
            FROM user_watchlists w
            INNER JOIN stocks s ON w.stock_id = s.id
            LEFT JOIN (
                SELECT fm1.stock_id, fm1.pe_ratio, fm1.eps, fm1.roe, fm1.dividend_yield
                FROM financial_metrics fm1
                WHERE fm1.id IN (
                    SELECT MAX(fm2.id)
                    FROM financial_metrics fm2
                    GROUP BY fm2.stock_id
                )
            ) fm ON s.id = fm.stock_id
            LEFT JOIN (
                SELECT ai1.stock_id, ai1.total_score, ai1.recommendation, ai1.summary_th
                FROM ai_analyses ai1
                WHERE ai1.id IN (
                    SELECT MAX(ai2.id)
                    FROM ai_analyses ai2
                    GROUP BY ai2.stock_id
                )
            ) ai ON s.id = ai.stock_id
            WHERE w.user_id = :user_id
            ORDER BY w.created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $items = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Watchlist query error: ' . $e->getMessage());
        return [];
    }

    // ประมวลผลและคำนวณการแจ้งเตือนราคาเป้าหมาย (Price Target Proximity Calculation)
    $result = [];
    foreach ($items as $item) {
        $currentPrice = (float)$item['current_price'];
        $targetBuy    = $item['target_buy_price'] !== null ? (float)$item['target_buy_price'] : null;
        $targetSell   = $item['target_sell_price'] !== null ? (float)$item['target_sell_price'] : null;

        $buyStatus  = 'none'; // 'hit', 'near', 'none'
        $sellStatus = 'none'; // 'hit', 'near', 'none'
        $buyDiffPct  = 0.0;
        $sellDiffPct = 0.0;

        // 1. ตรวจสอบราคาเป้าหมายซื้อ (Target Buy Price)
        if ($targetBuy !== null && $targetBuy > 0) {
            // คำนวณส่วนต่างว่าราคาปัจจุบันห่างจากเป้าซื้อกี่เปอร์เซ็นต์
            $buyDiffPct = (($currentPrice - $targetBuy) / $targetBuy) * 100;
            if ($currentPrice <= $targetBuy) {
                $buyStatus = 'hit'; // ราคาลงมาถึงหรือต่ำกว่าเป้าหมายซื้อแล้ว
            } elseif ($buyDiffPct <= 3.0) {
                $buyStatus = 'near'; // ราคาอยู่ห่างไม่เกิน +3% จากเป้าซื้อ (ใกล้เข้าเขตซื้อ)
            }
        }

        // 2. ตรวจสอบราคาเป้าหมายขาย (Target Sell Price)
        if ($targetSell !== null && $targetSell > 0) {
            // คำนวณส่วนต่างว่าราคาปัจจุบันห่างจากเป้าขายกี่เปอร์เซ็นต์
            $sellDiffPct = (($currentPrice - $targetSell) / $targetSell) * 100;
            if ($currentPrice >= $targetSell) {
                $sellStatus = 'hit'; // ราคาขึ้นไปถึงหรือเกินเป้าหมายขายแล้ว
            } elseif ($sellDiffPct >= -3.0 && $sellDiffPct < 0) {
                $sellStatus = 'near'; // ราคาอยู่ห่างไม่เกิน -3% จากเป้าขาย (ใกล้เข้าเขตขายทำกำไร)
            }
        }

        $item['buy_status']   = $buyStatus;
        $item['sell_status']  = $sellStatus;
        $item['buy_diff_pct'] = $buyDiffPct;
        $item['sell_diff_pct']= $sellDiffPct;

        $result[] = $item;
    }

    return $result;
}

/**
 * ตรวจสอบว่าหุ้นตัวนี้อยู่ใน Watchlist ของผู้ใช้หรือไม่
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $stockId
 * @return bool
 */
function isStockInWatchlist(PDO $pdo, int $userId, int $stockId): bool
{
    if ($userId <= 0 || $stockId <= 0) {
        return false;
    }

    $sql = "SELECT id FROM user_watchlists WHERE user_id = :user_id AND stock_id = :stock_id LIMIT 1";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':stock_id' => $stockId]);
        return (bool)$stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * สลับสถานะ Bookmark หุ้นเข้า/ออกจาก Watchlist (Toggle)
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $stockId
 * @return array
 */
function toggleWatchlist(PDO $pdo, int $userId, int $stockId): array
{
    if ($userId <= 0) {
        return ['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อนใช้งาน Watchlist', 'is_bookmarked' => false];
    }
    if ($stockId <= 0) {
        return ['success' => false, 'message' => 'ไม่พบข้อมูลหุ้นที่ระบุ', 'is_bookmarked' => false];
    }

    // ตรวจสอบว่ามีอยู่แล้วหรือไม่
    if (isStockInWatchlist($pdo, $userId, $stockId)) {
        // ลบออกจาก Watchlist
        $delSql = "DELETE FROM user_watchlists WHERE user_id = :user_id AND stock_id = :stock_id";
        $stmt = $pdo->prepare($delSql);
        $stmt->execute([':user_id' => $userId, ':stock_id' => $stockId]);
        return [
            'success' => true,
            'status' => 'removed',
            'is_bookmarked' => false,
            'message' => 'นำหุ้นออกจาก Watchlist เรียบร้อยแล้ว'
        ];
    }

    // เพิ่มเข้า Watchlist
    $insSql = "INSERT INTO user_watchlists (user_id, stock_id) VALUES (:user_id, :stock_id)";
    try {
        $stmt = $pdo->prepare($insSql);
        $stmt->execute([':user_id' => $userId, ':stock_id' => $stockId]);
        return [
            'success' => true,
            'status' => 'added',
            'is_bookmarked' => true,
            'message' => 'บันทึกหุ้นลงใน Watchlist เรียบร้อยแล้ว'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage(),
            'is_bookmarked' => false
        ];
    }
}

/**
 * อัปเดตราคาเป้าหมายและบันทึกโน้ตของหุ้นใน Watchlist
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $stockId
 * @param float|null $targetBuy
 * @param float|null $targetSell
 * @param string|null $notes
 * @return bool
 */
function updateWatchlistTarget(
    PDO $pdo,
    int $userId,
    int $stockId,
    ?float $targetBuy,
    ?float $targetSell,
    ?string $notes
): bool {
    if ($userId <= 0 || $stockId <= 0) {
        return false;
    }

    $sql = "UPDATE user_watchlists 
            SET target_buy_price = :target_buy,
                target_sell_price = :target_sell,
                notes = :notes
            WHERE user_id = :user_id AND stock_id = :stock_id";

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':target_buy'  => ($targetBuy !== null && $targetBuy > 0) ? $targetBuy : null,
            ':target_sell' => ($targetSell !== null && $targetSell > 0) ? $targetSell : null,
            ':notes'       => ($notes !== null && trim($notes) !== '') ? trim($notes) : null,
            ':user_id'     => $userId,
            ':stock_id'    => $stockId
        ]);
    } catch (PDOException $e) {
        error_log('Update watchlist error: ' . $e->getMessage());
        return false;
    }
}

/**
 * ลบหุ้นออกจาก Watchlist โดยตรง
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $stockId
 * @return bool
 */
function removeFromWatchlist(PDO $pdo, int $userId, int $stockId): bool
{
    if ($userId <= 0 || $stockId <= 0) {
        return false;
    }

    $sql = "DELETE FROM user_watchlists WHERE user_id = :user_id AND stock_id = :stock_id";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':stock_id' => $stockId]);
    } catch (PDOException $e) {
        return false;
    }
}
