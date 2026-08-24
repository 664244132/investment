<?php
declare(strict_types=1);

/**
 * pages/portfolio.php
 * SmartPort AI - Simulated Portfolio & Trade Execution Portale & Transaction Management
 * ออกแบบตามมาตรฐาน: Unified Sans Typography, Clean Rounded Rectangles, Icons Only, Zero External CDNs, Flat Subtle States
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/portfolio.php';

startAppSession();

// บังคับให้เข้าสู่ระบบก่อนใช้งานพอร์ต (Guard Clause)
requireLogin();

$pdo = getDbConnection();
$user = getCurrentUser();
$userId = (int)$user['id'];

// ประมวลผลคำสั่งซื้อ-ขายจำลอง (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'trade') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlashMessage('danger', 'การตรวจสอบความปลอดภัย CSRF ล้มเหลว กรุณารีเฟรชหน้าเว็บแล้วทำรายการใหม่อีกครั้ง');
        redirect(BASE_URL . '/pages/portfolio.php');
    }

    $stockId     = (int)($_POST['stock_id'] ?? 0);
    $type        = strtoupper(trim((string)($_POST['trade_type'] ?? 'BUY')));
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $strategyTag = trim((string)($_POST['strategy_tag'] ?? 'Value Investing'));
    $rationale   = trim((string)($_POST['rationale'] ?? ''));

    $tradeResult = executeSimulatedTrade($pdo, $userId, $stockId, $type, $quantity, $strategyTag, $rationale);

    if ($tradeResult['success']) {
        setFlashMessage('success', $tradeResult['message']);
    } else {
        setFlashMessage('danger', $tradeResult['message']);
    }

    redirect(BASE_URL . '/pages/portfolio.php');
}

// ดึงข้อมูลพอร์ตจำลอง
$portfolio = getUserPortfolio($pdo, $userId);

// หากไม่พบพอร์ต (กรณีเกิดปัญหา) ให้สร้างใหม่อัตโนมัติ
if (!$portfolio) {
    createDefaultPortfolio($pdo, $userId, $user['username']);
    $portfolio = getUserPortfolio($pdo, $userId);
}

$portfolioId = (int)$portfolio['id'];
$holdings = getPortfolioHoldings($pdo, $portfolioId);
$transactions = getPortfolioTransactions($pdo, $portfolioId);
$allStocks = getAllStocks($pdo);

// คำนวณสัดส่วน Asset Allocation (Cash vs Equities)
$cashVal = (float)$portfolio['cash_balance'];
$stocksVal = (float)$portfolio['total_stocks_value'];
$totalNetVal = (float)$portfolio['total_portfolio_value'];
$cashPct = ($totalNetVal > 0) ? ($cashVal / $totalNetVal) * 100 : 100;
$stocksPct = ($totalNetVal > 0) ? ($stocksVal / $totalNetVal) * 100 : 0;

$pageTitle = 'พอร์ตการลงทุนจำลอง - ' . APP_TITLE;
$activeNav = 'portfolio';

require_once __DIR__ . '/../templates/header.php';
?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">

      <!-- Flash Message Notification -->
      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <!-- Portfolio Header & Summary Controls -->
      <div class="portfolio-header-row">
        <div>
          <div class="d-flex align-center gap-sm section-mb-xs flex-wrap">
            <h1 class="portfolio-header-title">
              <?= icon('wallet', 'text-gold', 24) ?> <?= e($portfolio['portfolio_name']) ?>
            </h1>
          </div>
          <p class="text-sm text-muted">
            เงินทุนเริ่มต้น <?= formatMoney((float)$portfolio['initial_cash']) ?> · ค่าธรรมเนียมจำลอง 0.157% ต่อรายการ
          </p>
        </div>

        <div class="d-flex gap-sm flex-wrap">
          <a href="<?= e(BASE_URL) ?>/pages/export.php" class="btn btn-outline" title="ส่งออกรายงานและใบแจ้งยอดพอร์ต">
            <?= icon('file-text', 'text-gold', 14) ?> ส่งออกรายงาน (Export)
          </a>
          <button type="button" class="btn btn-buy open-trade-modal-btn" data-trade-type="BUY">
            <?= icon('plus', '', 14) ?> ส่งคำสั่งซื้อ (BUY)
          </button>
          <button type="button" class="btn btn-sell open-trade-modal-btn" data-trade-type="SELL">
            <?= icon('minus', '', 14) ?> ส่งคำสั่งขาย (SELL)
          </button>
        </div>
      </div>

      <!-- Overview 4 Metrics Grid -->
      <div class="metrics-grid section-mb-md">
        
        <div class="metric-card">
          <span class="metric-label">มูลค่าสินทรัพย์สุทธิ (Total Net Asset)</span>
          <span class="metric-value text-gold">
            <?= formatMoney((float)$portfolio['total_portfolio_value']) ?>
          </span>
          <div class="metric-sub">
            <span class="<?= getPnlColorClass((float)$portfolio['total_profit_loss']) ?>">
              <?= formatPercent((float)$portfolio['total_return_percent']) ?>
            </span>
            <span>(<?= formatMoney((float)$portfolio['total_profit_loss']) ?>)</span>
          </div>
        </div>

        <div class="metric-card">
          <span class="metric-label">เงินสดคงเหลือ (Available Cash)</span>
          <span class="metric-value">
            <?= formatMoney((float)$portfolio['cash_balance']) ?>
          </span>
          <div class="metric-sub">
            <span>สภาพคล่องพร้อมลงทุน</span>
          </div>
        </div>

        <div class="metric-card">
          <span class="metric-label">มูลค่าหุ้นที่ถือครอง (Equity Value)</span>
          <span class="metric-value">
            <?= formatMoney((float)$portfolio['total_stocks_value']) ?>
          </span>
          <div class="metric-sub">
            <span><?= (int)$portfolio['holdings_count'] ?> บริษัทในพอร์ต</span>
          </div>
        </div>

        <div class="metric-card">
          <span class="metric-label">กำไร/ขาดทุนที่ยังไม่ขาย (Unrealized P&L)</span>
          <span class="metric-value <?= getPnlColorClass((float)$portfolio['total_unrealized_pnl']) ?>">
            <?= ((float)$portfolio['total_unrealized_pnl'] >= 0 ? '+' : '') . formatMoney((float)$portfolio['total_unrealized_pnl']) ?>
          </span>
          <div class="metric-sub">
            <span>คำนวณตามราคาตลาดล่าสุด</span>
          </div>
        </div>

      </div>

      <!-- Asset Allocation Progress Bar -->
      <div class="card asset-allocation-card">
        <div class="asset-allocation-head">
          <div class="asset-allocation-title">
            สัดส่วนการจัดสรรสินทรัพย์ (Asset Allocation)
          </div>
          <div class="asset-allocation-legend">
            <span class="asset-legend-item asset-legend-gold">
              <span class="asset-dot asset-dot-gold"></span>
              เงินสด (Cash): <?= number_format($cashPct, 1) ?>% (<?= formatMoney($cashVal) ?>)
            </span>
            <span class="asset-legend-item asset-legend-gain">
              <span class="asset-dot asset-dot-gain"></span>
              หุ้น / ตราสารทุน (Stocks & Equities): <?= number_format($stocksPct, 1) ?>% (<?= formatMoney($stocksVal) ?>)
            </span>
          </div>
        </div>

        <div class="asset-allocation-bar">
          <div class="asset-alloc-seg asset-alloc-gold" style="--alloc-pct: <?= number_format($cashPct, 1) ?>%;" title="Cash: <?= number_format($cashPct, 1) ?>%"></div>
          <div class="asset-alloc-seg asset-alloc-gain" style="--alloc-pct: <?= number_format($stocksPct, 1) ?>%;" title="Equities: <?= number_format($stocksPct, 1) ?>%"></div>
        </div>
      </div>

      <!-- Current Holdings Table Card -->
      <div class="card section-mb-lg">
        <div class="card-header">
          <div>
            <h2 class="card-title">
              <?= icon('chart-bar', 'text-gold', 18) ?> รายการหุ้นที่ถือครองในพอร์ต (Current Holdings)
            </h2>
            <p class="metric-subtitle-meta">
              คำนวณราคาเฉลี่ยถ่วงน้ำหนัก (Weighted Average Cost)
            </p>
          </div>
          <span class="text-xs text-muted nowrap">
            อัปเดตราคาแบบ Real-time
          </span>
        </div>

        <div class="table-responsive">
          <table class="financial-table">
            <thead>
              <tr>
                <th>ชื่อย่อหุ้น</th>
                <th>บริษัท / ตลาด</th>
                <th class="th-right">จำนวนหุ้น</th>
                <th class="th-right">ราคาต้นทุนเฉลี่ย</th>
                <th class="th-right">ราคาตลาดล่าสุด</th>
                <th class="th-right">มูลค่าตลาด</th>
                <th class="th-right">กำไร / ขาดทุน (P&L)</th>
                <th class="th-center">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($holdings)): ?>
                <tr>
                  <td colspan="8" class="table-empty-cell">
                    ยังไม่มีหุ้นในพอร์ตจำลอง — คลิกปุ่ม <strong>"+ ส่งคำสั่งซื้อ"</strong> ด้านบนเพื่อเริ่มทดลองลงทุน
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($holdings as $item): ?>
                  <tr>
                    <td>
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($item['symbol']) ?>" class="link-gold-mono">
                        <?= e($item['symbol']) ?>
                      </a>
                    </td>
                    <td>
                      <div class="font-medium"><?= e($item['name_th'] ?: $item['name_en']) ?></div>
                      <div class="text-xs text-muted"><?= e($item['market']) ?> · <?= e($item['sector']) ?></div>
                    </td>
                    <td class="num font-bold">
                      <?= number_format((int)$item['quantity']) ?>
                    </td>
                    <td class="num">
                      <?= number_format((float)$item['average_price'], 2) ?>
                    </td>
                    <td class="num font-bold">
                      <?= number_format((float)$item['current_price'], 2) ?>
                    </td>
                    <td class="num font-bold text-primary">
                      <?= number_format((float)$item['market_value'], 2) ?>
                    </td>
                    <td class="num <?= getPnlColorClass((float)$item['unrealized_pnl']) ?>">
                      <strong><?= ((float)$item['unrealized_pnl'] >= 0 ? '+' : '') . number_format((float)$item['unrealized_pnl'], 2) ?></strong>
                      <div class="text-xs">
                        (<?= formatPercent((float)$item['return_percent']) ?>)
                      </div>
                    </td>
                    <td class="th-right nowrap">
                      <div class="d-inline-flex gap-xs">
                        <button type="button" class="btn btn-buy btn-sm open-trade-modal-btn" data-trade-type="BUY" data-stock-id="<?= (int)$item['stock_id'] ?>" data-price="<?= (float)$item['current_price'] ?>">
                          <?= icon('plus', '', 12) ?> ซื้อ
                        </button>
                        <button type="button" class="btn btn-sell btn-sm open-trade-modal-btn" data-trade-type="SELL" data-stock-id="<?= (int)$item['stock_id'] ?>" data-price="<?= (float)$item['current_price'] ?>">
                          <?= icon('minus', '', 12) ?> ขาย
                        </button>
                        <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($item['symbol']) ?>" class="btn btn-outline btn-sm">
                          <?= icon('bot', '', 12) ?> วิเคราะห์
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Transaction History Table Card -->
      <div class="card">
        <div class="card-header">
          <div>
            <h2 class="card-title">
              <?= icon('history', 'text-gold', 18) ?> ประวัติการทำรายการซื้อขาย (Transaction History)
            </h2>
            <p class="metric-subtitle-meta">
              บันทึกคำสั่งซื้อขายล่าสุดและค่าธรรมเนียมจริงในระบบจำลอง
            </p>
          </div>
          <span class="text-xs text-muted nowrap">
            <?= count($transactions) ?> รายการ
          </span>
        </div>

        <div class="table-responsive">
          <table class="financial-table">
            <thead>
              <tr>
                <th>วันที่ / เวลา</th>
                <th>ประเภท</th>
                <th>หลักทรัพย์</th>
                <th class="th-right">จำนวนหุ้น</th>
                <th class="th-right">ราคาที่ทำรายการ</th>
                <th class="th-right">มูลค่ารวม</th>
                <th class="th-right">ค่าธรรมเนียม</th>
                <th class="th-right">ยอดเงินสุทธิ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($transactions)): ?>
                <tr>
                  <td colspan="8" class="table-empty-cell">
                    ยังไม่มีประวัติการทำรายการในพอร์ตนี้
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                  <tr>
                    <td class="text-xs text-muted nowrap">
                      <?= date('d/m/Y H:i', strtotime((string)($tx['created_at'] ?? $tx['executed_at'] ?? 'now'))) ?>
                    </td>
                    <td>
                      <span class="badge <?= ($tx['transaction_type'] === 'BUY') ? 'badge-gain' : 'badge-loss' ?>">
                        <?= e($tx['transaction_type']) ?>
                      </span>
                    </td>
                    <td class="font-bold">
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($tx['symbol']) ?>" class="text-primary font-mono">
                        <?= e($tx['symbol']) ?>
                      </a>
                    </td>
                    <td class="num font-bold">
                      <?= number_format((int)$tx['quantity']) ?>
                    </td>
                    <td class="num">
                      ฿<?= number_format((float)$tx['price'], 2) ?>
                    </td>
                    <td class="num">
                      ฿<?= number_format((float)$tx['total_amount'], 2) ?>
                    </td>
                    <td class="num text-muted">
                      ฿<?= number_format((float)$tx['fee'], 2) ?>
                    </td>
                    <td class="num font-bold <?= ($tx['transaction_type'] === 'BUY') ? 'text-loss' : 'text-gain' ?>">
                      <?php
                        $isBuy = ($tx['transaction_type'] === 'BUY');
                        $netAmt = $isBuy ? ((float)$tx['total_amount'] + (float)$tx['fee']) : ((float)$tx['total_amount'] - (float)$tx['fee']);
                      ?>
                      <?= ($isBuy ? '-' : '+') ?>฿<?= number_format($netAmt, 2) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <!-- Reusable Trade Modal Component -->
  <?php
  $modalStock = null;
  $modalAllStocks = $allStocks;
  require __DIR__ . '/../templates/trade_modal.php';
  ?>

  <!-- Scripts & Footer Template -->
  <?php
  $extraScripts = '<script>
    window.BASE_URL = ' . json_encode(BASE_URL) . ';
    
    // อัปเดตราคาหุ้นใน Modal เมื่อเปลี่ยนหุ้นที่เลือก
    const stockSelect = document.getElementById("trade_stock_id");
    const priceDisplay = document.getElementById("modal_stock_price");
    if (stockSelect && priceDisplay) {
      stockSelect.addEventListener("change", () => {
        const selectedOpt = stockSelect.options[stockSelect.selectedIndex];
        const price = parseFloat(selectedOpt.getAttribute("data-price")) || 0;
        priceDisplay.setAttribute("data-price", price);
        priceDisplay.textContent = "฿" + price.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
        const qtyInput = document.getElementById("trade_quantity");
        if (qtyInput) qtyInput.dispatchEvent(new Event("input"));
      });
    }
  </script>';

  require_once __DIR__ . '/../templates/footer.php';
  ?>
