<!-- src/components/dashboard/InventoryOverview.vue -->
<template>
  <div class="card inventory-card">
    <div class="card-header">
      <h5 class="card-title">Inventory Overview</h5>
      <div class="view-options">
        <button class="refresh-btn" @click="refreshData" title="Refresh Data">
          <i class="fas fa-sync-alt"></i>
        </button>
        <!-- Replaced dropdown with last updated text -->
        <span class="last-updated">Last updated: {{ lastUpdatedText }}</span>
      </div>
    </div>
    
    <div class="card-body d-flex flex-column">
      <div class="stat-grid flex-grow-1">
        <div class="stat-card" @click="showProductList('all')" :class="{ active: activeCard === 'all' }">
          <div class="stat-info">
            <div class="stat-value">{{ totalProducts }}</div>
            <div class="stat-label">Total Products</div>
          </div>
          <div class="stat-trend" v-if="totalProductsTrend !== 0">
            <i :class="totalProductsTrend > 0 ? 'fas fa-arrow-up trend-up' : 'fas fa-arrow-down trend-down'"></i>
            <span :class="totalProductsTrend > 0 ? 'trend-up' : 'trend-down'">{{ Math.abs(totalProductsTrend) }}%</span>
          </div>
        </div>
        
        <div class="stat-card" @click="showProductList('low')" :class="{ active: activeCard === 'low', alert: lowStockCount > 0 }">
          <div class="stat-info">
            <div class="stat-value orange">{{ lowStockCount }}</div>
            <div class="stat-label">Low Stock</div>
          </div>
          <div class="stat-trend" v-if="lowStockTrend !== 0">
            <i :class="lowStockTrend < 0 ? 'fas fa-arrow-up trend-up' : 'fas fa-arrow-down trend-down'"></i>
            <span :class="lowStockTrend < 0 ? 'trend-up' : 'trend-down'">{{ Math.abs(lowStockTrend) }}%</span>
          </div>
        </div>
        
        <div class="stat-card" @click="showProductList('out')" :class="{ active: activeCard === 'out', alert: outOfStockCount > 0 }">
          <div class="stat-info">
            <div class="stat-value red">{{ outOfStockCount }}</div>
            <div class="stat-label">Out of Stock</div>
          </div>
          <div class="stat-trend" v-if="outOfStockTrend !== 0">
            <i :class="outOfStockTrend < 0 ? 'fas fa-arrow-up trend-up' : 'fas fa-arrow-down trend-down'"></i>
            <span :class="outOfStockTrend < 0 ? 'trend-up' : 'trend-down'">{{ Math.abs(outOfStockTrend) }}%</span>
          </div>
        </div>
        
        <div class="stat-card" @click="showProductList('sales')" :class="{ active: activeCard === 'sales' }">
          <div class="stat-info">
            <div class="stat-value green">RM{{ totalSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
            <div class="stat-label">Total Sales</div>
          </div>
          <div class="stat-trend" v-if="salesTrend !== 0">
            <i :class="salesTrend > 0 ? 'fas fa-arrow-up trend-up' : 'fas fa-arrow-down trend-down'"></i>
            <span :class="salesTrend > 0 ? 'trend-up' : 'trend-down'">{{ Math.abs(salesTrend) }}%</span>
          </div>
        </div>
      </div>
      
      <!-- Product list panel that appears when a card is clicked -->
      <div class="product-list-panel" v-if="showPanel">
        <div class="panel-header">
          <div class="header-actions">
            <button class="back-btn" @click="closePanel">
              <i class="fas fa-chevron-left"></i> Back
            </button>
            <h4>{{ panelTitle }}</h4>
          </div>
          <button class="close-btn" @click="closePanel">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="panel-content">
          <div v-if="filteredProducts.length === 0" class="no-data">
            No products to display
          </div>
          <table v-else class="product-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th v-if="activeCard === 'sales'">Sold</th>
                <th v-if="activeCard === 'sales'">Sales</th>
                <th v-if="activeCard === 'low' || activeCard === 'out'">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in filteredProducts" :key="product.id">
                <td>{{ product.name }}</td>
                <td>{{ product.category }}</td>
                <td>RM{{ product.price.toFixed(2) }}</td>
                <td>{{ product.stock }}</td>
                <td v-if="activeCard === 'sales'">{{ product.sold }}</td>
                <td v-if="activeCard === 'sales'">RM{{ product.sales.toFixed(2) }}</td>
                <td v-if="activeCard === 'low' || activeCard === 'out'">
                  <button class="action-btn" @click="navigateToUpdatePage(product)">
                    Restock
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Loader for when data is refreshing -->
      <div class="loader-overlay" v-if="isLoading">
        <div class="loader"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'InventoryOverview',
  data() {
    return {
      products: [],
      activeCard: null,
      showPanel: false,
      isLoading: false,
      totalProductsTrend: 3,
      lowStockTrend: -5,
      outOfStockTrend: -2,
      salesTrend: 8,
      lastUpdated: new Date()
    };
  },
  props: {
    // Add props to allow parent component to handle page navigation
    onNavigateToUpdate: {
      type: Function,
      default: null
    }
  },
  computed: {
    totalProducts() {
      return this.products.length;
    },
    lowStockCount() {
      return this.products.filter(product => 
        product.stock > 0 && product.stock <= product.reorderLevel
      ).length;
    },
    outOfStockCount() {
      return this.products.filter(product => product.stock <= 0).length;
    },
    totalSales() {
      return this.products.reduce((total, product) => total + product.sales, 0);
    },
    filteredProducts() {
      if (this.activeCard === 'all') {
        return [...this.products];
      } else if (this.activeCard === 'low') {
        return this.products.filter(product => 
          product.stock > 0 && product.stock <= product.reorderLevel
        );
      } else if (this.activeCard === 'out') {
        return this.products.filter(product => product.stock <= 0);
      } else if (this.activeCard === 'sales') {
        // Return all products sorted by sales (highest to lowest)
        return [...this.products].sort((a, b) => b.sales - a.sales);
      }
      return [];
    },
    panelTitle() {
      if (this.activeCard === 'all') return 'All Products';
      if (this.activeCard === 'low') return 'Low Stock Products';
      if (this.activeCard === 'out') return 'Out of Stock Products';
      if (this.activeCard === 'sales') return 'Top Selling Products';
      return '';
    },
    lastUpdatedText() {
      // Format the last updated time to a nice string
      return this.formatTime(this.lastUpdated);
    }
  },
  created() {
    this.fetchProducts();
  },
  methods: {
    fetchProducts() {
      this.isLoading = true;
      
      // Simulate loading delay
      setTimeout(() => {
        // In a real application, this would be an API call with time range filters
        const mockData = require('@/data/mockData').default;
        this.products = mockData.products;
        this.lastUpdated = new Date();
        this.isLoading = false;
      }, 600);
    },
    refreshData() {
      this.isLoading = true;
      // Simulate data refresh
      setTimeout(() => {
        this.fetchProducts();
        // Update trends randomly for demo purposes
        this.totalProductsTrend = Math.floor(Math.random() * 10) - 3;
        this.lowStockTrend = Math.floor(Math.random() * 10) - 5;
        this.outOfStockTrend = Math.floor(Math.random() * 10) - 5;
        this.salesTrend = Math.floor(Math.random() * 15);
        this.isLoading = false;
      }, 800);
    },
    showProductList(type) {
      this.activeCard = type;
      this.showPanel = true;
    },
    closePanel() {
      this.showPanel = false;
      this.activeCard = null;
    },
    navigateToUpdatePage(product) {
      // Store the product data in localStorage
      localStorage.setItem('editProduct', JSON.stringify(product));
      
      // If a navigation function was provided by the parent, use it
      if (this.onNavigateToUpdate) {
        this.onNavigateToUpdate(product);
        return;
      }
      
      // Find App.vue in the component hierarchy
      let appComponent = this.$root;
      
      // Set the current page to update
      if (appComponent && typeof appComponent.currentPage !== 'undefined') {
        appComponent.currentPage = 'update';
      } else {
        console.error('Could not find App component with currentPage property');
      }
    },
    formatTime(date) {
      // Format date to a more readable format
      const now = new Date();
      const diffMs = now - date;
      const diffSec = Math.round(diffMs / 1000);
      const diffMin = Math.round(diffSec / 60);
      const diffHour = Math.round(diffMin / 60);
      
      if (diffSec < 60) {
        return 'Just now';
      } else if (diffMin < 60) {
        return `${diffMin} minute${diffMin !== 1 ? 's' : ''} ago`;
      } else if (diffHour < 24) {
        return `${diffHour} hour${diffHour !== 1 ? 's' : ''} ago`;
      } else {
        // Format to standard date
        return date.toLocaleString();
      }
    }
  }
};
</script>

<style scoped>
.inventory-card {
  height: 572px; /* Match the height of Sales Performance */
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
}

.card {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  position: relative;
}

.card-header {
  background-color: #e9ecef;
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.card-body {
  padding: 20px;
  position: relative;
  flex-grow: 1;
  overflow: hidden;
}

.d-flex {
  display: flex;
}

.flex-column {
  flex-direction: column;
}

.flex-grow-1 {
  flex-grow: 1;
}

.view-options {
  display: flex;
  gap: 15px;
  align-items: center;
  justify-content: center;
}

.last-updated {
  color: #6c757d;
  font-size: 14px;
  font-style: italic;
}

.refresh-btn {
  background: none;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.2s;
}

.refresh-btn:hover {
  background-color: #f0f0f0;
  color: #4361ee;
}

/* Stat grid and cards */
.stat-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 15px;
  height: 100%;
  align-items: stretch;
}

.stat-card {
  background-color: #f9fafb;
  border-radius: 8px;
  padding: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  cursor: pointer;
  transition: all 0.3s;
  border: 2px solid transparent;
  text-align: center;
  height: auto;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card.active {
  border-color: #4361ee;
}

.stat-card.alert {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(247, 37, 133, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(247, 37, 133, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(247, 37, 133, 0);
  }
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background-color: #eaeaea;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  font-size: 18px;
  color: #6c757d;
}

.stat-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.stat-value {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 5px;
  color: #333;
}

.red {
  color: #f72585;
}

.orange {
  color: #f8961e;
}

.green {
  color: #137333;
}

.stat-label {
  font-size: 14px;
  color: #6c757d;
}

.stat-trend {
  position: absolute;
  bottom: 10px;
  right: 10px;
  font-size: 12px;
  display: flex;
  align-items: center;
}

.trend-up {
  color: #4cc9f0;
}

.trend-down {
  color: #f72585;
}

.stat-trend i {
  margin-right: 3px;
}

/* Product list panel */
.product-list-panel {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: white;
  z-index: 10;
  animation: slideIn 0.3s ease-out forwards;
  display: flex;
  flex-direction: column;
}

@keyframes slideIn {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  background-color: #f8f9fa;
}

.header-actions {
  display: flex;
  align-items: center;
}

.panel-header h4 {
  margin: 0;
  margin-left: 15px;
  font-size: 16px;
  font-weight: 600;
}

.back-btn {
  background-color: #4361ee;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 8px 15px;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.back-btn i {
  margin-right: 5px;
}

.back-btn:hover {
  background-color: #3a56d4;
}

.close-btn {
  background: none;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  cursor: pointer;
}

.close-btn:hover {
  background-color: #f0f0f0;
  color: #f72585;
}

.panel-content {
  flex: 1;
  overflow-y: auto;
  padding: 15px 20px;
}

.no-data {
  text-align: center;
  padding: 30px;
  color: #6c757d;
  font-style: italic;
}

.product-table {
  width: 100%;
  border-collapse: collapse;
}

.product-table th {
  text-align: left;
  padding: 10px;
  border-bottom: 2px solid #eee;
  font-weight: 600;
  color: #555;
}

.product-table td {
  padding: 12px 10px;
  border-bottom: 1px solid #eee;
}

.action-btn {
  background-color: #4361ee;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 6px 12px;
  font-size: 12px;
  cursor: pointer;
}

.action-btn:hover {
  background-color: #3f37c9;
}

/* Loading spinner */
.loader-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 20;
}

.loader {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #4361ee;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>