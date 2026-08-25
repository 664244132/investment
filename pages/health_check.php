<?php
declare(strict_types=1);

/**
 * pages/health_check.php
 * SmartPort AI - System Health & Self-Diagnosis Engine
 * ระบบตรวจสอบความสมบูรณ์ ความปลอดภัย การเชื่อมต่อฐานข้อมูล และความพร้อมของทุกโมดูลในระบบ
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/portfolio.php';
require_once __DIR__ . '/../functions/watchlist.php';
require_once __DIR__ . '/../functions/journal.php';
require_once __DIR__ . '/../functions/dca.php';
require_once __DIR__ . '/../functions/financial_models.php';
require_once __DIR__ . '/../functions/export.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();

// รันการตรวจสอบแบบองค์รวม (Holistic Diagnostics Suite)
$diagnostics = [
    'environment' => [],
    'database'    => [],
    'assets'      => [],
    'engines'     => [],
    'security'    => []
];

$allPassed = true;
$totalTests = 0;
$passedTests = 0;

// 1. ตรวจสอบสภาพแวดล้อมระบบและ PHP Extensions
$phpVersion = PHP_VERSION;
$isPhp8 = version_compare($phpVersion, '8.0.0', '>=');
$diagnostics['environment'][] = [
    'title'  => 'PHP Version (>= 8.0)',
    'passed' => $isPhp8,
    'detail' => 'เวอร์ชันปัจจุบัน: PHP ' . $phpVersion
];

$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session', 'curl'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $diagnostics['environment'][] = [
        'title'  => 'PHP Extension: ' . $ext,
        'passed' => $loaded,
        'detail' => $loaded ? 'เปิดใช้งานพร้อมทำงาน' : 'ไม่พบ Extension นี้ในระบบ'
    ];
}

// 2. ตรวจสอบการเชื่อมต่อฐานข้อมูลและตารางหลัก
$dbConnected = ($pdo !== null);
$diagnostics['database'][] = [
    'title'  => 'การเชื่อมต่อฐานข้อมูล MySQL / MariaDB (PDO)',
    'passed' => $dbConnected,
    'detail' => $dbConnected ? 'เชื่อมต่อสำเร็จ (Host: ' . DB_HOST . ', DB: ' . DB_NAME . ')' : 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'
];

if ($dbConnected) {
    $expectedTables = [
        'users', 'stocks', 'financial_metrics', 'financial_statements', 
        'ai_analyses', 'portfolios', 'portfolio_items', 'transactions', 
        'user_watchlists', 'trading_journals'
    ];

    foreach ($expectedTables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `" . $table . "`");
            $count = $stmt ? (int)$stmt->fetchColumn() : 0;
            $diagnostics['database'][] = [
                'title'  => 'ตารางฐานข้อมูล: ' . $table,
                'passed' => true,
                'detail' => 'พบตารางพร้อมใช้งาน (' . number_format($count) . ' แถว)'
            ];
        } catch (Throwable $e) {
            $diagnostics['database'][] = [
                'title'  => 'ตารางฐานข้อมูล: ' . $table,
                'passed' => false,
                'detail' => 'ไม่พบตารางหรือเกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }
}

// 3. ตรวจสอบความพร้อมของ Local Assets & Libraries (Zero External CDN Rule)
$assetFiles = [
    'css/variables.css'    => 'Design Tokens & Variables Stylesheet',
    'css/layout.css'       => 'Layout & Grid Stylesheet',
    'css/components.css'   => 'Components & Design Tokens Stylesheet',
    'js/chart.umd.min.js'  => 'Local Chart.js Library',
    'js/main.js'           => 'Main UI & Search Controller',
    'js/chart_engine.js'   => 'Interactive Stock Chart Engine'
];

foreach ($assetFiles as $relPath => $desc) {
    $fullPath = dirname(__DIR__) . '/' . $relPath;
    $exists = file_exists($fullPath) && filesize($fullPath) > 0;
    $diagnostics['assets'][] = [
        'title'  => $desc . ' (' . $relPath . ')',
        'passed' => $exists,
        'detail' => $exists ? 'ไฟล์พร้อมใช้งาน (ขนาด ' . number_format(filesize($fullPath)) . ' bytes)' : 'ไม่พบไฟล์หรือไฟล์ว่างเปล่า'
    ];
}

// 4. ตรวจสอบความถูกต้องของการคำนวณในโมดูลต่างๆ (Core Engines Test)
// 4.1 Stock Engine Test
$testStock = getStockBySymbol($pdo, 'DELTA');
$stockEngineOk = ($testStock !== null && isset($testStock['symbol']));
$diagnostics['engines'][] = [
    'title'  => 'Stock Analysis Engine (functions/stocks.php)',
    'passed' => $stockEngineOk,
    'detail' => $stockEngineOk ? 'ดึงข้อมูลหุ้น DELTA สำเร็จ (ราคา ฿' . number_format((float)$testStock['current_price'], 2) . ')' : 'ไม่สามารถดึงข้อมูลหุ้นทดสอบได้'
];

// 4.2 Financial Models Engine (Altman Z & Piotroski F)
$sampleData = [
    'roa' => 8.5, 'roe' => 18.0, 'net_profit_margin' => 12.0, 
    'de_ratio' => 0.6, 'current_ratio' => 1.8, 'revenue_growth_yoy' => 10.0,
    'free_cash_flow' => 5000000, 'net_profit' => 4000000
];
$zTest = calculateAltmanZScore($sampleData);
$fTest = calculatePiotroskiFScore($sampleData);
$modelsOk = (isset($zTest['score'], $zTest['zone']) && isset($fTest['score'], $fTest['checklist']));
$diagnostics['engines'][] = [
    'title'  => 'Advanced Financial Health Models (Altman Z & Piotroski F)',
    'passed' => $modelsOk,
    'detail' => $modelsOk ? 'คำนวณสำเร็จ: Z-Score = ' . $zTest['score'] . ' (' . $zTest['zone'] . '), F-Score = ' . $fTest['score'] . '/9' : 'การคำนวณโมเดลล้มเหลว'
];

// 4.3 DCA Simulator Engine Test
$dcaTest = simulateDcaGrowth(5000.0, 5, 10.0, 3.0, true);
$dcaOk = (isset($dcaTest['final_stock_value']) && $dcaTest['final_stock_value'] > 0);
$diagnostics['engines'][] = [
    'title'  => 'DCA Simulator & Growth Engine (functions/dca.php)',
    'passed' => $dcaOk,
    'detail' => $dcaOk ? 'จำลอง 5 ปีสำเร็จ (มูลค่าพอร์ตคาดการณ์: ฿' . number_format($dcaTest['final_stock_value'], 2) . ')' : 'การจำลอง DCA ล้มเหลว'
];

// 5. ตรวจสอบความปลอดภัยและเซสชัน (Security & CSRF Validation)
$sessionActive = (session_status() === PHP_SESSION_ACTIVE);
$diagnostics['security'][] = [
    'title'  => 'Session Lifecycle Manager (startAppSession)',
    'passed' => $sessionActive,
    'detail' => $sessionActive ? 'เซสชันทำงานปกติ (Session ID: ' . substr(session_id(), 0, 8) . '...)' : 'เซสชันไม่พร้อมทำงาน'
];

$csrfToken = generateCsrfToken();
$csrfValid = verifyCsrfToken($csrfToken);
$diagnostics['security'][] = [
    'title'  => 'CSRF Token Generation & Verification (Timing Attack Safe)',
    'passed' => $csrfValid,
    'detail' => $csrfValid ? 'กลไก CSRF Protection ทำงานถูกต้องสมบูรณ์' : 'การตรวจสอบ CSRF ล้มเหลว'
];

$pwHash = password_hash('TestSecurity123!', PASSWORD_BCRYPT);
$pwValid = password_verify('TestSecurity123!', $pwHash);
$diagnostics['security'][] = [
    'title'  => 'Password Hashing & Verification (Bcrypt)',
    'passed' => $pwValid,
    'detail' => $pwValid ? 'อัลกอริทึม Bcrypt แฮชและตรวจสอบรหัสผ่านถูกต้อง' : 'การตรวจสอบรหัสผ่านล้มเหลว'
];

// คำนวณผลรวม
foreach ($diagnostics as $group) {
    foreach ($group as $test) {
        $totalTests++;
        if ($test['passed']) {
            $passedTests++;
        } else {
            $allPassed = false;
        }
    }
}

$pageTitle = 'ตรวจสอบสุขภาพระบบ - ' . APP_TITLE;
$activeNav = 'index';

require_once __DIR__ . '/../templates/header.php';
?>

  <main class="main-content">
    <div class="container">

      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <!-- Header Section -->
      <div class="section-heading-row section-mb-md">
        <div>
          <h1 class="section-heading-title">
            <?= icon('activity', 'text-gold', 24) ?> การตรวจสุขภาพระบบ (System Health & Self-Diagnosis)
          </h1>
          <p class="chart-subtitle-meta">
            ตรวจสอบความพร้อมของสภาพแวดล้อม PHP, ฐานข้อมูล, ไลบรารี Local Assets, โมเดลการเงิน และระบบความปลอดภัย
          </p>
        </div>
        <div class="d-flex gap-sm align-center">
          <span class="badge <?= $allPassed ? 'badge-gain' : 'badge-loss' ?> badge-health-pill">
            <?= $allPassed ? icon('check-circle', '', 14) . ' ทุกระบบทำงานปกติ (100% Operational)' : icon('alert', '', 14) . ' พบข้อผิดพลาด' ?>
          </span>
          <a href="<?= e(BASE_URL) ?>/pages/health_check.php" class="btn btn-outline">
            <?= icon('repeat', '', 14) ?> รันการตรวจซ้ำ
          </a>
        </div>
      </div>

      <!-- Overview Stats Grid -->
      <div class="metrics-grid section-mb-md">
        <div class="metric-card">
          <span class="metric-label">จำนวนการทดสอบทั้งหมด</span>
          <span class="metric-value"><?= $totalTests ?> การทดสอบ</span>
        </div>
        <div class="metric-card">
          <span class="metric-label">ผลการทดสอบที่ผ่าน</span>
          <span class="metric-value text-gain"><?= $passedTests ?> ผ่าน</span>
        </div>
        <div class="metric-card">
          <span class="metric-label">ผลการทดสอบที่ไม่ผ่าน</span>
          <span class="metric-value <?= ($totalTests - $passedTests === 0) ? 'text-gain' : 'text-loss' ?>">
            <?= $totalTests - $passedTests ?> ล้มเหลว
          </span>
        </div>
        <div class="metric-card">
          <span class="metric-label">อัตราความสมบูรณ์ของระบบ</span>
          <span class="metric-value text-gold">
            <?= number_format(($passedTests / max(1, $totalTests)) * 100, 1) ?>%
          </span>
        </div>
      </div>

      <!-- Diagnostics Groups -->
      <div class="diagnostics-list-col">

        <!-- Group 1: Environment -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <?= icon('cpu', 'text-gold', 18) ?> 1. สภาพแวดล้อมของเซิร์ฟเวอร์ & PHP Runtime
            </h2>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="th-col-num">สถานะ</th>
                  <th>หัวข้อการตรวจสอบ</th>
                  <th>รายละเอียด</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($diagnostics['environment'] as $test): ?>
                  <tr>
                    <td class="th-center">
                      <?= $test['passed'] ? icon('check-circle', 'text-gain', 18) : icon('close', 'text-loss', 18) ?>
                    </td>
                    <td class="font-bold"><?= e($test['title']) ?></td>
                    <td class="text-muted"><?= e($test['detail']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Group 2: Database -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <?= icon('shield', 'text-gold', 18) ?> 2. ฐานข้อมูลและโครงสร้างตาราง (Database & Schema Integrity)
            </h2>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="th-col-num">สถานะ</th>
                  <th>หัวข้อการตรวจสอบ</th>
                  <th>รายละเอียด</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($diagnostics['database'] as $test): ?>
                  <tr>
                    <td class="th-center">
                      <?= $test['passed'] ? icon('check-circle', 'text-gain', 18) : icon('close', 'text-loss', 18) ?>
                    </td>
                    <td class="font-bold"><?= e($test['title']) ?></td>
                    <td class="text-muted"><?= e($test['detail']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Group 3: Static Assets -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <?= icon('layers', 'text-gold', 18) ?> 3. ความพร้อมของไฟล์สไตล์ชีทและไลบรารี Local (Zero CDN Compliance)
            </h2>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="th-col-num">สถานะ</th>
                  <th>หัวข้อการตรวจสอบ</th>
                  <th>รายละเอียด</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($diagnostics['assets'] as $test): ?>
                  <tr>
                    <td class="th-center">
                      <?= $test['passed'] ? icon('check-circle', 'text-gain', 18) : icon('close', 'text-loss', 18) ?>
                    </td>
                    <td class="font-bold"><?= e($test['title']) ?></td>
                    <td class="text-muted"><?= e($test['detail']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Group 4: Core Engines -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <?= icon('calculator', 'text-gold', 18) ?> 4. ตรรกะและโมดูลการคำนวณทางการเงิน (Financial Engines)
            </h2>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="th-col-num">สถานะ</th>
                  <th>หัวข้อการตรวจสอบ</th>
                  <th>รายละเอียด</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($diagnostics['engines'] as $test): ?>
                  <tr>
                    <td class="th-center">
                      <?= $test['passed'] ? icon('check-circle', 'text-gain', 18) : icon('close', 'text-loss', 18) ?>
                    </td>
                    <td class="font-bold"><?= e($test['title']) ?></td>
                    <td class="text-muted"><?= e($test['detail']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Group 5: Security & Session -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <?= icon('shield', 'text-gold', 18) ?> 5. ระบบความปลอดภัยและเซสชัน (Security & CSRF Protection)
            </h2>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="th-col-num">สถานะ</th>
                  <th>หัวข้อการตรวจสอบ</th>
                  <th>รายละเอียด</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($diagnostics['security'] as $test): ?>
                  <tr>
                    <td class="th-center">
                      <?= $test['passed'] ? icon('check-circle', 'text-gain', 18) : icon('close', 'text-loss', 18) ?>
                    </td>
                    <td class="font-bold"><?= e($test['title']) ?></td>
                    <td class="text-muted"><?= e($test['detail']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
