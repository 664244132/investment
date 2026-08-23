<?php
declare(strict_types=1);

/**
 * 📝 pages/register.php
 * หน้าสมัครสมาชิกใหม่ (Create Account)
 * ออกแบบตามมาตรฐาน: Icons Only (Zero Emojis), Local Assets Only (Zero External CDNs)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';

startAppSession();

if (isLoggedIn()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName        = (string)($_POST['full_name'] ?? '');
    $username        = (string)($_POST['username'] ?? '');
    $email           = (string)($_POST['email'] ?? '');
    $password        = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    $agreed          = isset($_POST['agreed']);

    if (!$agreed) {
        $error = 'กรุณายินยอมรับข้อกำหนดในการใช้งานและนโยบายความเป็นส่วนตัว';
    } elseif ($password !== $confirmPassword) {
        $error = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง';
    } else {
        $pdo = getDbConnection();
        $result = registerUser($pdo, $username, $email, $password, $fullName);

        if ($result['success']) {
            loginUser($pdo, $username, $password);
            setFlashMessage('success', $result['message']);
            redirect('index.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิกฟรี - <?= e(APP_TITLE) ?></title>
  
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

      <div class="header-actions">
        <span style="font-size: 0.875rem; color: var(--text-muted);">มีบัญชีอยู่แล้ว?</span>
        <a href="<?= e(BASE_URL) ?>/pages/login.php" class="btn btn-outline btn-sm">
          เข้าสู่ระบบ
        </a>
      </div>
    </div>
  </header>

  <!-- Register Form Container -->
  <main class="main-content auth-wrapper">
    <div class="auth-card auth-card-wide" style="display: grid; grid-template-columns: 1fr; gap: 2.5rem;">
      
      <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem;" class="register-grid">
        
        <!-- Left Column: Benefits & Value Proposition -->
        <div style="display: flex; flex-direction: column; justify-content: center; gap: 1.75rem;">
          <div>
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.75rem; border-radius: var(--radius-full); background: var(--color-success-subtle); color: var(--color-success); border: 1px solid var(--color-success-border); font-size: 0.75rem; font-family: var(--font-mono); margin-bottom: 1rem;">
              <?= icon('zap', 'text-gain', 14) ?> พอร์ตจำลองเงินทุน 1,000,000 บาท ฟรี
            </div>
            <h1 style="font-family: var(--font-serif); font-size: 2rem; line-height: 1.3; color: var(--text-primary); margin-bottom: 0.75rem;">
              ตัดสินใจลงทุนอย่างชาญฉลาด<br>ด้วยพลังของ AI
            </h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
              AlphaVision ช่วยกลั่นกรองงบการเงินที่ซับซ้อนให้กลายเป็นบทวิเคราะห์ที่ชัดเจนและนำไปใช้ได้จริง พร้อมทดลองเทรดในพอร์ตจำลองแบบ Real-time
            </p>
          </div>

          <!-- Feature Perks -->
          <div style="display: flex; flex-direction: column; gap: 0.85rem;">
            <?php
            $perks = [
                'ฟรีเงินทุนจำลอง 1,000,000 บาท ทดลองซื้อขายหุ้นจริงแบบ Real-time',
                'เข้าถึงบทวิเคราะห์เจาะลึกงบการเงิน 3 ด้าน (งบดุล, งบกำไรขาดทุน, งบกระแสเงินสด)',
                'ระบบ AI สรุปจุดเด่น-จุดเสี่ยง และให้คะแนน AI Stock Score (0-100)',
                'ตัวกรองคัดเลือกหุ้นคุณค่าและหุ้นเติบโต (Smart Stock Screener)'
            ];
            foreach ($perks as $perk):
            ?>
              <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                <div style="width: 20px; height: 20px; border-radius: 50%; background: var(--color-success-subtle); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; margin-top: 2px; flex-shrink: 0;">
                  <?= icon('check', 'text-gain', 12) ?>
                </div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;"><?= e($perk) ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Testimonial Box -->
          <div style="background: var(--bg-card-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.25rem;">
            <div style="color: var(--accent-gold); margin-bottom: 0.5rem; display: flex; gap: 2px;">
              <?= icon('star', 'text-gold', 14) ?>
              <?= icon('star', 'text-gold', 14) ?>
              <?= icon('star', 'text-gold', 14) ?>
              <?= icon('star', 'text-gold', 14) ?>
              <?= icon('star', 'text-gold', 14) ?>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.85rem; font-style: italic; margin-bottom: 0.75rem;">
              "ระบบ AI สรุปงบการเงินได้รวดเร็วมาก ช่วยประหยัดเวลาการแกะงบไปได้เยอะ และพอร์ตจำลองทำให้กล้าทดลองกลยุทธ์ใหม่ๆ"
            </p>
            <div style="display: flex; align-items: center; gap: 0.65rem;">
              <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--accent-gold); color: var(--text-dark); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                <?= icon('user', '', 14, '#1D1616') ?>
              </div>
              <div>
                <p style="font-size: 0.8rem; font-weight: 600; color: var(--text-primary);">คุณธนภัทร</p>
                <p style="font-size: 0.7rem; color: var(--text-muted);">นักลงทุนอิสระ</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Registration Form -->
        <div>
          <div style="margin-bottom: 1.5rem;">
            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; color: var(--text-primary); margin-bottom: 0.25rem;">
              สร้างบัญชีผู้ใช้งานของคุณ
            </h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">
              กรอกข้อมูลเพื่อเริ่มต้นใช้งานระบบและรับพอร์ตจำลองทันที
            </p>
          </div>

          <?php if ($error !== null): ?>
            <div class="alert alert-danger">
              <?= icon('alert', 'text-loss', 16) ?>
              <span><?= e($error) ?></span>
            </div>
          <?php endif; ?>

          <form action="<?= e(BASE_URL) ?>/pages/register.php" method="POST">
            
            <div class="form-group">
              <label for="full_name" class="form-label">ชื่อ - นามสกุล</label>
              <div class="form-input-wrapper">
                <input 
                  type="text" 
                  id="full_name" 
                  name="full_name" 
                  class="form-input" 
                  placeholder="เช่น สมชาย นักลงทุน" 
                  value="<?= e($_POST['full_name'] ?? '') ?>"
                >
              </div>
            </div>

            <div class="form-group">
              <label for="username" class="form-label">ชื่อผู้ใช้ (Username) *</label>
              <div class="form-input-wrapper">
                <input 
                  type="text" 
                  id="username" 
                  name="username" 
                  class="form-input" 
                  placeholder="เช่น investor_pro" 
                  value="<?= e($_POST['username'] ?? '') ?>"
                  required
                >
              </div>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">อีเมล (Email Address) *</label>
              <div class="form-input-wrapper">
                <input 
                  type="email" 
                  id="email" 
                  name="email" 
                  class="form-input" 
                  placeholder="name@domain.com" 
                  value="<?= e($_POST['email'] ?? '') ?>"
                  required
                >
              </div>
            </div>

            <div class="form-group">
              <label for="reg_password" class="form-label">รหัสผ่าน (Password) *</label>
              <div class="form-input-wrapper">
                <input 
                  type="password" 
                  id="reg_password" 
                  name="password" 
                  class="form-input" 
                  placeholder="ความยาวอย่างน้อย 6-8 ตัวอักษร" 
                  required
                >
                <button type="button" class="input-toggle-btn" data-target="reg_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                  <?= icon('eye', '', 16) ?>
                </button>
              </div>
              <!-- Strength meter -->
              <div class="pw-strength-bar">
                <div class="pw-strength-segment"></div>
                <div class="pw-strength-segment"></div>
                <div class="pw-strength-segment"></div>
                <div class="pw-strength-segment"></div>
              </div>
            </div>

            <div class="form-group">
              <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านอีกครั้ง *</label>
              <div class="form-input-wrapper">
                <input 
                  type="password" 
                  id="confirm_password" 
                  name="confirm_password" 
                  class="form-input" 
                  placeholder="พิมพ์รหัสผ่านเดิมซ้ำอีกครั้ง" 
                  required
                >
                <button type="button" class="input-toggle-btn" data-target="confirm_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                  <?= icon('eye', '', 16) ?>
                </button>
              </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
              <label style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted); cursor: pointer;">
                <input type="checkbox" name="agreed" value="1" <?= isset($_POST['agreed']) ? 'checked' : '' ?> style="margin-top: 3px; accent-color: var(--accent-gold);" required>
                <span>
                  ฉันยอมรับ <span style="color: var(--accent-gold);">เงื่อนไขการให้บริการ</span> และ <span style="color: var(--accent-gold);">นโยบายความเป็นส่วนตัว</span>
                </span>
              </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
              สร้างบัญชีและรับพอร์ตจำลองฟรี
            </button>

          </form>

          <p style="text-align: center; margin-top: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
            มีบัญชีอยู่แล้ว? 
            <a href="<?= e(BASE_URL) ?>/pages/login.php" style="color: var(--accent-gold); font-weight: 600;">
              เข้าสู่ระบบที่นี่
            </a>
          </p>

        </div>

      </div>

    </div>
  </main>

  <style>
    @media (min-width: 992px) {
      .register-grid {
        grid-template-columns: 1.1fr 1fr !important;
      }
    }
  </style>

  <!-- Scripts (Local Assets) -->
  <script src="<?= e(JS_URL) ?>/main.js"></script>
</body>
</html>
