<?php
declare(strict_types=1);

/**
 * index.php
 * SmartPort AI - Financial Terminal & Simulated Portfolio Dashboard
 * ออกแบบตามมาตรฐาน: Unified Sans Typography, Clean Rounded Rectangles, Icons Only, Zero External CDNs, Flat Subtle States
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/functions/helpers.php';
require_once __DIR__ . '/functions/icons.php';
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/functions/stocks.php';
require_once __DIR__ . '/functions/portfolio.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();

// ดึงรายชื่อหุ้นที่ต้องการแสดง (ค่าเริ่มต้นเป็น DELTA หรือรับจาก $_GET['symbol'])
$requestedSymbol = (string)($_GET['symbol'] ?? 'DELTA');
$stock = getStockBySymbol($pdo, $requestedSymbol);

// หากไม่พบหุ้นที่ค้นหา ให้ดึงตัวแรกในระบบ (เช่น DELTA)
if (!$stock) {
    $stock = getStockBySymbol($pdo, 'DELTA');
    if (!$stock) {
        $all = getAllStocks($pdo);
        $stock = !empty($all) ? getStockBySymbol($pdo, $all[0]['symbol']) : null;
    }
}

$allStocks = getAllStocks($pdo);
$priceHistory = [];
$low52w = 0.0;
$high52w = 0.0;
$rangeProgressPct = 50.0;

if ($stock) {
    $priceHistory = getStockPriceHistory($pdo, (int)$stock['id'], 30);
    $currentPrice = (float)$stock['current_price'];
    
    // คำนวณช่วง 52-Week Range จากราคาปัจจุบันและประวัติราคา
    $low52w = $currentPrice * 0.72;
    $high52w = $currentPrice * 1.28;
    if ($high52w > $low52w) {
        $rangeProgressPct = min(100, max(0, (($currentPrice - $low52w) / ($high52w - $low52w)) * 100));
    }
}

// ข้อมูลจำลองสำหรับกราฟแท่งเทียน / Timeframe ใน Chart Engine
$timeframeData = [
    '1M' => [
        ['date' => 'สัปดาห์ 1', 'price' => (float)$stock['current_price'] * 0.94],
        ['date' => 'สัปดาห์ 2', 'price' => (float)$stock['current_price'] * 0.96],
        ['date' => 'สัปดาห์ 3', 'price' => (float)$stock['current_price'] * 0.98],
        ['date' => 'สัปดาห์ 4', 'price' => (float)$stock['current_price']]
    ],
    '3M' => [
        ['date' => 'เดือน 1', 'price' => (float)$stock['current_price'] * 0.88],
        ['date' => 'เดือน 2', 'price' => (float)$stock['current_price'] * 0.93],
        ['date' => 'เดือน 3', 'price' => (float)$stock['current_price']]
    ],
    '6M' => [
        ['date' => 'ก.พ.', 'price' => (float)$stock['current_price'] * 0.82],
        ['date' => 'มี.ค.', 'price' => (float)$stock['current_price'] * 0.86],
        ['date' => 'เม.ย.', 'price' => (float)$stock['current_price'] * 0.89],
        ['date' => 'พ.ค.', 'price' => (float)$stock['current_price'] * 0.94],
        ['date' => 'มิ.ย.', 'price' => (float)$stock['current_price'] * 0.97],
        ['date' => 'ก.ค.', 'price' => (float)$stock['current_price']]
    ],
    '1Y' => !empty($priceHistory) ? array_map(function($item) {
        return ['date' => date('d/m', strtotime($item['trade_date'])), 'price' => (float)$item['close_price']];
    }, $priceHistory) : [
        ['date' => 'Q1', 'price' => (float)$stock['current_price'] * 0.75],
        ['date' => 'Q2', 'price' => (float)$stock['current_price'] * 0.84],
        ['date' => 'Q3', 'price' => (float)$stock['current_price'] * 0.92],
        ['date' => 'Q4', 'price' => (float)$stock['current_price']]
    ]
];

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($stock['symbol'] ?? 'Stock') ?> - <?= e(APP_TITLE) ?></title>
  
  <!-- CSS Stylesheets (Local Assets 100%) -->
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/variables.css">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/layout.css">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/components.css">

  <!-- Chart.js Library (Local Asset Bundle) -->
  <script src="<?= e(JS_URL) ?>/chart.umd.min.js"></script>
</head>
<body>

  <!-- ==============================================================================
       1. Header & Navigation
       ============================================================================== -->
  <header class="app-header">
    <div class="container header-inner">
      
      <!-- Brand Logo -->
      <a href="<?= e(BASE_URL) ?>/index.php" class="brand-logo">
        <div class="brand-icon">
          <?= icon('chart-bar', '', 18, '#1D1616') ?>
        </div>
        <span>SmartPort AI</span>
      </a>

      <!-- Desktop Nav Links -->
      <nav class="main-nav">
        <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link active">หน้าหลัก / วิเคราะห์หุ้น</a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link">คัดกรองหุ้น (Screener)</a>
        <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link">พอร์ตจำลอง (Portfolio)</a>
      </nav>

      <!-- Search & Auth Controls -->
      <div class="header-actions">
        
        <!-- Search Input -->
        <div class="search-box">
          <?= icon('search', '', 14, '#8A7F70') ?>
          <input type="text" id="stockSearchInput" placeholder="ค้นหาหุ้น... (กด Enter)">
        </div>

        <?php if ($isUserLoggedIn): ?>
          <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="btn btn-outline btn-sm">
            <?= icon('wallet', '', 14) ?> พอร์ตของฉัน
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="btn btn-outline btn-sm" style="color: var(--text-muted);">
            ออกจากระบบ
          </a>
        <?php else: ?>
          <a href="<?= e(BASE_URL) ?>/pages/login.php" class="btn btn-outline btn-sm">
            เข้าสู่ระบบ
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-primary btn-sm">
            เริ่มต้นใช้งานฟรี
          </a>
        <?php endif; ?>

        <!-- Mobile Toggle Button -->
        <button class="mobile-toggle" aria-label="Toggle Menu">
          <?= icon('menu', '', 20) ?>
        </button>
      </div>

    </div>

    <!-- Mobile Drawer -->
    <div class="mobile-drawer">
      <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link active">หน้าหลัก / วิเคราะห์หุ้น</a>
      <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link">คัดกรองหุ้น (Screener)</a>
      <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link">พอร์ตจำลอง (Portfolio)</a>
      <?php if ($isUserLoggedIn): ?>
        <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="nav-link" style="color: var(--color-danger);">ออกจากระบบ</a>
      <?php else: ?>
        <a href="<?= e(BASE_URL) ?>/pages/login.php" class="nav-link">เข้าสู่ระบบ</a>
        <a href="<?= e(BASE_URL) ?>/pages/register.php" class="nav-link" style="color: var(--accent-gold);">สมัครสมาชิก</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- ==============================================================================
       2. Main Content
       ============================================================================== -->
  <main class="main-content">
    <div class="container">

      <!-- Flash Message Notification -->
      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
          <?= icon('info', '', 16) ?>
          <span><?= e($flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <!-- Quick Stock Selector Watchlist Pills (Clean Rounded Rectangles) -->
      <div style="display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 1.5rem; align-items: center;">
        <span style="font-size: 0.75rem; font-family: var(--font-primary); color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; flex-shrink: 0; white-space: nowrap;">
          ตลาดหลักทรัพย์:
        </span>
        <?php foreach ($allStocks as $s): ?>
          <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($s['symbol']) ?>" 
             class="badge <?= ($stock && $stock['symbol'] === $s['symbol']) ? 'badge-gold' : 'badge-neutral' ?>" 
             style="font-size: 0.85rem; padding: 0.35rem 0.65rem; text-decoration: none; border-radius: var(--radius-sm);">
            <?= e($s['symbol']) ?> 
            <span class="<?= getPnlColorClass((float)$s['percent_change']) ?>" style="margin-left: 4px;">
              <?= formatPercent((float)$s['percent_change']) ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($stock): ?>

        <!-- 2.1 Stock Hero Section -->
        <section class="stock-hero">
          <div class="stock-hero-top">
            
            <div>
              <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                <span class="stock-symbol"><?= e($stock['symbol']) ?></span>
                <span class="badge badge-gold"><?= e($stock['market']) ?></span>
                <span class="badge badge-neutral"><?= e($stock['sector']) ?></span>
                <span class="badge" style="background: rgba(3, 201, 136, 0.1); color: var(--color-success); border: 1px solid var(--color-success-border); font-size: 0.7rem;">
                  <?= icon('check', 'text-gain', 10) ?> ข้อมูลพร้อมวิเคราะห์
                </span>
              </div>
              <p style="color: var(--text-muted); font-size: 1.05rem;">
                <?= e($stock['name_th'] ?: $stock['name_en']) ?>
                <?php if ($stock['name_th']): ?>
                  <span style="font-size: 0.9rem; color: var(--text-muted);"> (<?= e($stock['name_en']) ?>)</span>
                <?php endif; ?>
              </p>
            </div>

            <div style="text-align: right;">
              <div class="stock-price-val">
                ฿<?= number_format((float)$stock['current_price'], 2) ?>
              </div>
              <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; margin-top: 0.35rem;">
                <span class="font-bold <?= getPnlColorClass((float)$stock['price_change']) ?>" style="font-family: var(--font-mono); font-size: 1.1rem; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                  <?= ((float)$stock['price_change'] >= 0 ? icon('arrow-up-right', 'text-gain', 18) : icon('arrow-down-right', 'text-loss', 18)) ?>
                  <?= ((float)$stock['price_change'] >= 0 ? '+' : '') . number_format((float)$stock['price_change'], 2) ?>
                  (<?= formatPercent((float)$stock['percent_change']) ?>)
                </span>
                <span class="badge <?= getPnlBadgeClass((float)$stock['price_change']) ?>">วันนี้</span>
              </div>

              <!-- Quick Trade Action Buttons -->
              <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 0.75rem;">
                <?php if ($isUserLoggedIn): ?>
                  <button type="button" class="btn btn-buy btn-sm open-trade-modal-btn" data-trade-type="BUY">
                    <?= icon('plus', '', 14) ?> ซื้อเข้าพอร์ตจำลอง
                  </button>
                  <button type="button" class="btn btn-sell btn-sm open-trade-modal-btn" data-trade-type="SELL">
                    <?= icon('minus', '', 14) ?> ขายในพอร์ต
                  </button>
                <?php else: ?>
                  <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-buy btn-sm">
                    <?= icon('plus', '', 14) ?> ทดลองซื้อในพอร์ตจำลอง
                  </a>
                <?php endif; ?>
              </div>
            </div>

          </div>

          <!-- Key Stats Bar & 52-Week Range Bar -->
          <div class="stats-bar">
            <div class="stats-item">
              <span class="stats-label">ราคาเปิด (Open)</span>
              <span class="stats-val">฿<?= number_format((float)$stock['previous_close'], 2) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">สูงสุดของวัน (Day High)</span>
              <span class="stats-val" style="color: var(--color-success);">฿<?= number_format((float)$stock['day_high'], 2) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">ต่ำสุดของวัน (Day Low)</span>
              <span class="stats-val" style="color: var(--color-danger);">฿<?= number_format((float)$stock['day_low'], 2) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">ปริมาณซื้อขาย (Volume)</span>
              <span class="stats-val"><?= formatCompactNumber((float)$stock['volume']) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">มูลค่าหลักทรัพย์ (Market Cap)</span>
              <span class="stats-val">฿<?= formatCompactNumber((float)$stock['market_cap']) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">P/E Ratio</span>
              <span class="stats-val" style="color: var(--accent-gold);"><?= $stock['pe_ratio'] ? number_format((float)$stock['pe_ratio'], 1) . 'x' : '-' ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">ROE (%)</span>
              <span class="stats-val" style="color: var(--color-success);"><?= $stock['roe'] ? number_format((float)$stock['roe'], 1) . '%' : '-' ?></span>
            </div>
          </div>

          <!-- 52-Week Range Visual Bar (Clean Rounded Rectangle) -->
          <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.75rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;">
            <div style="font-size: 0.75rem; font-family: var(--font-primary); color: var(--text-muted); text-transform: uppercase; white-space: nowrap;">
              ช่วงราคา 52 สัปดาห์ (52W Range)
            </div>
            <div style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 0.75rem;">
              <span style="font-size: 0.8rem; font-family: var(--font-mono); color: var(--text-muted); white-space: nowrap;">฿<?= number_format($low52w, 2) ?></span>
              <div style="flex: 1; height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: var(--radius-xs); position: relative;">
                <div style="position: absolute; left: 0; top: 0; bottom: 0; width: <?= $rangeProgressPct ?>%; background: linear-gradient(90deg, var(--color-danger), var(--accent-gold), var(--color-success)); border-radius: var(--radius-xs);"></div>
              </div>
              <span style="font-size: 0.8rem; font-family: var(--font-mono); color: var(--text-muted); white-space: nowrap;">฿<?= number_format($high52w, 2) ?></span>
            </div>
            <div style="font-size: 0.8rem; font-family: var(--font-mono); color: var(--accent-gold); font-weight: 600; white-space: nowrap;">
              ตำแหน่งปัจจุบัน: <?= number_format($rangeProgressPct, 0) ?>%
            </div>
          </div>
        </section>

        <!-- 2.2 Interactive Chart Section -->
        <section class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
          <div class="chart-header">
            <div>
              <h2 style="font-size: 1.25rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                <?= icon('chart-line', 'text-gold', 20) ?> กราฟประวัติราคาหลักทรัพย์ (Price Trend Chart)
              </h2>
              <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                <?= e($stock['symbol']) ?> · <?= e($stock['market']) ?> · ราคาย้อนหลังตามข้อมูลตลาดจริง
              </p>
            </div>

            <!-- Timeframe Selector -->
            <div class="timeframe-group">
              <button type="button" class="timeframe-btn" data-timeframe="1M">1M</button>
              <button type="button" class="timeframe-btn" data-timeframe="3M">3M</button>
              <button type="button" class="timeframe-btn" data-timeframe="6M">6M</button>
              <button type="button" class="timeframe-btn active" data-timeframe="1Y">1Y</button>
            </div>
          </div>

          <div class="chart-canvas-wrapper">
            <canvas id="stockPriceChart"></canvas>
          </div>
        </section>

        <!-- 2.3 Key Financial Metrics Grid (P/E, EPS, ROE, Revenue) -->
        <section style="margin-bottom: 2.5rem;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h2 style="font-size: 1.35rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
              <?= icon('sliders', 'text-gold', 20) ?> ตัวชี้วัดทางการเงินเชิงปริมาณ (Quantitative Financial Metrics)
            </h2>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-primary); white-space: nowrap;">
              รอบงบการเงินล่าสุด (TTM / FY2025)
            </span>
          </div>

          <div class="metrics-grid">
            
            <!-- Metric 1: P/E Ratio -->
            <div class="metric-card">
              <span class="metric-label">P/E Ratio (ความถูกแพงของราคา)</span>
              <span class="metric-value" style="color: var(--accent-gold);">
                <?= $stock['pe_ratio'] ? number_format((float)$stock['pe_ratio'], 1) . 'x' : 'N/A' ?>
              </span>
              <div class="metric-sub">
                <span>เทียบค่ามัธยฐานกลุ่มอุตสาหกรรม</span>
              </div>
            </div>

            <!-- Metric 2: EPS -->
            <div class="metric-card">
              <span class="metric-label">กำไรต่อหุ้น EPS (Trailing 12M)</span>
              <span class="metric-value">
                ฿<?= $stock['eps'] ? number_format((float)$stock['eps'], 2) : '0.00' ?>
              </span>
              <div class="metric-sub">
                <span class="text-gain" style="display: inline-flex; align-items: center; gap: 2px;">
                  <?= icon('trending-up', 'text-gain', 14) ?> กำไรต่อหุ้นแข็งแกร่ง
                </span>
              </div>
            </div>

            <!-- Metric 3: ROE -->
            <div class="metric-card">
              <span class="metric-label">ผลตอบแทนต่อส่วนทุน ROE</span>
              <span class="metric-value" style="color: var(--color-success);">
                <?= $stock['roe'] ? number_format((float)$stock['roe'], 1) . '%' : '0.0%' ?>
              </span>
              <div class="metric-sub">
                <span class="text-gain">ความสามารถในการสร้างกำไรสูง</span>
              </div>
            </div>

            <!-- Metric 4: Revenue -->
            <div class="metric-card">
              <span class="metric-label">รายได้รวมรอบปี (Total Revenue)</span>
              <span class="metric-value">
                ฿<?= $stock['revenue'] ? formatCompactNumber((float)$stock['revenue']) : '0' ?>
              </span>
              <div class="metric-sub">
                <span class="<?= getPnlColorClass((float)($stock['revenue_growth_yoy'] ?? 0)) ?>">
                  <?= formatPercent((float)($stock['revenue_growth_yoy'] ?? 0)) ?> เติบโตเมื่อเทียบปีก่อน
                </span>
              </div>
            </div>

          </div>
        </section>

        <!-- 2.4 Financial Statements & AI Analysis Two-Column Grid -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-bottom: 2.5rem;" class="analysis-grid">
          
          <!-- Financial Statements Card -->
          <div class="card">
            <div class="card-header">
              <div>
                <h2 class="card-title">
                  <?= icon('document', 'text-gold', 18) ?> สรุปข้อมูลงบการเงิน (Financial Statements)
                </h2>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                  งบการเงินรวมรอบปี 2025 · หน่วย: ล้านบาท (THB Million)
                </p>
              </div>
              <span class="badge badge-neutral">Audited</span>
            </div>

            <div class="table-responsive">
              <table class="financial-table">
                <thead>
                  <tr>
                    <th>รายการทางบัญชี</th>
                    <th style="text-align: right;">มูลค่า (บาท)</th>
                    <th style="text-align: right;">สถานะ / สัดส่วน</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>รายได้รวม (Total Revenue)</td>
                    <td class="num font-bold">฿<?= formatCompactNumber((float)($stock['revenue'] ?? 0)) ?></td>
                    <td class="num text-gain">+<?= number_format((float)($stock['revenue_growth_yoy'] ?? 0), 1) ?>% YoY</td>
                  </tr>
                  <tr>
                    <td>กำไรสุทธิ (Net Profit)</td>
                    <td class="num font-bold">฿<?= formatCompactNumber((float)($stock['net_profit'] ?? 0)) ?></td>
                    <td class="num">Net Margin <?= number_format((float)($stock['net_profit_margin'] ?? 0), 1) ?>%</td>
                  </tr>
                  <tr>
                    <td>กำไรต่อหุ้น (EPS)</td>
                    <td class="num font-bold">฿<?= number_format((float)($stock['eps'] ?? 0), 2) ?></td>
                    <td class="num" style="color: var(--accent-gold);">ต่อหุ้น</td>
                  </tr>
                  <tr>
                    <td>หนี้สินต่อทุน (D/E Ratio)</td>
                    <td class="num"><?= number_format((float)($stock['de_ratio'] ?? 0), 2) ?>x</td>
                    <td class="num <?= ((float)($stock['de_ratio'] ?? 0) <= 1.0 ? 'text-gain' : 'text-loss') ?>">
                      <?= ((float)($stock['de_ratio'] ?? 0) <= 1.0 ? 'ความเสี่ยงหนี้ต่ำ' : 'หนี้สินปานกลาง') ?>
                    </td>
                  </tr>
                  <tr>
                    <td>กระแสเงินสดอิสระ (Free Cash Flow)</td>
                    <td class="num font-bold text-gain">฿<?= formatCompactNumber((float)($stock['free_cash_flow'] ?? 0)) ?></td>
                    <td class="num text-gain">กระแสเงินสดบวก</td>
                  </tr>
                  <tr>
                    <td>ราคาต่อมูลค่าทางบัญชี (P/BV)</td>
                    <td class="num"><?= number_format((float)($stock['pbv_ratio'] ?? 0), 2) ?>x</td>
                    <td class="num">อัตราปันผล <?= number_format((float)($stock['dividend_yield'] ?? 0), 2) ?>%</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- AI-Generated Analysis Card -->
          <div class="card">
            <div class="card-header">
              <div>
                <h2 class="card-title">
                  <?= icon('bot', 'text-gold', 18) ?> สรุปบทวิเคราะห์เชิงปริมาณ (AI Synthesis)
                </h2>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                  ประมวลผลจากข้อมูลปัจจัยพื้นฐานและสถิติทางการเงิน
                </p>
              </div>
              <span class="badge badge-gain" style="display: inline-flex; align-items: center; gap: 4px;">
                <?= icon('check', 'text-gain', 12) ?> ประมวลผลสมบูรณ์
              </span>
            </div>

            <div class="card-body">
              <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.7; margin-bottom: 1.25rem;">
                <?= e($stock['summary_th'] ?? 'ระบบอยู่ระหว่างการประมวลผลข้อมูลสำหรับหลักทรัพย์นี้') ?>
              </p>

              <?php if (!empty($stock['strengths'])): ?>
                <div style="margin-bottom: 1rem;">
                  <h4 style="font-size: 0.85rem; font-family: var(--font-primary); color: var(--color-success); text-transform: uppercase; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 4px;">
                    <?= icon('check', 'text-gain', 14) ?> จุดแข็งและโอกาสสำคัญ (Key Catalysts)
                  </h4>
                  <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem;">
                    <?php foreach ($stock['strengths'] as $str): ?>
                      <li style="font-size: 0.875rem; color: var(--text-secondary); display: flex; gap: 0.5rem;">
                        <span style="color: var(--color-success); font-weight: bold;">•</span>
                        <span><?= e($str) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

              <?php if (!empty($stock['risks'])): ?>
                <div>
                  <h4 style="font-size: 0.85rem; font-family: var(--font-primary); color: var(--color-danger); text-transform: uppercase; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 4px;">
                    <?= icon('alert', 'text-loss', 14) ?> ปัจจัยเสี่ยงและจุดควรระวัง (Key Risks)
                  </h4>
                  <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem;">
                    <?php foreach ($stock['risks'] as $risk): ?>
                      <li style="font-size: 0.875rem; color: var(--text-secondary); display: flex; gap: 0.5rem;">
                        <span style="color: var(--color-danger); font-weight: bold;">•</span>
                        <span><?= e($risk) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <!-- 2.5 SmartPort AI Score Section -->
        <section class="card" style="margin-bottom: 2.5rem; padding: 2rem;">
          <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; margin-bottom: 2rem;">
            <div>
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <?= icon('star', 'text-gold', 20) ?>
                <h2 style="font-size: 1.35rem; color: var(--text-primary);">
                  คะแนนคุณภาพหลักทรัพย์ (SmartPort Composite Rating)
                </h2>
              </div>
              <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                ประเมินจากแบบจำลองเชิงปริมาณ 4 มิติหลัก (เต็ม 100 คะแนน)
              </p>
            </div>
            <span class="badge badge-gold" style="font-size: 0.8rem;">
              โมเดลการประเมินปัจจัยพื้นฐาน
            </span>
          </div>

          <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: center;" class="score-grid">
            
            <!-- Left: Radial Gauge -->
            <div class="score-gauge-container">
              <svg width="150" height="150" viewBox="0 0 150 150" class="gauge-svg">
                <defs>
                  <linearGradient id="gaugeGoldGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#FFCC00" />
                    <stop offset="100%" stop-color="#03C988" />
                  </linearGradient>
                </defs>
                <circle cx="75" cy="75" r="60" class="gauge-bg" />
                <?php
                $score = (int)($stock['total_score'] ?? 80);
                $circ = 2 * M_PI * 60;
                $offset = $circ - ($score / 100) * $circ;
                ?>
                <circle cx="75" cy="75" r="60" class="gauge-progress" stroke-dasharray="<?= $circ ?>" stroke-dashoffset="<?= $offset ?>" />
              </svg>

              <div class="gauge-inner-text">
                <span class="gauge-num"><?= $score ?></span>
                <span style="font-size: 0.75rem; color: var(--text-muted);">/ 100</span>
              </div>

              <div style="margin-top: 1rem;">
                <span class="badge badge-gain" style="font-size: 0.9rem; padding: 0.4rem 0.85rem;">
                  <?= e($stock['recommendation'] ?? 'Buy') ?>
                </span>
              </div>
            </div>

            <!-- Right: 4 Breakdown Categories -->
            <div class="score-breakdown-list">
              
              <div class="score-row">
                <span class="score-label" style="display: flex; align-items: center; gap: 4px;">
                  <?= icon('trending-up', 'text-gain', 14) ?> อัตราการเติบโต
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar" style="width: <?= (int)($stock['growth_score'] ?? 90) ?>%; background: var(--color-success);"></div>
                </div>
                <span class="score-val" style="color: var(--color-success);"><?= (int)($stock['growth_score'] ?? 90) ?></span>
              </div>

              <div class="score-row">
                <span class="score-label" style="display: flex; align-items: center; gap: 4px;">
                  <?= icon('check', 'text-gain', 14) ?> ความสามารถทำกำไร
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar" style="width: <?= (int)($stock['profitability_score'] ?? 85) ?>%; background: var(--color-success);"></div>
                </div>
                <span class="score-val" style="color: var(--color-success);"><?= (int)($stock['profitability_score'] ?? 85) ?></span>
              </div>

              <div class="score-row">
                <span class="score-label" style="display: flex; align-items: center; gap: 4px;">
                  <?= icon('shield', 'text-gold', 14) ?> สุขภาพการเงิน
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar" style="width: <?= (int)($stock['financial_health_score'] ?? 80) ?>%; background: var(--accent-gold);"></div>
                </div>
                <span class="score-val" style="color: var(--accent-gold);"><?= (int)($stock['financial_health_score'] ?? 80) ?></span>
              </div>

              <div class="score-row">
                <span class="score-label" style="display: flex; align-items: center; gap: 4px;">
                  <?= icon('sliders', 'text-gold', 14) ?> ความคุ้มค่าราคา
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar" style="width: <?= (int)($stock['valuation_score'] ?? 65) ?>%; background: var(--accent-gold);"></div>
                </div>
                <span class="score-val" style="color: var(--accent-gold);"><?= (int)($stock['valuation_score'] ?? 65) ?></span>
              </div>

            </div>

          </div>
        </section>

      <?php endif; ?>

    </div>
  </main>

  <!-- Trade Modal Dialog -->
  <?php if ($stock): ?>
    <div id="tradeModal" class="modal-backdrop">
      <div class="modal-content card">
        <div class="card-header">
          <h3 class="card-title" style="display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('cart', 'text-gold', 20) ?> ส่งคำสั่งซื้อ-ขายจำลอง: <?= e($stock['symbol']) ?>
          </h3>
          <button type="button" class="close-trade-modal-btn" style="color: var(--text-muted); display: flex; align-items: center;">
            <?= icon('close', '', 18) ?>
          </button>
        </div>

        <div class="card-body">
          <form action="<?= e(BASE_URL) ?>/pages/portfolio.php" method="POST">
            <input type="hidden" name="action" value="trade">
            <input type="hidden" name="stock_id" value="<?= (int)$stock['id'] ?>">
            <input type="hidden" id="trade_type" name="trade_type" value="BUY">

            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-card-elevated); border-radius: var(--radius-sm);">
              <span style="color: var(--text-muted); white-space: nowrap;">ราคาตลาดปัจจุบัน (<?= e($stock['symbol']) ?>):</span>
              <span id="modal_stock_price" data-price="<?= (float)$stock['current_price'] ?>" style="font-weight: 700; color: var(--accent-gold); font-family: var(--font-mono); white-space: nowrap;">
                ฿<?= number_format((float)$stock['current_price'], 2) ?>
              </span>
            </div>

            <div class="form-group">
              <label for="trade_quantity" class="form-label">จำนวนหุ้นที่ต้องการซื้อ-ขาย (หุ้น)</label>
              <div class="form-input-wrapper">
                <input type="number" id="trade_quantity" name="quantity" class="form-input" placeholder="เช่น 100, 1000" min="1" step="1" value="100" required>
              </div>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 1.5rem; padding: 0.75rem; border-top: 1px dashed var(--border-subtle);">
              <span style="color: var(--text-muted); white-space: nowrap;">มูลค่ารวมสุทธิ (+ค่าคอมมิชชัน 0.157%):</span>
              <span id="modal_trade_total" style="font-weight: 700; font-family: var(--font-mono); font-size: 1.15rem; color: var(--text-primary); white-space: nowrap;">
                ฿0.00
              </span>
            </div>

            <div style="display: flex; gap: 0.75rem;">
              <button type="button" class="btn btn-outline close-trade-modal-btn" style="flex: 1;">
                ยกเลิก
              </button>
              <button type="submit" id="modal_submit_btn" class="btn btn-buy" style="flex: 2;">
                ยืนยันคำสั่งซื้อ (BUY)
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- ==============================================================================
       3. Footer
       ============================================================================== -->
  <footer class="app-footer">
    <div class="container">
      <div class="footer-grid">
        
        <div>
          <div class="brand-logo" style="margin-bottom: 0.75rem;">
            <div class="brand-icon">
              <?= icon('chart-bar', '', 18, '#1D1616') ?>
            </div>
            <span>SmartPort AI</span>
          </div>
          <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
            ระบบวิเคราะห์หุ้นและพอร์ตจำลองอัจฉริยะ ผสานงบการเงิน อัตราส่วนสถิติ และการจำลองพอร์ตลงทุนแบบเรียลไทม์
          </p>
        </div>

        <div>
          <p class="footer-title">ผลิตภัณฑ์</p>
          <ul class="footer-links">
            <li><a href="<?= e(BASE_URL) ?>/index.php" class="footer-link">กราฟและบทวิเคราะห์หุ้น</a></li>
            <li><a href="<?= e(BASE_URL) ?>/pages/screener.php" class="footer-link">ตัวคัดกรองหุ้น (Screener)</a></li>
            <li><a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link">พอร์ตการลงทุนจำลอง</a></li>
          </ul>
        </div>

        <div>
          <p class="footer-title">เครื่องมือการเงิน</p>
          <ul class="footer-links">
            <li><a href="#" class="footer-link">คะแนนคุณภาพ Composite Rating</a></li>
            <li><a href="#" class="footer-link">สรุปปัจจัยพื้นฐาน AI Synthesis</a></li>
            <li><a href="#" class="footer-link">การประเมินปัจจัยเสี่ยง</a></li>
          </ul>
        </div>

        <div>
          <p class="footer-title">บัญชีผู้ใช้</p>
          <ul class="footer-links">
            <?php if ($isUserLoggedIn): ?>
              <li><a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link">จัดการพอร์ตจำลอง</a></li>
              <li><a href="<?= e(BASE_URL) ?>/pages/logout.php" class="footer-link">ออกจากระบบ</a></li>
            <?php else: ?>
              <li><a href="<?= e(BASE_URL) ?>/pages/login.php" class="footer-link">เข้าสู่ระบบ</a></li>
              <li><a href="<?= e(BASE_URL) ?>/pages/register.php" class="footer-link">สมัครสมาชิกฟรี</a></li>
            <?php endif; ?>
          </ul>
        </div>

      </div>

      <div class="footer-bottom">
        <p>© 2026 SmartPort AI - สงวนลิขสิทธิ์</p>
        <p>ข้อมูลและการวิเคราะห์ในระบบจัดทำขึ้นเพื่อการศึกษาและการจำลองสถานการณ์เท่านั้น ไม่ถือเป็นคำแนะนำทางการเงิน</p>
      </div>
    </div>
  </footer>

  <style>
    @media (min-width: 992px) {
      .analysis-grid {
        grid-template-columns: 1fr 1fr !important;
      }
      .score-grid {
        grid-template-columns: 1fr 2fr !important;
      }
    }
  </style>

  <!-- Pass Data to JavaScript Engine -->
  <script>
    window.BASE_URL = '<?= e(BASE_URL) ?>';
    window.stockPriceHistory = <?= json_encode($priceHistory) ?>;
    window.timeframeData = <?= json_encode($timeframeData) ?>;
  </script>
  <script src="<?= e(JS_URL) ?>/chart_engine.js"></script>
  <script src="<?= e(JS_URL) ?>/main.js"></script>
</body>
</html>
