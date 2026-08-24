<?php
declare(strict_types=1);

/**
 * pages/login.php
 * SmartPort AI - Authentication Portal - User Authentication
 * ออกแบบตามมาตรฐาน: Unified Sans Typography, Clean Rounded Rectangles, Icons Only, Zero External CDNs, Flat Subtle States
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';

startAppSession();

// หากเข้าสู่ระบบอยู่แล้ว ให้ย้ายไปหน้า Dashboard ทันที (Early Return)
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = null;

// จัดการเมื่อมีการส่งฟอร์ม (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'การตรวจสอบความปลอดภัย CSRF ล้มเหลว กรุณารีเฟรชหน้าเว็บแล้วเข้าสู่ระบบใหม่อีกครั้ง';
    } else {
        $identifier = (string)($_POST['identifier'] ?? '');
        $password   = (string)($_POST['password'] ?? '');

        $pdo = getDbConnection();
        $result = loginUser($pdo, $identifier, $password);

        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect(BASE_URL . '/index.php');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'เข้าสู่ระบบ - ' . APP_TITLE;
$activeNav = 'login';

require_once __DIR__ . '/../templates/header.php';
?>

  <!-- Login Form Wrapper -->
  <main class="main-content auth-wrapper">
    <div class="auth-card">
      
      <!-- Card Header -->
      <div class="auth-header-center">
        <div class="auth-icon-badge">
          <?= icon('lock', 'text-gold', 24) ?>
        </div>
        <h1 class="auth-title">
          ยินดีต้อนรับกลับมา
        </h1>
        <p class="auth-subtitle">
          เข้าสู่ระบบเพื่อเข้าถึงบทวิเคราะห์หุ้นและพอร์ตจำลอง
        </p>
      </div>

      <!-- Flash Message Notification -->
      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert">
          <?= icon('alert-triangle', '', 16) ?>
          <span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form action="<?= e(BASE_URL) ?>/pages/login.php" method="POST">
        <?= getCsrfInputField() ?>
        
        <div class="form-group">
          <label for="identifier" class="form-label">ชื่อผู้ใช้ หรือ อีเมล</label>
          <div class="form-input-wrapper">
            <input 
              type="text" 
              id="identifier" 
              name="identifier" 
              class="form-input" 
              placeholder="กรอกชื่อผู้ใช้ หรือ อีเมลของคุณ" 
              value="<?= e($_POST['identifier'] ?? '') ?>"
              required 
              autofocus
            >
          </div>
        </div>

        <div class="form-group">
          <label for="login_password" class="form-label">รหัสผ่าน</label>
          <div class="form-input-wrapper">
            <input 
              type="password" 
              id="login_password" 
              name="password" 
              class="form-input" 
              placeholder="กรอกรหัสผ่านของคุณ" 
              required
            >
            <button type="button" class="input-toggle-btn" data-target="login_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
              <?= icon('eye', '', 16) ?>
            </button>
          </div>
        </div>

        <div class="auth-links-row">
          <label class="auth-checkbox-label">
            <input type="checkbox" name="remember" class="form-checkbox-input">
            จดจำฉันในระบบ
          </label>
          <span class="text-gold cursor-pointer">ลืมรหัสผ่าน?</span>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg">
          เข้าสู่ระบบ (Sign In)
        </button>

      </form>

      <p class="auth-footer-text">
        ยังไม่มีบัญชีผู้ใช้? 
        <a href="<?= e(BASE_URL) ?>/pages/register.php" class="auth-footer-link">
          สมัครสมาชิกฟรีที่นี่
        </a>
      </p>

    </div>
  </main>

  <!-- Footer Template -->
  <?php require_once __DIR__ . '/../templates/footer.php'; ?>
