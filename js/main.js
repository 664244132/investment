/**
 * ⚡ js/main.js
 * SmartPort AI - Core UI Interactions, Bespoke Luxury Dropdowns, Live Global Stock Search & Autocomplete, Password Toggles, and Trade Modal
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
          tradeSubmitBtn.textContent = 'ยืนยันคำสั่งซื้อ (BUY)';
        } else {
          tradeSubmitBtn.className = 'btn btn-sell btn-full';
          tradeSubmitBtn.textContent = 'ยืนยันคำสั่งขาย (SELL)';
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

  // 5. Live Global Stock Search & Autocomplete
  const stockSearchInput = document.getElementById('stockSearchInput');
  const searchBoxContainer = document.querySelector('.search-box');

  if (stockSearchInput && searchBoxContainer) {
    searchBoxContainer.style.position = 'relative';

    const dropdown = document.createElement('div');
    dropdown.className = 'search-dropdown';
    dropdown.style.display = 'none';
    searchBoxContainer.appendChild(dropdown);

    let debounceTimer = null;

    stockSearchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const query = stockSearchInput.value.trim();

      if (query.length === 0) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(async () => {
        try {
          const baseUrl = window.BASE_URL || '';
          const res = await fetch(`${baseUrl}/api/search.php?q=${encodeURIComponent(query)}`);
          if (!res.ok) return;

          const data = await res.json();
          if (data.success && data.results && data.results.length > 0) {
            dropdown.innerHTML = '';
            data.results.forEach(item => {
              const row = document.createElement('a');
              row.className = 'search-dropdown-item';
              row.href = `${baseUrl}/index.php?symbol=${encodeURIComponent(item.symbol)}`;

              const priceText = (item.current_price !== null && item.current_price !== undefined)
                ? `฿${Number(item.current_price).toFixed(2)}`
                : (item.market || 'GLOBAL');

              row.innerHTML = `
                <div>
                  <div style="font-weight: 700; font-family: var(--font-mono); color: var(--accent-gold);">${item.symbol}</div>
                  <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.name}</div>
                </div>
                <div style="text-align: right;">
                  <span class="badge badge-neutral" style="font-size: 0.7rem;">${priceText}</span>
                </div>
              `;

              dropdown.appendChild(row);
            });
            dropdown.style.display = 'block';
          } else {
            dropdown.innerHTML = `
              <div style="padding: 0.75rem; text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                กด Enter เพื่อดึงข้อมูลหุ้น "${query.toUpperCase()}" จากตลาดโลก
              </div>
            `;
            dropdown.style.display = 'block';
          }
        } catch (err) {
          console.error('Search error:', err);
        }
      }, 250);
    });

    document.addEventListener('click', (e) => {
      if (!searchBoxContainer.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });

    stockSearchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const query = stockSearchInput.value.trim().toUpperCase();
        if (query) {
          dropdown.style.display = 'none';
          window.location.href = (window.BASE_URL || '') + '/index.php?symbol=' + encodeURIComponent(query);
        }
      }
    });
  }

  // ==============================================================================
  // 6. 🌟 Luxury Custom Dropdown Initializer for all <select> elements
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
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
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
          ${isSelected ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#FFCC00" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>' : ''}
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
              el.innerHTML = `<span>${spanText}</span><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#FFCC00" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
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
});
