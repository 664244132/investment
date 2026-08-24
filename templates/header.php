<?php
declare(strict_types=1);

/**
 * templates/header.php
 * Modular Document Head & Luxury Glassmorphic Navigation Bar
 * SmartPort AI - 100% Local Assets, Icons Only, Safe Area Ready
 */

// ตรวจสอบค่าเริ่มต้นสำหรับตัวแปรที่ใช้ใน Header
$pageTitle = $pageTitle ?? APP_TITLE;
$activeNav = $activeNav ?? '';
$isUserLoggedIn = $isUserLoggedIn ?? isLoggedIn();
$currentUser = $currentUser ?? getCurrentUser();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  
  <!-- Favicon Icons (Native Brand Icon) -->
  <link rel="icon" type="image/svg+xml" href="<?= e(BASE_URL) ?>/favicon.svg">
  <link rel="icon" type="image/png" href="<?= e(BASE_URL) ?>/favicon.png">
  <link rel="apple-touch-icon" href="<?= e(BASE_URL) ?>/favicon.png">

  <!-- CSS Stylesheets (Local Assets 100%) -->
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/variables.css?v=<?= e(APP_VERSION) ?>">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/layout.css?v=<?= e(APP_VERSION) ?>">
  <link rel="stylesheet" href="<?= e(CSS_URL) ?>/components.css?v=<?= e(APP_VERSION) ?>">

  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.toggleMobileDrawer = function(e) {
      if (e) {
        if (e.stopPropagation) e.stopPropagation();
      }
      var drawer = document.getElementById('mobileDrawer');
      var btn = document.getElementById('mobileMenuBtn');
      var iconMenu = document.querySelector('.toggle-icon-menu');
      var iconClose = document.querySelector('.toggle-icon-close');
      if (!drawer) return;
      var isOpen = drawer.classList.toggle('open');
      if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      if (iconMenu) iconMenu.style.display = isOpen ? 'none' : 'inline-block';
      if (iconClose) iconClose.style.display = isOpen ? 'inline-block' : 'none';
    };
  </script>

  <?php if (!empty($extraHead)): ?>
    <?= $extraHead ?>
  <?php endif; ?>
</head>
<body>

  <!-- ==============================================================================
       Ultra-Luxury Glassmorphic Header & Navigation Bar
       ============================================================================== -->
  <header class="app-header">
    <div class="container header-inner">
      
      <!-- Brand Logo -->
      <a href="<?= e(BASE_URL) ?>/index.php" class="brand-logo">
        <div class="brand-icon">
          <?= icon('chart-bar', '', 20, '#1A1300') ?>
        </div>
        <div class="brand-text">
          <span>SmartPort</span>
          <span class="brand-gold">AI</span>
        </div>
      </a>

      <!-- Desktop Nav Links (Segmented Glass Rail) -->
      <nav class="main-nav">
        <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link <?= ($activeNav === 'index') ? 'active' : '' ?>">
          วิเคราะห์หุ้น
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link <?= ($activeNav === 'screener') ? 'active' : '' ?>">
          คัดกรอง
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/compare.php" class="nav-link <?= ($activeNav === 'compare') ? 'active' : '' ?>">
          เปรียบเทียบ
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/dca.php" class="nav-link <?= ($activeNav === 'dca') ? 'active' : '' ?>">
          ออมหุ้น DCA
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/watchlist.php" class="nav-link <?= ($activeNav === 'watchlist') ? 'active' : '' ?>">
          ติดตาม
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/journal.php" class="nav-link <?= ($activeNav === 'journal') ? 'active' : '' ?>">
          บันทึกเทรด
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link <?= ($activeNav === 'portfolio') ? 'active' : '' ?>">
          พอร์ตจำลอง
        </a>
      </nav>

      <!-- Desktop Search & Auth Controls -->
      <div class="header-actions">
        
        <!-- Stock Live Search Box -->
        <div class="search-box">
          <?= icon('search', '', 14, '#8A7F70') ?>
          <input type="text" id="stockSearchInput" placeholder="ค้นหาหุ้น... (กด Enter)">
        </div>

        <?php if ($isUserLoggedIn): ?>
          <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="btn btn-outline btn-sm btn-header-action btn-header-portfolio">
            <?= icon('wallet', '', 14) ?> พอร์ตของฉัน
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="btn btn-outline btn-sm btn-header-action btn-header-logout">
            ออกจากระบบ
          </a>
        <?php else: ?>
          <a href="<?= e(BASE_URL) ?>/pages/login.php" class="btn btn-outline btn-sm btn-header-action btn-header-login">
            เข้าสู่ระบบ
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-primary btn-sm btn-header-action btn-header-register">
            เริ่มต้นใช้งานฟรี
          </a>
        <?php endif; ?>

      </div>

      <!-- Mobile Hamburger Toggle Button (Direct Child) -->
      <button class="mobile-toggle" id="mobileMenuBtn" aria-label="Toggle Menu" aria-expanded="false" type="button" onclick="window.toggleMobileDrawer(event)">
        <span class="toggle-icon-menu"><?= icon('menu', '', 20) ?></span>
        <span class="toggle-icon-close d-none"><?= icon('close', '', 20) ?></span>
      </button>

    </div>

    <!-- Mobile Drawer Navigation -->
    <div class="mobile-drawer" id="mobileDrawer">
      <!-- Mobile Quick Search Box -->
      <div class="mobile-search-row">
        <div class="search-box mobile-search-box">
          <?= icon('search', '', 14, '#8A7F70') ?>
          <input type="text" id="mobileStockSearchInput" placeholder="ค้นหาหุ้นด่วน... (กด Enter)">
        </div>
      </div>

      <!-- Mobile Navigation Links -->
      <div class="mobile-nav-links">
        <a href="<?= e(BASE_URL) ?>/index.php" class="nav-link <?= ($activeNav === 'index') ? 'active' : '' ?>">
          <span class="mobile-nav-item">
            <?= icon('chart-bar', 'text-gold', 16) ?> หน้าหลัก / วิเคราะห์หุ้น
          </span>
          <?= icon('arrow-up-right', 'text-muted', 14) ?>
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="nav-link <?= ($activeNav === 'screener') ? 'active' : '' ?>">
          <span class="mobile-nav-item">
            <?= icon('filter', 'text-gold', 16) ?> คัดกรองหุ้น (Screener)
          </span>
          <?= icon('arrow-up-right', 'text-muted', 14) ?>
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/compare.php" class="nav-link <?= ($activeNav === 'compare') ? 'active' : '' ?>">
          <span class="mobile-nav-item">
            <?= icon('scale', 'text-gold', 16) ?> เปรียบเทียบหุ้น (Compare)
          </span>
          <?= icon('arrow-up-right', 'text-muted', 14) ?>
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/dca.php" class="nav-link <?= ($activeNav === 'dca') ? 'active' : '' ?>">
          <span class="mobile-nav-item">
            <?= icon('repeat', 'text-gold', 16) ?> จำลองออมหุ้น (DCA)
          </span>
          <?= icon('arrow-up-right', 'text-muted', 14) ?>
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/watchlist.php" class="nav-link <?= ($activeNav === 'watchlist') ? 'active' : '' ?>">
          <span class="mobile-nav-item">
            <?= icon('bookmark', 'text-gold', 16) ?> รายการติดตาม (Watchlist)
          </span>
          <?= icon('arrow-up-right', 'text-muted', 14) ?>
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/journal.php" class="nav-link <?= ($activeNav === 'journal') ? 'active' : '' ?>">
          <span class="mobile-nav-item">
            <?= icon('book-open', 'text-gold', 16) ?> บันทึกการเทรด (Journal)
          </span>
          <?= icon('arrow-up-right', 'text-muted', 14) ?>
        </a>
        <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="nav-link <?= ($activeNav === 'portfolio') ? 'active' : '' ?>">
          <span class="mobile-nav-item">
            <?= icon('wallet', 'text-gold', 16) ?> พอร์ตจำลอง (Portfolio)
          </span>
          <?= icon('arrow-up-right', 'text-muted', 14) ?>
        </a>
      </div>

      <!-- Mobile Auth / User Section -->
      <div class="mobile-auth-section">
        <?php if ($isUserLoggedIn): ?>
          <div class="mobile-user-info">
            <div class="d-flex align-center gap-xs">
              <div class="mobile-user-avatar">
                <?= icon('user', 'text-gold', 16) ?>
              </div>
              <div>
                <div class="font-bold text-primary text-sm"><?= e($currentUser['full_name'] ?: $currentUser['username']) ?></div>
                <div class="text-xs text-muted"><?= e($currentUser['email'] ?? '') ?></div>
              </div>
            </div>
            <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="btn btn-outline btn-sm text-loss">
              <?= icon('close', 'text-loss', 12) ?> ออกจากระบบ
            </a>
          </div>
        <?php else: ?>
          <div class="grid-2col-gap-sm">
            <a href="<?= e(BASE_URL) ?>/pages/login.php" class="btn btn-outline btn-sm btn-justify-center">
              เข้าสู่ระบบ
            </a>
            <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-primary btn-sm btn-justify-center">
              สมัครสมาชิกฟรี
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>
