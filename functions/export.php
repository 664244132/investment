<?php
declare(strict_types=1);

/**
 * functions/export.php
 * SmartPort AI - Portfolio Statement & Transaction CSV Export Engine
 * ส่งออกรายงานพอร์ตการลงทุนเป็น CSV (UTF-8 BOM สำหรับ Microsoft Excel) และใบแจ้งยอดทางการเงิน
 * ปฏิบัติตามมาตรฐาน Strict Types, PDO Prepared Statements และ Security Best Practices
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/portfolio.php';

/**
 * ส่งออกรายการหุ้นที่ถือครองในพอร์ตเป็นไฟล์ CSV (UTF-8 with BOM)
 *
 * @param PDO $pdo
 * @param int $portfolioId
 * @return void
 */
function exportHoldingsToCsv(PDO $pdo, int $portfolioId): void
{
    $holdings = getPortfolioHoldings($pdo, $portfolioId);
    $filename = 'smartport_holdings_' . date('Y-m-d_His') . '.csv';

    // ส่งออก HTTP Headers สำหรับการดาวน์โหลดไฟล์ CSV
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // เขียน UTF-8 BOM สำหรับ Microsoft Excel ให้เปิดภาษาไทยได้ถูกต้อง
    fwrite($output, "\xEF\xBB\xBF");

    // Header Columns
    fputcsv($output, [
        'สัญลักษณ์หุ้น (Symbol)',
        'ชื่อบริษัท (Company Name)',
        'ตลาด (Market)',
        'กลุ่มอุตสาหกรรม (Sector)',
        'จำนวนหุ้นที่ถือ (Shares)',
        'ราคาต้นทุนเฉลี่ย (Avg Cost)',
        'ราคาตลาดล่าสุด (Market Price)',
        'มูลค่ารวมตามราคาตลาด (Market Value)',
        'กำไร/ขาดทุนยังไม่รับรู้ (Unrealized P&L)',
        'อัตราผลตอบแทน (%)'
    ]);

    foreach ($holdings as $h) {
        fputcsv($output, [
            $h['symbol'],
            $h['name_th'] ?: $h['name_en'],
            $h['market'],
            $h['sector'],
            (int)$h['quantity'],
            number_format((float)$h['average_price'], 2, '.', ''),
            number_format((float)$h['current_price'], 2, '.', ''),
            number_format((float)$h['market_value'], 2, '.', ''),
            number_format((float)$h['unrealized_pnl'], 2, '.', ''),
            number_format((float)$h['unrealized_pnl_pct'], 2, '.', '') . '%'
        ]);
    }

    fclose($output);
    exit;
}

/**
 * ส่งออกประวัติธุรกรรมทั้งหมดเป็นไฟล์ CSV (UTF-8 with BOM)
 *
 * @param PDO $pdo
 * @param int $portfolioId
 * @param string|null $fromDate
 * @param string|null $toDate
 * @return void
 */
function exportTransactionsToCsv(PDO $pdo, int $portfolioId, ?string $fromDate = null, ?string $toDate = null): void
{
    $sql = "SELECT t.id, t.transaction_type, t.quantity, t.price, t.total_amount, t.fee, t.realized_pnl, t.created_at,
                   s.symbol, s.name_th, s.name_en, s.market,
                   j.strategy_tag, j.rationale
            FROM transactions t
            INNER JOIN stocks s ON t.stock_id = s.id
            LEFT JOIN trading_journals j ON t.id = j.transaction_id
            WHERE t.portfolio_id = :p_id";

    $params = [':p_id' => $portfolioId];

    if (!empty($fromDate)) {
        $sql .= " AND DATE(t.created_at) >= :from_date";
        $params[':from_date'] = $fromDate;
    }
    if (!empty($toDate)) {
        $sql .= " AND DATE(t.created_at) <= :to_date";
        $params[':to_date'] = $toDate;
    }

    $sql .= " ORDER BY t.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    $filename = 'smartport_transactions_' . date('Y-m-d_His') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // เขียน UTF-8 BOM
    fwrite($output, "\xEF\xBB\xBF");

    // Header Columns
    fputcsv($output, [
        'วันที่และเวลา (Date/Time)',
        'รหัสธุรกรรม (Transaction ID)',
        'สัญลักษณ์หุ้น (Symbol)',
        'ชื่อบริษัท (Company Name)',
        'ประเภท (Type)',
        'จำนวนหุ้น (Shares)',
        'ราคาต่อหุ้น (Price/Share)',
        'มูลค่ารวมสุทธิ (Total Amount)',
        'ค่าคอมมิชชัน (Broker Fee)',
        'กำไร/ขาดทุนที่รับรู้ (Realized P&L)',
        'กลยุทธ์ (Strategy Tag)',
        'เหตุผลการเทรด (Trading Rationale)'
    ]);

    foreach ($transactions as $t) {
        $pnlStr = $t['realized_pnl'] !== null ? number_format((float)$t['realized_pnl'], 2, '.', '') : '-';
        fputcsv($output, [
            date('Y-m-d H:i:s', strtotime((string)$t['created_at'])),
            '#TRX-' . str_pad((string)$t['id'], 6, '0', STR_PAD_LEFT),
            $t['symbol'],
            $t['name_th'] ?: $t['name_en'],
            $t['transaction_type'],
            (int)$t['quantity'],
            number_format((float)$t['price'], 2, '.', ''),
            number_format((float)$t['total_amount'], 2, '.', ''),
            number_format((float)$t['fee'], 2, '.', ''),
            $pnlStr,
            $t['strategy_tag'] ?? 'General',
            $t['rationale'] ?? '-'
        ]);
    }

    fclose($output);
    exit;
}

/**
 * ดึงข้อมูลสรุปสำหรับสร้างใบแจ้งยอดพอร์ตการลงทุน (Official Account Statement Data)
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $portfolioId
 * @return array
 */
function getStatementData(PDO $pdo, int $userId, int $portfolioId): array
{
    $portfolio = getUserPortfolio($pdo, $userId);
    $holdings = getPortfolioHoldings($pdo, $portfolioId);
    $transactions = getPortfolioTransactions($pdo, $portfolioId);

    // ดึงข้อมูลผู้ใช้
    $uStmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = :id LIMIT 1");
    $uStmt->execute([':id' => $userId]);
    $userInfo = $uStmt->fetch();

    return [
        'user'         => $userInfo,
        'portfolio'    => $portfolio,
        'holdings'     => $holdings,
        'transactions' => $transactions,
        'as_of_date'   => formatThaiDate(time(), true),
        'account_no'   => 'SPT-' . str_pad((string)$userId, 6, '0', STR_PAD_LEFT)
    ];
}
