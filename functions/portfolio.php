<?php
declare(strict_types=1);

/**
 * functions/portfolio.php
 * SmartPort AI - Portfolio Simulation & Trade Execution Engine
 * รองรับการจำลองซื้อ-ขายหุ้นตามราคาตลาดจริงแบบ Real-time คำนวณต้นทุนเฉลี่ย และผลกำไร-ขาดทุน
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/helpers.php';

/**
 * ดึงข้อมูลสรุปพอร์ตการลงทุนจำลองของผู้ใช้ พร้อมคำนวณมูลค่าสินทรัพย์รวมและ P&L
 *
 * @param PDO $pdo
 * @param int $userId
 * @return array|null
 */
function getUserPortfolio(PDO $pdo, int $userId): ?array
{
    // 1. ดึงข้อมูลพอร์ตหลักของผู้ใช้
    $sql = "SELECT id, user_id, portfolio_name, initial_cash, cash_balance, created_at, updated_at
            FROM portfolios
            WHERE user_id = :user_id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $portfolio = $stmt->fetch();

    // Guard Clause 1: หากยังไม่มีพอร์ต ให้ส่งคืน null
    if (!$portfolio) {
        return null;
    }

    $portfolioId = (int)$portfolio['id'];
    $cashBalance = (float)$portfolio['cash_balance'];
    $initialCash = (float)$portfolio['initial_cash'];

    // 2. ดึงรายการหุ้นที่ถือครองเพื่อคำนวณมูลค่าตลาดสด (Market Value)
    $holdings = getPortfolioHoldings($pdo, $portfolioId);

    $totalStocksValue = 0.0;
    $totalCostValue   = 0.0;

    foreach ($holdings as $item) {
        $totalStocksValue += (float)$item['market_value'];
        $totalCostValue   += (float)$item['cost_value'];
    }

    $totalPortfolioValue = $cashBalance + $totalStocksValue;
    $totalUnrealizedPnl  = $totalStocksValue - $totalCostValue;
    $totalProfitLoss     = $totalPortfolioValue - $initialCash;
    $totalReturnPercent  = ($initialCash > 0) ? ($totalProfitLoss / $initialCash) * 100 : 0.0;

    $portfolio['total_stocks_value']   = $totalStocksValue;
    $portfolio['total_cost_value']     = $totalCostValue;
    $portfolio['total_portfolio_value']= $totalPortfolioValue;
    $portfolio['total_unrealized_pnl'] = $totalUnrealizedPnl;
    $portfolio['total_profit_loss']    = $totalProfitLoss;
    $portfolio['total_return_percent'] = $totalReturnPercent;
    $portfolio['total_pnl']            = $totalProfitLoss;
    $portfolio['total_pnl_percent']    = $totalReturnPercent;
    $portfolio['holdings_count']       = count($holdings);

    return $portfolio;
}

/**
 * ดึงรายการหุ้นที่ถือครองในพอร์ตจำลอง พร้อมคำนวณกำไร/ขาดทุนแต่ละตัว
 *
 * @param PDO $pdo
 * @param int $portfolioId
 * @return array
 */
function getPortfolioHoldings(PDO $pdo, int $portfolioId): array
{
    $sql = "SELECT pi.id, pi.portfolio_id, pi.stock_id, pi.quantity, pi.average_price,
                   s.symbol, s.name_th, s.name_en, s.market, s.sector, s.current_price, s.percent_change
            FROM portfolio_items pi
            INNER JOIN stocks s ON pi.stock_id = s.id
            WHERE pi.portfolio_id = :portfolio_id AND pi.quantity > 0
            ORDER BY (pi.quantity * s.current_price) DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':portfolio_id' => $portfolioId]);
    $items = $stmt->fetchAll();

    $result = [];
    foreach ($items as $item) {
        $qty          = (int)$item['quantity'];
        $avgPrice     = (float)$item['average_price'];
        $currentPrice = (float)$item['current_price'];

        $costValue    = $qty * $avgPrice;
        $marketValue  = $qty * $currentPrice;
        $unrealizedPnl= $marketValue - $costValue;
        $returnPct    = ($costValue > 0) ? ($unrealizedPnl / $costValue) * 100 : 0.0;

        $item['cost_value']         = $costValue;
        $item['market_value']       = $marketValue;
        $item['unrealized_pnl']     = $unrealizedPnl;
        $item['return_percent']     = $returnPct;
        $item['unrealized_pnl_pct'] = $returnPct;

        $result[] = $item;
    }

    return $result;
}

/**
 * ดึงประวัติรายการซื้อ-ขายจำลอง (Transactions)
 *
 * @param PDO $pdo
 * @param int $portfolioId
 * @param int $limit
 * @return array
 */
function getPortfolioTransactions(PDO $pdo, int $portfolioId, int $limit = 50): array
{
    $sql = "SELECT t.id, t.transaction_type, t.quantity, t.price, t.total_amount, t.fee, t.realized_pnl, t.created_at,
                   s.symbol, s.name_en, s.name_th
            FROM transactions t
            INNER JOIN stocks s ON t.stock_id = s.id
            WHERE t.portfolio_id = :portfolio_id
            ORDER BY t.created_at DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':portfolio_id', $portfolioId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * ส่งคำสั่งจำลองการซื้อ หรือ ขายหุ้น (Execute Simulated Trade)
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $stockId
 * @param string $type 'BUY' หรือ 'SELL'
 * @param int $quantity จำนวนหุ้นที่ต้องการซื้อขาย
 * @param string $strategyTag กลยุทธ์การลงทุน เช่น Value Investing, Growth Stock, etc.
 * @param string $rationale เหตุผลในการตัดสินใจซื้อ-ขาย
 * @return array ['success' => bool, 'message' => string]
 */
function executeSimulatedTrade(
    PDO $pdo,
    int $userId,
    int $stockId,
    string $type,
    int $quantity,
    string $strategyTag = 'Value Investing',
    string $rationale = ''
): array {
    $type = strtoupper(trim($type));

    // Guard Clause 1: ตรวจสอบจำนวนหุ้น
    if ($quantity <= 0) {
        return ['success' => false, 'message' => 'จำนวนหุ้นต้องมากกว่า 0'];
    }

    // Guard Clause 2: ตรวจสอบประเภทคำสั่ง
    if ($type !== 'BUY' && $type !== 'SELL') {
        return ['success' => false, 'message' => 'ประเภทคำสั่งไม่ถูกต้อง (ต้องเป็น BUY หรือ SELL)'];
    }

    // 1. ดึงข้อมูลพอร์ตของผู้ใช้
    $portfolioSql = "SELECT id, cash_balance FROM portfolios WHERE user_id = :user_id LIMIT 1";
    $pStmt = $pdo->prepare($portfolioSql);
    $pStmt->execute([':user_id' => $userId]);
    $portfolio = $pStmt->fetch();

    if (!$portfolio) {
        return ['success' => false, 'message' => 'ไม่พบข้อมูลพอร์ตการลงทุนของผู้ใช้'];
    }

    $portfolioId = (int)$portfolio['id'];
    $cashBalance = (float)$portfolio['cash_balance'];

    // 2. ดึงราคาปัจจุบันของหุ้น
    $stockSql = "SELECT id, symbol, current_price FROM stocks WHERE id = :stock_id LIMIT 1";
    $sStmt = $pdo->prepare($stockSql);
    $sStmt->execute([':stock_id' => $stockId]);
    $stock = $sStmt->fetch();

    if (!$stock) {
        return ['success' => false, 'message' => 'ไม่พบข้อมูลหุ้นที่เลือกในระบบ'];
    }

    $price = (float)$stock['current_price'];
    $subtotal = $quantity * $price;
    $fee = $subtotal * SIMULATED_BROKER_FEE_RATE; // ค่าคอมมิชชัน 0.157%

    try {
        $pdo->beginTransaction();

        if ($type === 'BUY') {
            $totalCost = $subtotal + $fee;

            // Guard Clause: ตรวจสอบเงินสดคงเหลือ
            if ($cashBalance < $totalCost) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'message' => sprintf(
                        'เงินสดในพอร์ตไม่เพียงพอ (ต้องการ %s บาท แต่มีคงเหลือ %s บาท)',
                        number_format($totalCost, 2),
                        number_format($cashBalance, 2)
                    )
                ];
            }

            // หักเงินสดออกจากพอร์ต
            $newCash = $cashBalance - $totalCost;
            $updateCashSql = "UPDATE portfolios SET cash_balance = :cash, updated_at = NOW() WHERE id = :id";
            $ucStmt = $pdo->prepare($updateCashSql);
            $ucStmt->execute([':cash' => $newCash, ':id' => $portfolioId]);

            // อัปเดตรายการหุ้นที่ถือครอง (คำนวณต้นทุนเฉลี่ยใหม่ Average Cost)
            $checkHoldSql = "SELECT id, quantity, average_price FROM portfolio_items 
                             WHERE portfolio_id = :p_id AND stock_id = :s_id LIMIT 1";
            $chStmt = $pdo->prepare($checkHoldSql);
            $chStmt->execute([':p_id' => $portfolioId, ':s_id' => $stockId]);
            $existingHold = $chStmt->fetch();

            if ($existingHold) {
                $oldQty   = (int)$existingHold['quantity'];
                $oldAvg   = (float)$existingHold['average_price'];
                $newQty   = $oldQty + $quantity;
                $newAvg   = (($oldQty * $oldAvg) + ($quantity * $price)) / $newQty;

                $updateHoldSql = "UPDATE portfolio_items 
                                  SET quantity = :qty, average_price = :avg, updated_at = NOW() 
                                  WHERE id = :id";
                $uhStmt = $pdo->prepare($updateHoldSql);
                $uhStmt->execute([':qty' => $newQty, ':avg' => $newAvg, ':id' => $existingHold['id']]);
            } else {
                $insertHoldSql = "INSERT INTO portfolio_items (portfolio_id, stock_id, quantity, average_price) 
                                  VALUES (:p_id, :s_id, :qty, :avg)";
                $ihStmt = $pdo->prepare($insertHoldSql);
                $ihStmt->execute([
                    ':p_id' => $portfolioId,
                    ':s_id' => $stockId,
                    ':qty'  => $quantity,
                    ':avg'  => $price
                ]);
            }

            // บันทึกธุรกรรม
            $trxSql = "INSERT INTO transactions (portfolio_id, stock_id, transaction_type, quantity, price, total_amount, fee, realized_pnl) 
                       VALUES (:p_id, :s_id, 'BUY', :qty, :price, :amount, :fee, NULL)";
            $tStmt = $pdo->prepare($trxSql);
            $tStmt->execute([
                ':p_id'   => $portfolioId,
                ':s_id'   => $stockId,
                ':qty'    => $quantity,
                ':price'  => $price,
                ':amount' => $subtotal,
                ':fee'    => $fee
            ]);
            $trxId = (int)$pdo->lastInsertId();

            // บันทึก Trading Journal (ถ้ามีตาราง trading_journals)
            try {
                $jSql = "INSERT INTO trading_journals (transaction_id, user_id, strategy_tag, rationale, lessons_learned) 
                         VALUES (:trx_id, :u_id, :tag, :rationale, NULL)";
                $jStmt = $pdo->prepare($jSql);
                $jStmt->execute([
                    ':trx_id'    => $trxId,
                    ':u_id'      => $userId,
                    ':tag'       => !empty($strategyTag) ? $strategyTag : 'Value Investing',
                    ':rationale' => !empty($rationale) ? $rationale : 'เข้าซื้อสะสมตามกลยุทธ์'
                ]);
            } catch (PDOException $je) {
                // ข้ามหากตารางยังไม่ได้สร้าง
            }

            $pdo->commit();

            return [
                'success' => true,
                'message' => sprintf(
                    'จำลองการซื้อสำเร็จ! ซื้อหุ้น %s จำนวน %s หุ้น ที่ราคา %s บาท (ยอดรวม %s บาท)',
                    $stock['symbol'],
                    number_format($quantity),
                    number_format($price, 2),
                    number_format($totalCost, 2)
                )
            ];

        } else {
            // === คำสั่ง SELL (ขายหุ้น) ===
            $checkHoldSql = "SELECT id, quantity, average_price FROM portfolio_items 
                             WHERE portfolio_id = :p_id AND stock_id = :s_id LIMIT 1";
            $chStmt = $pdo->prepare($checkHoldSql);
            $chStmt->execute([':p_id' => $portfolioId, ':s_id' => $stockId]);
            $existingHold = $chStmt->fetch();

            $heldQty = $existingHold ? (int)$existingHold['quantity'] : 0;

            if ($heldQty < $quantity) {
                $pdo->rollBack();
                return [
                    'success' => false,
                    'message' => sprintf(
                        'จำนวนหุ้น %s ที่ถือครองไม่เพียงพอสำหรับการขาย (มีอยู่ %s หุ้น แต่ต้องการขาย %s หุ้น)',
                        $stock['symbol'],
                        number_format($heldQty),
                        number_format($quantity)
                    )
                ];
            }

            $avgPrice = (float)$existingHold['average_price'];
            $costBasis = $quantity * $avgPrice;
            $netProceeds = $subtotal - $fee;
            $realizedPnl = $netProceeds - $costBasis;

            // เพิ่มเงินสดเข้าพอร์ต
            $newCash = $cashBalance + $netProceeds;
            $updateCashSql = "UPDATE portfolios SET cash_balance = :cash, updated_at = NOW() WHERE id = :id";
            $ucStmt = $pdo->prepare($updateCashSql);
            $ucStmt->execute([':cash' => $newCash, ':id' => $portfolioId]);

            // ตัดจำนวนหุ้นที่ถือครอง
            $remainingQty = $heldQty - $quantity;
            if ($remainingQty > 0) {
                $updateHoldSql = "UPDATE portfolio_items SET quantity = :qty, updated_at = NOW() WHERE id = :id";
                $uhStmt = $pdo->prepare($updateHoldSql);
                $uhStmt->execute([':qty' => $remainingQty, ':id' => $existingHold['id']]);
            } else {
                $deleteHoldSql = "DELETE FROM portfolio_items WHERE id = :id";
                $dhStmt = $pdo->prepare($deleteHoldSql);
                $dhStmt->execute([':id' => $existingHold['id']]);
            }

            // บันทึกธุรกรรม
            $trxSql = "INSERT INTO transactions (portfolio_id, stock_id, transaction_type, quantity, price, total_amount, fee, realized_pnl) 
                       VALUES (:p_id, :s_id, 'SELL', :qty, :price, :amount, :fee, :pnl)";
            $tStmt = $pdo->prepare($trxSql);
            $tStmt->execute([
                ':p_id'   => $portfolioId,
                ':s_id'   => $stockId,
                ':qty'    => $quantity,
                ':price'  => $price,
                ':amount' => $subtotal,
                ':fee'    => $fee,
                ':pnl'    => $realizedPnl
            ]);
            $trxId = (int)$pdo->lastInsertId();

            // บันทึก Trading Journal (ถ้ามีตาราง trading_journals)
            try {
                $jSql = "INSERT INTO trading_journals (transaction_id, user_id, strategy_tag, rationale, lessons_learned) 
                         VALUES (:trx_id, :u_id, :tag, :rationale, NULL)";
                $jStmt = $pdo->prepare($jSql);
                $jStmt->execute([
                    ':trx_id'    => $trxId,
                    ':u_id'      => $userId,
                    ':tag'       => !empty($strategyTag) ? $strategyTag : 'Value Investing',
                    ':rationale' => !empty($rationale) ? $rationale : 'ขายทำกำไร/ตัดขาดทุนตามแผน'
                ]);
            } catch (PDOException $je) {
                // ข้ามหากตารางยังไม่ได้สร้าง
            }

            $pdo->commit();

            return [
                'success' => true,
                'message' => sprintf(
                    'จำลองการขายสำเร็จ! ขายหุ้น %s จำนวน %s หุ้น ได้รับเงินสุทธิ %s บาท (กำไร/ขาดทุนรับรู้: %s%s บาท)',
                    $stock['symbol'],
                    number_format($quantity),
                    number_format($netProceeds, 2),
                    ($realizedPnl >= 0 ? '+' : ''),
                    number_format($realizedPnl, 2)
                )
            ];
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Simulated Trade Error: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการทำรายการจำลอง กรุณาลองใหม่อีกครั้ง'
        ];
    }
}
