<!-- src/components/dashboard/InventoryOverview.vue -->
<template>
  <div class="inventory-overview">
    <div class="card-header">
      <h3 class="card-title">Inventory Overview</h3>
      <div class="view-options">
        <button class="refresh-btn" @click="refreshData" title="Refresh Data">
          <i class="fas fa-sync-alt"></i>
        </button>
        <select v-model="timeRange" class="time-selector" @change="fetchProducts">
          <option value="all">All Time</option>
          <option value="year">This Year</option>
          <option value="quarter">This Quarter</option>
          <option value="month">This Month</option>
          <option value="week">This Week</option>
        </select>
      </div>
    </div>
    
    <div class="stat-grid">
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
</template>

<script>
export default {
  name: 'InventoryOverview',
  data() {
    return {
      products: [],
      timeRange: 'all',
      activeCard: null,
      showPanel: false,
      isLoading: false,
      totalProductsTrend: 3,  // Simulated trend values
      lowStockTrend: -5,
      outOfStockTrend: -2,
      salesTrend: 8
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
      
      // Otherwise, emit an event that the parent component can listen for
      this.$emit('navigate-to-update', product);
    }
  }
};
</script>

<style scoped>
.inventory-overview {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 20px;
  height: 100%;
  position: relative;
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.view-options {
  display: flex;
  gap: 10px;
  align-items: center;
  justify-content: center; /* Center horizontally */
}

.time-selector {
  padding: 6px 10px;
  border-radius: 4px;
  border: 1px solid #ddd;
  background-color: white;
  font-size: 14px;
  color: #555;
  text-align: center;
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

.stat-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin: 0 auto; /* Center the grid */
  max-width: 800px; /* Limit width for better centering */
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
  margin-bottom: 12px; /* Changed from margin-right to margin-bottom */
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
  color: #333; /* Default black color for Total Products */
}

.red {
  color: #f72585; /* Red for Out of Stock */
}

.orange {
  color: #f8961e; /* Orange for Low Stock */
}

.green {
  color: #137333; /* Green for Total Sales */
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
  text-align: center;
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
  text-align: center;
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
  text-align: center;
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
  margin: 0 auto; /* Center the table */
}

.product-table th {
  text-align: center; /* Center header text */
  padding: 10px;
  border-bottom: 2px solid #eee;
  font-weight: 600;
  color: #555;
}

.product-table td {
  padding: 12px 10px;
  border-bottom: 1px solid #eee;
  text-align: center; /* Center all cell content */
}

.action-btn {
  background-color: #4361ee;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 6px 12px;
  font-size: 12px;
  cursor: pointer;
  margin: 0 auto; /* Center the button */
  display: block;
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