/**
 * ⚡ js/main.js
 * จัดการการโต้ตอบ UI ทั่วไป, ระบบค้นหาหุ้น, สลับดูรหัสผ่าน, คำนวณความแข็งแรงของรหัสผ่าน และ Trade Modal
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Drawer Toggle
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mobileDrawer = document.querySelector('.mobile-drawer');
  if (mobileToggle && mobileDrawer) {
    mobileToggle.addEventListener('click', () => {
      mobileDrawer.classList.toggle('open');
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
      btn.innerHTML = isPassword 
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>' 
        : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" y1="2" x2="22" y2="22"></line></svg>';
    });
  });

  // 3. Password Strength Meter (บนหน้าสมัครสมาชิก)
  const passwordInput = document.getElementById('reg_password');
  const strengthSegments = document.querySelectorAll('.pw-strength-segment');

  if (passwordInput && strengthSegments.length > 0) {
    passwordInput.addEventListener('input', () => {
      const val = passwordInput.value;
      let score = 0;

      if (val.length >= 6) score++;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
      if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) score++;

      strengthSegments.forEach((seg, idx) => {
        if (idx < score) {
          if (score <= 1) seg.style.backgroundColor = 'var(--color-danger)';
          else if (score <= 2) seg.style.backgroundColor = 'var(--accent-gold)';
          else seg.style.backgroundColor = 'var(--color-success)';
        } else {
          seg.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
        }
      });
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

  function calculateTradeTotal() {
    if (!tradeQtyInput || !tradeStockPrice || !tradeTotalDisplay) return;
    const qty = parseInt(tradeQtyInput.value, 10) || 0;
    const price = parseFloat(tradeStockPrice.getAttribute('data-price')) || 0;
    const feeRate = 0.00157; // 0.157%
    const subtotal = qty * price;
    const fee = subtotal * feeRate;
    const total = subtotal + fee;

    tradeTotalDisplay.textContent = '฿' + total.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  if (tradeQtyInput) {
    tradeQtyInput.addEventListener('input', calculateTradeTotal);
  }

  openTradeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-trade-type') || 'BUY';
      if (tradeTypeInput) tradeTypeInput.value = type;
      
      if (tradeSubmitBtn) {
        if (type === 'BUY') {
          tradeSubmitBtn.className = 'btn btn-buy btn-full';
          tradeSubmitBtn.textContent = 'ยืนยันการซื้อ (BUY)';
        } else {
          tradeSubmitBtn.className = 'btn btn-sell btn-full';
          tradeSubmitBtn.textContent = 'ยืนยันการขาย (SELL)';
        }
      }

      calculateTradeTotal();
      if (tradeModal) tradeModal.classList.add('open');
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

  // 5. Stock Search Enter Navigation
  const stockSearchInput = document.getElementById('stockSearchInput');
  if (stockSearchInput) {
    stockSearchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const query = stockSearchInput.value.trim().toUpperCase();
        if (query) {
          window.location.href = window.BASE_URL + '/index.php?symbol=' + encodeURIComponent(query);
        }
      }
    });
  }
});
