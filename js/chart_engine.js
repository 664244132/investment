/**
 * js/chart_engine.js
 * SmartPort AI - Live Market Chart Engine & Dynamic Timeframe Switcher (100% Real-Time API)
 * รองรับ Timeframe: 1H (รายชั่วโมง), 1D (รายวัน), 1W (รายอาทิตย์), 1M (1 เดือน), 1Y (1 ปี), 5Y (5 ปี)
 * ธีมสีทอง Luxury Dark Financial Theme บน Chart.js Local Engine
 */

document.addEventListener('DOMContentLoaded', () => {
  const chartCanvas = document.getElementById('stockPriceChart');
  if (!chartCanvas) return;

  const ctx = chartCanvas.getContext('2d');
  const timeframeButtons = document.querySelectorAll('.timeframe-btn');
  const symbol = window.stockSymbol || '';
  const currencySymbol = window.stockCurrency || '฿';
  const baseUrl = window.BASE_URL || '';

  // In-Memory Timeframe Data Cache (0ms Instant Switching)
  const chartCache = new Map();

  // สร้าง Gradient สีทองใต้เส้นกราฟ
  const goldGradient = ctx.createLinearGradient(0, 0, 0, 320);
  goldGradient.addColorStop(0, 'rgba(255, 204, 0, 0.38)');
  goldGradient.addColorStop(0.7, 'rgba(255, 204, 0, 0.05)');
  goldGradient.addColorStop(1, 'rgba(255, 204, 0, 0.0)');

  // จัดเตรียม Initial Data
  const initialHistory = window.stockPriceHistory || [];
  let initialLabels = [];
  let initialPrices = [];
  let initialMeta = [];

  if (Array.isArray(initialHistory) && initialHistory.length > 5) {
    initialLabels = initialHistory.map(d => {
      if (d.display_date) return d.display_date;
      if (d.trade_date) {
        const parts = d.trade_date.split('-');
        if (parts.length === 3) return `${parts[2]}/${parts[1]}`;
        return d.trade_date;
      }
      return d.date || '';
    });
    initialPrices = initialHistory.map(d => parseFloat(d.close_price || d.close || d.price || 0));
    initialMeta = initialHistory;
    // Cache the initial dataset
    chartCache.set('1M', initialHistory);
  } else {
    initialLabels = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7', 'Day 8', 'Day 9', 'Day 10'];
    initialPrices = [100.0, 101.5, 103.0, 102.5, 104.0, 105.5, 104.5, 106.0, 107.5, 109.0];
  }

  // สร้าง Chart.js Instance
  const priceChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: initialLabels,
      datasets: [{
        label: 'ราคาหุ้น (Close Price)',
        data: initialPrices,
        borderColor: '#FFCC00',
        borderWidth: 2.5,
        backgroundColor: goldGradient,
        fill: true,
        tension: 0.2,
        pointBackgroundColor: '#FFCC00',
        pointBorderColor: '#1A1300',
        pointBorderWidth: 2,
        pointRadius: initialPrices.length > 40 ? 1.5 : 3,
        pointHoverRadius: 6,
        pointHoverBackgroundColor: '#FFCC00',
        pointHoverBorderColor: '#FFFFFF',
        pointHoverBorderWidth: 2,
        _rawMeta: initialMeta
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      devicePixelRatio: window.devicePixelRatio || 1,
      animation: {
        duration: 350,
        easing: 'easeOutQuart'
      },
      interaction: {
        intersect: false,
        mode: 'index',
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#231B1B',
          titleColor: '#A3998C',
          titleFont: {
            family: "'JetBrains Mono', 'Fira Code', monospace",
            size: 11,
            weight: 'normal'
          },
          bodyColor: '#FFCC00',
          bodyFont: {
            family: "'JetBrains Mono', 'Fira Code', monospace",
            size: 13,
            weight: 'bold'
          },
          borderColor: 'rgba(255, 204, 0, 0.35)',
          borderWidth: 1,
          padding: 10,
          displayColors: false,
          callbacks: {
            title: function(items) {
              if (!items.length) return '';
              const idx = items[0].dataIndex;
              const meta = priceChart.data.datasets[0]._rawMeta;
              if (meta && meta[idx]) {
                const item = meta[idx];
                return item.date || item.trade_date || items[0].label;
              }
              return items[0].label;
            },
            label: function(context) {
              const val = context.parsed.y;
              const formattedPrice = currencySymbol + Number(val).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              });

              const idx = context.dataIndex;
              const meta = priceChart.data.datasets[0]._rawMeta;
              if (meta && meta[idx]) {
                const item = meta[idx];
                const high = item.high || item.high_price;
                const low = item.low || item.low_price;
                if (high !== undefined && low !== undefined && high > 0 && low > 0) {
                  return [
                    `ราคาปิด: ${formattedPrice}`,
                    `สูงสุด (H): ${currencySymbol}${Number(high).toFixed(2)} | ต่ำสุด (L): ${currencySymbol}${Number(low).toFixed(2)}`
                  ];
                }
              }

              return `ราคา: ${formattedPrice}`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            color: 'rgba(255, 204, 0, 0.04)',
            drawBorder: false
          },
          ticks: {
            color: '#8A7F70',
            font: {
              family: "'JetBrains Mono', 'Fira Code', monospace",
              size: 10
            },
            maxRotation: 0,
            autoSkip: true,
            maxTicksLimit: 8
          }
        },
        y: {
          position: 'right',
          grid: {
            color: 'rgba(255, 204, 0, 0.06)',
            drawBorder: false
          },
          ticks: {
            color: '#8A7F70',
            font: {
              family: "'JetBrains Mono', 'Fira Code', monospace",
              size: 11
            },
            callback: function(value) {
              return currencySymbol + Number(value).toLocaleString();
            }
          }
        }
      }
    }
  });

  /**
   * อัปเดตข้อมูลกราฟตาม Timeframe ที่เลือกจาก Live Market API
   */
  async function loadTimeframeData(tf, activeBtn) {
    if (!symbol) return;

    // 1. ตรวจสอบ In-Memory Cache
    if (chartCache.has(tf)) {
      const cached = chartCache.get(tf);
      if (Array.isArray(cached) && cached.length > 0) {
        applyChartData(cached, tf);
        return;
      }
    }

    // 2. แสดงสถานะกำลังโหลด
    if (activeBtn) {
      activeBtn.style.opacity = '0.5';
    }

    try {
      const url = `${baseUrl}/api/chart_data.php?symbol=${encodeURIComponent(symbol)}&timeframe=${encodeURIComponent(tf)}`;
      const res = await fetch(url);
      if (!res.ok) throw new Error('HTTP ' + res.status);

      const json = await res.json();
      if (json.success && Array.isArray(json.data) && json.data.length > 0) {
        chartCache.set(tf, json.data);
        applyChartData(json.data, tf);
      }
    } catch (err) {
      console.warn('Failed to load chart data for timeframe:', tf, err);
    } finally {
      if (activeBtn) {
        activeBtn.style.opacity = '1';
      }
    }
  }

  /**
   * เรนเดอร์ชุดข้อมูลใหม่ลงใน Chart.js
   */
  function applyChartData(dataList, tf) {
    if (!dataList || dataList.length === 0) return;

    const labels = dataList.map(d => {
      if (d.display_date) return d.display_date;
      if (d.trade_date) {
        const parts = d.trade_date.split('-');
        if (parts.length === 3) return `${parts[2]}/${parts[1]}`;
        return d.trade_date;
      }
      return d.date || '';
    });

    const prices = dataList.map(d => parseFloat(d.close || d.close_price || d.price || 0));

    priceChart.data.labels = labels;
    priceChart.data.datasets[0].data = prices;
    priceChart.data.datasets[0]._rawMeta = dataList;
    priceChart.data.datasets[0].pointRadius = prices.length > 40 ? 1.5 : (prices.length > 20 ? 2.5 : 3.5);
    priceChart.update();
  }

  // ผูก Event Listener กับปุ่ม Timeframe Switcher ทุกปุ่ม
  timeframeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const tf = btn.getAttribute('data-timeframe');
      if (!tf) return;

      timeframeButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      loadTimeframeData(tf, btn);
    });
  });

  // ถ้าเริ่มต้นไม่มีข้อมูลประวัติ หรือข้อมูลน้อยกว่า 15 จุด ให้ดึง 1M สดจาก API ทันที
  if (initialHistory.length < 15 && symbol) {
    const active1mBtn = document.querySelector('.timeframe-btn[data-timeframe="1M"]');
    loadTimeframeData('1M', active1mBtn);
  }
});
