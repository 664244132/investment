<?php
declare(strict_types=1);

/**
 * pages/compare.php
 * SmartPort AI - Stock Peer Comparison & Benchmark Matrix
 * เปรียบเทียบตัวชี้วัดทางการเงินเชิงลึกและคะแนน AI ระหว่างหุ้น 2-4 ตัวเคียงข้างกัน
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/comparison.php';
require_once __DIR__ . '/../functions/portfolio.php';
require_once __DIR__ . '/../functions/financial_models.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();

// รับรายชื่อหุ้นที่ต้องการเปรียบเทียบจาก $_GET['symbols']
$rawSymbols = (string)($_GET['symbols'] ?? 'DELTA,NVDA,PTT');
$symbolArray = array_filter(array_map('trim', explode(',', $rawSymbols)));

if (empty($symbolArray)) {
    $symbolArray = ['DELTA', 'NVDA', 'PTT'];
}

$comparedStocks = getStocksForComparison($pdo, $symbolArray);

// หากไม่พบหุ้นตามที่ระบุ ให้ดึงหุ้นเริ่มต้น
if (empty($comparedStocks)) {
    $comparedStocks = getStocksForComparison($pdo, ['DELTA', 'NVDA', 'PTT']);
}

$allStocks = getAllStocks($pdo);
$winners = identifyWinningMetrics($comparedStocks);
$peerAvg = calculatePeerAverages($comparedStocks);

// เตรียมข้อมูลสำหรับ Chart.js Multi-Bar Comparison
$chartLabels = ['คะแนนการเติบโต (Growth)', 'ความสามารถทำกำไร (Profitability)', 'สุขภาพการเงิน (Health)', 'ความคุ้มค่าราคา (Valuation)'];
$chartColors = [
    ['bg' => 'rgba(255, 204, 0, 0.75)', 'border' => '#FFCC00'],
    ['bg' => 'rgba(3, 201, 136, 0.75)', 'border' => '#03C988'],
    ['bg' => 'rgba(0, 180, 216, 0.75)', 'border' => '#00B4D8'],
    ['bg' => 'rgba(238, 108, 77, 0.75)', 'border' => '#EE6C4D']
];

$chartDatasets = [];
foreach ($comparedStocks as $idx => $s) {
    $color = $chartColors[$idx % count($chartColors)];
    $chartDatasets[] = [
        'label' => $s['symbol'],
        'data' => [
            (int)($s['growth_score'] ?? 75),
            (int)($s['profitability_score'] ?? 75),
            (int)($s['financial_health_score'] ?? 75),
            (int)($s['valuation_score'] ?? 75)
        ],
        'backgroundColor' => $color['bg'],
        'borderColor' => $color['border'],
        'borderWidth' => 1.5,
        'borderRadius' => 4
    ];
}

$pageTitle = 'เปรียบเทียบหุ้นคู่แข่ง - ' . APP_TITLE;
$activeNav = 'compare';
$extraHead = '<script src="' . e(JS_URL) . '/chart.umd.min.js"></script>';

require_once __DIR__ . '/../templates/header.php';
?>

  <main class="main-content">
    <div class="container">

      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <!-- Header Section -->
      <div class="section-heading-row section-mb-md">
        <div>
          <h1 class="section-heading-title">
            <?= icon('scale', 'text-gold', 24) ?> เปรียบเทียบหุ้นคู่แข่ง (Stock Peer Comparison)
          </h1>
          <p class="chart-subtitle-meta">
            วิเคราะห์จุดแข็ง-จุดอ่อน เปรียบเทียบอัตราส่วนงบการเงิน และโมเดลคะแนน AI สรุปผู้ชนะในแต่ละมิติ (สูงสุด 4 บริษัท)
          </p>
        </div>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="btn btn-outline btn-sm">
          <?= icon('filter', '', 14) ?> คัดกรองหุ้นใน Screener
        </a>
      </div>

      <!-- Quick Preset Comparison Strategies -->
      <div class="watchlist-bar">
        <span class="watchlist-label">กลุ่มเปรียบเทียบยอดนิยม:</span>
        <a href="<?= e(BASE_URL) ?>/pages/compare.php?symbols=DELTA,NVDA,AAPL" class="watchlist-chip <?= ($rawSymbols === 'DELTA,NVDA,AAPL') ? 'active' : '' ?>">
          ยักษ์ใหญ่ AI & Tech (DELTA vs NVDA vs AAPL)
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/compare.php?symbols=PTT,SCB,CPALL" class="watchlist-chip <?= ($rawSymbols === 'PTT,SCB,CPALL') ? 'active' : '' ?>">
          หุ้นใหญ่ปันผลสูง (PTT vs SCB vs CPALL)
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/compare.php?symbols=BDMS,CPALL,AOT" class="watchlist-chip <?= ($rawSymbols === 'BDMS,CPALL,AOT') ? 'active' : '' ?>">
          หุ้นบริโภค & สุขภาพ (BDMS vs CPALL vs AOT)
        </a>
      </div>

      <!-- Active Compared Stocks Pills & Search Selector -->
      <div class="compare-pills-bar">
        <span class="watchlist-label font-bold text-muted">
          หุ้นที่เลือก (<?= count($comparedStocks) ?>/4):
        </span>

        <?php foreach ($comparedStocks as $cs): ?>
          <div class="compare-pill">
            <span><?= e($cs['symbol']) ?></span>
            <?php if (count($comparedStocks) > 1): 
              // คำนวณ URL เมื่อลบหุ้นตัวนี้ออก
              $remSymbols = array_filter($symbolArray, function($sym) use ($cs) {
                  return strtoupper($sym) !== strtoupper($cs['symbol']);
              });
              $remUrl = BASE_URL . '/pages/compare.php?symbols=' . implode(',', $remSymbols);
            ?>
              <a href="<?= e($remUrl) ?>" class="compare-pill-remove" title="นำ <?= e($cs['symbol']) ?> ออก" aria-label="Remove <?= e($cs['symbol']) ?>">
                <?= icon('close', '', 14) ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php if (count($comparedStocks) < 4): ?>
          <!-- Dropdown เลือกหุ้นเพิ่ม -->
          <div class="d-flex align-center gap-sm ml-auto">
            <select id="addStockSelect" class="form-input form-select-sm" onchange="handleAddStockToCompare(this)">
              <option value="">+ เพิ่มหุ้นเปรียบเทียบ...</option>
              <?php foreach ($allStocks as $as): 
                $alreadySelected = in_array(strtoupper($as['symbol']), array_map('strtoupper', $symbolArray), true);
                if (!$alreadySelected):
              ?>
                <option value="<?= e($as['symbol']) ?>">
                  <?= e($as['symbol']) ?> - <?= e($as['name_th'] ?: $as['name_en']) ?> (<?= e($as['market']) ?>)
                </option>
              <?php endif; endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
      </div>

      <!-- Spotlight Champions Summary Grid -->
      <?php if (count($comparedStocks) >= 2): ?>
        <div class="compare-champions-grid">
          
          <!-- AI Score Champion -->
          <?php if (!empty($winners['total_score'])): ?>
            <div class="champion-card">
              <div class="champion-icon-box">
                <?= icon('star', 'text-gold', 22) ?>
              </div>
              <div class="champion-info">
                <span class="champion-title">AI Score สูงสุด</span>
                <span class="champion-symbol"><?= e($winners['total_score']) ?></span>
                <span class="champion-metric">คะแนนรวมอันดับ 1</span>
              </div>
            </div>
          <?php endif; ?>

          <!-- Valuation Champion -->
          <?php if (!empty($winners['pe_ratio'])): ?>
            <div class="champion-card">
              <div class="champion-icon-box">
                <?= icon('sliders', 'text-gain', 22) ?>
              </div>
              <div class="champion-info">
                <span class="champion-title">Valuation คุ้มค่าสุด (P/E ต่ำ)</span>
                <span class="champion-symbol"><?= e($winners['pe_ratio']) ?></span>
                <span class="champion-metric">P/E ต่ำสุดในกลุ่ม</span>
              </div>
            </div>
          <?php endif; ?>

          <!-- Profitability Champion -->
          <?php if (!empty($winners['roe'])): ?>
            <div class="champion-card">
              <div class="champion-icon-box">
                <?= icon('trending-up', 'text-gold', 22) ?>
              </div>
              <div class="champion-info">
                <span class="champion-title">ผลตอบแทนต่อทุน (ROE) สูงสุด</span>
                <span class="champion-symbol"><?= e($winners['roe']) ?></span>
                <span class="champion-metric">ความสามารถทำกำไรสูงสุด</span>
              </div>
            </div>
          <?php endif; ?>

          <!-- Dividend Champion -->
          <?php if (!empty($winners['dividend_yield'])): ?>
            <div class="champion-card">
              <div class="champion-icon-box">
                <?= icon('zap', 'text-gold', 22) ?>
              </div>
              <div class="champion-info">
                <span class="champion-title">เงินปันผลตอบแทน (Yield) สูงสุด</span>
                <span class="champion-symbol"><?= e($winners['dividend_yield']) ?></span>
                <span class="champion-metric">Yield สูงสุดในกลุ่ม</span>
              </div>
            </div>
          <?php endif; ?>

        </div>
      <?php endif; ?>

      <!-- Multi-Dimension AI Score Radar / Bar Chart -->
      <div class="card section-mb-lg">
        <div class="card-header">
          <h2 class="card-title">
            <?= icon('chart-bar', 'text-gold', 18) ?> แผนภูมิเปรียบเทียบคะแนน AI 4 มิติ (Growth / Profit / Health / Value)
          </h2>
          <span class="metric-subtitle-meta">เกณฑ์คะแนนเต็ม 100 ในแต่ละด้าน</span>
        </div>
        <div class="card-body">
          <div class="compare-chart-wrapper">
            <canvas id="compareBarChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Financial Comparison Matrix Table -->
      <div class="compare-matrix-wrapper">
        <div class="table-responsive">
          <table class="compare-matrix-table">
            <thead>
              <tr>
                <th class="metric-col-head">ตัวชี้วัดทางการเงิน / มิติการประเมิน</th>
                <?php foreach ($comparedStocks as $s): ?>
                  <th class="stock-col-head">
                    <div class="compare-stock-header-col">
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($s['symbol']) ?>" class="compare-symbol-link">
                        <?= e($s['symbol']) ?>
                      </a>
                      <span class="badge badge-neutral badge-xs"><?= e($s['market']) ?> · <?= e($s['sector']) ?></span>
                      <span class="compare-price-val">
                        ฿<?= number_format((float)$s['current_price'], 2) ?>
                      </span>
                    </div>
                  </th>
                <?php endforeach; ?>
                <th class="benchmark-col-head">
                  ค่าเฉลี่ยกลุ่ม<br><span class="metric-subtitle-meta">(Peer Average)</span>
                </th>
              </tr>
            </thead>
            <tbody>

              <!-- Section: AI Evaluation -->
              <tr class="category-section-row">
                <td colspan="<?= count($comparedStocks) + 2 ?>">
                  <?= icon('star', 'text-gold', 14) ?> 1. การประเมินและคะแนนรวม AI (AI Composite Score)
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">AI Composite Score (0-100)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['total_score'] ?? '') === $s['symbol'];
                  $score = (int)($s['total_score'] ?? 0);
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <span class="<?= $isWinner ? 'score-text-winner' : 'score-text-regular' ?>">
                      <?= $score > 0 ? $score : '-' ?>
                    </span>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('award', '', 10) ?> อันดับ 1</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['total_score'] ?? '-' ?>
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">AI Recommendation Tag</td>
                <?php foreach ($comparedStocks as $s): 
                  $rec = (string)($s['recommendation'] ?? 'Hold');
                  $badgeClass = ($rec === 'Strong Buy' || $rec === 'Buy') ? 'badge-gain' : (($rec === 'Sell') ? 'badge-loss' : 'badge-gold');
                ?>
                  <td class="stock-val-cell">
                    <span class="badge <?= $badgeClass ?> badge-sm">
                      <?= e($rec) ?>
                    </span>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">-</td>
              </tr>

              <!-- Section: Valuation -->
              <tr class="category-section-row">
                <td colspan="<?= count($comparedStocks) + 2 ?>">
                  <?= icon('sliders', 'text-gold', 14) ?> 2. ความคุ้มค่าของราคา (Valuation Multiples)
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">P/E Ratio (เท่า)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['pe_ratio'] ?? '') === $s['symbol'];
                  $pe = $s['pe_ratio'] !== null ? (float)$s['pe_ratio'] : null;
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <?= $pe ? number_format($pe, 1) . 'x' : '-' ?>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('check', '', 10) ?> ถูกสุด</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['pe_ratio'] ? number_format((float)$peerAvg['pe_ratio'], 1) . 'x' : '-' ?>
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">P/BV Ratio (เท่า)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['pbv_ratio'] ?? '') === $s['symbol'];
                  $pbv = $s['pbv_ratio'] !== null ? (float)$s['pbv_ratio'] : null;
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <?= $pbv ? number_format($pbv, 2) . 'x' : '-' ?>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('check', '', 10) ?> ถูกสุด</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['pbv_ratio'] ? number_format((float)$peerAvg['pbv_ratio'], 2) . 'x' : '-' ?>
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">กำไรต่อหุ้น (EPS - บาท)</td>
                <?php foreach ($comparedStocks as $s): ?>
                  <td class="stock-val-cell">
                    <?= $s['eps'] !== null ? '฿' . number_format((float)$s['eps'], 2) : '-' ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">-</td>
              </tr>

              <!-- Section: Profitability -->
              <tr class="category-section-row">
                <td colspan="<?= count($comparedStocks) + 2 ?>">
                  <?= icon('trending-up', 'text-gold', 14) ?> 3. ความสามารถในการทำกำไร (Profitability & Returns)
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">ROE (% ผลตอบแทนต่อส่วนของผู้ถือหุ้น)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['roe'] ?? '') === $s['symbol'];
                  $roe = $s['roe'] !== null ? (float)$s['roe'] : null;
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <?= $roe ? number_format($roe, 1) . '%' : '-' ?>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('award', '', 10) ?> สูงสุด</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['roe'] ? number_format((float)$peerAvg['roe'], 1) . '%' : '-' ?>
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">อัตรากำไรสุทธิ (Net Margin %)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['net_profit_margin'] ?? '') === $s['symbol'];
                  $npm = $s['net_profit_margin'] !== null ? (float)$s['net_profit_margin'] : null;
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <?= $npm ? number_format($npm, 1) . '%' : '-' ?>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('award', '', 10) ?> สูงสุด</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['net_profit_margin'] ? number_format((float)$peerAvg['net_profit_margin'], 1) . '%' : '-' ?>
                </td>
              </tr>

              <!-- Section: Growth & Dividend -->
              <tr class="category-section-row">
                <td colspan="<?= count($comparedStocks) + 2 ?>">
                  <?= icon('zap', 'text-gold', 14) ?> 4. การเติบโตและเงินปันผล (Growth & Dividend)
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">การเติบโตของรายได้ (Revenue Growth YoY)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['revenue_growth_yoy'] ?? '') === $s['symbol'];
                  $growth = $s['revenue_growth_yoy'] !== null ? (float)$s['revenue_growth_yoy'] : null;
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <?= $growth ? sprintf('%+.1f%%', $growth) : '-' ?>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('award', '', 10) ?> เติบโตเด่น</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['revenue_growth_yoy'] ? sprintf('%+.1f%%', $peerAvg['revenue_growth_yoy']) : '-' ?>
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">อัตราเงินปันผลตอบแทน (Dividend Yield %)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['dividend_yield'] ?? '') === $s['symbol'];
                  $yield = $s['dividend_yield'] !== null ? (float)$s['dividend_yield'] : null;
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <?= $yield ? number_format($yield, 2) . '%' : '-' ?>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('zap', '', 10) ?> ปันผลสูงสุด</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['dividend_yield'] ? number_format((float)$peerAvg['dividend_yield'], 2) . '%' : '-' ?>
                </td>
              </tr>

              <!-- Section: Financial Health -->
              <tr class="category-section-row">
                <td colspan="<?= count($comparedStocks) + 2 ?>">
                  <?= icon('shield', 'text-gold', 14) ?> 5. ภาระหนี้และสุขภาพทางการเงิน (Solvency & Health)
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">Altman Z-Score (ความเสี่ยงล้มละลาย)</td>
                <?php foreach ($comparedStocks as $s): 
                  $z = calculateAltmanZScore($s);
                ?>
                  <td class="stock-val-cell">
                    <span class="font-bold text-base <?= e($z['color_class']) ?>">
                      <?= number_format($z['score'], 2) ?>
                    </span>
                    <br>
                    <span class="badge <?= e($z['badge_class']) ?> badge-xs">
                      <?= e($z['zone']) ?>
                    </span>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">-</td>
              </tr>

              <tr>
                <td class="metric-title-cell">Piotroski F-Score (เกณฑ์งบ 9 จุด)</td>
                <?php foreach ($comparedStocks as $s): 
                  $f = calculatePiotroskiFScore($s);
                ?>
                  <td class="stock-val-cell">
                    <span class="font-bold text-base <?= ($f['score'] >= 8) ? 'text-gain' : (($f['score'] >= 5) ? 'text-gold' : 'text-loss') ?>">
                      <?= $f['score'] ?> / 9
                    </span>
                    <br>
                    <span class="badge <?= e($f['badge_class']) ?> badge-xs">
                      <?= e($f['rating']) ?>
                    </span>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">-</td>
              </tr>

              <tr>
                <td class="metric-title-cell">หนี้สินต่อทุน (D/E Ratio)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['de_ratio'] ?? '') === $s['symbol'];
                  $de = $s['de_ratio'] !== null ? (float)$s['de_ratio'] : null;
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    <?= $de !== null ? number_format($de, 2) . 'x' : '-' ?>
                    <?php if ($isWinner): ?>
                      <span class="badge-winner-tag"><?= icon('check', '', 10) ?> หนี้ต่ำสุด</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">
                  <?= $peerAvg['de_ratio'] ? number_format((float)$peerAvg['de_ratio'], 2) . 'x' : '-' ?>
                </td>
              </tr>

              <tr>
                <td class="metric-title-cell">มูลค่าหลักทรัพย์ (Market Cap)</td>
                <?php foreach ($comparedStocks as $s): 
                  $isWinner = ($winners['market_cap'] ?? '') === $s['symbol'];
                ?>
                  <td class="stock-val-cell <?= $isWinner ? 'winner-cell' : '' ?>">
                    ฿<?= formatCompactNumber((float)$s['market_cap']) ?>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">-</td>
              </tr>

              <!-- Actions Row -->
              <tr>
                <td class="metric-title-cell font-bold">ส่งคำสั่งซื้อ-ขายจำลอง</td>
                <?php foreach ($comparedStocks as $s): 
                  $currentPrice = (float)$s['current_price'];
                ?>
                  <td class="stock-val-cell">
                    <button type="button" class="btn btn-buy btn-sm btn-w-full open-trade-modal-btn"
                            data-trade-type="BUY"
                            data-symbol="<?= e($s['symbol']) ?>"
                            data-price="<?= (float)$currentPrice ?>"
                            data-stock-id="<?= e((string)$s['id']) ?>"
                            onclick="openTradeModal('BUY', '<?= e($s['symbol']) ?>', <?= (float)$currentPrice ?>, <?= e((string)$s['id']) ?>)">
                      <?= icon('cart', '', 13) ?> ซื้อ <?= e($s['symbol']) ?>
                    </button>
                  </td>
                <?php endforeach; ?>
                <td class="benchmark-val-cell">-</td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <?php require __DIR__ . '/../templates/trade_modal.php'; ?>

  <script>
    // เพิ่มหุ้นเข้าสู่การเปรียบเทียบ
    function handleAddStockToCompare(selectElem) {
      var symbol = selectElem.value;
      if (!symbol) return;
      
      var currentSymbols = <?= json_encode($symbolArray) ?>;
      if (currentSymbols.length >= 4) {
        alert('เปรียบเทียบได้สูงสุด 4 หุ้นพร้อมกัน');
        return;
      }
      
      currentSymbols.push(symbol);
      window.location.href = window.BASE_URL + '/pages/compare.php?symbols=' + currentSymbols.join(',');
    }

    // เรนเดอร์ Chart.js Multi-Bar Comparison Chart
    document.addEventListener('DOMContentLoaded', function() {
      var ctx = document.getElementById('compareBarChart');
      if (!ctx || typeof Chart === 'undefined') return;

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: <?= json_encode($chartLabels) ?>,
          datasets: <?= json_encode($chartDatasets) ?>
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          devicePixelRatio: window.devicePixelRatio || 1,
          plugins: {
            legend: {
              position: 'top',
              labels: {
                color: '#D4C9B4',
                font: { family: 'system-ui', size: 12, weight: 'bold' },
                padding: 16,
                usePointStyle: true,
                pointStyle: 'rectRounded'
              }
            },
            tooltip: {
              backgroundColor: 'rgba(30, 22, 22, 0.95)',
              titleColor: '#FFCC00',
              bodyColor: '#F0E6D0',
              borderColor: 'rgba(255, 204, 0, 0.3)',
              borderWidth: 1,
              padding: 10,
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': ' + context.parsed.y + ' / 100 คะแนน';
                }
              }
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: { color: '#8A7F70', font: { family: 'system-ui', size: 11 } }
            },
            y: {
              min: 0,
              max: 100,
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: { color: '#8A7F70', font: { family: 'monospace', size: 11 }, stepSize: 25 }
            }
          }
        }
      });
    });
  </script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
