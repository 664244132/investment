<?php
declare(strict_types=1);

/**
 * errors/404.php
 * หน้าแสดงผลข้อผิดพลาด 404 Not Found (ไม่พบหน้าที่ต้องการ)
 * SmartPort AI - Standalone Error View (No Navbar & No Footer, 100% Local Assets)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';

// ส่งสถานะ HTTP 404 Not Found
http_response_code(404);

$pageTitle = '404 ไม่พบหน้าเว็บ - ' . APP_TITLE;
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
      <div class="error-icon-wrapper error-icon-404">
        <?= icon('search', '', 36, 'currentColor') ?>
      </div>

      <!-- Error Code Number -->
      <div class="error-code-number text-gain">404</div>

      <!-- Heading -->
      <h1 class="error-heading">ไม่พบหน้าที่คุณต้องการ (Page Not Found)</h1>

      <!-- Description -->
      <p class="error-message">
        หน้าเว็บหรือข้อมูลหุ้นที่คุณกำลังค้นหาอาจถูกย้าย เปลี่ยนชื่อ หรือไม่มีอยู่ในระบบ โปรดตรวจสอบ URL หรือค้นหาหุ้นที่ต้องการใหม่อีกครั้ง
      </p>

    </div>
  </main>

</body>
</html>
