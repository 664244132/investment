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
    $stockId  = (int)($_POST['stock_id'] ?? 0);
    $type     = strtoupper(trim((string)($_POST['trade_type'] ?? 'BUY')));
    $quantity = (int)($_POST['quantity'] ?? 0);

    $tradeResult = executeSimulatedTrade($pdo, $userId, $stockId, $type, $quantity);

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

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>พอร์ตการลงทุนจำลอง - <?= e(APP_TITLE) ?></title>
  
  <!-- CSS Stylesheets (Local Assets) -->
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/variables.css">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/layout.css">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/components.css">
</head>
<body>

  <!-- Header -->
  <header class="app-header">
    <div class="container header-inner">
      <a href="<?= e(BASE_URL) ?>/index.php" class="brand-logo">
        <div class="brand-icon">
          <?= icon('chart-bar', '', 18, '#1D1616') ?>
        </div>
        <span>SmartPort AI</span>
      </a>

      <nav class="main-nav">
        <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link">หน้าหลัก / วิเคราะห์หุ้น</a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link">คัดกรองหุ้น (Screener)</a>
        <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link active">พอร์ตจำลอง (Portfolio)</a>
      </nav>

      <div class="header-actions">
        <div class="search-box">
          <?= icon('search', '', 14, '#8A7F70') ?>
          <input type="text" id="stockSearchInput" placeholder="ค้นหาหุ้น... (กด Enter)">
        </div>

        <span style="font-size: 0.85rem; color: var(--text-muted); display: none; @media(min-width:768px){display:inline;}; white-space: nowrap;">
          <?= icon('user', '', 14) ?> <?= e($user['full_name'] ?? $user['username']) ?>
        </span>
        <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="btn btn-outline btn-sm">
          ออกจากระบบ
        </a>

        <button class="mobile-toggle" aria-label="Toggle Navigation">
          <?= icon('menu', '', 20) ?>
        </button>
      </div>
    </div>

    <!-- Mobile Drawer -->
    <div class="mobile-drawer">
      <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link">หน้าหลัก / วิเคราะห์หุ้น</a>
      <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link">คัดกรองหุ้น (Screener)</a>
      <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link active">พอร์ตจำลอง (Portfolio)</a>
      <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="nav-link" style="color: var(--color-danger);">ออกจากระบบ</a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">

      <!-- Flash Message -->
      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
          <?= icon('info', '', 16) ?>
          <span><?= e($flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <!-- Portfolio Header & Summary Controls -->
      <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2rem;">
        <div>
          <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <h1 style="font-size: 1.85rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
              <?= icon('wallet', 'text-gold', 24) ?> <?= e($portfolio['portfolio_name']) ?>
            </h1>
            <span class="badge badge-gold">จำลองตามราคาตลาดจริง</span>
          </div>
          <p style="color: var(--text-muted); font-size: 0.85rem;">
            เงินทุนเริ่มต้น <?= formatMoney((float)$portfolio['initial_cash']) ?> · ค่าธรรมเนียมจำลอง 0.157% ต่อรายการ
          </p>
        </div>

        <div style="display: flex; gap: 0.75rem;">
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

      <!-- Asset Allocation Progress Bar (Clean Rounded Rectangle) -->
      <div class="card" style="padding: 1.25rem 1.5rem; margin-bottom: 2.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
          <div style="font-size: 0.8rem; font-family: var(--font-primary); color: var(--text-primary); text-transform: uppercase; font-weight: 700; white-space: nowrap;">
            สัดส่วนการจัดสรรสินทรัพย์ (Asset Allocation)
          </div>
          <div style="display: flex; gap: 1.5rem; font-size: 0.8rem; font-family: var(--font-mono); flex-wrap: wrap;">
            <span style="display: flex; align-items: center; gap: 6px; color: var(--accent-gold); white-space: nowrap;">
              <span style="width: 10px; height: 10px; border-radius: var(--radius-xs); background: var(--accent-gold); display: inline-block;"></span>
              เงินสด (Cash): <?= number_format($cashPct, 1) ?>% (<?= formatMoney($cashVal) ?>)
            </span>
            <span style="display: flex; align-items: center; gap: 6px; color: var(--color-success); white-space: nowrap;">
              <span style="width: 10px; height: 10px; border-radius: var(--radius-xs); background: var(--color-success); display: inline-block;"></span>
              หุ้น / ตราสารทุน (Stocks & Equities): <?= number_format($stocksPct, 1) ?>% (<?= formatMoney($stocksVal) ?>)
            </span>
          </div>
        </div>

        <div style="height: 10px; background: rgba(255, 255, 255, 0.06); border-radius: var(--radius-xs); display: flex; overflow: hidden;">
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

      <!-- Transaction History Card -->
      <div class="card">
        <div class="card-header">
          <div>
            <h2 class="card-title">
              <?= icon('history', 'text-gold', 18) ?> ประวัติคำสั่งซื้อ-ขายจำลอง (Order & Transaction Audit)
            </h2>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
              บันทึกประวัติการทำรายการย้อนหลังพร้อมค่าคอมมิชชัน
            </p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="financial-table">
            <thead>
              <tr>
                <th>วันที่ / เวลา</th>
                <th>ประเภทคำสั่ง</th>
                <th>ชื่อหุ้น</th>
                <th style="text-align: right;">จำนวนหุ้น</th>
                <th style="text-align: right;">ราคาที่ทำรายการ</th>
                <th style="text-align: right;">มูลค่ารวม</th>
                <th style="text-align: right;">ค่าคอมมิชชัน</th>
                <th style="text-align: right;">กำไร/ขาดทุนที่รับรู้จริง</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($transactions)): ?>
                <tr>
                  <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    ยังไม่มีประวัติการทำรายการ
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($transactions as $trx): ?>
                  <tr>
                    <td style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
                      <?= e(date('d/m/Y H:i', strtotime($trx['created_at']))) ?>
                    </td>
                    <td>
                      <?php if ($trx['transaction_type'] === 'BUY'): ?>
                        <span class="badge badge-gain">BUY (ซื้อ)</span>
                      <?php else: ?>
                        <span class="badge badge-loss">SELL (ขาย)</span>
                      <?php endif; ?>
                    </td>
                    <td style="font-weight: 700; font-family: var(--font-mono); white-space: nowrap;">
                      <?= e($trx['symbol']) ?>
                    </td>
                    <td class="num">
                      <?= number_format((int)$trx['quantity']) ?>
                    </td>
                    <td class="num">
                      <?= number_format((float)$trx['price'], 2) ?>
                    </td>
                    <td class="num font-bold">
                      <?= number_format((float)$trx['total_amount'], 2) ?>
                    </td>
                    <td class="num" style="color: var(--text-muted);">
                      <?= number_format((float)$trx['fee'], 2) ?>
                    </td>
                    <td class="num">
                      <?php if ($trx['realized_pnl'] !== null): ?>
                        <span class="<?= getPnlColorClass((float)$trx['realized_pnl']) ?> font-bold">
                          <?= ((float)$trx['realized_pnl'] >= 0 ? '+' : '') . number_format((float)$trx['realized_pnl'], 2) ?>
                        </span>
                      <?php else: ?>
                        <span style="color: var(--text-muted);">-</span>
                      <?php endif; ?>
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

  <!-- Trade Modal Dialog -->
  <div id="tradeModal" class="modal-backdrop">
    <div class="modal-content card">
      <div class="card-header">
        <h3 class="card-title" id="modalTitle" style="display: flex; align-items: center; gap: 0.5rem;">
          <?= icon('cart', 'text-gold', 18) ?> ส่งคำสั่งซื้อ-ขายจำลอง (Simulated Order)
        </h3>
        <button type="button" class="close-trade-modal-btn" style="color: var(--text-muted); display: flex; align-items: center;">
          <?= icon('close', '', 18) ?>
        </button>
      </div>

      <div class="card-body">
        <form action="<?= e(BASE_URL) ?>/pages/portfolio.php" method="POST">
          <input type="hidden" name="action" value="trade">
          <input type="hidden" id="trade_type" name="trade_type" value="BUY">

          <div class="form-group">
            <label for="trade_stock_id" class="form-label">เลือกหลักทรัพย์</label>
            <div class="form-input-wrapper">
              <select id="trade_stock_id" name="stock_id" class="form-input" style="cursor: pointer;" required>
                <?php foreach ($allStocks as $s): ?>
                  <option value="<?= (int)$s['id'] ?>" data-price="<?= (float)$s['current_price'] ?>">
                    <?= e($s['symbol']) ?> - <?= e($s['name_th'] ?: $s['name_en']) ?> (฿<?= number_format((float)$s['current_price'], 2) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-card-elevated); border-radius: var(--radius-sm);">
            <span style="color: var(--text-muted); white-space: nowrap;">ราคาตลาดล่าสุด:</span>
            <span id="modal_stock_price" data-price="<?= (float)($allStocks[0]['current_price'] ?? 0) ?>" style="font-weight: 700; color: var(--accent-gold); font-family: var(--font-mono); white-space: nowrap;">
              ฿<?= number_format((float)($allStocks[0]['current_price'] ?? 0), 2) ?>
            </span>
          </div>

          <div class="form-group">
            <label for="trade_quantity" class="form-label">จำนวนหุ้น (หุ้น)</label>
            <div class="form-input-wrapper">
              <input type="number" id="trade_quantity" name="quantity" class="form-input" placeholder="เช่น 100, 1000" min="1" step="1" value="100" required>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 1.5rem; padding: 0.75rem; border-top: 1px dashed var(--border-subtle);">
            <span style="color: var(--text-muted); white-space: nowrap;">มูลค่ารวมสุทธิ (+ค่าคอมมิชชัน 0.157%):</span>
            <span id="modal_trade_total" style="font-weight: 700; font-family: var(--font-mono); font-size: 1.1rem; color: var(--text-primary); white-space: nowrap;">
              ฿0.00
            </span>
          </div>

          <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn btn-outline close-trade-modal-btn" style="flex: 1;">
              ยกเลิก
            </button>
            <button type="submit" id="modal_submit_btn" class="btn btn-buy" style="flex: 2;">
              ยืนยันคำสั่ง
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="app-footer">
    <div class="container footer-bottom">
      <p>© 2026 SmartPort AI - ระบบวิเคราะห์หุ้นและพอร์ตจำลองอัจฉริยะ</p>
      <p>ข้อมูลจำลองเพื่อการศึกษาและการวิเคราะห์ ไม่ถือเป็นคำแนะนำทางการเงิน</p>
    </div>
  </footer>

  <script>
    window.BASE_URL = '<?= e(BASE_URL) ?>';
    
    // อัปเดตราคาหุ้นใน Modal เมื่อเปลี่ยนหุ้นที่เลือก
    const stockSelect = document.getElementById('trade_stock_id');
    const priceDisplay = document.getElementById('modal_stock_price');
    if (stockSelect && priceDisplay) {
      stockSelect.addEventListener('change', () => {
        const selectedOpt = stockSelect.options[stockSelect.selectedIndex];
        const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
        priceDisplay.setAttribute('data-price', price);
        priceDisplay.textContent = '฿' + price.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
        const qtyInput = document.getElementById('trade_quantity');
        if (qtyInput) qtyInput.dispatchEvent(new Event('input'));
      });
    }
  </script>
  <script src="<?= e(JS_URL) ?>/main.js"></script>
</body>
</html>
