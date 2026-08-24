<?php
declare(strict_types=1);

/**
 * templates/footer.php
 * Modular Institutional Footer & Global JavaScript Bundle
 * SmartPort AI - 100% Local Assets
 */
?>
  <!-- ==============================================================================
       Institutional Footer
       ============================================================================== -->
  <footer class="app-footer">
    <div class="container">
      
      <!-- Top Grid: 3 Clean Columns -->
      <div class="footer-grid">
        
        <!-- Col 1: Brand & Project Mission -->
        <div class="footer-brand-col">
          <div class="brand-logo">
            <div class="brand-icon">
              <?= icon('chart-bar', '', 20, '#1A1300') ?>
            </div>
            <div class="brand-text">
              <span>SmartPort</span>
              <span class="brand-gold">AI</span>
            </div>
          </div>
          
          <p class="footer-desc">
            ระบบวิเคราะห์ปัจจัยพื้นฐานหุ้นไทยและสากล สรุปคะแนน AI Composite Score 4 มิติ และจำลองการซื้อ-ขายหุ้นเสมือนจริงด้วยเงินทุน 1,000,000 บาท บนสถาปัตยกรรม Native Local 100%
          </p>
        </div>

        <!-- Col 2: Core Platform Modules -->
        <div>
          <div class="footer-title-wrap">
            <?= icon('globe', 'text-gold', 15) ?>
            <h4 class="footer-title">ระบบการลงทุน</h4>
          </div>
          <ul class="footer-links">
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/index.php" class="footer-link">
                <?= icon('arrow-up-right', 'text-muted', 13) ?> วิเคราะห์หุ้นรายตัว &amp; กราฟราคา
              </a>
            </li>
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="footer-link">
                <?= icon('arrow-up-right', 'text-muted', 13) ?> คัดกรองหุ้นคุณค่า (AI Screener)
              </a>
            </li>
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link">
                <?= icon('arrow-up-right', 'text-muted', 13) ?> พอร์ตการลงทุนจำลอง ฿1,000,000
              </a>
            </li>
          </ul>
        </div>

        <!-- Col 3: Account & Access -->
        <div>
          <div class="footer-title-wrap">
            <?= icon('user', 'text-gold', 15) ?>
            <h4 class="footer-title">บัญชีผู้ใช้งาน</h4>
          </div>

          <ul class="footer-links">
            <?php if (isLoggedIn()): ?>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link text-gold">
                  <?= icon('wallet', 'text-gold', 14) ?> พอร์ตจำลองของฉัน
                </a>
              </li>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="footer-link text-loss">
                  <?= icon('close', 'text-loss', 14) ?> ออกจากระบบ
                </a>
              </li>
            <?php else: ?>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/login.php" class="footer-link">
                  <?= icon('user', 'text-muted', 14) ?> เข้าสู่ระบบ
                </a>
              </li>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/register.php" class="footer-link text-gold">
                  <?= icon('zap', 'text-gold', 14) ?> สมัครสมาชิกใหม่
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

      </div>

      <!-- Footer Bottom Copyright -->
      <div class="footer-bottom">
        <div>
          &copy; <?= date('Y') ?> <strong><?= e(APP_TITLE) ?></strong>. ข้อมูลเพื่อการศึกษาและจำลองการลงทุนเท่านั้น มิใช่คำแนะนำทางการเงิน
        </div>
      </div>

    </div>
  </footer>

  <!-- Core JavaScript Interactivity (100% Local) -->
  <script src="<?= e(JS_URL) ?>/main.js?v=<?= e(APP_VERSION) ?>"></script>

  <?php if (!empty($extraScripts)): ?>
    <?= $extraScripts ?>
  <?php endif; ?>

</body>
</html>
