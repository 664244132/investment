<?php
declare(strict_types=1);

/**
 * pages/journal.php
 * SmartPort AI - Trading Journal & Performance Analytics
 * ไดอารี่บันทึกเหตุผลการเทรด สถิติ Win Rate, Profit Factor, และการวิเคราะห์ผลงานตามกลยุทธ์
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/portfolio.php';
require_once __DIR__ . '/../functions/journal.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();
$userId = (int)($user['id'] ?? 0);

// จัดการการอัปเดตบทเรียนที่ได้รับ (Lessons Learned)
if ($isUserLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_lesson') {
    $journalId = (int)($_POST['journal_id'] ?? 0);
    $lessons   = trim((string)($_POST['lessons_learned'] ?? ''));

    if ($journalId > 0) {
        $updated = updateTradeJournalLesson($pdo, $journalId, $userId, $lessons);
        if ($updated) {
            setFlashMessage('success', 'บันทึกบทเรียนการเทรด (Lessons Learned) เรียบร้อยแล้ว');
        } else {
            setFlashMessage('danger', 'เกิดข้อผิดพลาดในการบันทึกบทเรียน');
        }
    }
    redirect(BASE_URL . '/pages/journal.php');
}

$portfolio = $isUserLoggedIn ? getUserPortfolio($pdo, $userId) : null;
$portfolioId = $portfolio ? (int)$portfolio['id'] : 0;

$selectedStrategy = isset($_GET['strategy']) && $_GET['strategy'] !== '' ? (string)$_GET['strategy'] : 'all';
$journalEntries = $isUserLoggedIn ? getUserJournalEntries($pdo, $userId, $selectedStrategy) : [];
$perfMetrics = $isUserLoggedIn ? calculateTradingPerformanceMetrics($pdo, $portfolioId, $userId) : [];

$pageTitle = 'สมุดบันทึกการเทรด - ' . APP_TITLE;
$activeNav = 'journal';

require_once __DIR__ . '/../templates/header.php';
?>

  <main class="main-content">
    <div class="container">

      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <!-- Header Section -->
      <div class="section-heading-row section-mb-md">
        <div>
          <h1 class="section-heading-title">
            <?= icon('book-open', 'text-gold', 24) ?> สมุดบันทึกการเทรด & วิเคราะห์พฤติกรรม (Trading Journal)
          </h1>
          <p class="chart-subtitle-meta">
            ติดตามผลลัพธ์การตัดสินใจ บันทึกเหตุผลการเข้าทำรายการ สถิติ Win Rate และการสะท้อนบทเรียนหลังปิดออเดอร์
          </p>
        </div>
        <div>
          <a href="<?= e(BASE_URL) ?>/pages/portfolio.php" class="btn btn-outline btn-sm">
            <?= icon('wallet', '', 14) ?> กลับไปพอร์ตจำลอง
          </a>
        </div>
      </div>

      <?php if (!$isUserLoggedIn): ?>
        <!-- Guest State -->
        <div class="empty-watchlist-state">
          <div class="empty-watchlist-icon">
            <?= icon('book-open', '', 28) ?>
          </div>
          <h2 class="empty-state-title">
            เข้าสู่ระบบเพื่อเริ่มใช้งาน Trading Journal
          </h2>
          <p class="empty-state-desc">
            บันทึกเหตุผลการเทรด แท็กกลยุทธ์ และวิเคราะห์ประสิทธิภาพ Win Rate ของการเทรดพอร์ตจำลองอย่างเป็นระบบ
          </p>
          <div class="d-flex justify-center gap-md">
            <a href="<?= e(BASE_URL) ?>/pages/login.php" class="btn btn-primary">
              <?= icon('user', '', 14) ?> เข้าสู่ระบบ
            </a>
            <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-outline">
              เริ่มต้นใช้งานฟรี
            </a>
          </div>
        </div>
      <?php else: ?>

        <!-- Performance Analytics KPI Cards -->
        <div class="performance-kpi-grid">
          
          <!-- Win Rate KPI -->
          <div class="perf-card">
            <div class="perf-label">
              <?= icon('target', 'text-gold', 14) ?> อัตราความสำเร็จ (Win Rate)
            </div>
            <div class="perf-val <?= ($perfMetrics['win_rate'] >= 50) ? 'text-gain' : (($perfMetrics['total_closed_trades'] > 0) ? 'text-loss' : '') ?>">
              <?= number_format($perfMetrics['win_rate'], 1) ?>%
            </div>
            <div class="perf-sub">
              ชนะ <?= $perfMetrics['winning_trades'] ?> / ทั้งหมด <?= $perfMetrics['total_closed_trades'] ?> ออเดอร์
            </div>
          </div>

          <!-- Profit Factor KPI -->
          <div class="perf-card">
            <div class="perf-label">
              <?= icon('award', 'text-gold', 14) ?> Profit Factor
            </div>
            <div class="perf-val <?= ($perfMetrics['profit_factor'] >= 1.5) ? 'text-gain' : 'text-gold' ?>">
              <?= number_format($perfMetrics['profit_factor'], 2) ?>x
            </div>
            <div class="perf-sub">
              กำไรรวม / ขาดทุนรวม
            </div>
          </div>

          <!-- Total Realized P&L KPI -->
          <div class="perf-card">
            <div class="perf-label">
              <?= icon('wallet', 'text-gold', 14) ?> กำไร/ขาดทุนรับรู้สุทธิ
            </div>
            <div class="perf-val <?= getPnlColorClass($perfMetrics['total_realized_pnl']) ?>">
              <?= ($perfMetrics['total_realized_pnl'] >= 0 ? '+' : '') ?>฿<?= number_format($perfMetrics['total_realized_pnl'], 2) ?>
            </div>
            <div class="perf-sub">
              กำไร ฿<?= number_format($perfMetrics['gross_profit'], 2) ?> · ขาดทุน ฿<?= number_format($perfMetrics['gross_loss'], 2) ?>
            </div>
          </div>

          <!-- Average Win vs Loss KPI -->
          <div class="perf-card">
            <div class="perf-label">
              <?= icon('activity', 'text-gold', 14) ?> กำไรเฉลี่ย / ขาดทุนเฉลี่ย
            </div>
            <div class="perf-val text-base">
              <span class="text-gain">+฿<?= number_format($perfMetrics['avg_win'], 2) ?></span>
              <span class="text-muted text-sm"> / </span>
              <span class="text-loss">-฿<?= number_format($perfMetrics['avg_loss'], 2) ?></span>
            </div>
            <div class="perf-sub">
              ออเดอร์ดีสุด: <span class="text-gain">+฿<?= number_format($perfMetrics['best_trade'], 2) ?></span>
            </div>
          </div>

        </div>

        <!-- Strategy Performance Breakdown Table -->
        <?php if (!empty($perfMetrics['strategy_breakdown'])): ?>
          <div class="strategy-breakdown-card">
            <div class="card-header">
              <h2 class="card-title">
                <?= icon('pie-chart', 'text-gold', 18) ?> สถิติประสิทธิภาพแยกตามกลยุทธ์การลงทุน (Strategy Breakdown)
              </h2>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>กลยุทธ์ (Strategy Tag)</th>
                    <th class="th-center">จำนวนออเดอร์</th>
                    <th class="th-center">ชนะ (Win)</th>
                    <th class="th-center">แพ้ (Loss)</th>
                    <th class="th-center">Win Rate (%)</th>
                    <th class="th-right">กำไร/ขาดทุนสะสม</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($perfMetrics['strategy_breakdown'] as $strat): ?>
                    <tr>
                      <td class="font-bold">
                        <span class="badge badge-gold font-bold">
                          <?= e($strat['tag']) ?>
                        </span>
                      </td>
                      <td class="th-center font-mono"><?= (int)$strat['total_trades'] ?></td>
                      <td class="th-center font-mono text-gain"><?= (int)$strat['wins'] ?></td>
                      <td class="th-center font-mono text-loss"><?= (int)$strat['losses'] ?></td>
                      <td class="th-center font-mono font-bold <?= ($strat['win_rate'] >= 50) ? 'text-gain' : 'text-loss' ?>">
                        <?= number_format((float)$strat['win_rate'], 1) ?>%
                      </td>
                      <td class="num <?= getPnlColorClass((float)$strat['total_pnl']) ?>">
                        <?= ($strat['total_pnl'] >= 0 ? '+' : '') ?>฿<?= number_format((float)$strat['total_pnl'], 2) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <!-- Strategy Filter Bar -->
        <div class="watchlist-bar section-mb-md">
          <span class="watchlist-label">กรองตามกลยุทธ์:</span>
          <a href="<?= e(BASE_URL) ?>/pages/journal.php?strategy=all" class="watchlist-chip <?= ($selectedStrategy === 'all') ? 'active' : '' ?>">
            <?= icon('layers', '', 12) ?> ทั้งหมด
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/journal.php?strategy=Value Investing" class="watchlist-chip <?= ($selectedStrategy === 'Value Investing') ? 'active' : '' ?>">
            <?= icon('star', 'text-gold', 12) ?> Value Investing
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/journal.php?strategy=Growth Stock" class="watchlist-chip <?= ($selectedStrategy === 'Growth Stock') ? 'active' : '' ?>">
            <?= icon('trending-up', 'text-gain', 12) ?> Growth Stock
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/journal.php?strategy=Dividend Play" class="watchlist-chip <?= ($selectedStrategy === 'Dividend Play') ? 'active' : '' ?>">
            <?= icon('zap', 'text-gold', 12) ?> Dividend Play
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/journal.php?strategy=Technical Breakout" class="watchlist-chip <?= ($selectedStrategy === 'Technical Breakout') ? 'active' : '' ?>">
            <?= icon('activity', 'text-gold', 12) ?> Technical Breakout
          </a>
          <a href="<?= e(BASE_URL) ?>/pages/journal.php?strategy=AI Recommendation" class="watchlist-chip <?= ($selectedStrategy === 'AI Recommendation') ? 'active' : '' ?>">
            <?= icon('cpu', 'text-gold', 12) ?> AI Recommendation
          </a>
        </div>

        <!-- Journal Entries Timeline -->
        <?php if (empty($journalEntries)): ?>
          <div class="empty-watchlist-state">
            <div class="empty-watchlist-icon">
              <?= icon('book-open', '', 28) ?>
            </div>
            <h2 class="empty-state-title">
              ยังไม่มีประวัติการบันทึกใน Trading Journal
            </h2>
            <p class="empty-state-desc">
              เมื่อคุณส่งคำสั่งซื้อหรือขายหุ้นในพอร์ตจำลอง ระบบจะให้คุณระบุกลยุทธ์และบันทึกเหตุผลการเทรดเพื่อนำมาวิเคราะห์ในหน้านี้
            </p>
            <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="btn btn-primary">
              <?= icon('search', '', 14) ?> คัดกรองหุ้นเพื่อเริ่มเทรด
            </a>
          </div>
        <?php else: ?>
          <div class="journal-timeline">
            <?php foreach ($journalEntries as $j): 
              $isBuy = ($j['transaction_type'] === 'BUY');
              $pnl = $j['realized_pnl'] !== null ? (float)$j['realized_pnl'] : null;
            ?>
              <div class="journal-card">
                
                <!-- Card Header -->
                <div class="journal-card-header">
                  <div class="journal-stock-group">
                    <span class="badge <?= $isBuy ? 'badge-gain' : 'badge-loss' ?> badge-sm font-bold">
                      <?= $isBuy ? icon('arrow-up-right', 'text-gain', 12) . ' BUY' : icon('arrow-down-right', 'text-loss', 12) . ' SELL' ?>
                    </span>
                    <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($j['symbol']) ?>" class="journal-symbol">
                      <?= e($j['symbol']) ?>
                    </a>
                    <span class="text-sm text-muted">
                      <?= e($j['name_th'] ?: $j['name_en']) ?>
                    </span>
                    <span class="badge badge-gold badge-xs">
                      <?= e($j['strategy_tag']) ?>
                    </span>
                  </div>

                  <div class="d-flex align-center gap-md flex-wrap">
                    <span class="font-mono text-sm text-primary">
                      <?= number_format((int)$j['quantity']) ?> หุ้น @ ฿<?= number_format((float)$j['price'], 2) ?>
                    </span>
                    <?php if ($pnl !== null): ?>
                      <span class="badge <?= ($pnl >= 0) ? 'badge-gain' : 'badge-loss' ?> font-mono badge-sm font-bold">
                        Realized P&L: <?= ($pnl >= 0 ? '+' : '') ?>฿<?= number_format($pnl, 2) ?>
                      </span>
                    <?php endif; ?>
                    <span class="text-xs text-muted d-flex align-center gap-xs">
                      <?= icon('clock', '', 12) ?> <?= date('d M Y H:i', strtotime($j['trade_date'])) ?>
                    </span>
                  </div>
                </div>

                <!-- Trading Rationale -->
                <div class="journal-rationale-box">
                  <div class="journal-rationale-label">
                    <?= icon('target', 'text-gold', 12) ?> เหตุผลในการตัดสินใจเทรด (Trading Rationale)
                  </div>
                  <div>
                    <?= nl2br(e($j['rationale'] ?: 'ไม่มีการระบุเหตุผลในการเข้าทำรายการ')) ?>
                  </div>
                </div>

                <!-- Lessons Learned Section -->
                <?php if (!empty($j['lessons_learned'])): ?>
                  <div class="journal-lesson-box">
                    <div class="journal-lesson-label">
                      <?= icon('check-circle', 'text-gain', 12) ?> บทเรียนที่ได้รับ (Lessons Learned)
                    </div>
                    <div>
                      <?= nl2br(e($j['lessons_learned'])) ?>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Form to Add/Edit Lessons Learned -->
                <details class="journal-summary-details">
                  <summary class="journal-summary-btn">
                    <?= icon('edit', '', 12) ?> <?= empty($j['lessons_learned']) ? '+ เพิ่มบทเรียนที่ได้รับ (Post-trade Reflection)' : 'แก้ไขบทเรียนที่ได้รับ' ?>
                  </summary>
                  <form action="<?= e(BASE_URL) ?>/pages/journal.php" method="POST" class="form-flex-col-sm mt-sm">
                    <input type="hidden" name="action" value="update_lesson">
                    <input type="hidden" name="journal_id" value="<?= (int)$j['journal_id'] ?>">
                    <textarea name="lessons_learned" class="form-input text-sm" rows="2" placeholder="บันทึกข้อคิด เช่น ทำตามแผนได้ดี, อารมณ์ FOMO เข้าแทรก, ขายเร็วเกินไป..."><?= e($j['lessons_learned'] ?? '') ?></textarea>
                    <div class="d-flex justify-end">
                      <button type="submit" class="btn btn-primary btn-sm">
                        <?= icon('check', '', 12) ?> บันทึกบทเรียน
                      </button>
                    </div>
                  </form>
                </details>

              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php endif; ?>

    </div>
  </main>

  <?php require_once __DIR__ . '/../templates/footer.php'; ?>
