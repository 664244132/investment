<?php
declare(strict_types=1);

/**
 * pages/watchlist.php
 * SmartPort AI - Watchlist & Price Target Alerts Dashboard
 * แสดงรายการหุ้นที่ผู้ใช้บันทึกไว้ พร้อมระบบแจ้งเตือนราคาเป้าหมาย และเครื่องมือจัดการ
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/watchlist.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/portfolio.php';

startAppSession();

$pdo = getDbConnection();
$user = getCurrentUser();
$isUserLoggedIn = isLoggedIn();
$userId = (int)($user['id'] ?? 0);

$watchlist = [];
$stats = [
    'total' => 0,
    'hit_buy' => 0,
    'near_buy' => 0,
    'hit_sell' => 0,
    'high_score' => 0
];

if ($isUserLoggedIn && $userId > 0) {
    $watchlist = getUserWatchlist($pdo, $userId);
    $stats['total'] = count($watchlist);
    
    foreach ($watchlist as $item) {
        if ($item['buy_status'] === 'hit') {
            $stats['hit_buy']++;
        } elseif ($item['buy_status'] === 'near') {
            $stats['near_buy']++;
        }
        
        if ($item['sell_status'] === 'hit') {
            $stats['hit_sell']++;
        }
        
        if ((int)($item['total_score'] ?? 0) >= 80) {
            $stats['high_score']++;
        }
    }
}

$pageTitle = 'รายการหุ้นที่ติดตาม (Watchlist) - ' . APP_TITLE;
$activeNav = 'watchlist';

// ดึงรายการหุ้นทั้งหมดสำหรับ Trade Modal
$allStocks = getAllStocks($pdo);

require_once __DIR__ . '/../templates/header.php';
?>

  <main class="main-content">
    <div class="container">

      <?php require __DIR__ . '/../templates/flash.php'; ?>

      <!-- Header Section -->
      <div class="section-heading-row section-mb-md">
        <div>
          <h1 class="section-heading-title">
            <?= icon('bookmark', 'text-gold', 24) ?> รายการหลักทรัพย์ที่สนใจ (Stock Watchlist)
          </h1>
          <p class="chart-subtitle-meta">
            ติดตามความเคลื่อนไหวราคาหุ้นโปรด กำหนดราคาเป้าหมายซื้อ-ขาย พร้อมระบบแจ้งเตือนเชิงสถิติ Real-time
          </p>
        </div>
        <div class="d-flex gap-sm">
          <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="btn btn-outline btn-sm">
            <?= icon('filter', '', 14) ?> ค้นหาหุ้นใน Screener
          </a>
          <a href="<?= e(BASE_URL) ?>/index.php" class="btn btn-primary btn-sm">
            <?= icon('chart-bar', '', 14) ?> วิเคราะห์หุ้นรายตัว
          </a>
        </div>
      </div>

      <?php if (!$isUserLoggedIn): ?>
        <!-- State: ผู้ใช้ยังไม่ได้เข้าสู่ระบบ -->
        <div class="empty-watchlist-state">
          <div class="empty-watchlist-icon">
            <?= icon('lock', 'text-gold', 28) ?>
          </div>
          <h2 class="empty-state-title">เข้าสู่ระบบเพื่อใช้งาน Watchlist</h2>
          <p class="empty-state-desc">
            บันทึกหุ้นที่คุณสนใจ กำหนดราคาเป้าหมายซื้อ/ขาย และรับการแจ้งเตือนสถิติราคาแบบเรียลไทม์ได้ฟรี
          </p>
          <div class="d-inline-flex gap-sm">
            <a href="<?= e(BASE_URL) ?>/pages/login.php" class="btn btn-primary">
              <?= icon('user', '', 14) ?> เข้าสู่ระบบ
            </a>
            <a href="<?= e(BASE_URL) ?>/pages/register.php" class="btn btn-outline">
              สมัครสมาชิกฟรี
            </a>
          </div>
        </div>

      <?php elseif (empty($watchlist)): ?>
        <!-- State: ยังไม่มีหุ้นใน Watchlist -->
        <div class="empty-watchlist-state">
          <div class="empty-watchlist-icon">
            <?= icon('bookmark', 'text-gold', 28) ?>
          </div>
          <h2 class="empty-state-title">ยังไม่มีหุ้นใน Watchlist ของคุณ</h2>
          <p class="empty-state-desc">
            คุณสามารถกดไอคอน Bookmark <?= icon('bookmark', 'text-gold', 14) ?> บนหน้าวิเคราะห์หุ้น หรือคัดกรองหุ้นจาก Screener เพื่อเพิ่มเข้ามาในรายการติดตาม
          </p>
          <a href="<?= e(BASE_URL) ?>/pages/screener.php" class="btn btn-primary">
            <?= icon('filter', '', 14) ?> เริ่มคัดกรองหุ้นเข้า Watchlist
          </a>
        </div>

      <?php else: ?>

        <!-- KPI Summary Cards Bar -->
        <div class="stats-bar stats-bar-4 section-mb-md">
          <div class="stat-card">
            <span class="stat-label">หุ้นใน Watchlist</span>
            <span class="stat-value text-gold"><?= e((string)$stats['total']) ?> <span class="text-xs text-muted font-normal">บริษัท</span></span>
            <span class="stat-desc">ที่กำลังติดตามความเคลื่อนไหว</span>
          </div>

          <div class="stat-card">
            <span class="stat-label">แตะราคาเป้าหมายซื้อ</span>
            <span class="stat-value text-gain"><?= e((string)$stats['hit_buy']) ?> <span class="text-xs text-muted font-normal">ตัว</span></span>
            <span class="stat-desc">ราคาลงถึงหรือต่ำกว่าเป้า</span>
          </div>

          <div class="stat-card">
            <span class="stat-label">ใกล้เข้าเขตซื้อ (≤ 3%)</span>
            <span class="stat-value text-gold"><?= e((string)$stats['near_buy']) ?> <span class="text-xs text-muted font-normal">ตัว</span></span>
            <span class="stat-desc">อยู่ในระยะน่าจับตามอง</span>
          </div>

          <div class="stat-card">
            <span class="stat-label">หุ้นคุณภาพสูง (Score 80+)</span>
            <span class="stat-value text-gold"><?= e((string)$stats['high_score']) ?> <span class="text-xs text-muted font-normal">ตัว</span></span>
            <span class="stat-desc">ผ่านเกณฑ์คะแนน AI รอบด้าน</span>
          </div>
        </div>

        <!-- Watchlist Grid -->
        <div class="watchlist-grid" id="watchlistContainer">
          <?php foreach ($watchlist as $item): 
            $currentPrice = (float)$item['current_price'];
            $pctChange    = (float)$item['percent_change'];
            $score        = (int)($item['total_score'] ?? 0);
          ?>
            <div class="watchlist-card" id="watchlist-card-<?= e((string)$item['stock_id']) ?>">
              
              <div>
                <!-- Card Header -->
                <div class="watchlist-card-head">
                  <div class="watchlist-stock-info">
                    <div class="d-flex align-center gap-sm">
                      <a href="<?= e(BASE_URL) ?>/index.php?symbol=<?= e($item['symbol']) ?>" class="watchlist-symbol-link">
                        <?= e($item['symbol']) ?>
                      </a>
                      <span class="badge badge-gold badge-xs"><?= e($item['market']) ?></span>
                    </div>
                    <span class="watchlist-company-name"><?= e($item['name_th'] ?: $item['name_en']) ?></span>
                  </div>

                  <div class="watchlist-price-box">
                    <span class="watchlist-price-main"><?= formatMoney($currentPrice) ?></span>
                    <span class="badge <?= getPnlBadgeClass($pctChange) ?> badge-sm">
                      <?= formatPercent($pctChange) ?>
                    </span>
                  </div>
                </div>

                <!-- Metrics Bar -->
                <div class="watchlist-metrics-bar mt-md">
                  <div class="watchlist-metric-item">
                    <span class="watchlist-metric-label">P/E Ratio</span>
                    <span class="watchlist-metric-val"><?= $item['pe_ratio'] ? formatNumber((float)$item['pe_ratio'], 2) . 'x' : 'N/A' ?></span>
                  </div>
                  <div class="watchlist-metric-item">
                    <span class="watchlist-metric-label">ROE</span>
                    <span class="watchlist-metric-val"><?= $item['roe'] ? formatPercent((float)$item['roe']) : 'N/A' ?></span>
                  </div>
                  <div class="watchlist-metric-item">
                    <span class="watchlist-metric-label">AI Score</span>
                    <span class="watchlist-metric-val <?= getScoreColorClass($score) ?>"><?= $score > 0 ? $score . '/100' : 'N/A' ?></span>
                  </div>
                </div>

                <!-- Price Targets Section -->
                <div class="watchlist-targets-container mt-md">
                  
                  <!-- Target Buy Row -->
                  <div class="watchlist-target-row target-buy-row">
                    <div class="watchlist-target-info">
                      <?= icon('crosshair', 'text-gain', 14) ?>
                      <span class="watchlist-target-label">เป้าซื้อ:</span>
                      <span class="watchlist-target-val">
                        <?= $item['target_buy_price'] ? formatMoney((float)$item['target_buy_price']) : '<span class="text-muted font-normal">ไม่ได้ตั้ง</span>' ?>
                      </span>
                    </div>
                    <div>
                      <?php if ($item['buy_status'] === 'hit'): ?>
                        <span class="badge-target-hit">แตะเป้าซื้อแล้ว</span>
                      <?php elseif ($item['buy_status'] === 'near'): ?>
                        <span class="badge-target-near">ใกล้เป้าซื้อ (<?= sprintf('+%.1f%%', $item['buy_diff_pct']) ?>)</span>
                      <?php elseif ($item['target_buy_price']): ?>
                        <span class="text-xs text-muted font-mono">
                          <?= sprintf('%+.1f%%', $item['buy_diff_pct']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Target Sell Row -->
                  <div class="watchlist-target-row target-sell-row">
                    <div class="watchlist-target-info">
                      <?= icon('trending-up', 'text-loss', 14) ?>
                      <span class="watchlist-target-label">เป้าขาย:</span>
                      <span class="watchlist-target-val">
                        <?= $item['target_sell_price'] ? formatMoney((float)$item['target_sell_price']) : '<span class="text-muted font-normal">ไม่ได้ตั้ง</span>' ?>
                      </span>
                    </div>
                    <div>
                      <?php if ($item['sell_status'] === 'hit'): ?>
                        <span class="badge-target-sell-hit">แตะเป้าขายแล้ว</span>
                      <?php elseif ($item['sell_status'] === 'near'): ?>
                        <span class="badge-target-near">ใกล้เป้าขาย (<?= sprintf('%.1f%%', $item['sell_diff_pct']) ?>)</span>
                      <?php elseif ($item['target_sell_price']): ?>
                        <span class="text-xs text-muted font-mono">
                          <?= sprintf('%+.1f%%', $item['sell_diff_pct']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>

                </div>

                <!-- Personal Notes (if any) -->
                <?php if (!empty($item['notes'])): ?>
                  <div class="watchlist-notes-box mt-sm">
                    "<?= e($item['notes']) ?>"
                  </div>
                <?php endif; ?>

              </div>

              <!-- Card Actions -->
              <div class="watchlist-card-actions">
                <div class="d-flex gap-xs">
                  <button type="button" class="btn btn-outline btn-sm" 
                          onclick="openTargetEditModal(<?= e((string)$item['stock_id']) ?>, '<?= e($item['symbol']) ?>', '<?= e((string)($item['target_buy_price'] ?? '')) ?>', '<?= e((string)($item['target_sell_price'] ?? '')) ?>', '<?= e(addslashes($item['notes'] ?? '')) ?>')"
                          title="แก้ไขราคาเป้าหมายและโน้ต">
                    <?= icon('edit', '', 13) ?> ตั้งเป้าหมาย
                  </button>
                  <button type="button" class="btn btn-outline btn-sm text-loss" 
                          onclick="removeWatchlistItem(<?= e((string)$item['stock_id']) ?>, '<?= e($item['symbol']) ?>')"
                          title="ลบออกจาก Watchlist">
                    <?= icon('trash', 'text-loss', 13) ?>
                  </button>
                </div>

                <button type="button" class="btn btn-buy btn-sm" 
                        onclick="openTradeModal('BUY', '<?= e($item['symbol']) ?>', <?= $currentPrice ?>, <?= e((string)$item['stock_id']) ?>)">
                  <?= icon('cart', '', 13) ?> เทรด
                </button>
              </div>

            </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </div>
  </main>

  <!-- ==============================================================================
       Target Price & Notes Edit Modal
       ============================================================================== -->
  <div class="modal-backdrop" id="targetEditModal" role="dialog" aria-modal="true" aria-labelledby="targetModalTitle">
    <div class="modal-content">
      
      <div class="modal-header">
        <div class="modal-title-flex">
          <?= icon('crosshair', 'text-gold', 18) ?>
          <span class="modal-title" id="targetModalTitle">ตั้งราคาเป้าหมาย & โน้ต</span>
          <span class="badge badge-gold" id="targetModalStockSymbol">STOCK</span>
        </div>
        <button type="button" class="modal-close-btn" onclick="closeTargetEditModal()" aria-label="Close Modal">
          <?= icon('close', '', 18) ?>
        </button>
      </div>

      <form id="targetEditForm" onsubmit="handleTargetFormSubmit(event)">
        <input type="hidden" id="targetStockId" name="stock_id" value="">

        <div class="modal-body">
          
          <div class="form-group">
            <label class="form-label" for="targetBuyInput">ราคาเป้าหมายที่ต้องการซื้อ (Target Buy Price - บาท)</label>
            <div class="form-input-wrapper">
              <input type="number" step="0.25" min="0" class="form-input" id="targetBuyInput" name="target_buy" placeholder="เช่น 140.00">
            </div>
            <span class="text-xs text-muted">ระบบจะแจ้งเตือนเมื่อราคาตลาดลงมาแตะหรือใกล้ราคาที่ตั้งไว้</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="targetSellInput">ราคาเป้าหมายที่ต้องการขาย (Target Take-Profit - บาท)</label>
            <div class="form-input-wrapper">
              <input type="number" step="0.25" min="0" class="form-input" id="targetSellInput" name="target_sell" placeholder="เช่น 165.00">
            </div>
            <span class="form-hint-xs">ระบบจะแจ้งเตือนเมื่อราคาตลาดขึ้นไปถึงเป้าหมายทำกำไร</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="targetNotesInput">บันทึกช่วยจำส่วนตัว (Personal Investment Notes)</label>
            <textarea class="form-input textarea-resizable" id="targetNotesInput" name="notes" rows="3" placeholder="เช่น รอรับเมื่อย่อตัวแถวแนวรับ EMA50, พื้นฐานงบ Q3 โตต่อเนื่อง..."></textarea>
          </div>

        </div>

        <div class="modal-footer modal-actions-row">
          <button type="button" class="btn btn-outline modal-btn-cancel" onclick="closeTargetEditModal()">
            ยกเลิก
          </button>
          <button type="submit" class="btn btn-primary modal-btn-submit" id="targetSubmitBtn">
            <?= icon('check', '', 14) ?> บันทึกเป้าหมาย
          </button>
        </div>
      </form>

    </div>
  </div>

  <?php require __DIR__ . '/../templates/trade_modal.php'; ?>

  <script>
    // เปิด Modal แก้ไขราคาเป้าหมาย
    function openTargetEditModal(stockId, symbol, buyPrice, sellPrice, notes) {
      document.getElementById('targetStockId').value = stockId;
      document.getElementById('targetModalStockSymbol').textContent = symbol;
      document.getElementById('targetBuyInput').value = buyPrice || '';
      document.getElementById('targetSellInput').value = sellPrice || '';
      document.getElementById('targetNotesInput').value = notes || '';
      
      var modal = document.getElementById('targetEditModal');
      if (modal) modal.classList.add('open');
    }

    // ปิด Modal แก้ไขราคาเป้าหมาย
    function closeTargetEditModal() {
      var modal = document.getElementById('targetEditModal');
      if (modal) modal.classList.remove('open');
    }

    // ส่งข้อมูลแก้ไขราคาเป้าหมายผ่าน AJAX
    async function handleTargetFormSubmit(event) {
      event.preventDefault();
      var btn = document.getElementById('targetSubmitBtn');
      if (btn) btn.disabled = true;

      var stockId = document.getElementById('targetStockId').value;
      var targetBuy = document.getElementById('targetBuyInput').value;
      var targetSell = document.getElementById('targetSellInput').value;
      var notes = document.getElementById('targetNotesInput').value;

      try {
        var response = await fetch(window.BASE_URL + '/api/watchlist_toggle.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'update_target',
            stock_id: parseInt(stockId, 10),
            target_buy: targetBuy !== '' ? parseFloat(targetBuy) : null,
            target_sell: targetSell !== '' ? parseFloat(targetSell) : null,
            notes: notes
          })
        });

        var result = await response.json();
        if (result.success) {
          window.location.reload();
        } else {
          alert(result.message || 'เกิดข้อผิดพลาดในการบันทึก');
          if (btn) btn.disabled = false;
        }
      } catch (err) {
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        if (btn) btn.disabled = false;
      }
    }

    // ลบหุ้นออกจาก Watchlist
    async function removeWatchlistItem(stockId, symbol) {
      if (!confirm('คุณต้องการนำหุ้น ' + symbol + ' ออกจาก Watchlist ใช่หรือไม่?')) {
        return;
      }

      try {
        var response = await fetch(window.BASE_URL + '/api/watchlist_toggle.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'remove',
            stock_id: parseInt(stockId, 10)
          })
        });

        var result = await response.json();
        if (result.success) {
          var card = document.getElementById('watchlist-card-' + stockId);
          if (card) {
            card.remove();
          }
          var remainingCards = document.querySelectorAll('.watchlist-card');
          if (remainingCards.length === 0) {
            window.location.reload();
          }
        } else {
          alert(result.message || 'ลบไม่สำเร็จ');
        }
      } catch (err) {
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
      }
    }

    // ปิด Modal เมื่อคลิกนอกหน้าต่าง
    window.addEventListener('click', function(e) {
      var modal = document.getElementById('targetEditModal');
      if (e.target === modal) {
        closeTargetEditModal();
      }
    });
  </script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
