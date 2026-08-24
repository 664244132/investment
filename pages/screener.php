<?php
declare(strict_types=1);

/**
 * pages/screener.php
 * SmartPort AI - Stock Screener & Quantitative Filter Engine - Fundamental & Quantitative Stock Screener
 * ออกแบบตามมาตรฐาน: Unified Sans Typography, Clean Rounded Rectangles, Icons Only, Zero External CDNs, Flat Subtle States
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/watchlist.php';
require_once __DIR__ . '/../functions/financial_models.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();

$userWatchlistStockIds = [];
if (isLoggedIn() && $user && isset($user['id'])) {
    $wlItems = getUserWatchlist($pdo, (int)$user['id']);
    foreach ($wlItems as $wli) {
        $userWatchlistStockIds[(int)$wli['stock_id']] = true;
    }
}

$sectors = getAllSectors($pdo);

// รับเงื่อนไขการคัดกรองจาก $_GET
$filters = [
    'sector'    => (string)($_GET['sector'] ?? 'all'),
    'max_pe'    => isset($_GET['max_pe']) && $_GET['max_pe'] !== '' ? (float)$_GET['max_pe'] : null,
    'min_roe'   => isset($_GET['min_roe']) && $_GET['min_roe'] !== '' ? (float)$_GET['min_roe'] : null,
    'min_yield' => isset($_GET['min_yield']) && $_GET['min_yield'] !== '' ? (float)$_GET['min_yield'] : null,
    'min_score' => isset($_GET['min_score']) && $_GET['min_score'] !== '' ? (int)$_GET['min_score'] : null,
];

$screenedStocks = screenStocks($pdo, $filters);

$pageTitle = 'คัดกรองหุ้น - ' . APP_TITLE;
$activeNav = 'screener';

require_once __DIR__ . '/../templates/header.php';
?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      
      <div class="section-mb-md">
        <div class="d-flex align-center gap-sm section-mb-xs flex-wrap">
          <h1 class="portfolio-header-title">
            <?= icon('filter', 'text-gold', 24) ?> เครื่องมือคัดกรองหลักทรัพย์ (Quantitative Stock Screener)
          </h1>
        </div>
        <p class="text-sm text-muted">
          คัดกรองหุ้นคุณค่าและหุ้นเติบโตตามตัวชี้วัดทางการเงินเชิงลึกและคะแนนคุณภาพ Composite Rating
        </p>
      </div>

      <!-- Quick Strategy Presets -->
      <div class="watchlist-bar">
        <span class="watchlist-label">
          กลยุทธ์ยอดนิยม:
        </span>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php?sector=all&min_yield=4.0" class="watchlist-chip">
          <?= icon('zap', 'text-gold', 12) ?> หุ้นปันผลสูง (Yield > 4%)
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php?sector=all&max_pe=20&min_roe=15" class="watchlist-chip">
          <?= icon('trending-up', 'text-gain', 12) ?> หุ้นเติบโตราคาถูก (P/E < 20 & ROE > 15%)
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php?sector=all&min_score=80" class="watchlist-chip">
          <?= icon('star', 'text-gold', 12) ?> หุ้นพื้นฐานแกร่ง (Composite Rating > 80)
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php?sector=all&max_pe=25&min_roe=12&min_score=75" class="watchlist-chip">
          <?= icon('shield', 'text-gold', 12) ?> หุ้นสุขภาพการเงินแกร่ง (Altman Safe & High F-Score)
        </a>
      </div>

      <!-- Screener Form Card -->
      <div class="card section-mb-md">
        <div class="card-header">
          <h2 class="card-title">
            <?= icon('sliders', 'text-gold', 18) ?> กำหนดเงื่อนไขการคัดกรอง
          </h2>
          <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="text-xs text-gold d-flex align-center gap-xs nowrap">
            <?= icon('close', '', 14) ?> ล้างตัวกรองทั้งหมด
          </a>
        </div>

        <div class="card-body">
          <form action="<?= e(BASE_URL) ?>/pages/screener.php" method="GET" class="screener-filter-form">
            
            <div class="form-group mb-none">
              <label for="filter_sector" class="form-label">กลุ่มอุตสาหกรรม (Sector)</label>
              <select id="filter_sector" name="sector" class="form-input">
                <option value="all" <?= ($filters['sector'] === 'all') ? 'selected' : '' ?>>ทั้งหมด (All Sectors)</option>
                <?php foreach ($sectors as $sec): ?>
                  <option value="<?= e($sec) ?>" <?= ($filters['sector'] === $sec) ? 'selected' : '' ?>>
                    <?= e($sec) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group mb-none">
              <label for="filter_pe" class="form-label">P/E Ratio ไม่เกิน (เท่า)</label>
              <input type="number" id="filter_pe" name="max_pe" class="form-input" placeholder="เช่น 25" step="0.1" value="<?= e((string)$filters['max_pe']) ?>">
            </div>

            <div class="form-group mb-none">
              <label for="filter_roe" class="form-label">ROE ขั้นต่ำ (%)</label>
              <input type="number" id="filter_roe" name="min_roe" class="form-input" placeholder="เช่น 15" step="0.1" value="<?= e((string)$filters['min_roe']) ?>">
            </div>

            <div class="form-group mb-none">
              <label for="filter_yield" class="form-label">ปันผล Dividend Yield ขั้นต่ำ (%)</label>
              <input type="number" id="filter_yield" name="min_yield" class="form-input" placeholder="เช่น 3" step="0.1" value="<?= e((string)$filters['min_yield']) ?>">
            </div>

            <div class="form-group mb-none">
              <label for="filter_score" class="form-label">Composite Rating ขั้นต่ำ</label>
              <input type="number" id="filter_score" name="min_score" class="form-input" placeholder="เช่น 75" min="0" max="100" value="<?= e((string)$filters['min_score']) ?>">
            </div>

            <div>
              <button type="submit" class="btn btn-primary btn-full">
                <?= icon('search', '', 14, '#1D1616') ?> ค้นหาและกรองหุ้น
              </button>
            </div>

          </form>
        </div>
      </div>

      <!-- Results Table Card -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">
            <?= icon('chart-bar', 'text-gold', 18) ?> ผลการคัดกรอง (พบ <?= count($screenedStocks) ?> บริษัท)
          </h2>
          <span class="text-xs text-muted nowrap">
            เรียงตาม Composite Rating และ ROE
          </span>
        </div>

        <div class="table-responsive">
          <table class="financial-table">
            <thead>
              <tr>
                <th>ชื่อย่อหุ้น</th>
                <th>ชื่อบริษัท / อุตสาหกรรม</th>
                <th class="th-right">ราคาล่าสุด</th>
                <th class="th-right">% เปลี่ยนแปลง</th>
                <th class="th-right">P/E (x)</th>
                <th class="th-right">ROE (%)</th>
                <th class="th-right">Dividend Yield</th>
                <th class="th-center">คะแนนคุณภาพ</th>
                <th class="th-center">คำแนะนำ AI</th>
                <th class="th-center">ดูรายละเอียด</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($screenedStocks)): ?>
                <tr>
                  <td colspan="10" class="table-empty-cell-padded">
                    ไม่พบข้อมูลหุ้นที่ตรงกับเงื่อนไขการคัดกรอง — กรุณาปรับลดเงื่อนไขเพื่อค้นหาใหม่
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($screenedStocks as $s): ?>
                  <tr>
                    <td class="font-bold">
                      <div class="d-flex align-center gap-xs">
                        <?php $isBmk = isset($userWatchlistStockIds[(int)$s['id']]); ?>
                        <button type="button" class="btn-bookmark btn-bm-sm <?= $isBmk ? 'bookmarked' : '' ?>" 
                                onclick="window.toggleWatchlistStock(<?= e((string)$s['id']) ?>, this)"
                                title="<?= $isBmk ? 'นำออกจาก Watchlist' : 'เพิ่มใน Watchlist' ?>" 
                                aria-label="Toggle Watchlist">
                          <span class="bm-icon-outline <?= $isBmk ? 'd-none' : '' ?>">
                            <?= icon('bookmark', '', 14) ?>
                          </span>
                          <span class="bm-icon-filled <?= $isBmk ? '' : 'd-none' ?>">
                            <?= icon('bookmark-filled', 'text-gold', 14) ?>
                          </span>
                        </button>
                        <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($s['symbol']) ?>" class="link-gold-mono">
                          <?= e($s['symbol']) ?>
                        </a>
                      </div>
                    </td>
                    <td>
                      <div class="font-medium"><?= e($s['name_th'] ?: $s['name_en']) ?></div>
                      <div class="text-xs text-muted"><?= e($s['sector']) ?></div>
                    </td>
                    <td class="num font-bold">
                      ฿<?= number_format((float)$s['current_price'], 2) ?>
                    </td>
                    <td class="num <?= getPnlColorClass((float)$s['percent_change']) ?>">
                      <?= formatPercent((float)$s['percent_change']) ?>
                    </td>
                    <td class="num">
                      <?= $s['pe_ratio'] ? number_format((float)$s['pe_ratio'], 1) . 'x' : '-' ?>
                    </td>
                    <td class="num font-bold text-gain">
                      <?= $s['roe'] ? number_format((float)$s['roe'], 1) . '%' : '-' ?>
                    </td>
                    <td class="num">
                      <?= $s['dividend_yield'] ? number_format((float)$s['dividend_yield'], 2) . '%' : '-' ?>
                    </td>
                    <td class="th-center">
                      <?php if ($s['total_score']): ?>
                        <span class="badge badge-gold font-bold">
                          <?= icon('star', 'text-gold', 12) ?> <?= (int)$s['total_score'] ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="th-center">
                      <?php
                      $rec = (string)($s['recommendation'] ?? '');
                      $badgeClass = ($rec === 'Strong Buy' || $rec === 'Buy') ? 'badge-gain' : (($rec === 'Sell') ? 'badge-loss' : 'badge-gold');
                      ?>
                      <span class="badge <?= $badgeClass ?>">
                        <?= e($rec ?: 'Hold') ?>
                      </span>
                    </td>
                    <td class="th-center">
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($s['symbol']) ?>" class="btn btn-outline btn-sm">
                        <?= icon('bot', '', 12) ?> วิเคราะห์
                      </a>
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

  <!-- Scripts & Footer Template -->
  <?php
  $extraScripts = '<script>window.BASE_URL = ' . json_encode(BASE_URL) . ';</script>';
  require_once __DIR__ . '/../templates/footer.php';
  ?>
