<template>
  <div class="card sales-trend">
    <div class="card-header">
      <div class="header-content">
        <h5 class="card-title">Sales Trend</h5>
        <div class="chart-controls">
          <select v-model="viewType" class="chart-type-select">
            <option value="monthly">Monthly View</option>
            <option value="category">Category View</option>
          </select>
          <button @click="refreshChart" class="refresh-btn" title="Refresh chart">
            <i class="bi bi-arrow-clockwise" :class="{ 'spinning': isRefreshing }"></i>
          </button>
        </div>
      </div>
    </div>
    
    <div class="card-body">
      <!-- Summary Statistics -->
      <div class="category-stats mb-3" v-if="hasData">
        <div class="stat-item">
          <div class="stat-value">RM{{ totalSales.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
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

      <!-- Debug Info -->
      <div class="debug-info" v-if="showDebug">
        <p>Products: {{ products?.length || 0 }}</p>
        <p>View Type: {{ viewType }}</p>
        <p>Has Data: {{ hasData }}</p>
        <p>Chart Data: {{ chartData ? 'Available' : 'Missing' }}</p>
      </div>

      <!-- Chart Container -->
      <div class="chart-container">
        <!-- Loading State -->
        <div v-if="isLoading" class="loading">
          <div class="spinner"></div>
          <p>Loading chart...</p>
        </div>
        
        <!-- No Data State -->
        <div v-else-if="!hasData" class="no-data">
          <i class="bi bi-graph-up"></i>
          <p>No data available</p>
          <small>{{ products?.length || 0 }} products loaded</small>
          <button @click="showDebug = !showDebug" class="debug-btn">Toggle Debug</button>
        </div>
        
        <!-- Chart Canvas -->
        <canvas 
          v-else
          ref="chartCanvas" 
          id="salesChart"
          width="400" 
          height="300"
        ></canvas>
      </div>

      <!-- Sales breakdown list with collapsible dropdown -->
      <div class="category-breakdown mt-3" v-if="hasData">
        <div class="breakdown-header" @click="toggleBreakdown">
          <h6 class="breakdown-title">{{ viewType === 'monthly' ? 'Monthly Breakdown' : 'Category Performance' }}</h6>
          <i class="bi dropdown-arrow" :class="showBreakdown ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
        </div>
        
        <transition name="slide-fade">
          <div v-show="showBreakdown" class="breakdown-content">
            <div class="category-list">
              <div 
                v-for="(item, index) in salesBreakdown" 
                :key="item.label"
                class="category-item"
              >
                <div class="category-info">
                  <div 
                    class="category-color" 
                    :style="{ backgroundColor: getColor(index) }"
                  ></div>
                  <div class="category-details">
                    <div class="category-name">{{ item.label }}</div>
                    <div class="category-products">{{ item.sublabel }}</div>
                  </div>
                </div>
                <div class="category-sales">
                  <div class="sales-amount">RM{{ item.value.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
                  <div class="sales-percentage" :class="item.trend > 0 ? 'trend-up' : 'trend-down'">
                    <i :class="item.trend > 0 ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    {{ Math.abs(item.trend) }}%
                  </div>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';

export default {
  name: 'SalesTrend',
  props: {
    products: {
      type: Array,
      default: () => []
    },
    sales: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      viewType: 'monthly',
      chartInstance: null,
      isLoading: false,
      isRefreshing: false,
      showDebug: false,
      showBreakdown: true, // Controls dropdown visibility
      months: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"]
    };
  },
  computed: {
    hasData() {
      return this.products && this.products.length > 0;
    },
    totalSales() {
      if (!this.hasData) return 0;
      return this.products.reduce((sum, product) => sum + (product.sales || 0), 0);
    },
    totalProducts() {
      return this.products ? this.products.length : 0;
    },
    categories() {
      if (!this.hasData) return [];
      return [...new Set(this.products.map(product => product.category))];
    },
    categorySales() {
      if (!this.hasData) return [];
      
      const salesByCategory = {};
      this.products.forEach(product => {
        const category = product.category;
        salesByCategory[category] = (salesByCategory[category] || 0) + (product.sales || 0);
      });

      return Object.entries(salesByCategory)
        .map(([category, salesValue]) => ({
          category,
          sales: Number(salesValue)
        }))
        .sort((a, b) => b.sales - a.sales);
    },
    bestSellingCategory() {
      if (!this.hasData || this.categorySales.length === 0) return 'N/A';
      return this.categorySales[0].category;
    },
    monthlySalesData() {
      if (!this.hasData) return [];
      
      // Generate simulated monthly data based on total sales
      return this.months.map((month, monthIndex) => {
        // Create realistic monthly variations
        const baseAmount = this.totalSales / this.months.length;
        const seasonalFactor = 0.8 + (Math.sin((monthIndex / this.months.length) * Math.PI * 2) + 1) * 0.2;
        const randomFactor = 0.9 + Math.random() * 0.2;
        
        return {
          month,
          sales: Math.round(baseAmount * seasonalFactor * randomFactor),
          orders: Math.floor((baseAmount * seasonalFactor * randomFactor) / 50),
          trend: Math.floor(Math.random() * 20) - 10
        };
      });
    },
    salesBreakdown() {
      if (this.viewType === 'monthly') {
        return this.monthlySalesData.map(item => ({
          label: item.month,
          sublabel: `${item.orders} orders`,
          value: item.sales,
          trend: item.trend
        }));
      } else {
        return this.categorySales.map((item) => {
          const productsInCategory = this.products.filter(p => p.category === item.category).length;
          const trend = Math.floor(Math.random() * 30) - 15;
          
          return {
            label: item.category,
            sublabel: `${productsInCategory} products`,
            value: item.sales,
            trend: trend
          };
        });
      }
    },
    chartData() {
      if (!this.hasData) return null;

      const colors = [
        'rgba(78, 115, 223, 1)',    // Blue
        'rgba(28, 200, 138, 1)',    // Green
        'rgba(246, 194, 62, 1)',    // Yellow
        'rgba(231, 74, 59, 1)',     // Red
        'rgba(133, 135, 150, 1)',   // Gray
        'rgba(54, 185, 204, 1)'     // Cyan
      ];

      if (this.viewType === 'monthly') {
        const data = this.monthlySalesData;
        
        return {
          labels: data.map(item => item.month),
          datasets: [{
            label: 'Monthly Sales',
            data: data.map(item => item.sales),
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
          }]
        };
      } else {
        const data = this.categorySales;
        
        return {
          labels: data.map(item => item.category),
          datasets: [{
            label: 'Sales by Category',
            data: data.map(item => item.sales),
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            borderColor: 'rgba(28, 200, 138, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: data.map((_, index) => colors[index % colors.length]),
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 8,
            pointHoverRadius: 10
          }]
        };
      }
    }
  },
  watch: {
    products: {
      handler(newProducts) {
        console.log('SalesTrend: Products changed:', newProducts?.length);
        if (newProducts && newProducts.length > 0) {
          this.$nextTick(() => {
            this.createChart();
          });
        }
      },
      deep: true,
      immediate: true
    },
    viewType() {
      this.createChart();
    }
  },
  mounted() {
    console.log('SalesTrend mounted with products:', this.products?.length);
    this.$nextTick(() => {
      if (this.hasData) {
        this.createChart();
      }
    });
  },
  beforeUnmount() {
    if (this.chartInstance) {
      this.chartInstance.destroy();
    }
  },
  methods: {
    async createChart() {
      console.log('Creating sales chart...', {
        hasData: this.hasData,
        canvasExists: !!this.$refs.chartCanvas,
        chartData: !!this.chartData,
        viewType: this.viewType
      });

      // Destroy existing chart
      if (this.chartInstance) {
        this.chartInstance.destroy();
        this.chartInstance = null;
      }

      // Check if we have everything needed
      if (!this.hasData || !this.chartData) {
        console.log('Cannot create sales chart: missing data');
        return;
      }

      // Wait for canvas to be available
      await this.$nextTick();
      
      if (!this.$refs.chartCanvas) {
        console.log('Cannot create sales chart: canvas not found');
        return;
      }

      try {
        const ctx = this.$refs.chartCanvas.getContext('2d');
        
        const config = {
          type: 'line',
          data: this.chartData,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'top',
                labels: {
                  color: '#ffffff',
                  usePointStyle: true,
                  padding: 15
                }
              },
              tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: 'rgba(255, 255, 255, 0.2)',
                borderWidth: 1,
                callbacks: {
                  label: (context) => {
                    return `Sales: RM${context.raw.toLocaleString()}`;
                  }
                }
              }
            },
            scales: {
              x: {
                ticks: { color: '#ffffff' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
              },
              y: {
                beginAtZero: true,
                ticks: {
                  color: '#ffffff',
                  callback: (value) => `RM${value.toLocaleString()}`
                },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
              }
            },
            elements: {
              point: {
                hoverRadius: 8
              }
            }
          }
        };

        this.chartInstance = new Chart(ctx, config);
        console.log('Sales chart created successfully!');
        
      } catch (error) {
        console.error('Error creating sales chart:', error);
      }
    },

    async refreshChart() {
      this.isRefreshing = true;
      try {
        await new Promise(resolve => setTimeout(resolve, 500));
        this.createChart();
      } catch (error) {
        console.error('Error refreshing chart:', error);
      } finally {
        this.isRefreshing = false;
      }
    },

    getColor(index) {
      const colors = [
        'rgba(78, 115, 223, 0.8)',
        'rgba(28, 200, 138, 0.8)', 
        'rgba(246, 194, 62, 0.8)',
        'rgba(231, 74, 59, 0.8)',
        'rgba(133, 135, 150, 0.8)',
        'rgba(54, 185, 204, 0.8)'
      ];
      return colors[index % colors.length];
    },

    toggleBreakdown() {
      this.showBreakdown = !this.showBreakdown;
    }
  }
};
</script>

<style scoped>
.sales-trend {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
}

.card-header {
  background: rgba(255, 255, 255, 0.08);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.card-title {
  color: #ffffff;
  font-weight: 600;
  margin: 0;
  font-size: 1.1rem;
}

.chart-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.chart-type-select {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 0.9rem;
}

.chart-type-select option {
  background: #2a2a2a;
  color: white;
}

.refresh-btn, .debug-btn {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.refresh-btn:hover, .debug-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.category-stats {
  display: flex;
  justify-content: space-around;
  text-align: center;
  padding: 15px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-item {
  flex: 1;
}

.stat-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: #4a9eff;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.85rem;
  color: #ccc;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.debug-info {
  background: rgba(255, 255, 255, 0.05);
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 15px;
  font-size: 0.85rem;
}

.chart-container {
  height: 300px;
  position: relative;
  margin: 20px 0;
}

.loading, .no-data {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: #666;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-left: 4px solid #4a9eff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

.no-data i {
  font-size: 3rem;
  margin-bottom: 15px;
  opacity: 0.5;
}

.no-data small {
  color: #999;
  margin-top: 5px;
  display: block;
}

.category-breakdown {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 15px;
}

/* New dropdown styles */
.breakdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 10px 0;
  transition: all 0.3s ease;
  user-select: none;
  border-radius: 6px;
}

.breakdown-header:hover {
  background: rgba(255, 255, 255, 0.05);
  padding: 10px 12px;
}

.breakdown-title {
  color: #ffffff;
  font-weight: 600;
  margin: 0;
  font-size: 1rem;
}

.dropdown-arrow {
  color: #ffffff;
  font-size: 1rem;
  transition: transform 0.3s ease;
}

.breakdown-content {
  overflow: hidden;
}

.category-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 15px;
}

/* Transition styles */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  transition: background 0.3s ease;
}

.category-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.category-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.category-color {
  width: 16px;
  height: 16px;
  border-radius: 50%;
}

.category-details {
  display: flex;
  flex-direction: column;
}

.category-name {
  font-weight: 600;
  color: white;
  margin-bottom: 2px;
}

.category-products {
  font-size: 0.85rem;
  color: #ccc;
}

.category-sales {
  text-align: right;
}

.sales-amount {
  font-weight: 600;
  color: #4a9eff;
  margin-bottom: 2px;
}

.sales-percentage {
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
}

.trend-up {
  color: #28a745;
}

.trend-down {
  color: #dc3545;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .category-stats {
    flex-direction: column;
    gap: 15px;
    text-align: left;
  }
  
  .chart-container {
    height: 250px;
  }
  
  .category-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .category-sales {
    text-align: left;
    width: 100%;
  }
}
</style>