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
      
      <!-- Top Grid: 4 Clean Columns -->
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

        <!-- Col 2: Stock Analysis & Screening Modules -->
        <div>
          <div class="footer-title-wrap">
            <?= icon('trending-up', 'text-gold', 15) ?>
            <h4 class="footer-title">วิเคราะห์ &amp; คัดกรองหุ้น</h4>
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
              <a href="<?= e(BASE_URL) ?>/pages/compare.php" class="footer-link">
                <?= icon('scale', 'text-muted', 13) ?> เปรียบเทียบหุ้นคู่แข่ง (Compare)
              </a>
            </li>
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/dca.php" class="footer-link">
                <?= icon('chart-bar', 'text-muted', 13) ?> จำลองการลงทุนสะสม (DCA)
              </a>
            </li>
          </ul>
        </div>

        <!-- Col 3: Portfolio & Statement Modules -->
        <div>
          <div class="footer-title-wrap">
            <?= icon('wallet', 'text-gold', 15) ?>
            <h4 class="footer-title">พอร์ตการลงทุน &amp; รายงาน</h4>
          </div>
          <ul class="footer-links">
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link">
                <?= icon('wallet', 'text-muted', 13) ?> พอร์ตจำลอง ฿1,000,000
              </a>
            </li>
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/watchlist.php" class="footer-link">
                <?= icon('bookmark', 'text-muted', 13) ?> รายการหุ้นเฝ้าดู (Watchlist)
              </a>
            </li>
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/journal.php" class="footer-link">
                <?= icon('document', 'text-muted', 13) ?> บันทึกการเทรด (Journal)
              </a>
            </li>
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/export.php" class="footer-link">
                <?= icon('file-text', 'text-muted', 13) ?> ใบรายงานสถานะพอร์ต (Export)
              </a>
            </li>
          </ul>
        </div>

        <!-- Col 4: Account & System Diagnostics -->
        <div>
          <div class="footer-title-wrap">
            <?= icon('user', 'text-gold', 15) ?>
            <h4 class="footer-title">บัญชีผู้ใช้ &amp; ระบบ</h4>
          </div>

          <ul class="footer-links">
            <?php if (isLoggedIn()): ?>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="footer-link text-gold">
                  <?= icon('wallet', 'text-gold', 13) ?> พอร์ตจำลองของฉัน
                </a>
              </li>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/logout.php" class="footer-link text-loss">
                  <?= icon('close', 'text-loss', 13) ?> ออกจากระบบ
                </a>
              </li>
            <?php else: ?>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/login.php" class="footer-link">
                  <?= icon('user', 'text-muted', 13) ?> เข้าสู่ระบบ
                </a>
              </li>
              <li class="footer-link-item">
                <a href="<?= e(BASE_URL) ?>/pages/register.php" class="footer-link text-gold">
                  <?= icon('zap', 'text-gold', 13) ?> สมัครสมาชิกใหม่
                </a>
              </li>
            <?php endif; ?>
            <li class="footer-link-item">
              <a href="<?= e(BASE_URL) ?>/pages/health_check.php" class="footer-link">
                <?= icon('check', 'text-gain', 13) ?> ตรวจสอบระบบ (Health Check)
              </a>
            </li>
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
