/**
 * js/main.js
 * SmartPort AI - Core UI Interactions, Bespoke Luxury Dropdowns, Live Global Stock Search & Autocomplete, Password Toggles, and Trade Modal
 */

// Shared SVG Vector Icon Dictionary (DRY Architecture)
const SVG_ICONS = {
  eye: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-icon-svg"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
  eyeOff: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-icon-svg"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" y1="2" x2="22" y2="22"></line></svg>',
  chevronDown: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>',
  checkGold: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#FFCC00" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
  chevronUpSmall: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>',
  chevronDownSmall: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>'
};

function initSmartPort() {
  // 1. Mobile Drawer Toggle & Interaction
  const mobileToggle = document.getElementById('mobileMenuBtn') || document.querySelector('.mobile-toggle');
  const mobileDrawer = document.getElementById('mobileDrawer') || document.querySelector('.mobile-drawer');
  const toggleIconMenu = document.querySelector('.toggle-icon-menu');
  const toggleIconClose = document.querySelector('.toggle-icon-close');

  window.toggleMobileDrawer = function(e, forceState) {
    if (e && e.stopPropagation) e.stopPropagation();
    if (!mobileDrawer) return;
    const isOpening = (forceState !== undefined) ? forceState : !mobileDrawer.classList.contains('open');
    
    if (isOpening) {
      mobileDrawer.classList.add('open');
      if (mobileToggle) {
        mobileToggle.setAttribute('aria-expanded', 'true');
        if (toggleIconMenu) toggleIconMenu.style.display = 'none';
        if (toggleIconClose) toggleIconClose.style.display = 'inline-block';
      }
    } else {
      mobileDrawer.classList.remove('open');
      if (mobileToggle) {
        mobileToggle.setAttribute('aria-expanded', 'false');
        if (toggleIconMenu) toggleIconMenu.style.display = 'inline-block';
        if (toggleIconClose) toggleIconClose.style.display = 'none';
      }
    }
  };

  if (mobileDrawer) {
    // Close when clicking any nav link inside drawer
    mobileDrawer.querySelectorAll('.nav-link, a').forEach(link => {
      link.addEventListener('click', () => {
        window.toggleMobileDrawer(null, false);
      });
    });

    // Close when clicking outside header & drawer
    document.addEventListener('click', (e) => {
      const header = document.querySelector('.app-header');
      if (header && !header.contains(e.target) && mobileDrawer.classList.contains('open')) {
        window.toggleMobileDrawer(null, false);
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mobileDrawer.classList.contains('open')) {
        window.toggleMobileDrawer(null, false);
      }
    });
  }

  // 2. Password Visibility Toggle
  const toggleButtons = document.querySelectorAll('.input-toggle-btn');
  toggleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (!input) return;

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.innerHTML = isPassword ? SVG_ICONS.eye : SVG_ICONS.eyeOff;
    });
  });

  // 3. Password Strength Meter (บนหน้าสมัครสมาชิก)
  const passwordInput = document.getElementById('reg_password');
  const strengthSegments = document.querySelectorAll('.pw-strength-segment');
  const strengthLabel = document.getElementById('pwStrengthLabel');

  if (passwordInput && strengthSegments.length > 0) {
    passwordInput.addEventListener('input', () => {
      const val = passwordInput.value;
      let score = 0;

      if (val.length >= 6) score++;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
      if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

      strengthSegments.forEach((seg, idx) => {
        if (idx < score) {
          if (score <= 1) seg.style.backgroundColor = 'var(--color-danger)';
          else if (score <= 2) seg.style.backgroundColor = 'var(--accent-gold)';
          else seg.style.backgroundColor = 'var(--color-success)';
        } else {
          seg.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
        }
      });

      if (strengthLabel) {
        if (val.length === 0) {
          strengthLabel.textContent = 'ระดับความปลอดภัย: ขั้นต่ำ 6 ตัวอักษร';
          strengthLabel.style.color = 'var(--text-muted)';
        } else if (score <= 1) {
          strengthLabel.textContent = 'ระดับความปลอดภัย: รหัสผ่านง่ายเกินไป';
          strengthLabel.style.color = 'var(--color-danger)';
        } else if (score <= 2) {
          strengthLabel.textContent = 'ระดับความปลอดภัย: ปานกลาง';
          strengthLabel.style.color = 'var(--accent-gold)';
        } else {
          strengthLabel.textContent = 'ระดับความปลอดภัย: ยอดเยี่ยม (ปลอดภัยสูงสุด)';
          strengthLabel.style.color = 'var(--color-success)';
        }
      }
    });
  }

  // 4. Trade Modal Dialog (จำลองการซื้อ-ขายหุ้น)
  const tradeModal = document.getElementById('tradeModal');
  const openTradeBtns = document.querySelectorAll('.open-trade-modal-btn');
  const closeTradeBtns = document.querySelectorAll('.close-trade-modal-btn');
  const tradeTypeInput = document.getElementById('trade_type');
  const tradeQtyInput = document.getElementById('trade_quantity');
  const tradeStockPrice = document.getElementById('modal_stock_price');
  const tradeTotalDisplay = document.getElementById('modal_trade_total');
  const tradeSubmitBtn = document.getElementById('modal_submit_btn');
  const modalHeldQtyDisplay = document.getElementById('modal_held_qty_display');
  const modalTradeAlert = document.getElementById('modal_trade_alert');
  const modalTradeAlertMsg = document.getElementById('modal_trade_alert_msg');
  const modalMaxQtyHint = document.getElementById('modal_max_qty_hint');
  const quickQtyBtns = document.querySelectorAll('.trade-quick-btn');

  function getTradeContext() {
    const type = (tradeTypeInput ? tradeTypeInput.value : 'BUY').toUpperCase();
    const stockHiddenInput = document.getElementById('trade_stock_id');
    const selectedOption = stockHiddenInput;

    const price = parseFloat(tradeStockPrice ? tradeStockPrice.getAttribute('data-price') : (selectedOption?.dataset?.price || '0')) || 0;
    const currencySymbol = tradeStockPrice?.getAttribute('data-currency-symbol') || selectedOption?.dataset?.currencySymbol || '฿';
    const fxRate = parseFloat(tradeStockPrice?.getAttribute('data-fx-rate') || selectedOption?.dataset?.fxRate || '1') || 1;
    const isThai = (selectedOption?.dataset?.isThai === '1') || (tradeStockPrice?.getAttribute('data-is-thai') === '1') || (currencySymbol === '฿');
    const heldQty = parseInt(selectedOption?.dataset?.heldQty || '0', 10) || 0;
    const cashBalance = parseFloat(tradeModal?.dataset?.cashBalance || '0') || 0;
    const feeRate = 0.00157; // 0.157%

    let maxAllowedQty = 0;
    if (type === 'SELL') {
      maxAllowedQty = Math.max(0, heldQty);
    } else {
      const priceInThb = price * fxRate;
      const costPerShareThb = priceInThb * (1 + feeRate);
      maxAllowedQty = (costPerShareThb > 0 && cashBalance > 0) ? Math.floor(cashBalance / costPerShareThb) : 0;
    }

    return { type, price, currencySymbol, fxRate, isThai, heldQty, cashBalance, feeRate, maxAllowedQty, selectedOption };
  }

  function calculateTradeTotal() {
    if (!tradeQtyInput || !tradeStockPrice || !tradeTotalDisplay) return;

    const ctx = getTradeContext();
    let qty = parseInt(tradeQtyInput.value, 10);
    if (isNaN(qty) || qty < 1) {
      qty = 1;
    }

    // Requirement 2: หากผู้ใช้กรอกหรือกดเกินจำนวนหุ้นสูงสุดที่มี ให้ระบบตั้งค่าเป็นจำนวนหุ้นสูงสุดที่มีอยู่
    if (ctx.maxAllowedQty > 0 && qty > ctx.maxAllowedQty) {
      qty = ctx.maxAllowedQty;
      tradeQtyInput.value = qty;
    }

    // กำหนด min/max บน input
    tradeQtyInput.min = '1';
    if (ctx.maxAllowedQty > 0) {
      tradeQtyInput.max = ctx.maxAllowedQty.toString();
    } else {
      tradeQtyInput.removeAttribute('max');
    }

    // อัปเดตข้อมูลการถือครอง
    if (modalHeldQtyDisplay) {
      modalHeldQtyDisplay.textContent = ctx.heldQty.toLocaleString() + ' หุ้น';
      modalHeldQtyDisplay.setAttribute('data-held', ctx.heldQty.toString());
    }

    // อัปเดตคำใบ้ Max
    if (modalMaxQtyHint) {
      if (ctx.type === 'SELL') {
        modalMaxQtyHint.textContent = `(มีอยู่สูงสุด ${ctx.heldQty.toLocaleString()} หุ้น)`;
      } else if (ctx.maxAllowedQty > 0) {
        modalMaxQtyHint.textContent = `(ซื้อได้สูงสุด ${ctx.maxAllowedQty.toLocaleString()} หุ้น)`;
      } else {
        modalMaxQtyHint.textContent = '(วงเงินไม่เพียงพอ)';
      }
    }

    const priceInThb = ctx.price * ctx.fxRate;
    const subtotalThb = qty * priceInThb;
    const feeThb = subtotalThb * ctx.feeRate;
    const totalThb = subtotalThb + feeThb;

    if (!ctx.isThai) {
      const subtotalOrig = qty * ctx.price;
      const feeOrig = subtotalOrig * ctx.feeRate;
      const totalOrig = subtotalOrig + feeOrig;
      tradeTotalDisplay.innerHTML = `${ctx.currencySymbol}${totalOrig.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })} <span class="text-xs text-muted font-normal">(≈ ฿${totalThb.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })})</span>`;
    } else {
      tradeTotalDisplay.textContent = '฿' + totalThb.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    // Requirement 1 & Alert Validation: แจ้งเตือนเมื่อซื้อเกินเงินสด/สินทรัพย์ที่ซื้อได้ หรือขายเกินที่มี
    let hasError = false;
    let alertMsg = '';

    if (ctx.type === 'BUY') {
      if (totalThb > ctx.cashBalance) {
        hasError = true;
        const shortage = totalThb - ctx.cashBalance;
        alertMsg = `ยอดซื้อรวมค่าคอมมิชชัน ฿${totalThb.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} เกินวงเงินที่ซื้อได้ (มีเงินสดคงเหลือ ฿${ctx.cashBalance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} · ขาดอีก ฿${shortage.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
      }
    } else if (ctx.type === 'SELL') {
      if (ctx.heldQty <= 0) {
        hasError = true;
        alertMsg = 'คุณยังไม่มีหุ้นนี้ในพอร์ต ไม่สามารถส่งคำสั่งขายได้';
      } else if (qty > ctx.heldQty) {
        hasError = true;
        alertMsg = `จำนวนหุ้นที่ต้องการขาย (${qty.toLocaleString()} หุ้น) เกินกว่าจำนวนที่ถือครองอยู่ (${ctx.heldQty.toLocaleString()} หุ้น)`;
      }
    }

    if (modalTradeAlert && modalTradeAlertMsg) {
      if (hasError) {
        modalTradeAlertMsg.textContent = alertMsg;
        modalTradeAlert.style.display = 'flex';
      } else {
        modalTradeAlert.style.display = 'none';
      }
    }

    if (tradeSubmitBtn) {
      tradeSubmitBtn.disabled = hasError;
    }
  }

  if (tradeQtyInput) {
    tradeQtyInput.addEventListener('input', () => {
      calculateTradeTotal();
    });

    // เมื่อผู้ใช้เปลี่ยนโฟกัสออก ตรวจสอบให้แน่ใจว่าต่ำสุดคือ 1
    tradeQtyInput.addEventListener('blur', () => {
      let val = parseInt(tradeQtyInput.value, 10);
      const ctx = getTradeContext();
      if (isNaN(val) || val < 1) {
        tradeQtyInput.value = 1;
      } else if (ctx.maxAllowedQty > 0 && val > ctx.maxAllowedQty) {
        tradeQtyInput.value = ctx.maxAllowedQty;
      }
      calculateTradeTotal();
    });
  }

  // Quick Quantity Buttons Handlers (+100, +500, +1000, MAX)
  quickQtyBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const ctx = getTradeContext();
      let currentVal = parseInt(tradeQtyInput.value, 10) || 0;

      if (btn.classList.contains('trade-quick-btn-max') || btn.id === 'modal_quick_max_btn') {
        // ปุ่ม MAX: ตั้งค่าเป็นจำนวนสูงสุดที่มีอยู่
        if (ctx.maxAllowedQty > 0) {
          tradeQtyInput.value = ctx.maxAllowedQty;
        } else {
          tradeQtyInput.value = 1;
        }
      } else {
        const addAmount = parseInt(btn.getAttribute('data-add'), 10) || 0;
        let nextVal = currentVal + addAmount;
        if (ctx.maxAllowedQty > 0 && nextVal > ctx.maxAllowedQty) {
          nextVal = ctx.maxAllowedQty;
        }
        tradeQtyInput.value = Math.max(1, nextVal);
      }

      tradeQtyInput.dispatchEvent(new Event('input', { bubbles: true }));
      tradeQtyInput.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  // Setup Trade Stock Searchbar Autocomplete
  const tradeStockSearchInput = document.getElementById('trade_stock_search');
  const tradeStockDropdown = document.getElementById('trade_stock_dropdown');
  const tradeStockOptions = document.querySelectorAll('.trade-stock-option');
  const tradeStockClearBtn = document.getElementById('trade_stock_search_clear');
  const tradeStockNoResults = document.getElementById('trade_stock_no_results');
  let highlightedOptionIndex = -1;

  function selectTradeStock(option) {
    if (!option) return;
    const stockId = option.dataset.stockId;
    const symbol = option.dataset.symbol;
    const name = option.dataset.name;
    const price = parseFloat(option.dataset.price) || 0;
    const currSym = option.dataset.currencySymbol || '฿';
    const fxRate = parseFloat(option.dataset.fxRate || '1') || 1;
    const isThai = (option.dataset.isThai === '1') || (currSym === '฿');
    const heldQty = parseInt(option.dataset.heldQty || '0', 10) || 0;

    // Update hidden input
    const hiddenInput = document.getElementById('trade_stock_id');
    if (hiddenInput) {
      hiddenInput.value = stockId;
      hiddenInput.dataset.symbol = symbol;
      hiddenInput.dataset.name = name;
      hiddenInput.dataset.price = price.toString();
      hiddenInput.dataset.currencySymbol = currSym;
      hiddenInput.dataset.fxRate = fxRate.toString();
      hiddenInput.dataset.isThai = isThai ? '1' : '0';
      hiddenInput.dataset.heldQty = heldQty.toString();
    }

    // Update Search Input text
    if (tradeStockSearchInput) {
      tradeStockSearchInput.value = `${symbol} - ${name}`;
      if (tradeStockClearBtn) tradeStockClearBtn.style.display = 'flex';
    }

    // Update Selected Class in Dropdown
    tradeStockOptions.forEach(opt => {
      const isCurrent = opt.dataset.stockId === stockId;
      opt.classList.toggle('selected', isCurrent);
      opt.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
    });

    // Update Modal Stock Price Display
    if (tradeStockPrice) {
      tradeStockPrice.setAttribute('data-price', price.toString());
      tradeStockPrice.setAttribute('data-currency-symbol', currSym);
      tradeStockPrice.setAttribute('data-fx-rate', fxRate.toString());
      tradeStockPrice.setAttribute('data-is-thai', isThai ? '1' : '0');

      if (!isThai) {
        tradeStockPrice.innerHTML = `${currSym}${price.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        })} <span class="text-xs text-muted font-normal">(≈ ฿${(price * fxRate).toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        })})</span>`;
      } else {
        tradeStockPrice.textContent = '฿' + price.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      }
    }

    // Update Held Qty
    if (modalHeldQtyDisplay) {
      modalHeldQtyDisplay.textContent = heldQty.toLocaleString() + ' หุ้น';
      modalHeldQtyDisplay.setAttribute('data-held', heldQty.toString());
    }

    // Close dropdown & calculate
    closeTradeStockDropdown();
    calculateTradeTotal();
  }

  function openTradeStockDropdown() {
    if (tradeStockDropdown) {
      tradeStockDropdown.style.display = 'block';
      if (tradeStockSearchInput) tradeStockSearchInput.setAttribute('aria-expanded', 'true');
    }
  }

  function closeTradeStockDropdown() {
    if (tradeStockDropdown) {
      tradeStockDropdown.style.display = 'none';
      if (tradeStockSearchInput) tradeStockSearchInput.setAttribute('aria-expanded', 'false');
    }
    highlightedOptionIndex = -1;
    tradeStockOptions.forEach(opt => opt.classList.remove('highlighted'));
  }

  function filterTradeStocks(query) {
    const term = (query || '').trim().toLowerCase();
    let visibleCount = 0;

    tradeStockOptions.forEach(opt => {
      const sym = (opt.dataset.symbol || '').toLowerCase();
      const name = (opt.dataset.name || '').toLowerCase();
      const market = (opt.dataset.market || '').toLowerCase();
      const isMatch = term === '' || sym.includes(term) || name.includes(term) || market.includes(term);

      opt.style.display = isMatch ? 'flex' : 'none';
      if (isMatch) visibleCount++;
    });

    if (tradeStockNoResults) {
      tradeStockNoResults.style.display = visibleCount === 0 ? 'flex' : 'none';
    }

    if (tradeStockClearBtn) {
      tradeStockClearBtn.style.display = query.length > 0 ? 'flex' : 'none';
    }
    openTradeStockDropdown();
  }

  if (tradeStockSearchInput) {
    tradeStockSearchInput.addEventListener('focus', () => {
      filterTradeStocks(tradeStockSearchInput.value);
    });

    tradeStockSearchInput.addEventListener('input', () => {
      filterTradeStocks(tradeStockSearchInput.value);
    });

    tradeStockSearchInput.addEventListener('keydown', (e) => {
      const visibleOptions = Array.from(tradeStockOptions).filter(opt => opt.style.display !== 'none');
      if (visibleOptions.length === 0) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlightedOptionIndex = (highlightedOptionIndex + 1) % visibleOptions.length;
        visibleOptions.forEach((opt, idx) => {
          opt.classList.toggle('highlighted', idx === highlightedOptionIndex);
          if (idx === highlightedOptionIndex) opt.scrollIntoView({ block: 'nearest' });
        });
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlightedOptionIndex = (highlightedOptionIndex - 1 + visibleOptions.length) % visibleOptions.length;
        visibleOptions.forEach((opt, idx) => {
          opt.classList.toggle('highlighted', idx === highlightedOptionIndex);
          if (idx === highlightedOptionIndex) opt.scrollIntoView({ block: 'nearest' });
        });
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (highlightedOptionIndex >= 0 && highlightedOptionIndex < visibleOptions.length) {
          selectTradeStock(visibleOptions[highlightedOptionIndex]);
        } else if (visibleOptions.length > 0) {
          selectTradeStock(visibleOptions[0]);
        }
      } else if (e.key === 'Escape') {
        closeTradeStockDropdown();
      }
    });
  }

  tradeStockOptions.forEach(opt => {
    opt.addEventListener('click', () => {
      selectTradeStock(opt);
    });
  });

  if (tradeStockClearBtn) {
    tradeStockClearBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (tradeStockSearchInput) {
        tradeStockSearchInput.value = '';
        tradeStockSearchInput.focus();
        filterTradeStocks('');
      }
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (tradeStockDropdown && tradeStockSearchInput) {
      if (!tradeStockDropdown.contains(e.target) && !tradeStockSearchInput.contains(e.target) && !tradeStockClearBtn?.contains(e.target)) {
        closeTradeStockDropdown();
      }
    }
  });

  // ฟังก์ชันสากลสำหรับเปิด Trade Modal (เรียกใช้ได้ทั้งผ่าน JS และ Inline onclick)
  window.openTradeModal = function(type = 'BUY', symbol = '', price = null, stockId = null) {
    if (tradeTypeInput) tradeTypeInput.value = type;

    let targetOption = null;
    if (stockId) {
      targetOption = document.querySelector(`.trade-stock-option[data-stock-id="${stockId}"]`);
    } else if (symbol) {
      targetOption = document.querySelector(`.trade-stock-option[data-symbol="${symbol}"]`);
    }

    if (targetOption) {
      selectTradeStock(targetOption);
    }

    if (price !== null && tradeStockPrice) {
      const numPrice = parseFloat(price.toString()) || 0;
      const currSym = tradeStockPrice.getAttribute('data-currency-symbol') || '฿';
      const fxRate = parseFloat(tradeStockPrice.getAttribute('data-fx-rate') || '1') || 1;
      const isThai = tradeStockPrice.getAttribute('data-is-thai') === '1' || currSym === '฿';

      tradeStockPrice.setAttribute('data-price', numPrice.toString());
      if (!isThai) {
        tradeStockPrice.innerHTML = `${currSym}${numPrice.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        })} <span class="text-xs text-muted font-normal">(≈ ฿${(numPrice * fxRate).toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        })})</span>`;
      } else {
        tradeStockPrice.textContent = '฿' + numPrice.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      }
    }

    const modalHeaderTitle = document.getElementById('modal_trade_header_title');
    if (symbol && modalHeaderTitle) {
      modalHeaderTitle.textContent = (type === 'SELL' ? 'ส่งคำสั่งขายจำลอง: ' : 'ส่งคำสั่งซื้อจำลอง: ') + symbol;
    }

    if (tradeSubmitBtn) {
      if (type === 'SELL') {
        tradeSubmitBtn.className = 'btn btn-sell btn-full modal-btn-submit';
        tradeSubmitBtn.textContent = 'ยืนยันคำสั่งขาย (SELL)';
      } else {
        tradeSubmitBtn.className = 'btn btn-buy btn-full modal-btn-submit';
        tradeSubmitBtn.textContent = 'ยืนยันคำสั่งซื้อ (BUY)';
      }
    }

    // Default quantity
    if (tradeQtyInput) {
      const ctx = getTradeContext();
      if (type === 'SELL') {
        tradeQtyInput.value = ctx.heldQty > 0 ? Math.min(100, ctx.heldQty) : 1;
      } else {
        tradeQtyInput.value = (ctx.maxAllowedQty > 0) ? Math.min(100, ctx.maxAllowedQty) : 100;
      }
    }

    calculateTradeTotal();
    if (tradeModal) tradeModal.classList.add('open');
  };

  openTradeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-trade-type') || 'BUY';
      const stockId = btn.getAttribute('data-stock-id');
      const customPrice = btn.getAttribute('data-price');
      const symbol = btn.getAttribute('data-symbol') || '';
      window.openTradeModal(type, symbol, customPrice, stockId);
    });
  });

  closeTradeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      if (tradeModal) tradeModal.classList.remove('open');
    });
  });

  // ปิด Modal เมื่อคลิกที่พื้นหลัง Backdrop
  if (tradeModal) {
    tradeModal.addEventListener('click', (e) => {
      if (e.target === tradeModal) {
        tradeModal.classList.remove('open');
      }
    });
  }

  // 5. Live Global Stock Search & Autocomplete (Support Desktop & Mobile Inputs)
  function setupSearchAutocomplete(inputId, containerSelector) {
    const inputEl = document.getElementById(inputId);
    const containerEl = document.querySelector(containerSelector);

    if (!inputEl || !containerEl) return;
    containerEl.style.position = 'relative';

    const dropdown = document.createElement('div');
    dropdown.className = 'search-dropdown';
    dropdown.style.display = 'none';
    containerEl.appendChild(dropdown);

    let debounceTimer = null;
    let abortController = null;
    const searchCache = new Map();

    const renderResults = (results, query) => {
      const baseUrl = window.BASE_URL || '';
      if (results && results.length > 0) {
        dropdown.innerHTML = '';
        results.forEach(item => {
          const row = document.createElement('a');
          row.className = 'search-dropdown-item';
          row.href = `${baseUrl}/index.php?symbol=${encodeURIComponent(item.symbol)}`;

          const isThai = (item.market === 'SET' || item.market === 'mai');
          const currencySymbol = isThai ? '฿' : '$';
          const priceText = (item.current_price !== null && item.current_price !== undefined && item.current_price > 0)
            ? `${currencySymbol}${Number(item.current_price).toFixed(2)}`
            : (item.market || 'GLOBAL');

          const changeHtml = (item.percent_change !== null && item.percent_change !== undefined)
            ? `<div class="${item.percent_change >= 0 ? 'search-result-item-gain' : 'search-result-item-loss'}">
                ${item.percent_change >= 0 ? '+' : ''}${Number(item.percent_change).toFixed(2)}%
               </div>`
            : `<div class="text-xs text-muted">${item.market || 'GLOBAL'}</div>`;

          row.innerHTML = `
            <div>
              <div class="search-result-symbol-link">
                ${item.symbol}
                <span class="search-result-market-tag">(${item.market || 'GLOBAL'})</span>
              </div>
              <div class="search-result-item-name">${item.name}</div>
            </div>
            <div class="th-right">
              <div class="search-result-price-main">${priceText}</div>
              ${changeHtml}
            </div>
          `;

          dropdown.appendChild(row);
        });
        dropdown.classList.add('d-block');
      } else {
        dropdown.innerHTML = `
          <div class="search-result-empty-msg">
            กด Enter เพื่อค้นหาและซิงค์หุ้น "${query.toUpperCase()}" จากตลาดโลกแบบสดๆ
          </div>
        `;
        dropdown.classList.add('d-block');
      }
    };

    inputEl.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const query = inputEl.value.trim();

      if (query.length === 0) {
        if (abortController) abortController.abort();
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
      }

      // ตรวจสอบ Memory Cache ก่อน (0ms Instant Render)
      if (searchCache.has(query.toUpperCase())) {
        renderResults(searchCache.get(query.toUpperCase()), query);
        return;
      }

      debounceTimer = setTimeout(async () => {
        try {
          if (abortController) abortController.abort();
          abortController = new AbortController();

          const baseUrl = window.BASE_URL || '';
          const res = await fetch(`${baseUrl}/api/search.php?q=${encodeURIComponent(query)}`, {
            signal: abortController.signal
          });
          if (!res.ok) return;

          const data = await res.json();
          if (data.success) {
            searchCache.set(query.toUpperCase(), data.results || []);
            renderResults(data.results || [], query);
          }
        } catch (err) {
          if (err.name !== 'AbortError') {
            console.error('Search error:', err);
          }
        }
      }, 200);
    });

    document.addEventListener('click', (e) => {
      if (!containerEl.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });

    inputEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const query = inputEl.value.trim().toUpperCase();
        if (query) {
          dropdown.style.display = 'none';
          window.location.href = (window.BASE_URL || '') + '/index.php?symbol=' + encodeURIComponent(query);
        }
      }
    });
  }

  setupSearchAutocomplete('stockSearchInput', '.header-actions .search-box');
  setupSearchAutocomplete('mobileStockSearchInput', '.mobile-search-row .mobile-search-box');

  // ==============================================================================
  // 6. Luxury Custom Dropdown Initializer for all <select> elements
  // ==============================================================================
  function initCustomDropdowns() {
    const selects = document.querySelectorAll('select.form-input');
    selects.forEach(select => {
      if (select.dataset.customized === 'true') return;
      select.dataset.customized = 'true';

      // ซ่อน select ดั้งเดิม
      select.style.display = 'none';

      // สร้าง Custom Container
      const container = document.createElement('div');
      container.className = 'custom-select-container';

      // สร้าง Trigger Button
      const trigger = document.createElement('div');
      trigger.className = 'custom-select-trigger';
      trigger.tabIndex = 0;

      const selectedOption = select.options[select.selectedIndex] || select.options[0];
      const initialText = selectedOption ? selectedOption.textContent.trim() : 'เลือกรายการ...';

      trigger.innerHTML = `
        <span class="custom-select-value">${initialText}</span>
        <span class="custom-select-chevron">
          ${SVG_ICONS.chevronDown}
        </span>
      `;

      // สร้าง Options Menu
      const menu = document.createElement('div');
      menu.className = 'custom-select-menu';

      Array.from(select.options).forEach((opt, idx) => {
        const item = document.createElement('div');
        const isSelected = (idx === select.selectedIndex);
        item.className = 'custom-select-item' + (isSelected ? ' selected' : '');
        item.dataset.value = opt.value;
        item.innerHTML = `
          <span>${opt.textContent.trim()}</span>
          ${isSelected ? SVG_ICONS.checkGold : ''}
        `;

        item.addEventListener('click', (e) => {
          e.stopPropagation();
          select.selectedIndex = idx;
          select.value = opt.value;

          // อัปเดตข้อความบน Trigger
          trigger.querySelector('.custom-select-value').textContent = opt.textContent.trim();

          // อัปเดตคลาสและไอคอน Selected
          menu.querySelectorAll('.custom-select-item').forEach((el, elIdx) => {
            const spanText = el.querySelector('span').textContent;
            if (elIdx === idx) {
              el.classList.add('selected');
              el.innerHTML = `<span>${spanText}</span>${SVG_ICONS.checkGold}`;
            } else {
              el.classList.remove('selected');
              el.innerHTML = `<span>${spanText}</span>`;
            }
          });

          container.classList.remove('open');

          // ยิง Event Change ไปยัง select ดั้งเดิมเพื่อให้ฟอร์มและฟังก์ชันอื่นๆ ทำงานได้ปกติ
          select.dispatchEvent(new Event('change', { bubbles: true }));
        });

        menu.appendChild(item);
      });

      // เปิด/ปิด Menu เมื่อคลิกที่ Trigger
      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        document.querySelectorAll('.custom-select-container.open').forEach(c => {
          if (c !== container) c.classList.remove('open');
        });
        container.classList.toggle('open');
      });

      container.appendChild(trigger);
      container.appendChild(menu);

      // แทรกเข้าไปใน DOM
      select.parentNode.insertBefore(container, select);
      container.appendChild(select);
    });

    // ปิดเมื่อคลิกนอกพื้นที่
    document.addEventListener('click', () => {
      document.querySelectorAll('.custom-select-container.open').forEach(c => {
        c.classList.remove('open');
      });
    });
  }

  initCustomDropdowns();

  // ==============================================================================
  // 7. Custom Dark-Gold Number Stepper for all <input type="number">
  // ==============================================================================
  function initCustomNumberInputs() {
    const numberInputs = document.querySelectorAll('input[type="number"].form-input');
    numberInputs.forEach(input => {
      if (input.dataset.stepperInitialized === 'true') return;
      input.dataset.stepperInitialized = 'true';

      const wrapper = document.createElement('div');
      wrapper.className = 'number-stepper-wrapper';

      const controls = document.createElement('div');
      controls.className = 'stepper-controls';

      // Up button (Increment)
      const upBtn = document.createElement('button');
      upBtn.type = 'button';
      upBtn.className = 'stepper-btn stepper-btn-up';
      upBtn.tabIndex = -1;
      upBtn.setAttribute('aria-label', 'เพิ่มจำนวน');
      upBtn.innerHTML = SVG_ICONS.chevronUpSmall;

      // Down button (Decrement)
      const downBtn = document.createElement('button');
      downBtn.type = 'button';
      downBtn.className = 'stepper-btn stepper-btn-down';
      downBtn.tabIndex = -1;
      downBtn.setAttribute('aria-label', 'ลดจำนวน');
      downBtn.innerHTML = SVG_ICONS.chevronDownSmall;

      const step = parseFloat(input.step) || 1;

      function updateVal(delta) {
        const dynamicMin = (input.min !== '') ? parseFloat(input.min) : -Infinity;
        const dynamicMax = (input.max !== '') ? parseFloat(input.max) : Infinity;
        let current = parseFloat(input.value);
        if (isNaN(current)) {
          current = (delta > 0) ? (dynamicMin !== -Infinity ? dynamicMin : 0) : (dynamicMax !== Infinity ? dynamicMax : 0);
        } else {
          current += delta;
        }

        // Precision calculation
        const stepParts = step.toString().split('.');
        const precision = (stepParts.length > 1) ? stepParts[1].length : 0;
        current = parseFloat(current.toFixed(precision));

        if (current < dynamicMin) current = dynamicMin;
        if (current > dynamicMax) current = dynamicMax;

        input.value = current;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }

      upBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        updateVal(step);
      });

      downBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        updateVal(-step);
      });

      controls.appendChild(upBtn);
      controls.appendChild(downBtn);

      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);
      wrapper.appendChild(controls);
    });
  }

  initCustomNumberInputs();

  // 6. Custom Themed Confirm Dialog (Promise-based)
  window.showAppConfirm = function(options) {
    return new Promise((resolve) => {
      let config = {
        title: 'ยืนยันการทำรายการ',
        message: '',
        confirmText: 'ยืนยัน',
        cancelText: 'ยกเลิก',
        type: 'danger'
      };

      if (typeof options === 'string') {
        config.message = options;
      } else if (typeof options === 'object' && options !== null) {
        config = Object.assign({}, config, options);
      }

      const existing = document.getElementById('appConfirmModal');
      if (existing) existing.remove();

      const isDanger = config.type === 'danger';
      const iconSvg = isDanger
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF5C5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFCC00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;

      const modalBackdrop = document.createElement('div');
      modalBackdrop.id = 'appConfirmModal';
      modalBackdrop.className = 'modal-backdrop app-confirm-backdrop open';

      modalBackdrop.innerHTML = `
        <div class="modal-content app-confirm-card" role="dialog" aria-modal="true">
          <div class="app-confirm-icon-box ${isDanger ? 'icon-box-danger' : 'icon-box-warning'}">
            ${iconSvg}
          </div>
          <h3 class="app-confirm-title">${config.title}</h3>
          <p class="app-confirm-message">${config.message}</p>
          <div class="app-confirm-actions">
            <button type="button" class="btn btn-outline app-confirm-cancel">${config.cancelText}</button>
            <button type="button" class="btn ${isDanger ? 'btn-sell' : 'btn-primary'} app-confirm-ok">${config.confirmText}</button>
          </div>
        </div>
      `;

      document.body.appendChild(modalBackdrop);

      const cleanup = (result) => {
        modalBackdrop.classList.remove('open');
        setTimeout(() => {
          if (modalBackdrop.parentNode) modalBackdrop.remove();
        }, 200);
        resolve(result);
      };

      modalBackdrop.querySelector('.app-confirm-cancel').addEventListener('click', () => cleanup(false));
      modalBackdrop.querySelector('.app-confirm-ok').addEventListener('click', () => cleanup(true));
      modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) cleanup(false);
      });
    });
  };

  // 7. Custom Themed Toast Notification System
  window.showAppToast = function(message, type = 'success') {
    let toastContainer = document.getElementById('appToastContainer');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'appToastContainer';
      toastContainer.className = 'app-toast-container';
      document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `app-toast app-toast-${type}`;
    
    let iconSvg = '';
    if (type === 'success') {
      iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#03C988" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
    } else if (type === 'error' || type === 'danger') {
      iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF5C5C" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`;
    } else if (type === 'warning') {
      iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFCC00" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
    } else {
      iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00B4D8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
    }

    toast.innerHTML = `
      <div class="app-toast-icon">${iconSvg}</div>
      <div class="app-toast-text">${message}</div>
    `;

    toastContainer.appendChild(toast);

    requestAnimationFrame(() => {
      toast.classList.add('show');
    });

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        if (toast.parentNode) toast.remove();
      }, 300);
    }, 3200);
  };

  // 8. Global Watchlist Bookmark Toggle Controller (Support Bookmark icon toggle on detail & list views)
  window.toggleWatchlistStock = async function(stockId, btnElement) {
    if (!stockId) return;
    if (btnElement) btnElement.disabled = true;

    try {
      const response = await fetch((window.BASE_URL || '') + '/api/watchlist_toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', stock_id: parseInt(stockId, 10) })
      });

      const result = await response.json();
      if (response.status === 401 && result.require_login) {
        const goLogin = await window.showAppConfirm({
          title: 'ต้องเข้าสู่ระบบก่อน',
          message: result.message + '<br><br>คุณต้องการไปที่หน้าเข้าสู่ระบบหรือไม่?',
          confirmText: 'เข้าสู่ระบบ',
          cancelText: 'ยกเลิก',
          type: 'warning'
        });
        if (goLogin) {
          window.location.href = (window.BASE_URL || '') + '/pages/login.php';
        }
        if (btnElement) btnElement.disabled = false;
        return;
      }

      if (result.success) {
        if (btnElement) {
          const isBookmarked = result.is_bookmarked;
          btnElement.classList.toggle('bookmarked', isBookmarked);
          btnElement.setAttribute('title', isBookmarked ? 'นำออกจาก Watchlist' : 'เพิ่มใน Watchlist');
          const outlineIcon = btnElement.querySelector('.bm-icon-outline');
          const filledIcon = btnElement.querySelector('.bm-icon-filled');
          if (outlineIcon) {
            outlineIcon.classList.toggle('d-none', isBookmarked);
            outlineIcon.style.display = isBookmarked ? 'none' : '';
          }
          if (filledIcon) {
            filledIcon.classList.toggle('d-none', !isBookmarked);
            filledIcon.style.display = isBookmarked ? '' : 'none';
          }
        }
        window.showAppToast(result.message || 'อัปเดต Watchlist เรียบร้อย', 'success');
      } else {
        window.showAppToast(result.message || 'เกิดข้อผิดพลาดในการทำรายการ', 'error');
      }
    } catch (err) {
      window.showAppToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
    } finally {
      if (btnElement) btnElement.disabled = false;
    }
  };

  // Alias for backwards compatibility
  window.toggleWatchlist = window.toggleWatchlistStock;
}

// Fail-safe bootstrap for any document readyState
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSmartPort);
} else {
  initSmartPort();
}
