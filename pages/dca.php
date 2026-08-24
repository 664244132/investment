<?php
declare(strict_types=1);

/**
 * pages/dca.php
 * SmartPort AI - DCA Simulator & Growth Calculator
 * เครื่องมือจำลองการออมหุ้นถัวเฉลี่ยรายเดือน ดอกเบี้ยทบต้น และเปรียบเทียบผลตอบแทน
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/portfolio.php';
require_once __DIR__ . '/../functions/dca.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();

$allStocks = getAllStocks($pdo);

// รับพารามิเตอร์การจำลองจาก $_GET
$selectedSymbol = (string)($_GET['symbol'] ?? 'DELTA');
$selectedStock = getStockBySymbol($pdo, $selectedSymbol);
if (!$selectedStock && !empty($allStocks)) {
    $selectedStock = $allStocks[0];
    $selectedSymbol = $selectedStock['symbol'];
}

$monthlyAmount = isset($_GET['monthly_amount']) && is_numeric($_GET['monthly_amount']) ? (float)$_GET['monthly_amount'] : 5000.0;
$years = isset($_GET['years']) && is_numeric($_GET['years']) ? (int)$_GET['years'] : 10;

// อัตราการเติบโตค่าเริ่มต้น (อิงจาก ROE/2 หรือ 10.0%)
$defaultGrowth = ($selectedStock && $selectedStock['roe'] !== null) ? max(4.0, (float)$selectedStock['roe'] * 0.5) : 10.0;
$growthRate = isset($_GET['growth_rate']) && is_numeric($_GET['growth_rate']) ? (float)$_GET['growth_rate'] : round($defaultGrowth, 1);

// อัตราเงินปันผลค่าเริ่มต้น
$defaultYield = ($selectedStock && $selectedStock['dividend_yield'] !== null) ? (float)$selectedStock['dividend_yield'] : 3.0;
$dividendYield = isset($_GET['dividend_yield']) && is_numeric($_GET['dividend_yield']) ? (float)$_GET['dividend_yield'] : round($defaultYield, 2);

$reinvestDividends = !isset($_GET['drip']) || $_GET['drip'] === '1' || $_GET['drip'] === 'on';

// รันแบบจำลอง DCA
$simResult = simulateDcaGrowth(
    $monthlyAmount,
    $years,
    $growthRate,
    $dividendYield,
    $reinvestDividends
);

// เตรียมข้อมูลกราฟ Chart.js
$chartLabels = [];
$stockData = [];
$principalData = [];
$indexData = [];
$bankData = [];

foreach ($simResult['yearly_breakdown'] as $row) {
    $chartLabels[] = 'ปีที่ ' . $row['year'];
    $stockData[] = round($row['stock_value'], 2);
    $principalData[] = round($row['principal'], 2);
    $indexData[] = round($row['index_value'], 2);
    $bankData[] = round($row['bank_value'], 2);
}

$pageTitle = 'เครื่องมือจำลองออมหุ้น DCA - ' . APP_TITLE;
$activeNav = 'dca';
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
            <?= icon('repeat', 'text-gold', 24) ?> เครื่องมือจำลองการออมหุ้น DCA (Growth & Compounding Calculator)
          </h1>
          <p class="chart-subtitle-meta">
            คำนวณพลังดอกเบี้ยทบต้นจากการลงทุนสม่ำเสมอรายเดือน (Dollar-Cost Averaging) พร้อมเปรียบเทียบกับเงินฝากและดัชนีตลาด
          </p>
        </div>
      </div>

      <!-- Quick Strategy Preset Bar -->
      <div class="watchlist-bar section-mb-md">
        <span class="watchlist-label">แผนออมหุ้นยอดนิยม:</span>
        <a href="<?= e(BASE_URL) ?>/pages/dca.php?symbol=<?= e($selectedSymbol) ?>&monthly_amount=3000&years=5&growth_rate=8&dividend_yield=3&drip=1" class="watchlist-chip <?= ($monthlyAmount == 3000 && $years == 5) ? 'active' : '' ?>">
          <?= icon('zap', 'text-gold', 12) ?> มือใหม่เริ่มออม (฿3,000/ด. · 5 ปี)
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/dca.php?symbol=<?= e($selectedSymbol) ?>&monthly_amount=5000&years=10&growth_rate=10&dividend_yield=3.5&drip=1" class="watchlist-chip <?= ($monthlyAmount == 5000 && $years == 10) ? 'active' : '' ?>">
          <?= icon('star', 'text-gain', 12) ?> สร้างอิสรภาพทางการเงิน (฿5,000/ด. · 10 ปี)
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/dca.php?symbol=<?= e($selectedSymbol) ?>&monthly_amount=10000&years=15&growth_rate=12&dividend_yield=4&drip=1" class="watchlist-chip <?= ($monthlyAmount == 10000 && $years == 15) ? 'active' : '' ?>">
          <?= icon('award', 'text-gold', 12) ?> แผนเกษียณมั่งคั่ง (฿10,000/ด. · 15 ปี)
        </a>
      </div>

      <div class="dca-calculator-grid">
        
        <!-- Left Column: DCA Input Configuration Form -->
        <div>
          <div class="dca-form-card">
            <h2 class="card-title-sm d-flex align-center gap-sm dca-card-title-head">
              <?= icon('sliders', 'text-gold', 18) ?> กำหนดพารามิเตอร์จำลอง
            </h2>

            <form action="<?= e(BASE_URL) ?>/pages/dca.php" method="GET" class="form-flex-col">
              
              <!-- Stock Universe Selector -->
              <div class="form-group mb-none">
                <label for="dca_symbol" class="form-label">เลือกหลักทรัพย์เป้าหมาย</label>
                <div class="form-input-wrapper">
                  <select id="dca_symbol" name="symbol" class="form-input" onchange="this.form.submit()">
                    <?php foreach ($allStocks as $s): ?>
                      <option value="<?= e($s['symbol']) ?>" <?= ($selectedSymbol === $s['symbol']) ? 'selected' : '' ?>>
                        <?= e($s['symbol']) ?> - <?= e($s['name_th'] ?: $s['name_en']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Monthly DCA Amount -->
              <div class="form-group mb-none">
                <label for="dca_monthly" class="form-label">เงินออมถัวเฉลี่ยรายเดือน (บาท/เดือน)</label>
                <div class="form-input-wrapper">
                  <input type="number" id="dca_monthly" name="monthly_amount" class="form-input" min="100" step="500" value="<?= (int)$monthlyAmount ?>" required>
                </div>
              </div>

              <!-- Investment Horizon (Years) -->
              <div class="form-group mb-none">
                <label for="dca_years" class="form-label">ระยะเวลาลงทุนสะสม (ปี)</label>
                <div class="form-input-wrapper">
                  <select id="dca_years" name="years" class="form-input">
                    <?php foreach ([1, 3, 5, 7, 10, 15, 20, 25, 30] as $y): ?>
                      <option value="<?= $y ?>" <?= ($years === $y) ? 'selected' : '' ?>>
                        <?= $y ?> ปี (<?= $y * 12 ?> งวด)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Expected Capital Growth Rate (CAGR %) -->
              <div class="form-group mb-none">
                <label for="dca_growth" class="form-label">คาดการณ์กำไรส่วนต่างราคาต่อปี (% CAGR)</label>
                <div class="form-input-wrapper">
                  <input type="number" id="dca_growth" name="growth_rate" class="form-input" min="-30" max="100" step="0.5" value="<?= $growthRate ?>" required>
                </div>
              </div>

              <!-- Dividend Yield Rate (%) -->
              <div class="form-group mb-none">
                <label for="dca_yield" class="form-label">อัตราเงินปันผลตอบแทนต่อปี (Yield %)</label>
                <div class="form-input-wrapper">
                  <input type="number" id="dca_yield" name="dividend_yield" class="form-input" min="0" max="30" step="0.1" value="<?= $dividendYield ?>" required>
                </div>
              </div>

              <!-- DRIP Toggle -->
              <div class="form-checkbox-row">
                <input type="checkbox" id="dca_drip" name="drip" value="1" <?= $reinvestDividends ? 'checked' : '' ?> class="form-checkbox-input">
                <label for="dca_drip" class="form-checkbox-label">
                  นำเงินปันผลกลับมาลงทุนต่อ (DRIP - Reinvestment)
                </label>
              </div>

              <button type="submit" class="btn btn-primary btn-w-full mt-sm">
                <?= icon('calculator', '', 16) ?> คำนวณแบบจำลอง DCA ใหม่
              </button>

            </form>
          </div>
        </div>

        <!-- Right Column: Results, KPI, Chart & Benchmarks -->
        <div>
          
          <!-- Summary KPI Cards -->
          <div class="dca-summary-grid">
            <div class="dca-summary-card">
              <span class="dca-summary-label">เงินต้นสะสมทั้งหมด</span>
              <span class="dca-summary-val text-muted">
                ฿<?= number_format($simResult['total_principal'], 0) ?>
              </span>
            </div>

            <div class="dca-summary-card dca-card-featured">
              <span class="dca-summary-label">มูลค่าพอร์ตสุทธิ ณ สิ้นสุด</span>
              <span class="dca-summary-val text-gold">
                ฿<?= number_format($simResult['final_stock_value'], 0) ?>
              </span>
            </div>

            <div class="dca-summary-card">
              <span class="dca-summary-label">กำไรส่วนต่างราคา</span>
              <span class="dca-summary-val text-gain">
                +฿<?= number_format($simResult['final_capital_gain'], 0) ?>
              </span>
            </div>

            <div class="dca-summary-card">
              <span class="dca-summary-label">เงินปันผลสะสม</span>
              <span class="dca-summary-val text-gold">
                +฿<?= number_format($simResult['total_dividends_paid'], 0) ?>
              </span>
            </div>
          </div>

          <!-- Benchmark Comparison Box -->
          <div class="dca-comparison-box">
            <div class="dca-benchmark-card stock-winner">
              <div class="dca-benchmark-title">
                <?= icon('award', 'text-gold', 14) ?> พอร์ตออมหุ้น DCA (<?= e($selectedSymbol) ?>)
              </div>
              <div class="dca-benchmark-val text-gold">
                ฿<?= number_format($simResult['final_stock_value'], 0) ?>
              </div>
              <div class="text-gain font-bold text-xs">
                ผลตอบแทนรวม: <?= sprintf('%+.1f%%', $simResult['total_return_pct']) ?>
              </div>
            </div>

            <div class="dca-benchmark-card">
              <div class="dca-benchmark-title">
                <?= icon('activity', 'text-muted', 14) ?> ดัชนีตลาดเฉลี่ย (SET Index 7.0%)
              </div>
              <div class="dca-benchmark-val text-blue">
                ฿<?= number_format($simResult['final_index_value'], 0) ?>
              </div>
              <div class="text-muted text-xs">
                ส่วนต่าง: ฿<?= number_format($simResult['final_stock_value'] - $simResult['final_index_value'], 0) ?>
              </div>
            </div>

            <div class="dca-benchmark-card">
              <div class="dca-benchmark-title">
                <?= icon('shield', 'text-muted', 14) ?> เงินฝากประจำธนาคาร (1.5%)
              </div>
              <div class="dca-benchmark-val text-muted">
                ฿<?= number_format($simResult['final_bank_value'], 0) ?>
              </div>
              <div class="text-muted text-xs">
                ส่วนต่าง: ฿<?= number_format($simResult['final_stock_value'] - $simResult['final_bank_value'], 0) ?>
              </div>
            </div>
          </div>

          <!-- Interactive Chart.js Multi-Line Growth Trajectory Chart -->
          <div class="card section-mb-md">
            <div class="card-header">
              <h3 class="card-title">
                <?= icon('chart-line', 'text-gold', 18) ?> แผนภูมิการเติบโตสะสมรายปี (Wealth Growth Trajectory)
              </h3>
              <span class="metric-subtitle-meta">ระยะเวลา <?= $years ?> ปี (<?= $years * 12 ?> งวด)</span>
            </div>
            <div class="card-body">
              <div class="chart-wrapper-320">
                <canvas id="dcaGrowthChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Year-by-Year Amortization Breakdown Table -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <?= icon('document', 'text-gold', 18) ?> ตารางแจกแจงผลตอบแทนรายปี (Amortization Schedule)
              </h3>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>สิ้นปีที่</th>
                    <th class="th-right">เงินต้นสะสม</th>
                    <th class="th-right">ปันผลประจำปี</th>
                    <th class="th-right">กำไรสะสม (Capital Gain)</th>
                    <th class="th-right">มูลค่าพอร์ตสุทธิ</th>
                    <th class="th-center">ROI รวม</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($simResult['yearly_breakdown'] as $row): ?>
                    <tr>
                      <td class="font-bold">ปีที่ <?= $row['year'] ?></td>
                      <td class="num text-muted">฿<?= number_format($row['principal'], 0) ?></td>
                      <td class="num text-gold">+฿<?= number_format($row['annual_dividend'], 0) ?></td>
                      <td class="num text-gain">+฿<?= number_format($row['capital_gain'], 0) ?></td>
                      <td class="num font-bold text-gold text-base">฿<?= number_format($row['stock_value'], 0) ?></td>
                      <td class="num th-center font-bold <?= ($row['roi_pct'] >= 0) ? 'text-gain' : 'text-loss' ?>">
                        <?= sprintf('%+.1f%%', $row['roi_pct']) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

      </div>

    </div>
  </main>

  <script>
    // เรนเดอร์ Chart.js Multi-Line Growth Trajectory
    document.addEventListener('DOMContentLoaded', function() {
      var ctx = document.getElementById('dcaGrowthChart');
      if (!ctx || typeof Chart === 'undefined') return;

      new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?= json_encode($chartLabels) ?>,
          datasets: [
            {
              label: 'พอร์ตออมหุ้น DCA (<?= e($selectedSymbol) ?>)',
              data: <?= json_encode($stockData) ?>,
              borderColor: '#FFCC00',
              backgroundColor: 'rgba(255, 204, 0, 0.12)',
              fill: true,
              tension: 0.3,
              borderWidth: 2.5,
              pointBackgroundColor: '#FFCC00',
              pointRadius: 3
            },
            {
              label: 'ดัชนีตลาด SET Index (7.0%)',
              data: <?= json_encode($indexData) ?>,
              borderColor: '#00B4D8',
              backgroundColor: 'transparent',
              borderDash: [5, 5],
              tension: 0.3,
              borderWidth: 1.8,
              pointRadius: 0
            },
            {
              label: 'เงินต้นสะสม (Principal)',
              data: <?= json_encode($principalData) ?>,
              borderColor: '#D4C9B4',
              backgroundColor: 'transparent',
              borderDash: [3, 3],
              tension: 0,
              borderWidth: 1.5,
              pointRadius: 0
            },
            {
              label: 'เงินฝากประจำ (1.5%)',
              data: <?= json_encode($bankData) ?>,
              borderColor: '#8A7F70',
              backgroundColor: 'transparent',
              borderDash: [2, 2],
              tension: 0,
              borderWidth: 1.2,
              pointRadius: 0
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          devicePixelRatio: window.devicePixelRatio || 1,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                color: '#D4C9B4',
                font: { family: 'system-ui', size: 11, weight: 'bold' },
                usePointStyle: true,
                padding: 14
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
                  return context.dataset.label + ': ฿' + Number(context.parsed.y).toLocaleString('th-TH');
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
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: {
                color: '#8A7F70',
                font: { family: 'monospace', size: 11 },
                callback: function(value) {
                  return '฿' + Number(value).toLocaleString('th-TH');
                }
              }
            }
          }
        }
      });
    });
  </script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
