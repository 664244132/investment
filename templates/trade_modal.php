<?php
declare(strict_types=1);

/**
 * templates/trade_modal.php
 * Reusable Trade Modal Component for Paper Trading (Simulated Buy/Sell)
 * SmartPort AI - 100% Local Assets
 */

$modalPdo = $pdo ?? getDbConnection();
$modalStock = $modalStock ?? ($stock ?? null);
$modalAllStocks = $modalAllStocks ?? ($allStocks ?? []);
if (empty($modalAllStocks)) {
    $modalAllStocks = getAllStocks($modalPdo);
}
$initialPrice = 0.0;

if ($modalStock) {
    $initialPrice = (float)($modalStock['current_price'] ?? 0);
} elseif (!empty($modalAllStocks)) {
    $initialPrice = (float)($modalAllStocks[0]['current_price'] ?? 0);
}

// ตรวจสอบและดึงข้อมูลเงินสดและหุ้นที่ถือครอง หากยังไม่ได้ส่งเข้ามา
if (!isset($userCashBalance) || !isset($userHoldingsMap)) {
    if (isLoggedIn()) {
        $modalPdo = $pdo ?? getDbConnection();
        $modalUser = getCurrentUser();
        if ($modalUser) {
            $modalPortfolio = getUserPortfolio($modalPdo, (int)$modalUser['id']);
            if ($modalPortfolio) {
                $userCashBalance = (float)$modalPortfolio['cash_balance'];
                $userTotalNetAsset = (float)$modalPortfolio['total_portfolio_value'];
                $modalHoldings = getPortfolioHoldings($modalPdo, (int)$modalPortfolio['id']);
                $userHoldingsMap = [];
                foreach ($modalHoldings as $mh) {
                    $userHoldingsMap[(int)$mh['stock_id']] = (int)$mh['quantity'];
                }
            }
        }
    }
}
$userCashBalance = $userCashBalance ?? 0.0;
$userTotalNetAsset = $userTotalNetAsset ?? 0.0;
$userHoldingsMap = $userHoldingsMap ?? [];

$initialStockId = $modalStock ? (int)$modalStock['id'] : (!empty($modalAllStocks) ? (int)$modalAllStocks[0]['id'] : 0);
$initialHeldQty = (int)($userHoldingsMap[$initialStockId] ?? 0);
$initialStockObj = $modalStock ?: (!empty($modalAllStocks) ? $modalAllStocks[0] : null);
$isInitialThai = $initialStockObj ? isThaiStock($initialStockObj['market'], $initialStockObj['symbol']) : true;
$initialCurrSym = $isInitialThai ? '฿' : '$';
$initialFxRate = $isInitialThai ? 1.0 : DEFAULT_USD_THB_RATE;
$initialPriceInThb = $initialPrice * $initialFxRate;
?>
  <!-- ==============================================================================
       Reusable Trade Modal Dialog
       ============================================================================== -->
  <div id="tradeModal" class="modal-backdrop" data-cash-balance="<?= $userCashBalance ?>" data-total-net-asset="<?= $userTotalNetAsset ?>" data-default-fx-rate="<?= DEFAULT_USD_THB_RATE ?>">
    <div class="modal-content">
      
      <div class="modal-header">
        <h3 class="modal-title modal-title-flex">
          <?= icon('cart', 'text-gold', 20) ?>
          <span id="modal_trade_header_title">
            <?= $modalStock ? 'ส่งคำสั่งจำลอง: ' . e($modalStock['symbol']) : 'ส่งคำสั่งซื้อ-ขายจำลอง (Paper Trade)' ?>
          </span>
        </h3>
        <button type="button" class="close-trade-modal-btn modal-close-btn" aria-label="Close">
          <?= icon('close', '', 18) ?>
        </button>
      </div>

      <div class="modal-body">
        <!-- Balance & Holding Stats Bar -->
        <div class="trade-modal-stats-bar">
          <div class="trade-modal-stat-item">
            <span class="trade-modal-stat-label">เงินสดคงเหลือ (Available Cash):</span>
            <span id="modal_cash_display" class="trade-modal-stat-val text-gold" data-cash="<?= $userCashBalance ?>">฿<?= number_format($userCashBalance, 2) ?></span>
          </div>
          <div class="trade-modal-stat-item">
            <span class="trade-modal-stat-label">หุ้นที่ถือครอง (Held Shares):</span>
            <span id="modal_held_qty_display" class="trade-modal-stat-val" data-held="<?= $initialHeldQty ?>"><?= number_format($initialHeldQty) ?> หุ้น</span>
          </div>
        </div>

        <!-- Real-time Alert Box -->
        <div id="modal_trade_alert" class="trade-alert-box trade-alert-danger" style="display: none;" role="alert">
          <div class="d-flex align-center gap-xs">
            <?= icon('alert-circle', 'text-loss', 16) ?>
            <span id="modal_trade_alert_msg">ยอดซื้อรวมค่าคอมมิชชันเกินมูลค่าเงินสด/สินทรัพย์ที่สามารถซื้อได้</span>
          </div>
        </div>

        <form action="<?= e(BASE_URL) ?>/pages/portfolio.php" method="POST" id="paperTradeForm">
          <?= getCsrfInputField() ?>
          <input type="hidden" name="action" value="trade">
          <input type="hidden" id="trade_type" name="trade_type" value="BUY">

          <?php
          $initialStockObj = $modalStock ?: (!empty($modalAllStocks) ? $modalAllStocks[0] : null);
          $initialSearchDisplay = $initialStockObj ? ($initialStockObj['symbol'] . ' - ' . ($initialStockObj['name_th'] ?: $initialStockObj['name_en'])) : '';
          $initialStockId = $initialStockObj ? (int)$initialStockObj['id'] : 0;
          $initialHeld = (int)($userHoldingsMap[$initialStockId] ?? 0);
          $initialIsThai = $initialStockObj ? isThaiStock($initialStockObj['market'], $initialStockObj['symbol']) : true;
          $initialSym = $initialIsThai ? '฿' : '$';
          $initialFx = $initialIsThai ? 1.0 : DEFAULT_USD_THB_RATE;
          $initialPriceVal = $initialStockObj ? (float)$initialStockObj['current_price'] : 0.0;
          ?>
          <!-- Stock Searchbar & Live Universe Autocomplete -->
          <div class="form-group trade-stock-search-group">
            <label for="trade_stock_search" class="form-label">เลือกหลักทรัพย์ (Stock Universe)</label>
            <div class="trade-search-input-wrapper">
              <div class="trade-search-icon">
                <?= icon('search', 'text-muted', 16) ?>
              </div>
              <input type="text" 
                     id="trade_stock_search" 
                     class="form-input trade-stock-search-input" 
                     placeholder="พิมพ์ชื่อย่อหรือชื่อหุ้นเพื่อค้นหา (เช่น PTT, NVDA, CPALL)..." 
                     value="<?= e($initialSearchDisplay) ?>" 
                     autocomplete="off"
                     aria-autocomplete="list"
                     aria-expanded="false"
                     required>
              <button type="button" 
                      id="trade_stock_search_clear" 
                      class="trade-search-clear-btn" 
                      aria-label="ล้างคำค้นหา" 
                      style="display: <?= !empty($initialSearchDisplay) ? 'flex' : 'none' ?>;">
                <?= icon('close', 'text-muted', 14) ?>
              </button>
            </div>

            <!-- Hidden input for standard Form POST / API compatibility -->
            <input type="hidden" 
                   id="trade_stock_id" 
                   name="stock_id" 
                   value="<?= $initialStockId ?>" 
                   data-symbol="<?= $initialStockObj ? e($initialStockObj['symbol']) : '' ?>"
                   data-name="<?= $initialStockObj ? e($initialStockObj['name_th'] ?: $initialStockObj['name_en']) : '' ?>"
                   data-price="<?= $initialPriceVal ?>" 
                   data-currency-symbol="<?= $initialSym ?>" 
                   data-fx-rate="<?= $initialFx ?>" 
                   data-is-thai="<?= $initialIsThai ? '1' : '0' ?>" 
                   data-held-qty="<?= $initialHeld ?>"
                   required>

            <!-- Autocomplete Dropdown List Container -->
            <div id="trade_stock_dropdown" class="trade-stock-dropdown" style="display: none;">
              <div class="trade-stock-list" id="trade_stock_list">
                <?php foreach ($modalAllStocks as $s): 
                  $sHeld    = (int)($userHoldingsMap[(int)$s['id']] ?? 0);
                  $sIsThai  = isThaiStock($s['market'], $s['symbol']);
                  $sCurrSym = $sIsThai ? '฿' : '$';
                  $sFxRate  = $sIsThai ? 1.0 : DEFAULT_USD_THB_RATE;
                  $sPrice   = (float)$s['current_price'];
                  $sPriceThb= $sPrice * $sFxRate;
                  $sName    = $s['name_th'] ?: $s['name_en'];
                  $isSelected = ($initialStockId === (int)$s['id']);
                ?>
                  <div class="trade-stock-option <?= $isSelected ? 'selected' : '' ?>" 
                       data-stock-id="<?= (int)$s['id'] ?>"
                       data-symbol="<?= e($s['symbol']) ?>"
                       data-name="<?= e($sName) ?>"
                       data-market="<?= e($s['market']) ?>"
                       data-price="<?= $sPrice ?>"
                       data-currency-symbol="<?= $sCurrSym ?>"
                       data-fx-rate="<?= $sFxRate ?>"
                       data-is-thai="<?= $sIsThai ? '1' : '0' ?>"
                       data-held-qty="<?= $sHeld ?>"
                       role="option"
                       aria-selected="<?= $isSelected ? 'true' : 'false' ?>">
                    <div class="trade-stock-option-main">
                      <div class="trade-stock-option-symbol">
                        <span class="stock-sym-badge"><?= e($s['symbol']) ?></span>
                        <span class="stock-market-tag"><?= e($s['market']) ?></span>
                        <?php if ($sHeld > 0): ?>
                          <span class="badge badge-gold text-xs"><?= number_format($sHeld) ?> หุ้น</span>
                        <?php endif; ?>
                      </div>
                      <div class="trade-stock-option-name"><?= e($sName) ?></div>
                    </div>
                    <div class="trade-stock-option-price">
                      <div class="trade-stock-price-main"><?= $sCurrSym . number_format($sPrice, 2) ?></div>
                      <?php if (!$sIsThai): ?>
                        <div class="trade-stock-price-sub">≈ ฿<?= number_format($sPriceThb, 2) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div id="trade_stock_no_results" class="trade-stock-empty" style="display: none;">
                <?= icon('alert-circle', 'text-muted', 16) ?> ไม่พบหลักทรัพย์ที่ตรงกับคำค้นหา
              </div>
            </div>
          </div>

          <!-- Live Market Price Display Box -->
          <div class="trade-price-row">
            <span class="trade-price-label">ราคาตลาดล่าสุด:</span>
            <span id="modal_stock_price" 
                  data-price="<?= $initialPrice ?>" 
                  data-currency-symbol="<?= $initialCurrSym ?>" 
                  data-fx-rate="<?= $initialFxRate ?>" 
                  data-is-thai="<?= $isInitialThai ? '1' : '0' ?>" 
                  class="trade-price-val">
              <?= $initialCurrSym . number_format($initialPrice, 2) ?>
              <?php if (!$isInitialThai): ?>
                <span class="text-xs text-muted font-normal">(≈ ฿<?= number_format($initialPriceInThb, 2) ?>)</span>
              <?php endif; ?>
            </span>
          </div>

          <!-- Quantity Stepper Input -->
          <div class="form-group">
            <div class="d-flex justify-between align-center section-mb-xs">
              <label for="trade_quantity" class="form-label mb-0">จำนวนหุ้นที่ต้องการซื้อ-ขาย (หุ้น)</label>
              <span id="modal_max_qty_hint" class="text-xs text-muted"></span>
            </div>
            <div class="form-input-wrapper">
              <input type="number" id="trade_quantity" name="quantity" class="form-input" placeholder="เช่น 100, 1000" min="1" step="1" value="100" required>
            </div>
            <!-- Quick Quantity Buttons -->
            <div class="trade-quick-qty-group">
              <button type="button" class="trade-quick-btn" data-add="100">+100</button>
              <button type="button" class="trade-quick-btn" data-add="500">+500</button>
              <button type="button" class="trade-quick-btn" data-add="1000">+1,000</button>
              <button type="button" class="trade-quick-btn trade-quick-btn-max" id="modal_quick_max_btn">MAX (สูงสุด)</button>
            </div>
          </div>

          <!-- Trading Journal: Strategy Tag & Rationale -->
          <div class="form-group">
            <label for="trade_strategy_tag" class="form-label">กลยุทธ์การลงทุน (Strategy Tag)</label>
            <div class="form-input-wrapper">
              <select id="trade_strategy_tag" name="strategy_tag" class="form-input">
                <option value="Value Investing">Value Investing (เน้นคุณค่า / พื้นฐาน)</option>
                <option value="Growth Stock">Growth Stock (หุ้นเติบโตสูง)</option>
                <option value="Dividend Play">Dividend Play (เน้นเงินปันผล)</option>
                <option value="Technical Breakout">Technical Breakout (เทคนิคอล / โมเมนตัม)</option>
                <option value="AI Recommendation">AI Recommendation (ตามสัญญาณ AI)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="trade_rationale" class="form-label">เหตุผลในการตัดสินใจเทรด (Trading Rationale)</label>
            <div class="form-input-wrapper">
              <textarea id="trade_rationale" name="rationale" class="form-input trade-rationale-textarea" rows="2" placeholder="เช่น กราฟเบรกแนวต้าน, P/E ต่ำกว่ากลุ่ม, AI Score 85+..."></textarea>
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
