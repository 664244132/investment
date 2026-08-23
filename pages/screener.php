<?php
declare(strict_types=1);

/**
 * 🔍 pages/screener.php
 * หน้าคัดกรองหุ้นอัจฉริยะ (Smart Stock Screener)
 * ออกแบบตามมาตรฐาน: Icons Only (Zero Emojis), Local Assets Only (Zero External CDNs)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$sectors = getAllSectors($pdo);

// รับค่าตัวกรองจาก Query Parameters (GET)
$filters = [
    'sector'       => (string)($_GET['sector'] ?? 'all'),
    'max_pe'       => !empty($_GET['max_pe']) ? (float)$_GET['max_pe'] : null,
    'min_roe'      => !empty($_GET['min_roe']) ? (float)$_GET['min_roe'] : null,
    'min_yield'    => !empty($_GET['min_yield']) ? (float)$_GET['min_yield'] : null,
    'min_score'    => !empty($_GET['min_score']) ? (int)$_GET['min_score'] : null
];

$screenedStocks = screenStocks($pdo, $filters);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>คัดกรองหุ้น (Stock Screener) - <?= e(APP_TITLE) ?></title>
  
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
        <span>AlphaVision AI</span>
      </a>

      <nav class="main-nav">
        <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link">หน้าหลัก / วิเคราะห์หุ้น</a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link active">คัดกรองหุ้น (Screener)</a>
        <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link">พอร์ตจำลอง (Portfolio)</a>
      </nav>

      <div class="header-actions">
        <div class="search-box">
          <?= icon('search', '', 14, '#8A7F70') ?>
          <input type="text" id="stockSearchInput" placeholder="ค้นหาหุ้น... (กด Enter)">
        </div>

        <?php if ($user): ?>
          <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="btn btn-outline btn-sm">
            <?= icon('wallet', '', 14) ?> พอร์ตของฉัน
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="btn btn-outline btn-sm" style="color: var(--text-muted);">
            ออก
          </a>
        <?php else: ?>
          <a href="<?= e(BASE_URL) ?>/pages/login.php" class="btn btn-outline btn-sm">
            เข้าสู่ระบบ
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-primary btn-sm">
            เริ่มต้นใช้งาน
          </a>
        <?php endif; ?>

        <button class="mobile-toggle" aria-label="Toggle Navigation">
          <?= icon('menu', '', 20) ?>
        </button>
      </div>
    </div>

    <!-- Mobile Drawer -->
    <div class="mobile-drawer">
      <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link">หน้าหลัก / วิเคราะห์หุ้น</a>
      <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link active">คัดกรองหุ้น (Screener)</a>
      <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link">พอร์ตจำลอง (Portfolio)</a>
      <?php if ($user): ?>
        <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="nav-link" style="color: var(--color-danger);">ออกจากระบบ</a>
      <?php else: ?>
        <a href="<?= e(BASE_URL) ?>/pages/login.php" class="nav-link">เข้าสู่ระบบ</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      
      <div style="margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
          <h1 style="font-family: var(--font-serif); font-size: 1.85rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('filter', 'text-gold', 24) ?> เครื่องมือคัดกรองหุ้น (Smart Stock Screener)
          </h1>
          <span class="badge badge-gold">AI-Powered</span>
        </div>
        <p style="color: var(--text-muted); font-size: 0.85rem;">
          ค้นหาหุ้นคุณค่าและหุ้นเติบโตสูงตามเกณฑ์อัตราส่วนทางการเงินและคะแนน AI Score
        </p>
      </div>

      <!-- Filters Card -->
      <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
          <h2 class="card-title">
            <?= icon('sliders', 'text-gold', 18) ?> กำหนดเงื่อนไขการคัดกรอง
          </h2>
          <a href="<?= e(BASE_URL) ?>/pages/screener.php" style="font-size: 0.8rem; color: var(--accent-gold);">
            ล้างตัวกรองทั้งหมด
          </a>
        </div>

        <div class="card-body">
          <form action="<?= e(BASE_URL) ?>/pages/screener.php" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; align-items: flex-end;">
            
            <div class="form-group" style="margin-bottom: 0;">
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

            <div class="form-group" style="margin-bottom: 0;">
              <label for="filter_pe" class="form-label">P/E Ratio ไม่เกิน (เท่า)</label>
              <input type="number" id="filter_pe" name="max_pe" class="form-input" placeholder="เช่น 25" step="0.1" value="<?= e((string)$filters['max_pe']) ?>">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
              <label for="filter_roe" class="form-label">ROE ขั้นต่ำ (%)</label>
              <input type="number" id="filter_roe" name="min_roe" class="form-input" placeholder="เช่น 15" step="0.1" value="<?= e((string)$filters['min_roe']) ?>">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
              <label for="filter_yield" class="form-label">ปันผล Dividend Yield ขั้นต่ำ (%)</label>
              <input type="number" id="filter_yield" name="min_yield" class="form-input" placeholder="เช่น 3" step="0.1" value="<?= e((string)$filters['min_yield']) ?>">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
              <label for="filter_score" class="form-label">AI Score ขั้นต่ำ (คะแนน)</label>
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
          <span style="font-size: 0.8rem; color: var(--text-muted);">
            เรียงตาม AI Score และ ROE
          </span>
        </div>

        <div class="table-responsive">
          <table class="financial-table">
            <thead>
              <tr>
                <th>ชื่อย่อหุ้น</th>
                <th>ชื่อบริษัท / อุตสาหกรรม</th>
                <th style="text-align: right;">ราคาล่าสุด</th>
                <th style="text-align: right;">% เปลี่ยนแปลง</th>
                <th style="text-align: right;">P/E (x)</th>
                <th style="text-align: right;">ROE (%)</th>
                <th style="text-align: right;">Dividend Yield</th>
                <th style="text-align: center;">AI Score</th>
                <th style="text-align: center;">AI คำแนะนำ</th>
                <th style="text-align: center;">ดูข้อมูล</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($screenedStocks)): ?>
                <tr>
                  <td colspan="10" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                    ไม่พบหุ้นที่ตรงกับเงื่อนไขที่กำหนด กรุณาลองปรับลดเงื่อนไขการกรอง
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($screenedStocks as $s): ?>
                  <tr>
                    <td>
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($s['symbol']) ?>" style="font-weight: 700; color: var(--accent-gold); font-family: var(--font-mono); font-size: 1.05rem;">
                        <?= e($s['symbol']) ?>
                      </a>
                    </td>
                    <td>
                      <div style="font-weight: 500;"><?= e($s['name_th'] ?: $s['name_en']) ?></div>
                      <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($s['sector']) ?></div>
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
                    <td class="num font-bold" style="color: var(--color-success);">
                      <?= $s['roe'] ? number_format((float)$s['roe'], 1) . '%' : '-' ?>
                    </td>
                    <td class="num">
                      <?= $s['dividend_yield'] ? number_format((float)$s['dividend_yield'], 2) . '%' : '-' ?>
                    </td>
                    <td style="text-align: center;">
                      <?php if ($s['total_score']): ?>
                        <span class="badge" style="background: var(--accent-gold-subtle); color: var(--accent-gold); border: 1px solid var(--accent-gold-border); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
                          <?= icon('star', 'text-gold', 12) ?> <?= (int)$s['total_score'] ?>
                        </span>
                      <?php else: ?>
                        <span style="color: var(--text-muted);">-</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                      <?php
                      $rec = (string)($s['recommendation'] ?? '');
                      $badgeClass = ($rec === 'Strong Buy' || $rec === 'Buy') ? 'badge-gain' : (($rec === 'Sell') ? 'badge-loss' : 'badge-gold');
                      ?>
                      <span class="badge <?= $badgeClass ?>">
                        <?= e($rec ?: 'Hold') ?>
                      </span>
                    </td>
                    <td style="text-align: center;">
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($s['symbol']) ?>" class="btn btn-outline btn-sm">
                        <?= icon('bot', '', 12) ?> วิเคราะห์ AI
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

  <!-- Footer -->
  <footer class="app-footer">
    <div class="container footer-bottom">
      <p>© 2026 AlphaVision AI - ระบบวิเคราะห์และคัดเลือกหุ้นด้วย AI พร้อมพอร์ตจำลอง</p>
      <p>ข้อมูลเพื่อการศึกษาและวิเคราะห์สถิติเท่านั้น</p>
    </div>
  </footer>

  <script>window.BASE_URL = '<?= e(BASE_URL) ?>';</script>
  <script src="<?= e(JS_URL) ?>/main.js"></script>
</body>
</html>
