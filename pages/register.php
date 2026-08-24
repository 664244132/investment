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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <div class="auth-card auth-card-wide">
      
      <div class="register-grid">
        
        <!-- Left Column: Benefits & Value Proposition -->
        <div class="d-flex flex-column gap-lg">
          <div>
            <h1 class="auth-title font-bold text-left section-mb-xs">
              ตัดสินใจลงทุนอย่างชาญฉลาดด้วยพลังของ AI
            </h1>
            <p class="auth-subtitle text-sm line-height-relaxed">
              SmartPort AI ช่วยกลั่นกรองงบการเงินที่ซับซ้อนให้กลายเป็นบทวิเคราะห์ที่ชัดเจนและนำไปใช้ได้จริง พร้อมทดลองเทรดในพอร์ตจำลองแบบ Real-time
            </p>
          </div>

          <!-- Feature Perks -->
          <div class="d-flex flex-column gap-sm">
            <?php
            $perks = [
                'ฟรีเงินทุนจำลอง 1,000,000 บาท ทดลองซื้อขายหุ้นจริงแบบ Real-time',
                'เข้าถึงบทวิเคราะห์เจาะลึกงบการเงิน 3 ด้าน (งบดุล, งบกำไรขาดทุน, งบกระแสเงินสด)',
                'ระบบ AI สรุปจุดเด่น-จุดเสี่ยง และให้คะแนน SmartPort Composite Rating (0-100)',
                'ตัวกรองคัดเลือกหุ้นคุณค่าและหุ้นเติบโต (Smart Stock Screener)'
            ];
            foreach ($perks as $perk):
            ?>
              <div class="auth-perk-row">
                <div class="auth-perk-icon">
                  <?= icon('check', 'text-gain', 12) ?>
                </div>
                <span class="auth-perk-text"><?= e($perk) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Right Column: Registration Form -->
        <div>
          
          <div class="section-mb-md">
            <h2 class="auth-title font-bold text-left section-mb-xs">
              สร้างบัญชีผู้ใช้ใหม่
            </h2>
            <p class="auth-subtitle text-sm line-height-relaxed">
              กรอกข้อมูลเพื่อเริ่มต้นใช้งานฟรี ไม่มีค่าใช้จ่ายแอบแฝง
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

          <form action="<?= e(BASE_URL) ?>/pages/register.php" method="POST">
            <?= getCsrfInputField() ?>
            
            <div class="form-group">
              <label for="reg_username" class="form-label">ชื่อผู้ใช้ (Username) *</label>
              <div class="form-input-wrapper">
                <input 
                  type="text" 
                  id="reg_username" 
                  name="username" 
                  class="form-input" 
                  placeholder="เช่น super_trader (ภาษาอังกฤษและตัวเลข 3-30 ตัว)" 
                  value="<?= e($_POST['username'] ?? '') ?>"
                  required 
                  autofocus
                >
              </div>
            </div>

            <div class="form-group">
              <label for="reg_email" class="form-label">อีเมล (Email Address) *</label>
              <div class="form-input-wrapper">
                <input 
                  type="email" 
                  id="reg_email" 
                  name="email" 
                  class="form-input" 
                  placeholder="เช่น trader@example.com" 
                  value="<?= e($_POST['email'] ?? '') ?>"
                  required
                >
              </div>
            </div>

            <div class="form-group">
              <label for="reg_full_name" class="form-label">ชื่อ-นามสกุล (Full Name)</label>
              <div class="form-input-wrapper">
                <input 
                  type="text" 
                  id="reg_full_name" 
                  name="full_name" 
                  class="form-input" 
                  placeholder="เช่น สมชาย ลงทุนดี" 
                  value="<?= e($_POST['full_name'] ?? '') ?>"
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
                  placeholder="กำหนดรหัสผ่านอย่างน้อย 6 ตัวอักษร" 
                  required
                >
                <button type="button" class="input-toggle-btn" data-target="reg_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                  <?= icon('eye', '', 16) ?>
                </button>
              </div>
              
              <!-- Password Strength Meter -->
              <div class="pw-strength-bar">
                <div class="pw-strength-segment"></div>
                <div class="pw-strength-segment"></div>
                <div class="pw-strength-segment"></div>
                <div class="pw-strength-segment"></div>
              </div>
              <span class="text-xs text-muted">
                แนะนำ: มีตัวพิมพ์ใหญ่-เล็ก ตัวเลข และสัญลักษณ์
              </span>
            </div>

            <div class="form-group">
              <label for="reg_confirm_password" class="form-label">ยืนยันรหัสผ่าน (Confirm Password) *</label>
              <div class="form-input-wrapper">
                <input 
                  type="password" 
                  id="reg_confirm_password" 
                  name="confirm_password" 
                  class="form-input" 
                  placeholder="พิมพ์รหัสผ่านเดิมอีกครั้ง" 
                  required
                >
                <button type="button" class="input-toggle-btn" data-target="reg_confirm_password" aria-label="แสดง/ซ่อนรหัสผ่าน">
                  <?= icon('eye', '', 16) ?>
                </button>
              </div>
            </div>

            <div class="section-mb-md">
              <label class="auth-terms-label">
                <input type="checkbox" name="agreed" value="1" <?= isset($_POST['agreed']) ? 'checked' : '' ?> class="form-checkbox-input" required>
                <span>
                  ฉันยอมรับ <span class="text-gold">เงื่อนไขการให้บริการ</span> และ <span class="text-gold">นโยบายความเป็นส่วนตัว</span>
                </span>
              </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
              สร้างบัญชีและรับพอร์ตจำลองฟรี
            </button>

          </form>

          <p class="auth-footer-text">
            มีบัญชีอยู่แล้ว? 
            <a href="<?= e(BASE_URL) ?>/pages/login.php" class="auth-footer-link">
              เข้าสู่ระบบที่นี่
            </a>
          </p>

        </div>

      </div>

    </div>
  </main>

  <!-- Footer Template -->
  <?php require_once __DIR__ . '/../templates/footer.php'; ?>
