/**
 * js/chart_engine.js
 * Chart Engine Wrapper for SmartPort AI (100% Local Asset & Retina/High-DPI Sharpness)
 * ธีมสีทอง #FFCC00 บนพื้นหลัง Dark #1D1616 สไตล์ Bloomberg / TradingView
 */

document.addEventListener('DOMContentLoaded', () => {
  const chartCanvas = document.getElementById('stockPriceChart');
  if (!chartCanvas) return;

  const ctx = chartCanvas.getContext('2d');
  const timeframeButtons = document.querySelectorAll('.timeframe-btn');

  // ตรวจสอบว่ามีข้อมูล Chart Data ถูกส่งมาจาก PHP หรือไม่
  const rawChartData = window.stockPriceHistory || [];
  
  // สร้าง Gradient สีทองสำหรับพื้นที่ใต้กราฟ
  const goldGradient = ctx.createLinearGradient(0, 0, 0, 300);
  goldGradient.addColorStop(0, 'rgba(255, 204, 0, 0.35)');
  goldGradient.addColorStop(0.8, 'rgba(255, 204, 0, 0.04)');
  goldGradient.addColorStop(1, 'rgba(255, 204, 0, 0.0)');

  // จัดเตรียม Labels และ Values
  let dates = rawChartData.map(d => d.trade_date || d.date);
  let prices = rawChartData.map(d => parseFloat(d.close_price || d.price));

  // หากข้อมูลน้อย ให้สร้างช่วงข้อมูลจำลองเพื่อความต่อเนื่องของกราฟ
  if (prices.length === 0) {
    dates = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7', 'Day 8', 'Day 9', 'Day 10'];
    prices = [140.0, 141.5, 143.0, 144.0, 145.0, 146.5, 145.5, 144.0, 142.0, 148.5];
  }

  // สร้าง Chart.js Instance
  const priceChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: dates,
      datasets: [{
        label: 'ราคาหุ้น (Close Price)',
        data: prices,
        borderColor: '#FFCC00',
        borderWidth: 2.5,
        backgroundColor: goldGradient,
        fill: true,
        tension: 0.25,
        pointBackgroundColor: '#FFCC00',
        pointBorderColor: '#231B1B',
        pointBorderWidth: 2,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointHoverBackgroundColor: '#FFCC00',
        pointHoverBorderColor: '#FFFFFF',
        pointHoverBorderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      devicePixelRatio: window.devicePixelRatio || 1,
      interaction: {
        intersect: false,
        mode: 'index',
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#2A2020',
          titleColor: '#8A7F70',
          titleFont: {
            family: "'JetBrains Mono', monospace",
            size: 11
          },
          bodyColor: '#FFCC00',
          bodyFont: {
            family: "'JetBrains Mono', monospace",
            size: 14,
            weight: 'bold'
          },
          borderColor: 'rgba(255, 204, 0, 0.3)',
          borderWidth: 1,
          padding: 10,
          displayColors: false,
          callbacks: {
            label: function(context) {
              return '฿' + context.parsed.y.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              });
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
              family: "'JetBrains Mono', monospace",
              size: 10
            },
            maxRotation: 0,
            autoSkip: true,
            maxTicksLimit: 7
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
              family: "'JetBrains Mono', monospace",
              size: 11
            },
            callback: function(value) {
              return '฿' + value.toLocaleString();
            }
          }
        }
      }
    }
  });

  // Timeframe Switcher
  timeframeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      timeframeButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const tf = btn.getAttribute('data-timeframe');
      // ปรับปรุงข้อมูลตาม Timeframe ที่เลือก
      if (window.timeframeData && window.timeframeData[tf]) {
        const tfData = window.timeframeData[tf];
        priceChart.data.labels = tfData.map(d => d.date);
        priceChart.data.datasets[0].data = tfData.map(d => d.price);
        priceChart.update();
      }
    });
  });
});
