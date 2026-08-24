<?php
declare(strict_types=1);

/**
 * pages/export.php
 * SmartPort AI - Portfolio Statement & Transaction Report Export Hub
 * ศูนย์ส่งออกรายงานการเงิน, ไฟล์ CSV (UTF-8 BOM สำหรับ Excel) และใบแจ้งยอดพอร์ตสไตล์โบรกเกอร์ (Printable A4)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/portfolio.php';
require_once __DIR__ . '/../functions/export.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();

if (!$isUserLoggedIn) {
    setFlashMessage('danger', 'กรุณาเข้าสู่ระบบก่อนใช้งานระบบส่งออกรายงาน');
    redirect(BASE_URL . '/pages/login.php');
}

$userId = (int)$user['id'];
$portfolio = getUserPortfolio($pdo, $userId);

if (!$portfolio) {
    createDefaultPortfolio($pdo, $userId, $user['username']);
    $portfolio = getUserPortfolio($pdo, $userId);
}

$portfolioId = (int)$portfolio['id'];

// 1. จัดการการดาวน์โหลดไฟล์ CSV ทันที
$action = (string)($_GET['action'] ?? '');
if ($action === 'download_holdings_csv') {
    exportHoldingsToCsv($pdo, $portfolioId);
}

if ($action === 'download_transactions_csv') {
    $fromDate = !empty($_GET['from_date']) ? (string)$_GET['from_date'] : null;
    $toDate = !empty($_GET['to_date']) ? (string)$_GET['to_date'] : null;
    exportTransactionsToCsv($pdo, $portfolioId, $fromDate, $toDate);
}

// 2. ดึงข้อมูลสำหรับเรนเดอร์ Statement View
$statement = getStatementData($pdo, $userId, $portfolioId);
$holdings = $statement['holdings'];
$transactions = $statement['transactions'];

$pageTitle = 'รายงานสถานะพอร์ตการลงทุนและรายการซื้อขายหลักทรัพย์ - ' . APP_TITLE;
$activeNav = 'portfolio';

require_once __DIR__ . '/../templates/header.php';
?>

  <main class="main-content">
    <div class="container">

      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <!-- Header Section -->
      <div class="section-heading-row" style="margin-bottom: 1.5rem;">
        <div>
          <h1 class="section-heading-title">
            <?= icon('file-text', 'text-gold', 24) ?> รายงานสรุปสถานะพอร์ตการลงทุนและบัญชีหลักทรัพย์
          </h1>
          <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 0.25rem;">
            ส่งออกไฟล์ข้อมูลบัญชี (Microsoft Excel / CSV) และพิมพ์ใบรายงานสรุปสถานะพอร์ตการลงทุนมาตรฐานทางการ
          </p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="btn btn-outline btn-sm">
            <?= icon('wallet', '', 14) ?> กลับไปพอร์ตของฉัน
          </a>
          <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <?= icon('printer', '', 14) ?> พิมพ์ใบรายงาน (Print / Save PDF)
          </button>
        </div>
      </div>

      <!-- Export Action Cards Hub -->
      <div class="export-hub-grid">
        
        <!-- Card 1: Holdings Inventory CSV -->
        <div class="export-action-card">
          <div>
            <div class="export-card-icon">
              <?= icon('wallet', 'text-gold', 24) ?>
            </div>
            <h3 style="font-size: 1.15rem; color: var(--text-primary); margin-bottom: 0.35rem;">
              รายงานรายการหลักทรัพย์ที่ถือครอง
            </h3>
            <p style="font-size: 0.825rem; color: var(--text-muted); line-height: 1.5;">
              ส่งออกข้อมูลรายการหุ้นทั้งหมดในพอร์ตการลงทุน ราคาต้นทุนเฉลี่ย ราคาปิดล่าสุด มูลค่าตามราคาตลาด และกำไร/ขาดทุน
            </p>
          </div>
          <a href="<?= e(BASE_URL) ?>/pages/export.php?action=download_holdings_csv" class="btn btn-primary" style="justify-content: center;">
            <?= icon('download', '', 14) ?> ดาวน์โหลดรายงานหุ้น (Excel / CSV)
          </a>
        </div>

        <!-- Card 2: Transaction History CSV -->
        <div class="export-action-card">
          <div>
            <div class="export-card-icon">
              <?= icon('document', 'text-gold', 24) ?>
            </div>
            <h3 style="font-size: 1.15rem; color: var(--text-primary); margin-bottom: 0.35rem;">
              รายงานประวัติธุรกรรมการซื้อขาย
            </h3>
            <p style="font-size: 0.825rem; color: var(--text-muted); line-height: 1.5;">
              ส่งออกบันทึกคำสั่งซื้อและขายหลักทรัพย์ทั้งหมด พร้อมค่าธรรมเนียม กำไรที่รับรู้ และเหตุผลการเข้าทำรายการ
            </p>
          </div>
          <form action="<?= e(BASE_URL) ?>/pages/export.php" method="GET" style="display: flex; flex-direction: column; gap: 0.5rem;">
            <input type="hidden" name="action" value="download_transactions_csv">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
              <input type="date" name="from_date" class="form-input" style="font-size: 0.78rem; padding: 0.35rem 0.5rem;" title="ตั้งแต่วันที่">
              <input type="date" name="to_date" class="form-input" style="font-size: 0.78rem; padding: 0.35rem 0.5rem;" title="ถึงวันที่">
            </div>
            <button type="submit" class="btn btn-outline" style="justify-content: center;">
              <?= icon('download', '', 14) ?> ดาวน์โหลดประวัติการซื้อขาย (Excel)
            </button>
          </form>
        </div>

        <!-- Card 3: Instant Print Button -->
        <div class="export-action-card">
          <div>
            <div class="export-card-icon">
              <?= icon('printer', 'text-gold', 24) ?>
            </div>
            <h3 style="font-size: 1.15rem; color: var(--text-primary); margin-bottom: 0.35rem;">
              พิมพ์ใบรายงานสรุปสถานะพอร์ต
            </h3>
            <p style="font-size: 0.825rem; color: var(--text-muted); line-height: 1.5;">
              พิมพ์หรือบันทึกเป็นเอกสารทางการ PDF ขนาด A4 จัดรูปแบบรายงานสถาบันการเงินที่ถูกต้องและเป็นระเบียบ
            </p>
          </div>
          <button type="button" class="btn btn-primary" onclick="window.print()" style="justify-content: center;">
            <?= icon('printer', '', 14) ?> สั่งพิมพ์เอกสารรายงาน (Print / PDF)
          </button>
        </div>

      </div>

      <!-- Official Account Statement Document (Printable A4 Component) -->
      <div class="statement-paper" id="printableStatement">
        
        <!-- Statement Header Row -->
        <div class="statement-brand-row">
          <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
              <?= icon('shield', 'text-gold', 26) ?>
              <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                SMARTPORT<span style="color: var(--accent-gold);">AI</span>
              </span>
            </div>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
              ระบบบริหารจัดการและวิเคราะห์การลงทุน SmartPort AI
            </p>
            <div style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-top: 0.5rem;">
              ใบรายงานสรุปสถานะพอร์ตการลงทุนและบัญชีหลักทรัพย์
            </div>
          </div>

          <div class="statement-meta-box">
            <div><span style="color: var(--text-muted);">เลขที่บัญชีหลักทรัพย์ :</span> <strong><?= e($statement['account_no']) ?></strong></div>
            <div><span style="color: var(--text-muted);">ชื่อผู้ถือบัญชี :</span> <strong><?= e($statement['user']['username']) ?></strong> (<?= e($statement['user']['email']) ?>)</div>
            <div><span style="color: var(--text-muted);">วันที่ออกรายงาน :</span> <strong><?= e($statement['as_of_date']) ?></strong></div>
            <div><span style="color: var(--text-muted);">ประเภทบัญชี :</span> <strong>บัญชีจำลองการซื้อขายหลักทรัพย์ (Cash Balance)</strong></div>
            <div><span style="color: var(--text-muted);">สกุลเงินอ้างอิง :</span> <strong>บาท (THB)</strong></div>
          </div>
        </div>

        <!-- Statement Financial Summary Box -->
        <div class="statement-summary-box">
          <div class="statement-summary-item">
            <span class="statement-summary-item-label">มูลค่าสินทรัพย์สุทธิรวม</span>
            <span class="statement-summary-item-val text-gold">
              ฿<?= number_format((float)$portfolio['total_portfolio_value'], 2) ?>
            </span>
          </div>

          <div class="statement-summary-item">
            <span class="statement-summary-item-label">ยอดเงินสดคงเหลือ</span>
            <span class="statement-summary-item-val">
              ฿<?= number_format((float)$portfolio['cash_balance'], 2) ?>
            </span>
          </div>

          <div class="statement-summary-item">
            <span class="statement-summary-item-label">มูลค่าหลักทรัพย์ตามราคาตลาด</span>
            <span class="statement-summary-item-val">
              ฿<?= number_format((float)$portfolio['total_stocks_value'], 2) ?>
            </span>
          </div>

          <div class="statement-summary-item">
            <span class="statement-summary-item-label">ผลกำไร / ขาดทุนสุทธิรวม</span>
            <?php
              $totalPnl = (float)($portfolio['total_pnl'] ?? $portfolio['total_profit_loss'] ?? 0.0);
              $totalPnlPct = (float)($portfolio['total_pnl_percent'] ?? $portfolio['total_return_percent'] ?? 0.0);
            ?>
            <span class="statement-summary-item-val <?= getPnlColorClass($totalPnl) ?>">
              <?= ($totalPnl >= 0 ? '+' : '') ?>฿<?= number_format($totalPnl, 2) ?>
              (<?= formatPercent($totalPnlPct) ?>)
            </span>
          </div>
        </div>

        <!-- Section 1: Active Holdings Inventory Table -->
        <div style="margin-bottom: 2.5rem;">
          <h3 style="font-size: 1.05rem; color: var(--text-primary); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('wallet', 'text-gold', 16) ?> ตารางที่ ๑ : รายการหลักทรัพย์ที่ถือครองในพอร์ตการลงทุน ณ วันที่ออกรายงาน
          </h3>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 40px; text-align: center;">ลำดับ</th>
                  <th>รหัสหลักทรัพย์</th>
                  <th>ชื่อบริษัท / กิจการ</th>
                  <th style="text-align: center;">ตลาด / กลุ่มอุตสาหกรรม</th>
                  <th style="text-align: right;">จำนวนหุ้น</th>
                  <th style="text-align: right;">ราคาต้นทุนเฉลี่ย</th>
                  <th style="text-align: right;">ราคาปิดล่าสุด</th>
                  <th style="text-align: right;">มูลค่าตลาดรวม</th>
                  <th style="text-align: right;">กำไร / ขาดทุน</th>
                  <th style="text-align: center;">อัตราผลตอบแทน</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($holdings)): ?>
                  <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                      ไม่มีรายการหลักทรัพย์ที่ถือครองในพอร์ตการลงทุน ณ ปัจจุบัน
                    </td>
                  </tr>
                <?php else: ?>
                  <?php $idx = 1; foreach ($holdings as $h): 
                    $unrealizedPnlVal = (float)($h['unrealized_pnl'] ?? 0.0);
                    $unrealizedPnlPctVal = (float)($h['unrealized_pnl_pct'] ?? $h['return_percent'] ?? 0.0);
                  ?>
                    <tr>
                      <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                      <td class="font-bold" style="font-family: var(--font-mono); color: var(--accent-gold);">
                        <?= e($h['symbol']) ?>
                      </td>
                      <td><?= e($h['name_th'] ?: $h['name_en']) ?></td>
                      <td style="text-align: center; font-size: 0.75rem; color: var(--text-muted);"><?= e($h['market']) ?> · <?= e($h['sector']) ?></td>
                      <td class="num font-bold"><?= number_format((int)$h['quantity']) ?></td>
                      <td class="num">฿<?= number_format((float)$h['average_price'], 2) ?></td>
                      <td class="num">฿<?= number_format((float)$h['current_price'], 2) ?></td>
                      <td class="num font-bold">฿<?= number_format((float)$h['market_value'], 2) ?></td>
                      <td class="num <?= getPnlColorClass($unrealizedPnlVal) ?>">
                        <?= ($unrealizedPnlVal >= 0 ? '+' : '') ?>฿<?= number_format($unrealizedPnlVal, 2) ?>
                      </td>
                      <td style="text-align: center;" class="font-bold <?= getPnlColorClass($unrealizedPnlVal) ?>">
                        <?= formatPercent($unrealizedPnlPctVal) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Section 2: Recent Transactions History Table -->
        <div>
          <h3 style="font-size: 1.05rem; color: var(--text-primary); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('history', 'text-gold', 16) ?> ตารางที่ ๒ : รายการบันทึกประวัติธุรกรรมการซื้อขายหลักทรัพย์
          </h3>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>วันที่และเวลาทำรายการ</th>
                  <th>รหัสคำสั่ง</th>
                  <th style="text-align: center;">รายการ</th>
                  <th>รหัสหลักทรัพย์</th>
                  <th style="text-align: right;">จำนวนหุ้น</th>
                  <th style="text-align: right;">ราคาต่อหุ้น</th>
                  <th style="text-align: right;">มูลค่ารวมสุทธิ</th>
                  <th style="text-align: right;">ค่าธรรมเนียม</th>
                  <th style="text-align: right;">กำไร / ขาดทุนรับรู้</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($transactions)): ?>
                  <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                      ยังไม่มีประวัติการทำธุรกรรมการซื้อขายในบัญชีนี้
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($transactions as $t): 
                    $isBuy = ($t['transaction_type'] === 'BUY');
                    $typeTh = $isBuy ? 'ซื้อ (BUY)' : 'ขาย (SELL)';
                    $pnl = $t['realized_pnl'] !== null ? (float)$t['realized_pnl'] : null;
                  ?>
                    <tr>
                      <td style="font-size: 0.8rem; color: var(--text-muted);"><?= formatThaiDate($t['created_at'], true) ?></td>
                      <td style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">#<?= str_pad((string)$t['id'], 5, '0', STR_PAD_LEFT) ?></td>
                      <td style="text-align: center; font-weight: 700;" class="<?= $isBuy ? 'text-gain' : 'text-loss' ?>">
                        <?= e($typeTh) ?>
                      </td>
                      <td class="font-bold" style="font-family: var(--font-mono); color: var(--accent-gold);"><?= e($t['symbol']) ?></td>
                      <td class="num"><?= number_format((int)$t['quantity']) ?></td>
                      <td class="num">฿<?= number_format((float)$t['price'], 2) ?></td>
                      <td class="num font-bold">฿<?= number_format((float)$t['total_amount'], 2) ?></td>
                      <td class="num" style="color: var(--text-muted); font-size: 0.8rem;">฿<?= number_format((float)$t['fee'], 2) ?></td>
                      <td class="num font-bold <?= $pnl !== null ? getPnlColorClass($pnl) : '' ?>">
                        <?= $pnl !== null ? (($pnl >= 0 ? '+' : '') . '฿' . number_format($pnl, 2)) : '-' ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Electronic Statement Notice & Disclaimer Banner -->
        <div style="margin-top: 2.5rem; padding: 1rem 1.25rem; background: rgba(255, 204, 0, 0.04); border: 1px solid rgba(255, 204, 0, 0.2); border-radius: var(--radius-sm); font-size: 0.825rem; color: var(--text-muted); line-height: 1.6;">
          <div style="display: flex; align-items: center; gap: 0.4rem; color: var(--accent-gold); font-weight: 700; margin-bottom: 0.25rem;">
            <?= icon('shield', '', 14) ?> หมายเหตุการรับรองเอกสารอิเล็กทรอนิกส์ (Electronic Statement Notice)
          </div>
          <p style="margin: 0;">
            เอกสารฉบับนี้จัดทำและประมวลผลขึ้นโดยระบบสารสนเทศอัตโนมัติของ <strong>SmartPort AI</strong> ข้อมูลแสดงสถานะบัญชีและรายการธุรกรรมตามฐานข้อมูลจริง ณ วันที่และเวลาที่ระบุข้างต้น จึงมีความสมบูรณ์ถูกต้องตามมาตรฐานระบบอิเล็กทรอนิกส์โดยมิต้องลงลายมือชื่อ
          </p>
        </div>

        <!-- Document Footer Seal -->
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: var(--text-muted); flex-wrap: wrap; gap: 1rem;">
          <div>
            ใบรายงานสรุปสถานะพอร์ตการลงทุนและบัญชีหลักทรัพย์ · <strong>SmartPort AI</strong>
          </div>
          <div>
            หน้า ๑ จาก ๑ · เอกสารทางการอิเล็กทรอนิกส์ (ไม่ต้องลงลายมือชื่อ)
          </div>
        </div>

      </div>

    </div>
  </main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
