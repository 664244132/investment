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
    $isThaiStock = ($stock['market'] === 'SET' || $stock['market'] === 'mai' || str_ends_with($stock['symbol'], '.BK'));
    $currSym = $isThaiStock ? '฿' : '$';
    
    if ($isUserLoggedIn && $userId > 0) {
        $isBookmarked = isStockInWatchlist($pdo, $userId, (int)$stock['id']);
    }

    $altmanZ = calculateAltmanZScore($stock);
    $piotroskiF = calculatePiotroskiFScore($stock);

    // คำนวณช่วง 52-Week Range และตำแหน่งเปอร์เซ็นต์จริงจาก API และสถิติ
    $range52wData = calculateStock52WeekRange($pdo, $stock);
    $low52w = $range52wData['low_52w'];
    $high52w = $range52wData['high_52w'];
    $rangeProgressPct = $range52wData['progress_pct'];
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
              <div class="modal-title-flex flex-wrap gap-xs section-mb-sm">
                <span class="stock-symbol"><?= e($stock['symbol']) ?></span>
                
                <!-- Quick Bookmark Button -->
                <button type="button" class="btn-bookmark <?= $isBookmarked ? 'bookmarked' : '' ?>" 
                        id="heroBookmarkBtn" 
                        onclick="window.toggleWatchlistStock(<?= e((string)$stock['id']) ?>, this)"
                        title="<?= $isBookmarked ? 'นำออกจาก Watchlist' : 'เพิ่มใน Watchlist' ?>" 
                        aria-label="Toggle Watchlist">
                  <span class="bm-icon-outline <?= $isBookmarked ? 'd-none' : '' ?>">
                    <?= icon('bookmark', '', 16) ?>
                  </span>
                  <span class="bm-icon-filled <?= $isBookmarked ? '' : 'd-none' ?>">
                    <?= icon('bookmark-filled', 'text-gold', 16) ?>
                  </span>
                </button>

                <a href="<?= e(BASE_URL) ?>/pages/compare.php?symbols=<?= e($stock['symbol']) ?>" class="btn btn-outline btn-sm" title="เปรียบเทียบ <?= e($stock['symbol']) ?> กับหุ้นคู่แข่ง">
                  <?= icon('scale', 'text-gold', 12) ?> เปรียบเทียบคู่แข่ง
                </a>
              </div>
              <p class="hero-company-name">
                <?= e($stock['name_th'] ?: $stock['name_en']) ?>
                <?php if ($stock['name_th'] && $stock['name_en'] && $stock['name_th'] !== $stock['name_en']): ?>
                  <span class="hero-company-en"> (<?= e($stock['name_en']) ?>)</span>
                <?php endif; ?>
              </p>
            </div>

            <div>
              <div class="stock-price-val">
                <?= e($currSym) ?><?= number_format((float)$stock['current_price'], 2) ?>
              </div>
              <div class="modal-title-flex justify-end section-mb-xs">
                <span class="font-bold font-mono text-lg d-inline-flex align-center gap-xs <?= getPnlColorClass((float)$stock['price_change']) ?>">
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
              <span class="stats-val"><?= e($currSym) ?><?= number_format((float)$stock['previous_close'], 2) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">สูงสุดของวัน (Day High)</span>
              <span class="stats-val text-gain"><?= e($currSym) ?><?= number_format((float)$stock['day_high'], 2) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">ต่ำสุดของวัน (Day Low)</span>
              <span class="stats-val text-loss"><?= e($currSym) ?><?= number_format((float)$stock['day_low'], 2) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">ปริมาณซื้อขาย (Volume)</span>
              <span class="stats-val"><?= formatCompactNumber((float)$stock['volume']) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">มูลค่าหลักทรัพย์ (Market Cap)</span>
              <span class="stats-val"><?= e($currSym) ?><?= formatCompactNumber((float)$stock['market_cap']) ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">P/E Ratio</span>
              <span class="stats-val text-gold"><?= $stock['pe_ratio'] ? number_format((float)$stock['pe_ratio'], 1) . 'x' : '-' ?></span>
            </div>
            <div class="stats-item">
              <span class="stats-label">ROE (%)</span>
              <span class="stats-val text-gain"><?= $stock['roe'] ? number_format((float)$stock['roe'], 1) . '%' : '-' ?></span>
            </div>
          </div>

          <div class="range-52w-card">
            <div class="range-52w-title">
              ช่วงราคา 52 สัปดาห์ (52W Range)
            </div>
            <div class="range-52w-slider">
              <span class="range-52w-num"><?= e($currSym) ?><?= number_format($low52w, 2) ?></span>
              <div class="range-52w-track">
                <div class="range-52w-fill" style="--progress-pct: <?= number_format($rangeProgressPct, 1) ?>%;"></div>
              </div>
              <span class="range-52w-num"><?= e($currSym) ?><?= number_format($high52w, 2) ?></span>
            </div>
            <div class="range-52w-pos">
              ตำแหน่งปัจจุบัน: <?= number_format($rangeProgressPct, 0) ?>%
            </div>
          </div>
        </section>

        <section class="card section-card">
          <div class="chart-header">
            <div>
              <h2 class="chart-title-heading">
                <?= icon('chart-line', 'text-gold', 20) ?> กราฟประวัติราคาหลักทรัพย์ (Price Trend Chart)
              </h2>
              <p class="chart-subtitle-meta">
                <?= e($stock['symbol']) ?> · <?= e($stock['market']) ?> · ราคาย้อนหลังตามข้อมูลตลาดจริง
              </p>
            </div>

            <div class="timeframe-group" id="chartTimeframeGroup">
              <button type="button" class="timeframe-btn" data-timeframe="1H" title="รายชั่วโมง (1H / 5 วันย้อนหลัง)">1H</button>
              <button type="button" class="timeframe-btn" data-timeframe="1D" title="รายวัน (1D / Intraday)">1D</button>
              <button type="button" class="timeframe-btn" data-timeframe="1W" title="รายอาทิตย์ (1W / 1 สัปดาห์)">1W</button>
              <button type="button" class="timeframe-btn active" data-timeframe="1M" title="1 เดือน (1M / รายวัน 1 เดือน)">1M</button>
              <button type="button" class="timeframe-btn" data-timeframe="1Y" title="1 ปี (1Y / รายสัปดาห์ 1 ปี)">1Y</button>
              <button type="button" class="timeframe-btn" data-timeframe="5Y" title="5 ปี (5Y / รายเดือน 5 ปี)">5Y</button>
            </div>
          </div>

          <div class="chart-canvas-wrapper">
            <canvas id="stockPriceChart"></canvas>
          </div>
        </section>

        <section class="section-mb-lg">
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
              <span class="metric-value text-gold">
                <?= $stock['pe_ratio'] ? number_format((float)$stock['pe_ratio'], 1) . 'x' : 'N/A' ?>
              </span>
              <div class="metric-sub">
                <span>เทียบค่ามัธยฐานกลุ่มอุตสาหกรรม</span>
              </div>
            </div>

            <div class="metric-card">
              <span class="metric-label">กำไรต่อหุ้น EPS (Trailing 12M)</span>
              <span class="metric-value">
                <?= e($currSym) ?><?= $stock['eps'] ? number_format((float)$stock['eps'], 2) : '0.00' ?>
              </span>
              <div class="metric-sub">
                <span class="text-gain d-inline-flex align-center gap-xs">
                  <?= icon('trending-up', 'text-gain', 14) ?> กำไรต่อหุ้นแข็งแกร่ง
                </span>
              </div>
            </div>

            <div class="metric-card">
              <span class="metric-label">ผลตอบแทนต่อส่วนทุน ROE</span>
              <span class="metric-value text-gain">
                <?= $stock['roe'] ? number_format((float)$stock['roe'], 1) . '%' : '0.0%' ?>
              </span>
              <div class="metric-sub">
                <span>ประสิทธิภาพสร้างผลตอบแทน</span>
              </div>
            </div>

            <div class="metric-card">
              <span class="metric-label">อัตราเงินปันผลตอบแทน (Dividend Yield)</span>
              <span class="metric-value text-gold">
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
                <p class="metric-subtitle-meta">
                  งบการเงินรวมรอบปี 2025 · หน่วย: <?= $isThaiStock ? 'ล้านบาท (THB Million)' : 'ล้านดอลลาร์ (USD Million)' ?>
                </p>
              </div>
              <span class="badge badge-neutral">Audited</span>
            </div>

            <div class="table-responsive">
              <table class="financial-table">
                <thead>
                  <tr>
                    <th>รายการทางบัญชี</th>
                    <th class="th-right">มูลค่า (<?= $isThaiStock ? 'บาท' : 'ดอลลาร์' ?>)</th>
                    <th class="th-right">สถานะ / สัดส่วน</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>รายได้รวม (Total Revenue)</td>
                    <td class="num font-bold"><?= e($currSym) ?><?= formatCompactNumber((float)($stock['revenue'] ?? 0)) ?></td>
                    <td class="num text-gain">+<?= number_format((float)($stock['revenue_growth_yoy'] ?? 0), 1) ?>% YoY</td>
                  </tr>
                  <tr>
                    <td>กำไรสุทธิ (Net Profit)</td>
                    <td class="num font-bold"><?= e($currSym) ?><?= formatCompactNumber((float)($stock['net_profit'] ?? 0)) ?></td>
                    <td class="num">Net Margin <?= number_format((float)($stock['net_profit_margin'] ?? 0), 1) ?>%</td>
                  </tr>
                  <tr>
                    <td>กำไรต่อหุ้น (EPS)</td>
                    <td class="num font-bold"><?= e($currSym) ?><?= number_format((float)($stock['eps'] ?? 0), 2) ?></td>
                    <td class="num text-gold">ต่อหุ้น</td>
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
                    <td class="num font-bold text-gain"><?= e($currSym) ?><?= formatCompactNumber((float)($stock['free_cash_flow'] ?? 0)) ?></td>
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
                <p class="metric-subtitle-meta">
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
                <div class="section-mb-sm">
                  <h4 class="analysis-list-title-gain">
                    <?= icon('check', 'text-gain', 14) ?> จุดแข็งและโอกาสสำคัญ (Key Catalysts)
                  </h4>
                  <ul class="analysis-list">
                    <?php foreach ($stock['strengths'] as $str): ?>
                      <li class="analysis-list-item">
                        <span class="bullet-gain">•</span>
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
                        <span class="bullet-loss">•</span>
                        <span><?= e($risk) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <section class="card section-card-lg">
          <div class="card-header-flex">
            <div>
              <div class="card-header-left">
                <?= icon('star', 'text-gold', 20) ?>
                <h2 class="card-header-title">
                  คะแนนคุณภาพหลักทรัพย์ (SmartPort Composite Rating)
                </h2>
              </div>
              <p class="chart-subtitle-meta">
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
                <span class="score-max-label">/ 100</span>
              </div>

              <div class="section-mb-sm">
                <span class="badge badge-gain">
                  <?= icon('check', 'text-gain', 14) ?> <?= e($stock['recommendation'] ?? 'Buy') ?>
                </span>
              </div>
            </div>

            <div class="score-breakdown-list">
              <div class="score-row">
                <span class="score-label d-flex align-center gap-xs">
                  <?= icon('trending-up', 'text-gain', 14) ?> อัตราการเติบโต
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar score-bar-gain" style="--score-pct: <?= (int)($stock['growth_score'] ?? 90) ?>%;"></div>
                </div>
                <span class="score-val text-gain"><?= (int)($stock['growth_score'] ?? 90) ?></span>
              </div>

              <div class="score-row">
                <span class="score-label d-flex align-center gap-xs">
                  <?= icon('check', 'text-gain', 14) ?> ความสามารถทำกำไร
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar score-bar-gain" style="--score-pct: <?= (int)($stock['profitability_score'] ?? 85) ?>%;"></div>
                </div>
                <span class="score-val text-gain"><?= (int)($stock['profitability_score'] ?? 85) ?></span>
              </div>

              <div class="score-row">
                <span class="score-label d-flex align-center gap-xs">
                  <?= icon('shield', 'text-gold', 14) ?> สุขภาพการเงิน
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar score-bar-gold" style="--score-pct: <?= (int)($stock['financial_health_score'] ?? 80) ?>%;"></div>
                </div>
                <span class="score-val text-gold"><?= (int)($stock['financial_health_score'] ?? 80) ?></span>
              </div>

              <div class="score-row">
                <span class="score-label d-flex align-center gap-xs">
                  <?= icon('sliders', 'text-gold', 14) ?> ความคุ้มค่าราคา
                </span>
                <div class="score-progress-track">
                  <div class="score-progress-bar score-bar-gold" style="--score-pct: <?= (int)($stock['valuation_score'] ?? 65) ?>%;"></div>
                </div>
                <span class="score-val text-gold"><?= (int)($stock['valuation_score'] ?? 65) ?></span>
              </div>
            </div>
          </div>

          <?php if ($altmanZ && $piotroskiF): ?>
            <!-- Advanced Statistical Models: Altman Z-Score & Piotroski F-Score -->
            <div class="models-grid">
              
              <!-- Altman Z-Score Card -->
              <div class="model-card">
                <div class="model-card-head">
                  <div class="model-card-title-group">
                    <div class="model-card-title">
                      <?= icon('shield', 'text-gold', 18) ?>
                      <span>Altman Z-Score</span>
                    </div>
                    <span class="model-card-subtitle">ความเสี่ยงการล้มละลาย</span>
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
                    <span class="score-max-label">/ 8.50</span>
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
                <p class="model-desc-text">
                  <?= e($altmanZ['description']) ?>
                </p>
              </div>

              <!-- Piotroski F-Score Card -->
              <div class="model-card">
                <div class="model-card-head">
                  <div class="model-card-title-group">
                    <div class="model-card-title">
                      <?= icon('star', 'text-gold', 18) ?>
                      <span>Piotroski F-Score</span>
                    </div>
                    <span class="model-card-subtitle">คุณภาพงบการเงิน 9 ด้าน</span>
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
                    <span class="score-max-label">/ <?= $piotroskiF['max_score'] ?> คะแนน</span>
                  </div>
                  <div class="score-progress-track mt-xs">
                    <div class="score-progress-bar <?= ($piotroskiF['score'] >= 8) ? 'score-bar-gain' : (($piotroskiF['score'] >= 5) ? 'score-bar-gold' : 'score-bar-loss') ?>" style="--score-pct: <?= number_format(($piotroskiF['score'] / 9) * 100, 1) ?>%;"></div>
                  </div>
                </div>
                <p class="model-desc-text">
                  <?= e($piotroskiF['description']) ?>
                </p>

                <!-- Checklist Dropdown details -->
                <details class="model-details-box">
                  <summary class="model-details-summary">
                    <?= icon('sliders', '', 12) ?> ดูรายละเอียดเกณฑ์ 9 ข้อ (Fundamental Checklist)
                  </summary>
                  <div class="fscore-checklist-box">
                    <?php foreach ($piotroskiF['checklist'] as $item): ?>
                      <div class="fscore-check-item">
                        <span class="fscore-item-title <?= $item['passed'] ? 'passed' : '' ?>">
                          <?= $item['passed'] ? icon('check-circle', 'text-gain', 14) : icon('close', 'text-loss', 14) ?>
                          <?= e($item['title']) ?>
                        </span>
                        <span class="badge <?= $item['passed'] ? 'badge-gain' : 'badge-neutral' ?> badge-xs">
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
    window.stockSymbol = ' . json_encode($stock ? $stock['symbol'] : '') . ';
    window.stockCurrency = ' . json_encode($currSym ?? '฿') . ';
    window.stockPriceHistory = ' . json_encode($priceHistory ?? []) . ';
  </script>
  <script src="' . e(JS_URL) . '/chart_engine.js?v=' . e(APP_VERSION) . '"></script>';

  require_once __DIR__ . '/templates/footer.php';
  ?>
