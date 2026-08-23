<?php
declare(strict_types=1);

/**
 * templates/trade_modal.php
 * Reusable Trade Modal Component for Paper Trading (Simulated Buy/Sell)
 * SmartPort AI - 100% Local Assets
 */

$modalStock = $modalStock ?? ($stock ?? null);
$modalAllStocks = $modalAllStocks ?? ($allStocks ?? []);
$initialPrice = 0.0;

if ($modalStock) {
    $initialPrice = (float)($modalStock['current_price'] ?? 0);
} elseif (!empty($modalAllStocks)) {
    $initialPrice = (float)($modalAllStocks[0]['current_price'] ?? 0);
}
?>
  <!-- ==============================================================================
       Reusable Trade Modal Dialog
       ============================================================================== -->
  <div id="tradeModal" class="modal-backdrop">
    <div class="modal-content card">
      
      <div class="card-header">
        <h3 class="card-title modal-title-flex">
          <?= icon('cart', 'text-gold', 20) ?>
          <span id="modal_trade_header_title">
            <?= $modalStock ? 'ส่งคำสั่งจำลอง: ' . e($modalStock['symbol']) : 'ส่งคำสั่งซื้อ-ขายจำลอง (Paper Trade)' ?>
          </span>
        </h3>
        <button type="button" class="close-trade-modal-btn modal-close-btn" aria-label="Close">
          <?= icon('close', '', 18) ?>
        </button>
      </div>

      <div class="card-body">
        <form action="<?= e(BASE_URL) ?>/pages/portfolio.php" method="POST">
          <input type="hidden" name="action" value="trade">
          <input type="hidden" id="trade_type" name="trade_type" value="BUY">

          <?php if (!empty($modalAllStocks)): ?>
            <!-- Stock Selection Dropdown -->
            <div class="form-group">
              <label for="trade_stock_id" class="form-label">เลือกหลักทรัพย์ (Stock Universe)</label>
              <div class="form-input-wrapper">
                <select id="trade_stock_id" name="stock_id" class="form-input" required>
                  <?php foreach ($modalAllStocks as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" data-price="<?= (float)$s['current_price'] ?>" <?= ($modalStock && (int)$modalStock['id'] === (int)$s['id']) ? 'selected' : '' ?>>
                      <?= e($s['symbol']) ?> - <?= e($s['name_th'] ?: $s['name_en']) ?> (฿<?= number_format((float)$s['current_price'], 2) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          <?php elseif ($modalStock): ?>
            <!-- Fixed Single Stock -->
            <input type="hidden" id="trade_stock_id" name="stock_id" value="<?= (int)$modalStock['id'] ?>">
          <?php endif; ?>

          <!-- Live Market Price Display Box -->
          <div class="trade-price-row">
            <span class="trade-price-label">ราคาตลาดล่าสุด:</span>
            <span id="modal_stock_price" data-price="<?= $initialPrice ?>" class="trade-price-val">
              ฿<?= number_format($initialPrice, 2) ?>
            </span>
          </div>

          <!-- Quantity Stepper Input -->
          <div class="form-group">
            <label for="trade_quantity" class="form-label">จำนวนหุ้นที่ต้องการซื้อ-ขาย (หุ้น)</label>
            <div class="form-input-wrapper">
              <input type="number" id="trade_quantity" name="quantity" class="form-input" placeholder="เช่น 100, 1000" min="1" step="1" value="100" required>
            </div>
          </div>

          <!-- Net Estimated Trade Total -->
          <div class="trade-total-row">
            <span class="trade-total-label">มูลค่ารวมสุทธิ (+ค่าคอมมิชชัน 0.157%):</span>
            <span id="modal_trade_total" class="trade-total-val">
              ฿0.00
            </span>
          </div>

          <!-- Modal Action Buttons -->
          <div class="modal-actions-row">
            <button type="button" class="btn btn-outline close-trade-modal-btn modal-btn-cancel">
              ยกเลิก
            </button>
            <button type="submit" id="modal_submit_btn" class="btn btn-buy modal-btn-submit">
              ยืนยันคำสั่งซื้อ (BUY)
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
