<?php
declare(strict_types=1);

/**
 * 🔐 pages/login.php
 * SmartPort AI - User Authentication
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

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ - <?= e(APP_TITLE) ?></title>
  
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
        <span>SmartPort AI</span>
      </a>

      <div class="header-actions">
        <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-primary btn-sm">
          เริ่มต้นใช้งานฟรี
        </a>
      </div>
    </div>
  </header>

  <!-- Login Form Wrapper -->
  <main class="main-content auth-wrapper">
    <div class="auth-card">
      
      <!-- Card Header -->
      <div style="text-align: center; margin-bottom: 2rem;">
        <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: var(--accent-gold-subtle); border: 1px solid var(--accent-gold-border); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
          <?= icon('lock', 'text-gold', 24) ?>
        </div>
        <h1 style="font-size: 1.65rem; color: var(--text-primary); margin-bottom: 0.25rem;">
          ยินดีต้อนรับกลับมา
        </h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
          เข้าสู่ระบบเพื่อเข้าถึงบทวิเคราะห์หุ้นและพอร์ตจำลอง
        </p>
      </div>

      <!-- Flash Message & Errors -->
      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
          <?= icon('info', '', 16) ?>
          <span><?= e($flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($error !== null): ?>
        <div class="alert alert-danger">
          <?= icon('alert', 'text-loss', 16) ?>
          <span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form action="<?= e(BASE_URL) ?>/pages/login.php" method="POST">
        
        <div class="form-group">
          <label for="identifier" class="form-label">ชื่อผู้ใช้ หรือ อีเมล</label>
          <div class="form-input-wrapper">
            <input 
              type="text" 
              id="identifier" 
              name="identifier" 
              class="form-input" 
              placeholder="เช่น investor_demo หรือ demo@investment.local" 
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
              placeholder="กรอกรหัสผ่านของคุณ (ตัวอย่าง: password123)" 
              required
            >
            <button type="button" class="input-toggle-btn" data-target="login_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
              <?= icon('eye', '', 16) ?>
            </button>
          </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.5rem;">
          <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
            <input type="checkbox" name="remember" style="accent-color: var(--accent-gold);">
            จดจำฉันในระบบ
          </label>
          <span style="color: var(--accent-gold); cursor: pointer;">ลืมรหัสผ่าน?</span>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg">
          เข้าสู่ระบบ (Sign In)
        </button>

      </form>

      <!-- Divider -->
      <div style="display: flex; align-items: center; gap: 0.75rem; margin: 1.5rem 0; color: var(--text-muted); font-size: 0.75rem;">
        <div style="flex: 1; height: 1px; background: var(--border-subtle);"></div>
        <span style="white-space: nowrap;">บัญชีทดสอบในระบบ</span>
        <div style="flex: 1; height: 1px; background: var(--border-subtle);"></div>
      </div>

      <!-- Quick Demo Account Box -->
      <div style="background: var(--bg-card-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.85rem; font-size: 0.8rem; color: var(--text-muted);">
        <p style="color: var(--accent-gold); font-weight: 600; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 4px; white-space: nowrap;">
          <?= icon('info', 'text-gold', 14) ?> บัญชี Demo สำหรับทดลองใช้:
        </p>
        <p>User: <code style="color: var(--text-primary);">investor_demo</code> | Pass: <code style="color: var(--text-primary);">password123</code></p>
      </div>

      <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
        ยังไม่มีบัญชีผู้ใช้? 
        <a href="<?= e(BASE_URL) ?>/pages/register.php" style="color: var(--accent-gold); font-weight: 600;">
          สมัครสมาชิกฟรีที่นี่
        </a>
      </p>

    </div>
  </main>

  <!-- Scripts (Local Asset) -->
  <script src="<?= e(JS_URL) ?>/main.js"></script>
</body>
</html>
