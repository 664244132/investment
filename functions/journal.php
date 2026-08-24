<?php
declare(strict_types=1);

/**
 * functions/journal.php
 * SmartPort AI - Trading Journal & Performance Analytics Engine
 * บันทึกเหตุผลการเทรด วิเคราะห์ Win Rate, Profit Factor, และประสิทธิภาพตามกลยุทธ์
 * ปฏิบัติตามมาตรฐาน PDO Prepared Statements, Strict Types และ Early Return Pattern
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * ดึงรายการบันทึกการเทรด (Trading Journal) ของผู้ใช้
 *
 * @param PDO $pdo
 * @param int $userId
 * @param string|null $strategyTag กรองเฉพาะกลยุทธ์ (ถ้ามี)
 * @return array
 */
function getUserJournalEntries(PDO $pdo, int $userId, ?string $strategyTag = null): array
{
    if ($userId <= 0) {
        return [];
    }

    try {
        $sql = "SELECT j.id AS journal_id, j.transaction_id, j.strategy_tag, j.rationale, j.lessons_learned, j.created_at AS journal_date,
                       t.transaction_type, t.quantity, t.price, t.total_amount, t.fee, t.realized_pnl, t.created_at AS trade_date,
                       s.id AS stock_id, s.symbol, s.name_th, s.name_en, s.market, s.sector, s.current_price
                FROM trading_journals j
                INNER JOIN transactions t ON j.transaction_id = t.id
                INNER JOIN stocks s ON t.stock_id = s.id
                WHERE j.user_id = :user_id";

        $params = [':user_id' => $userId];

        if ($strategyTag !== null && $strategyTag !== 'all' && $strategyTag !== '') {
            $sql .= " AND j.strategy_tag = :tag";
            $params[':tag'] = $strategyTag;
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // หากตาราง trading_journals ยังไม่ถูกสร้าง ให้คืนค่าว่าง
        return [];
    }
}

/**
 * บันทึกหรือแก้ไขบทเรียนที่ได้รับ (Lessons Learned) หลังปิดออเดอร์
 *
 * @param PDO $pdo
 * @param int $journalId
 * @param int $userId
 * @param string $lessons
 * @return bool
 */
function updateTradeJournalLesson(PDO $pdo, int $journalId, int $userId, string $lessons): bool
{
    if ($journalId <= 0 || $userId <= 0) {
        return false;
    }

    try {
        $sql = "UPDATE trading_journals 
                SET lessons_learned = :lessons, updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':lessons' => trim($lessons),
            ':id'      => $journalId,
            ':user_id' => $userId
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * คำนวณสถิติและตัวชี้วัดประสิทธิภาพการเทรด (Trading Performance Analytics)
 *
 * @param PDO $pdo
 * @param int $portfolioId
 * @param int $userId
 * @return array
 */
function calculateTradingPerformanceMetrics(PDO $pdo, int $portfolioId, int $userId): array
{
    $defaultMetrics = [
        'total_closed_trades' => 0,
        'winning_trades'      => 0,
        'losing_trades'       => 0,
        'break_even_trades'   => 0,
        'win_rate'            => 0.0,
        'total_realized_pnl'  => 0.0,
        'gross_profit'        => 0.0,
        'gross_loss'          => 0.0,
        'profit_factor'       => 0.0,
        'best_trade'          => 0.0,
        'worst_trade'         => 0.0,
        'avg_win'             => 0.0,
        'avg_loss'            => 0.0,
        'strategy_breakdown'  => []
    ];

    if ($portfolioId <= 0) {
        return $defaultMetrics;
    }

    // 1. ดึงออเดอร์ปิด (SELL) ทั้งหมดที่มี Realized P&L
    $sql = "SELECT t.id, t.stock_id, t.quantity, t.price, t.realized_pnl, t.created_at,
                   COALESCE(j.strategy_tag, 'General') AS strategy_tag,
                   s.symbol
            FROM transactions t
            LEFT JOIN trading_journals j ON t.id = j.transaction_id
            INNER JOIN stocks s ON t.stock_id = s.id
            WHERE t.portfolio_id = :p_id AND t.transaction_type = 'SELL' AND t.realized_pnl IS NOT NULL
            ORDER BY t.created_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':p_id' => $portfolioId]);
    $closedTrades = $stmt->fetchAll();

    if (empty($closedTrades)) {
        return $defaultMetrics;
    }

    $totalClosed = count($closedTrades);
    $winningCount = 0;
    $losingCount = 0;
    $breakEvenCount = 0;
    $totalPnl = 0.0;
    $grossProfit = 0.0;
    $grossLoss = 0.0;
    $bestTrade = -PHP_FLOAT_MAX;
    $worstTrade = PHP_FLOAT_MAX;

    $strategies = [];

    foreach ($closedTrades as $trade) {
        $pnl = (float)$trade['realized_pnl'];
        $totalPnl += $pnl;

        if ($pnl > 0) {
            $winningCount++;
            $grossProfit += $pnl;
        } elseif ($pnl < 0) {
            $losingCount++;
            $grossLoss += abs($pnl);
        } else {
            $breakEvenCount++;
        }

        if ($pnl > $bestTrade) {
            $bestTrade = $pnl;
        }
        if ($pnl < $worstTrade) {
            $worstTrade = $pnl;
        }

        // เก็บสถิติตามกลยุทธ์
        $tag = (string)$trade['strategy_tag'];
        if (!isset($strategies[$tag])) {
            $strategies[$tag] = [
                'tag'          => $tag,
                'total_trades' => 0,
                'wins'         => 0,
                'losses'       => 0,
                'total_pnl'    => 0.0
            ];
        }
        $strategies[$tag]['total_trades']++;
        if ($pnl > 0) {
            $strategies[$tag]['wins']++;
        } elseif ($pnl < 0) {
            $strategies[$tag]['losses']++;
        }
        $strategies[$tag]['total_pnl'] += $pnl;
    }

    $winRate = ($totalClosed > 0) ? ($winningCount / $totalClosed) * 100 : 0.0;
    $profitFactor = ($grossLoss > 0) ? ($grossProfit / $grossLoss) : ($grossProfit > 0 ? 99.99 : 0.0);
    $avgWin = ($winningCount > 0) ? ($grossProfit / $winningCount) : 0.0;
    $avgLoss = ($losingCount > 0) ? ($grossLoss / $losingCount) : 0.0;

    // คำนวณ Win rate สำหรับแต่ละ Strategy Breakdown
    foreach ($strategies as &$strat) {
        $strat['win_rate'] = ($strat['total_trades'] > 0) ? ($strat['wins'] / $strat['total_trades']) * 100 : 0.0;
    }
    unset($strat);

    return [
        'total_closed_trades' => $totalClosed,
        'winning_trades'      => $winningCount,
        'losing_trades'       => $losingCount,
        'break_even_trades'   => $breakEvenCount,
        'win_rate'            => $winRate,
        'total_realized_pnl'  => $totalPnl,
        'gross_profit'        => $grossProfit,
        'gross_loss'          => $grossLoss,
        'profit_factor'       => $profitFactor,
        'best_trade'          => $bestTrade > -PHP_FLOAT_MAX ? $bestTrade : 0.0,
        'worst_trade'         => $worstTrade < PHP_FLOAT_MAX ? $worstTrade : 0.0,
        'avg_win'             => $avgWin,
        'avg_loss'            => $avgLoss,
        'strategy_breakdown'  => array_values($strategies)
    ];
}
