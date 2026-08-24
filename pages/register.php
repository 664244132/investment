<?php
declare(strict_types=1);

/**
 * pages/register.php
 * SmartPort AI - User Registration Portal - User Registration & Instant Portfolio Provisioning
 * ออกแบบตามมาตรฐาน: Unified Sans Typography, Clean Rounded Rectangles, Icons Only, Zero External CDNs, Flat Subtle States
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';

startAppSession();

// หากเข้าสู่ระบบอยู่แล้ว ให้เปลี่ยนเส้นทางไปยังหน้าหลัก
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$pdo = getDbConnection();
$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'การตรวจสอบความปลอดภัย CSRF ล้มเหลว กรุณารีเฟรชหน้าเว็บแล้วสมัครสมาชิกใหม่อีกครั้ง';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');
        $fullName = trim((string)($_POST['full_name'] ?? ''));

        if ($password !== $confirmPassword) {
            $error = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง';
        } else {
            $result = registerUser($pdo, $username, $email, $password, $fullName);
            if ($result['success']) {
                // ทำการ Login อัตโนมัติหลังลงทะเบียนสำเร็จ
                loginUser($pdo, $username, $password);
                setFlashMessage('success', 'ยินดีต้อนรับสู่ SmartPort AI! ระบบได้สร้างพอร์ตจำลองเงินทุน 1,000,000 บาทให้คุณเรียบร้อยแล้ว');
                redirect(BASE_URL . '/pages/portfolio.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

$pageTitle = 'สมัครสมาชิก - ' . APP_TITLE;
$activeNav = 'register';

require_once __DIR__ . '/../templates/header.php';
?>

  <!-- Register Form Container -->
  <main class="main-content auth-wrapper">
    <div class="auth-card auth-form-card">
      
      <!-- Card Header -->
      <div class="auth-header-center">
        <div class="auth-icon-badge">
          <?= icon('zap', 'text-gold', 24) ?>
        </div>
        <h1 class="auth-title">
          สร้างบัญชีผู้ใช้ใหม่
        </h1>
        <p class="auth-subtitle">
          เริ่มต้นใช้งานฟรี • รับเงินทุนจำลอง ฿1,000,000 บาททันที
        </p>
      </div>

      <!-- Flash Message & Errors -->
      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert">
          <?= icon('alert-triangle', '', 16) ?>
          <span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Registration Form -->
      <form action="<?= e(BASE_URL) ?>/pages/register.php" method="POST" autocomplete="on">
        <?= getCsrfInputField() ?>
        
        <!-- Row 1: Username & Full Name -->
        <div class="auth-row-2">
          <div class="form-group">
            <label for="reg_username" class="form-label">
              ชื่อผู้ใช้ (Username) <span class="text-gold">*</span>
            </label>
            <div class="form-input-wrapper">
              <span class="input-icon-left"><?= icon('user', '', 15) ?></span>
              <input 
                type="text" 
                id="reg_username" 
                name="username" 
                class="form-input has-left-icon" 
                placeholder="เช่น trader_pro" 
                value="<?= e($_POST['username'] ?? '') ?>"
                autocomplete="username"
                required 
                autofocus
              >
            </div>
          </div>

          <div class="form-group">
            <label for="reg_full_name" class="form-label">
              ชื่อ-นามสกุล <span class="text-xs font-normal text-muted">(ไม่บังคับ)</span>
            </label>
            <div class="form-input-wrapper">
              <span class="input-icon-left"><?= icon('user-check', '', 15) ?></span>
              <input 
                type="text" 
                id="reg_full_name" 
                name="full_name" 
                class="form-input has-left-icon" 
                placeholder="เช่น สมชาย มั่งคั่ง" 
                value="<?= e($_POST['full_name'] ?? '') ?>"
                autocomplete="name"
              >
            </div>
          </div>
        </div>

        <!-- Row 2: Email Address -->
        <div class="form-group">
          <label for="reg_email" class="form-label">
            อีเมล (Email Address) <span class="text-gold">*</span>
          </label>
          <div class="form-input-wrapper">
            <span class="input-icon-left"><?= icon('mail', '', 15) ?></span>
            <input 
              type="email" 
              id="reg_email" 
              name="email" 
              class="form-input has-left-icon" 
              placeholder="เช่น your.email@example.com" 
              value="<?= e($_POST['email'] ?? '') ?>"
              autocomplete="email"
              required
            >
          </div>
        </div>

        <!-- Row 3: Password & Confirm Password -->
        <div class="auth-row-2">
          <div class="form-group">
            <label for="reg_password" class="form-label">
              รหัสผ่าน (Password) <span class="text-gold">*</span>
            </label>
            <div class="form-input-wrapper">
              <span class="input-icon-left"><?= icon('lock', '', 15) ?></span>
              <input 
                type="password" 
                id="reg_password" 
                name="password" 
                class="form-input has-left-icon has-toggle" 
                placeholder="อย่างน้อย 6 ตัวอักษร" 
                autocomplete="new-password"
                required
              >
              <button type="button" class="input-toggle-btn" data-target="reg_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                <?= icon('eye', '', 16) ?>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="reg_confirm_password" class="form-label">
              ยืนยันรหัสผ่าน <span class="text-gold">*</span>
            </label>
            <div class="form-input-wrapper">
              <span class="input-icon-left"><?= icon('lock', '', 15) ?></span>
              <input 
                type="password" 
                id="reg_confirm_password" 
                name="confirm_password" 
                class="form-input has-left-icon has-toggle" 
                placeholder="พิมพ์รหัสผ่านซ้ำอีกครั้ง" 
                autocomplete="new-password"
                required
              >
              <button type="button" class="input-toggle-btn" data-target="reg_confirm_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                <?= icon('eye', '', 16) ?>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Password Strength Meter & Interactive Guide -->
        <div class="pw-strength-container">
          <div class="pw-strength-bar">
            <div class="pw-strength-segment"></div>
            <div class="pw-strength-segment"></div>
            <div class="pw-strength-segment"></div>
            <div class="pw-strength-segment"></div>
          </div>
          <div class="pw-strength-text d-flex justify-between align-center">
            <span id="pwStrengthLabel">ระดับความปลอดภัย: ขั้นต่ำ 6 ตัวอักษร</span>
            <span class="text-muted">แนะนำ: ผสมตัวพิมพ์, ตัวเลข</span>
          </div>
        </div>

        <!-- Terms Agreement -->
        <div class="section-mb-md">
          <label class="auth-terms-label">
            <input type="checkbox" name="agreed" value="1" <?= isset($_POST['agreed']) ? 'checked' : '' ?> class="form-checkbox-input" required>
            <span>
              ฉันยอมรับ <span class="text-gold">เงื่อนไขการให้บริการ</span> และ <span class="text-gold">นโยบายความเป็นส่วนตัว</span> ของ SmartPort AI
            </span>
          </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-full btn-lg">
          <?= icon('zap', '', 16) ?>
          <span>สร้างบัญชีและรับพอร์ต 1,000,000 บาทฟรี</span>
        </button>

      </form>

      <!-- Footer Link -->
      <p class="auth-footer-text">
        มีบัญชีผู้ใช้อยู่แล้ว? 
        <a href="<?= e(BASE_URL) ?>/pages/login.php" class="auth-footer-link">
          เข้าสู่ระบบที่นี่
        </a>
      </p>

    </div>
  </main>

  <!-- Footer Template -->
  <?php require_once __DIR__ . '/../templates/footer.php'; ?>
