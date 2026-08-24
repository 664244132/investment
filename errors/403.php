<?php
declare(strict_types=1);

/**
 * errors/403.php
 * หน้าแสดงผลข้อผิดพลาด 403 Forbidden (การเข้าถึงถูกปฏิเสธ)
 * SmartPort AI - Standalone Error View (No Navbar & No Footer, 100% Local Assets)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';

// ส่งสถานะ HTTP 403 Forbidden
http_response_code(403);

$pageTitle = '403 การเข้าถึงถูกปฏิเสธ - ' . APP_TITLE;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  
  <!-- Favicon Icons (Native Brand Icon) -->
  <link rel="icon" type="image/svg+xml" href="<?= e(BASE_URL) ?>/assets/images/favicon.svg">
  <link rel="icon" type="image/png" href="<?= e(BASE_URL) ?>/assets/images/favicon.png">
  <link rel="apple-touch-icon" href="<?= e(BASE_URL) ?>/assets/images/favicon.png">

  <!-- CSS Stylesheets (100% Local Assets & Offline Webfonts) -->
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/fonts.css?v=<?= e(APP_VERSION) ?>">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/variables.css?v=<?= e(APP_VERSION) ?>">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/layout.css?v=<?= e(APP_VERSION) ?>">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/components.css?v=<?= e(APP_VERSION) ?>">
</head>
<body>

  <main class="error-page-container">
    <div class="error-card">
      
      <!-- Icon Wrapper -->
      <div class="error-icon-wrapper error-icon-403">
        <?= icon('shield', '', 36, 'currentColor') ?>
      </div>

      <!-- Error Code Number -->
      <div class="error-code-number text-gold">403</div>

      <!-- Heading -->
      <h1 class="error-heading">การเข้าถึงถูกปฏิเสธ (Forbidden)</h1>

      <!-- Description -->
      <p class="error-message">
        คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้หรือทรัพยากรที่ร้องขอ โปรดตรวจสอบสถานะการเข้าสู่ระบบ หรือติดต่อผู้ดูแลระบบหากเชื่อว่าเป็นข้อผิดพลาด
      </p>

    </div>
  </main>

</body>
</html>
