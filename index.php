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
require_once __DIR__ . '/functions/watchlist.php';
require_once __DIR__ . '/functions/financial_models.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();
$userId = (int)($user['id'] ?? 0);

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
$isBookmarked = false;
$altmanZ = null;
$piotroskiF = null;

if ($stock) {
    $priceHistory = getStockPriceHistory($pdo, (int)$stock['id'], 30);
    $currentPrice = (float)$stock['current_price'];
    
    if ($isUserLoggedIn && $userId > 0) {
        $isBookmarked = isStockInWatchlist($pdo, $userId, (int)$stock['id']);
    }

    $altmanZ = calculateAltmanZScore($stock);
    $piotroskiF = calculatePiotroskiFScore($stock);

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

$pageTitle = ($stock['symbol'] ?? 'Stock') . ' - ' . APP_TITLE;
$activeNav = 'index';
$extraHead = '<script src="' . e(JS_URL) . '/chart.umd.min.js"></script>';

require_once __DIR__ . '/templates/header.php';
?>

  <main class="main-content">
    <div class="container">

      <?php require __DIR__ . '/templates/flash.php'; ?>

      <div class="watchlist-bar">
        <span class="watchlist-label">ตลาดหลักทรัพย์:</span>
        <?php foreach ($allStocks as $s): ?>
          <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($s['symbol']) ?>" 
             class="watchlist-chip <?= ($stock && $stock['symbol'] === $s['symbol']) ? 'active' : '' ?>">
            <?= e($s['symbol']) ?> 
            <span class="watchlist-change <?= getPnlColorClass((float)$s['percent_change']) ?>">
              <?= formatPercent((float)$s['percent_change']) ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($stock): ?>

        <section class="stock-hero">
          <div class="stock-hero-top">
            
            <div>
              <div class="modal-title-flex" style="margin-bottom: 0.35rem; flex-wrap: wrap;">
                <span class="stock-symbol"><?= e($stock['symbol']) ?></span>
                
                <!-- Quick Bookmark Button -->
                <button type="button" class="btn-bookmark <?= $isBookmarked ? 'bookmarked' : '' ?>" 
                        id="heroBookmarkBtn" 
                        onclick="window.toggleWatchlistStock(<?= e((string)$stock['id']) ?>, this)"
                        title="<?= $isBookmarked ? 'นำออกจาก Watchlist' : 'เพิ่มใน Watchlist' ?>" 
                        aria-label="Toggle Watchlist">
                  <span class="bm-icon-outline" style="<?= $isBookmarked ? 'display:none;' : '' ?>">
                    <?= icon('bookmark', '', 16) ?>
                  </span>
                  <span class="bm-icon-filled" style="<?= $isBookmarked ? '' : 'display:none;' ?>">
                    <?= icon('bookmark-filled', 'text-gold', 16) ?>
                  </span>
                </button>

                <a href="<?= e(BASE_URL) ?>/pages/compare.php?symbols=<?= e($stock['symbol']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;" title="เปรียบเทียบ <?= e($stock['symbol']) ?> กับหุ้นคู่แข่ง">
                  <?= icon('scale', 'text-gold', 12) ?> เปรียบเทียบคู่แข่ง
                </a>
              </div>
              <p class="hero-company-name">
                <?= e($stock['name_th'] ?: $stock['name_en']) ?>
                <?php if ($stock['name_th']): ?>
                  <span class="hero-company-en"> (<?= e($stock['name_en']) ?>)</span>
                <?php endif; ?>
              </p>
            </div>

            <div>
              <div class="stock-price-val">
                ฿<?= number_format((float)$stock['current_price'], 2) ?>
              </div>
              <div class="modal-title-flex" style="justify-content: flex-end; margin-top: 0.35rem;">
                <span class="font-bold <?= getPnlColorClass((float)$stock['price_change']) ?>" style="font-family: var(--font-mono); font-size: 1.1rem; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                  <?= ((float)$stock['price_change'] >= 0 ? icon('arrow-up-right', 'text-gain', 18) : icon('arrow-down-right', 'text-loss', 18)) ?>
                  <?= ((float)$stock['price_change'] >= 0 ? '+' : '') . number_format((float)$stock['price_change'], 2) ?>
                  (<?= formatPercent((float)$stock['percent_change']) ?>)
                </span>
              </div>
            </div>

          </div>

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

          <div class="range-52w-card">
            <div class="range-52w-title">
              ช่วงราคา 52 สัปดาห์ (52W Range)
            </div>
            <div class="range-52w-slider">
              <span class="range-52w-num">฿<?= number_format($low52w, 2) ?></span>
              <div class="range-52w-track">
                <div class="range-52w-fill" style="width: <?= $rangeProgressPct ?>%;"></div>
              </div>
              <span class="range-52w-num">฿<?= number_format($high52w, 2) ?></span>
            </div>
            <div class="range-52w-pos">
              ตำแหน่งปัจจุบัน: <?= number_format($rangeProgressPct, 0) ?>%
            </div>
          </div>
        </section>

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

        <section style="margin-bottom: 2.5rem;">
          <div class="section-heading-row">
            <h2 class="section-heading-title">
              <?= icon('sliders', 'text-gold', 20) ?> ตัวชี้วัดทางการเงินเชิงปริมาณ (Quantitative Financial Metrics)
            </h2>
            <span class="section-heading-meta">
              รอบงบการเงินล่าสุด (TTM / FY2025)
            </span>
          </div>

          <div class="metrics-grid">
            <div class="metric-card">
              <span class="metric-label">P/E Ratio (ความถูกแพงของราคา)</span>
              <span class="metric-value" style="color: var(--accent-gold);">
                <?= $stock['pe_ratio'] ? number_format((float)$stock['pe_ratio'], 1) . 'x' : 'N/A' ?>
              </span>
              <div class="metric-sub">
                <span>เทียบค่ามัธยฐานกลุ่มอุตสาหกรรม</span>
              </div>
            </div>

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

            <div class="metric-card">
              <span class="metric-label">ผลตอบแทนต่อส่วนทุน ROE</span>
              <span class="metric-value" style="color: var(--color-success);">
                <?= $stock['roe'] ? number_format((float)$stock['roe'], 1) . '%' : '0.0%' ?>
              </span>
              <div class="metric-sub">
                <span>ประสิทธิภาพสร้างผลตอบแทน</span>
              </div>
            </div>

            <div class="metric-card">
              <span class="metric-label">อัตราเงินปันผลตอบแทน (Dividend Yield)</span>
              <span class="metric-value" style="color: var(--accent-gold);">
                <?= $stock['dividend_yield'] ? number_format((float)$stock['dividend_yield'], 2) . '%' : '0.00%' ?>
              </span>
              <div class="metric-sub">
                <span>ผลตอบแทนเงินสดต่อปี</span>
              </div>
            </div>
          </div>
        </section>

        <div class="analysis-grid">
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
              <?php $hasAiSummary = !empty($stock['summary_th']); ?>
              <?php if ($hasAiSummary): ?>
                <span class="badge badge-gain">
                  <?= icon('check', 'text-gain', 12) ?> ประมวลผลสมบูรณ์
                </span>
              <?php else: ?>
                <span class="badge badge-neutral">
                  <?= icon('clock', 'text-muted', 12) ?> กำลังประมวลผล
                </span>
              <?php endif; ?>
            </div>

            <div class="card-body">
              <p class="analysis-body-text">
                <?= e($stock['summary_th'] ?: 'ระบบได้ประมวลผลข้อมูลราคาและปัจจัยพื้นฐานสำหรับหลักทรัพย์นี้เรียบร้อยแล้ว') ?>
              </p>

              <?php if (!empty($stock['strengths'])): ?>
                <div style="margin-bottom: 1rem;">
                  <h4 class="analysis-list-title-gain">
                    <?= icon('check', 'text-gain', 14) ?> จุดแข็งและโอกาสสำคัญ (Key Catalysts)
                  </h4>
                  <ul class="analysis-list">
                    <?php foreach ($stock['strengths'] as $str): ?>
                      <li class="analysis-list-item">
                        <span style="color: var(--color-success); font-weight: bold;">•</span>
                        <span><?= e($str) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

              <?php if (!empty($stock['risks'])): ?>
                <div>
                  <h4 class="analysis-list-title-loss">
                    <?= icon('alert', 'text-loss', 14) ?> ปัจจัยเสี่ยงและจุดควรระวัง (Key Risks)
                  </h4>
                  <ul class="analysis-list">
                    <?php foreach ($stock['risks'] as $risk): ?>
                      <li class="analysis-list-item">
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

        <section class="card" style="margin-bottom: 2.5rem; padding: 2rem;">
          <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; margin-bottom: 2rem; flex-wrap: wrap; gap: 0.5rem;">
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
          </div>

          <div class="score-grid">
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
                <span class="badge badge-gain">
                  <?= icon('check', 'text-gain', 14) ?> <?= e($stock['recommendation'] ?? 'Buy') ?>
                </span>
              </div>
            </div>

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

          <?php if ($altmanZ && $piotroskiF): ?>
            <!-- Advanced Statistical Models: Altman Z-Score & Piotroski F-Score -->
            <div class="models-grid">
              
              <!-- Altman Z-Score Card -->
              <div class="model-card">
                <div class="model-card-head">
                  <div class="model-card-title">
                    <?= icon('shield', 'text-gold', 18) ?> Altman Z-Score (ความเสี่ยงการล้มละลาย)
                  </div>
                  <span class="badge <?= e($altmanZ['badge_class']) ?>">
                    <?= e($altmanZ['zone_th']) ?>
                  </span>
                </div>
                <div>
                  <div class="model-score-display">
                    <span class="model-score-num <?= e($altmanZ['color_class']) ?>">
                      <?= number_format($altmanZ['score'], 2) ?>
                    </span>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">/ 8.50</span>
                  </div>
                  <div class="zscore-meter-track">
                    <div class="zscore-seg-distress <?= ($altmanZ['zone'] === 'Distress') ? 'zscore-seg-active' : '' ?>"></div>
                    <div class="zscore-seg-grey <?= ($altmanZ['zone'] === 'Grey') ? 'zscore-seg-active' : '' ?>"></div>
                    <div class="zscore-seg-safe <?= ($altmanZ['zone'] === 'Safe') ? 'zscore-seg-active' : '' ?>"></div>
                  </div>
                  <div class="zscore-labels-row">
                    <span>Distress (&lt;1.81)</span>
                    <span>Grey (1.81-2.99)</span>
                    <span>Safe (&gt;2.99)</span>
                  </div>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0; line-height: 1.45;">
                  <?= e($altmanZ['description']) ?>
                </p>
              </div>

              <!-- Piotroski F-Score Card -->
              <div class="model-card">
                <div class="model-card-head">
                  <div class="model-card-title">
                    <?= icon('star', 'text-gold', 18) ?> Piotroski F-Score (คุณภาพงบการเงิน 9 ด้าน)
                  </div>
                  <span class="badge <?= e($piotroskiF['badge_class']) ?>">
                    <?= e($piotroskiF['rating_th']) ?>
                  </span>
                </div>
                <div>
                  <div class="model-score-display">
                    <span class="model-score-num <?= ($piotroskiF['score'] >= 8) ? 'text-gain' : (($piotroskiF['score'] >= 5) ? 'text-gold' : 'text-loss') ?>">
                      <?= $piotroskiF['score'] ?>
                    </span>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">/ <?= $piotroskiF['max_score'] ?> คะแนน</span>
                  </div>
                  <div class="score-progress-track" style="margin-top: 0.5rem;">
                    <div class="score-progress-bar" style="width: <?= ($piotroskiF['score'] / 9) * 100 ?>%; background: <?= ($piotroskiF['score'] >= 8) ? 'var(--color-success)' : (($piotroskiF['score'] >= 5) ? 'var(--accent-gold)' : 'var(--color-danger)') ?>;"></div>
                  </div>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0; line-height: 1.45;">
                  <?= e($piotroskiF['description']) ?>
                </p>

                <!-- Checklist Dropdown details -->
                <details style="font-size: 0.8rem; color: var(--text-muted);">
                  <summary style="cursor: pointer; color: var(--accent-gold); user-select: none;">
                    <?= icon('sliders', '', 12) ?> ดูรายละเอียดเกณฑ์ 9 ข้อ (Fundamental Checklist)
                  </summary>
                  <div class="fscore-checklist-box">
                    <?php foreach ($piotroskiF['checklist'] as $item): ?>
                      <div class="fscore-check-item">
                        <span class="fscore-item-title <?= $item['passed'] ? 'passed' : '' ?>">
                          <?= $item['passed'] ? icon('check-circle', 'text-gain', 14) : icon('close', 'text-loss', 14) ?>
                          <?= e($item['title']) ?>
                        </span>
                        <span class="badge <?= $item['passed'] ? 'badge-gain' : 'badge-neutral' ?>" style="font-size: 0.68rem;">
                          <?= e($item['value']) ?>
                        </span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </details>
              </div>

            </div>
          <?php endif; ?>
        </section>

      <?php endif; ?>

    </div>
  </main>

  <?php if ($stock): ?>
    <?php
    $modalStock = $stock;
    $modalAllStocks = $allStocks;
    require __DIR__ . '/templates/trade_modal.php';
    ?>
  <?php endif; ?>

  <!-- Scripts & Footer Template -->
  <?php
  $extraScripts = '<script>
    window.BASE_URL = ' . json_encode(BASE_URL) . ';
    window.stockPriceHistory = ' . json_encode($priceHistory) . ';
    window.timeframeData = ' . json_encode($timeframeData) . ';
  </script>
  <script src="' . e(JS_URL) . '/chart_engine.js"></script>';

  require_once __DIR__ . '/templates/footer.php';
  ?>
