<!-- src/components/dashboard/SalesTrendBeautiful.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Sales Trend</h5>
    </div>
    
    <div class="card-body">
      <div class="border-top pt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="title-area">
            <span class="subtitle text-muted">{{ viewType === 'monthly' ? 'Monthly Trend' : 'Category Breakdown' }}</span>
          </div>
          
          <div class="controls">
            <div class="toggle-container">
              <button 
                :class="['toggle-btn', viewType === 'monthly' ? 'active' : '']" 
                @click="viewType = 'monthly'"
              >
                Monthly
              </button>
              <button 
                :class="['toggle-btn', viewType === 'category' ? 'active' : '']" 
                @click="viewType = 'category'"
              >
                Category
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="summary-stats d-flex justify-content-between text-center">
        <div class="stat-item">
          <div class="stat-value">RM{{ totalSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
          <div class="stat-label">Total Sales</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ totalProducts }}</div>
          <div class="stat-label">Products</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ bestSellingCategory }}</div>
          <div class="stat-label">Top Category</div>
        </div>
      </div>
      
      <div class="chart-container" style="height: 350px;">
        <canvas ref="salesChart"></canvas>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue';
import Chart from 'chart.js/auto';
import mockData from '@/data/mockData.js';

export default {
  name: 'SalesTrendBeautiful',
  setup() {
    const salesChart = ref(null);
    const chartInstance = ref(null);
    const viewType = ref('monthly');

    const products = computed(() => mockData.products || []);
    const months = computed(() => mockData.months || []);
    const categories = computed(() => mockData.categories || []);

    const totalSales = computed(() =>
      products.value.reduce((sum, product) => sum + (product.sales || 0), 0)
    );

    const totalProducts = computed(() => products.value.length);

    const bestSellingCategory = computed(() => {
      const salesByCategory = {};
      products.value.forEach(product => {
        salesByCategory[product.category] = (salesByCategory[product.category] || 0) + (product.sales || 0);
      });

      return Object.entries(salesByCategory).reduce(
        (top, curr) => (curr[1] > top[1] ? curr : top),
        ['', 0]
      )[0];
    });

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('en-US', {
        maximumFractionDigits: 0
      }).format(Math.round(value));
    };

    const monthlySales = computed(() => {
      const distribution = [0.15, 0.18, 0.20, 0.22, 0.25];
      return months.value.map((month, index) => ({
        month,
        sales: totalSales.value * (distribution[index] || 0.2)
      }));
    });

    const categorySales = computed(() => {
      const salesByCategory = {};
      categories.value.forEach(cat => salesByCategory[cat] = 0);
      products.value.forEach(product => {
        salesByCategory[product.category] += product.sales || 0;
      });
      return Object.entries(salesByCategory).map(([category, sales]) => ({ category, sales }));
    });

    const updateChart = () => {
      if (!salesChart.value) return;
      const ctx = salesChart.value.getContext('2d');
      if (chartInstance.value) chartInstance.value.destroy();
      viewType.value === 'monthly' ? createMonthlyChart(ctx) : createCategoryChart(ctx);
    };

    const createMonthlyChart = (ctx) => {
      const data = monthlySales.value;
      const gradient = ctx.createLinearGradient(0, 0, 0, 400);
      gradient.addColorStop(0, 'rgba(78, 115, 223, 0.2)');
      gradient.addColorStop(1, 'rgba(78, 115, 223, 0.0)');

      chartInstance.value = new Chart(ctx, {
        type: 'line',
        data: {
          labels: months.value,
          datasets: [{
            label: 'Sales',
            data: data.map(item => item.sales.toFixed(2)),
            borderColor: '#4e73df',
            backgroundColor: gradient,
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: context => `RM${formatCurrency(context.parsed.y)}`
              }
            }
          },
          scales: {
            x: {
              ticks: { color: '#777' },
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: value => `RM${formatCurrency(value)}`
              },
              grid: {
                drawBorder: false,
                borderDash: [2],
                color: 'rgba(0,0,0,0.05)'
              }
            }
          }
        }
      });
    };

    const createCategoryChart = (ctx) => {
      const data = categorySales.value;
      const categoryColors = {
        'Electronics': 'rgba(78, 115, 223, 0.8)',
        'Gaming': 'rgba(28, 200, 138, 0.8)',
        'Accessories': 'rgba(246, 194, 62, 0.8)'
      };

      chartInstance.value = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.map(item => item.category),
          datasets: [{
            label: 'Sales',
            data: data.map(item => item.sales.toFixed(2)),
            backgroundColor: data.map(item => categoryColors[item.category] || '#4e73df'),
            borderRadius: 6,
            maxBarThickness: 60
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: context => `RM${formatCurrency(context.parsed.y)}`
              }
            }
          },
          scales: {
            x: {
              ticks: { color: '#777' },
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: value => `RM${formatCurrency(value)}`
              },
              grid: {
                drawBorder: false,
                borderDash: [2],
                color: 'rgba(0,0,0,0.05)'
              }
            }
          }
        }
      });
    };

    onMounted(updateChart);
    watch(viewType, updateChart);

    return {
      salesChart,
      viewType,
      formatCurrency,
      totalSales,
      totalProducts,
      bestSellingCategory
    };
  }
};
</script>

<style scoped>
.subtitle {
  font-size: 0.9rem;
  color: #6c757d;
}
.summary-stats {
  margin-bottom: 1rem;
}
.stat-item {
  flex: 1;
}
.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
}
.stat-label {
  font-size: 0.9rem;
  color: #6c757d;
}
.toggle-container {
  display: flex;
  gap: 0.5rem;
}
.toggle-btn {
  padding: 6px 12px;
  font-size: 0.85rem;
  border: 1px solid #ccc;
  background-color: #fff;
  color: #333;
  border-radius: 4px;
  cursor: pointer;
}
.toggle-btn.active {
  background-color: #4e73df;
  color: #fff;
  border-color: #4e73df;
}
.chart-container {
  position: relative;
  height: 350px;
}
</style>
