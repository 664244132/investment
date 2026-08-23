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
      
      <div class="footer-grid">
        
        <!-- Col 1: Brand & Disclaimer -->
        <div>
          <div class="brand-logo" style="margin-bottom: 1rem;">
            <div class="brand-icon">
              <?= icon('chart-bar', '', 20, '#1A1300') ?>
            </div>
            <div class="brand-text">
              <span>SmartPort</span>
              <span class="brand-gold">AI</span>
            </div>
          </div>
          <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; max-width: 320px;">
            แพลตฟอร์มวิเคราะห์งบการเงินและจำลองการลงทุนหุ้นไทยด้วยเทคโนโลยี AI เชิงลึก ช่วยให้นักลงทุนตัดสินใจได้อย่างแม่นยำและเป็นระบบ
          </p>
        </div>

        <!-- Col 2: Core Platform Links -->
        <div>
          <h4 class="footer-title">ระบบหลัก</h4>
          <ul class="footer-links">
            <li><a href="<?= e(BASE_URL) ?>/index.php" class="footer-link">วิเคราะห์หุ้นรายตัว</a></li>
            <li><a href="<?= e(BASE_URL) ?>/pages/screener.php" class="footer-link">คัดกรองหุ้นคุณค่า</a></li>
            <li><a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link">พอร์ตการลงทุนจำลอง</a></li>
          </ul>
        </div>

        <!-- Col 3: Research & Methodology -->
        <div>
          <h4 class="footer-title">โมเดล AI</h4>
          <ul class="footer-links">
            <li><span class="footer-link">Composite Rating (0-100)</span></li>
            <li><span class="footer-link">Quality & Growth Score</span></li>
            <li><span class="footer-link">Valuation & Risk Model</span></li>
          </ul>
        </div>

        <!-- Col 4: Account & Security -->
        <div>
          <h4 class="footer-title">บัญชีผู้ใช้</h4>
          <ul class="footer-links">
            <?php if (isLoggedIn()): ?>
              <li><a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link">ภาพรวมพอร์ตลงทุน</a></li>
              <li><a href="<?= e(BASE_URL) ?>/pages/logout.php" class="footer-link">ออกจากระบบ</a></li>
            <?php else: ?>
              <li><a href="<?= e(BASE_URL) ?>/pages/login.php" class="footer-link">เข้าสู่ระบบ</a></li>
              <li><a href="<?= e(BASE_URL) ?>/pages/register.php" class="footer-link">สมัครสมาชิกใหม่</a></li>
            <?php endif; ?>
          </ul>
        </div>

      </div>

      <div class="footer-bottom">
        <div>
          &copy; <?= date('Y') ?> <?= e(APP_TITLE) ?>. All rights reserved. ข้อมูลเพื่อการศึกษาและจำลองการลงทุนเท่านั้น มิใช่คำแนะนำทางการเงิน
        </div>
        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">
          System Status: <span style="color: var(--color-success);">Operational (100% Local)</span>
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
