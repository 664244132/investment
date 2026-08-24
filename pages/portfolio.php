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
      <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2rem;">
        <div>
          <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; flex-wrap: wrap;">
            <h1 style="font-size: clamp(1.4rem, 4vw, 1.85rem); color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
              <?= icon('wallet', 'text-gold', 24) ?> <?= e($portfolio['portfolio_name']) ?>
            </h1>
          </div>
          <p style="color: var(--text-muted); font-size: 0.85rem;">
            เงินทุนเริ่มต้น <?= formatMoney((float)$portfolio['initial_cash']) ?> · ค่าธรรมเนียมจำลอง 0.157% ต่อรายการ
          </p>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
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
      <div class="metrics-grid" style="margin-bottom: 1.5rem;">
        
        <div class="metric-card">
          <span class="metric-label">มูลค่าสินทรัพย์สุทธิ (Total Net Asset)</span>
          <span class="metric-value" style="color: var(--accent-gold);">
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
            <span class="asset-legend-item" style="color: var(--accent-gold);">
              <span class="asset-dot" style="background: var(--accent-gold);"></span>
              เงินสด (Cash): <?= number_format($cashPct, 1) ?>% (<?= formatMoney($cashVal) ?>)
            </span>
            <span class="asset-legend-item" style="color: var(--color-success);">
              <span class="asset-dot" style="background: var(--color-success);"></span>
              หุ้น / ตราสารทุน (Stocks & Equities): <?= number_format($stocksPct, 1) ?>% (<?= formatMoney($stocksVal) ?>)
            </span>
          </div>
        </div>

        <div class="asset-allocation-bar">
          <div style="width: <?= $cashPct ?>%; background: var(--accent-gold);" title="Cash: <?= number_format($cashPct, 1) ?>%"></div>
          <div style="width: <?= $stocksPct ?>%; background: var(--color-success);" title="Equities: <?= number_format($stocksPct, 1) ?>%"></div>
        </div>
      </div>

      <!-- Current Holdings Table Card -->
      <div class="card" style="margin-bottom: 2.5rem;">
        <div class="card-header">
          <div>
            <h2 class="card-title">
              <?= icon('chart-bar', 'text-gold', 18) ?> รายการหุ้นที่ถือครองในพอร์ต (Current Holdings)
            </h2>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
              คำนวณราคาเฉลี่ยถ่วงน้ำหนัก (Weighted Average Cost)
            </p>
          </div>
          <span style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
            อัปเดตราคาแบบ Real-time
          </span>
        </div>

        <div class="table-responsive">
          <table class="financial-table">
            <thead>
              <tr>
                <th>ชื่อย่อหุ้น</th>
                <th>บริษัท / ตลาด</th>
                <th style="text-align: right;">จำนวนหุ้น</th>
                <th style="text-align: right;">ราคาต้นทุนเฉลี่ย</th>
                <th style="text-align: right;">ราคาตลาดล่าสุด</th>
                <th style="text-align: right;">มูลค่าตลาด</th>
                <th style="text-align: right;">กำไร / ขาดทุน (P&L)</th>
                <th style="text-align: center;">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($holdings)): ?>
                <tr>
                  <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                    ยังไม่มีหุ้นในพอร์ตจำลอง — คลิกปุ่ม <strong>"+ ส่งคำสั่งซื้อ"</strong> ด้านบนเพื่อเริ่มทดลองลงทุน
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($holdings as $item): ?>
                  <tr>
                    <td>
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($item['symbol']) ?>" style="font-weight: 700; color: var(--accent-gold); font-family: var(--font-mono); font-size: 1.05rem; white-space: nowrap;">
                        <?= e($item['symbol']) ?>
                      </a>
                    </td>
                    <td>
                      <div style="font-weight: 500;"><?= e($item['name_th'] ?: $item['name_en']) ?></div>
                      <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($item['market']) ?> · <?= e($item['sector']) ?></div>
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
                    <td class="num font-bold" style="color: var(--text-primary);">
                      <?= number_format((float)$item['market_value'], 2) ?>
                    </td>
                    <td class="num <?= getPnlColorClass((float)$item['unrealized_pnl']) ?>">
                      <strong><?= ((float)$item['unrealized_pnl'] >= 0 ? '+' : '') . number_format((float)$item['unrealized_pnl'], 2) ?></strong>
                      <div style="font-size: 0.75rem;">
                        (<?= formatPercent((float)$item['return_percent']) ?>)
                      </div>
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                      <div style="display: inline-flex; gap: 0.35rem;">
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
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
              บันทึกคำสั่งซื้อขายล่าสุดและค่าธรรมเนียมจริงในระบบจำลอง
            </p>
          </div>
          <span style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
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
                <th style="text-align: right;">จำนวนหุ้น</th>
                <th style="text-align: right;">ราคาที่ทำรายการ</th>
                <th style="text-align: right;">มูลค่ารวม</th>
                <th style="text-align: right;">ค่าธรรมเนียม</th>
                <th style="text-align: right;">ยอดเงินสุทธิ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($transactions)): ?>
                <tr>
                  <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                    ยังไม่มีประวัติการทำรายการในพอร์ตนี้
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                  <tr>
                    <td style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
                      <?= date('d/m/Y H:i', strtotime($tx['executed_at'])) ?>
                    </td>
                    <td>
                      <span class="badge <?= ($tx['transaction_type'] === 'BUY') ? 'badge-gain' : 'badge-loss' ?>">
                        <?= e($tx['transaction_type']) ?>
                      </span>
                    </td>
                    <td class="font-bold">
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($tx['symbol']) ?>" style="color: var(--text-primary); font-family: var(--font-mono);">
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
                    <td class="num" style="color: var(--text-muted);">
                      ฿<?= number_format((float)$tx['fee'], 2) ?>
                    </td>
                    <td class="num font-bold <?= ($tx['transaction_type'] === 'BUY') ? 'text-loss' : 'text-gain' ?>">
                      <?= ($tx['transaction_type'] === 'BUY' ? '-' : '+') ?>฿<?= number_format((float)$tx['net_amount'], 2) ?>
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
