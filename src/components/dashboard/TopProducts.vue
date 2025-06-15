<template>
  <div class="card top-products">
    <div class="card-header">
      <div class="header-content">
        <h5 class="card-title">Top Products</h5>
        <div class="controls">
          <select v-model="sortBy" class="sort-select">
            <option value="sales">By Sales</option>
            <option value="sold">By Units Sold</option>
            <option value="stock">By Stock Level</option>
            <option value="value">By Inventory Value</option>
          </select>
          <select v-model="displayCount" class="count-select">
            <option value="5">Top 5</option>
            <option value="10">Top 10</option>
            <option value="15">Top 15</option>
            <option value="all">Show All</option>
          </select>
          <button @click="refreshData" class="refresh-btn" title="Refresh data">
            <i class="bi bi-arrow-clockwise" :class="{ 'spinning': isRefreshing }"></i>
          </button>
        </div>
      </div>
    </div>
    
    <div class="card-body">
      <!-- Summary Stats -->
      <div class="summary-stats" v-if="hasData">
        <div class="stat-item">
          <div class="stat-value">{{ totalProducts }}</div>
          <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">RM{{ totalSales.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
          <div class="stat-label">Total Sales</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ totalSold.toLocaleString() }}</div>
          <div class="stat-label">Units Sold</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">RM{{ averagePrice.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
          <div class="stat-label">Avg Price</div>
        </div>
      </div>

      <!-- Products Table/List -->
      <div class="products-container">
        <div v-if="isLoading || isRefreshing" class="loading">
          <div class="spinner"></div>
          <p>Loading products...</p>
        </div>
        
        <div v-else-if="!hasData" class="no-data">
          <i class="bi bi-box"></i>
          <p>No products available</p>
          <small>Products will appear here once they are added to inventory</small>
        </div>
        
        <div v-else class="products-table">
          <!-- Table Header -->
          <div class="table-header">
            <div class="header-cell rank">#</div>
            <div class="header-cell product">Product</div>
            <div class="header-cell category">Category</div>
            <div class="header-cell price">Price</div>
            <div class="header-cell stock">Stock</div>
            <div class="header-cell sales">Sales</div>
            <div class="header-cell performance">Performance</div>
          </div>
          
          <!-- Table Body -->
          <div class="table-body">
            <div
              v-for="(product, index) in displayedProducts"
              :key="product.id"
              class="product-row"
              :class="{ 
                'top-performer': index < 3,
                'low-stock': product.stock <= product.reorder_level,
                'out-of-stock': product.stock <= 0
              }"
              @click="viewProductDetails(product)"
            >
              <!-- Rank -->
              <div class="cell rank">
                <div class="rank-badge" :class="getRankClass(index)">
                  {{ index + 1 }}
                </div>
              </div>
              
              <!-- Product Info -->
              <div class="cell product">
                <div class="product-info">
                  <div class="product-name">{{ product.name }}</div>
                  <div class="product-id">ID: {{ product.id }}</div>
                </div>
              </div>
              
              <!-- Category -->
              <div class="cell category">
                <span class="category-badge">{{ product.category }}</span>
              </div>
              
              <!-- Price -->
              <div class="cell price">
                <div class="price-display">RM{{ product.price.toFixed(2) }}</div>
              </div>
              
              <!-- Stock -->
              <div class="cell stock">
                <div class="stock-info">
                  <div class="stock-amount" :class="getStockClass(product)">
                    {{ product.stock }}
                  </div>
                  <div class="stock-status">
                    {{ getStockStatus(product) }}
                  </div>
                </div>
              </div>
              
              <!-- Sales -->
              <div class="cell sales">
                <div class="sales-info">
                  <div class="sales-amount">RM{{ (product.sales || 0).toFixed(2) }}</div>
                  <div class="units-sold">{{ (product.sold || 0) }} units</div>
                </div>
              </div>
              
              <!-- Performance -->
              <div class="cell performance">
                <div class="performance-indicator">
                  <div class="performance-bar">
                    <div 
                      class="performance-fill" 
                      :style="{ width: getPerformancePercent(product) + '%' }"
                      :class="getPerformanceClass(product)"
                    ></div>
                  </div>
                  <div class="performance-text">{{ getPerformancePercent(product) }}%</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- View All Button -->
          <div v-if="hasMoreProducts" class="view-all">
            <button @click="showAllProducts" class="view-all-btn">
              <i class="bi bi-eye"></i>
              View All {{ totalProducts }} Products
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TopProducts',
  props: {
    products: {
      type: Array,
      default: () => []
    }
  },
  emits: ['view-product', 'refresh-data'],
  data() {
    return {
      sortBy: 'sales',
      displayCount: '10',
      isLoading: false,
      isRefreshing: false
    };
  },
  computed: {
    hasData() {
      return this.products && this.products.length > 0;
    },
    totalProducts() {
      return this.products.length;
    },
    totalSales() {
      return this.products.reduce((sum, product) => sum + (product.sales || 0), 0);
    },
    totalSold() {
      return this.products.reduce((sum, product) => sum + (product.sold || 0), 0);
    },
    averagePrice() {
      if (this.totalProducts === 0) return 0;
      return this.products.reduce((sum, product) => sum + (product.price || 0), 0) / this.totalProducts;
    },
    sortedProducts() {
      if (!this.hasData) return [];
      
      const sorted = [...this.products].sort((a, b) => {
        switch (this.sortBy) {
          case 'sales':
            return (b.sales || 0) - (a.sales || 0);
          case 'sold':
            return (b.sold || 0) - (a.sold || 0);
          case 'stock':
            return (b.stock || 0) - (a.stock || 0);
          case 'value':
            return (b.price * b.stock) - (a.price * a.stock);
          default:
            return (b.sales || 0) - (a.sales || 0);
        }
      });
      
      return sorted;
    },
    displayedProducts() {
      if (this.displayCount === 'all') {
        return this.sortedProducts;
      }
      
      const count = parseInt(this.displayCount);
      return this.sortedProducts.slice(0, count);
    },
    hasMoreProducts() {
      return this.displayCount !== 'all' && this.sortedProducts.length > parseInt(this.displayCount);
    },
    maxSales() {
      if (!this.hasData) return 1;
      return Math.max(...this.products.map(p => p.sales || 0)) || 1;
    }
  },
  watch: {
    products: {
      handler() {
        // Auto-refresh when products change
        this.refreshData();
      },
      deep: true
    }
  },
  methods: {
    async refreshData() {
      this.isRefreshing = true;
      try {
        // Emit event to parent to refresh data
        this.$emit('refresh-data');
        
        // Wait a bit for the refresh to complete
        await new Promise(resolve => setTimeout(resolve, 500));
      } catch (error) {
        console.error('Error refreshing data:', error);
      } finally {
        this.isRefreshing = false;
      }
    },
    
    showAllProducts() {
      this.displayCount = 'all';
    },
    
    viewProductDetails(product) {
      this.$emit('view-product', product);
    },
    
    getRankClass(index) {
      if (index === 0) return 'rank-gold';
      if (index === 1) return 'rank-silver';
      if (index === 2) return 'rank-bronze';
      return 'rank-default';
    },
    
    getStockClass(product) {
      if (product.stock <= 0) return 'stock-out';
      if (product.stock <= product.reorder_level) return 'stock-low';
      return 'stock-good';
    },
    
    getStockStatus(product) {
      if (product.stock <= 0) return 'Out of Stock';
      if (product.stock <= product.reorder_level) return 'Low Stock';
      if (product.stock > product.reorder_level * 2) return 'Well Stocked';
      return 'In Stock';
    },
    
    getPerformancePercent(product) {
      const sales = product.sales || 0;
      return Math.round((sales / this.maxSales) * 100);
    },
    
    getPerformanceClass(product) {
      const percent = this.getPerformancePercent(product);
      if (percent >= 80) return 'performance-excellent';
      if (percent >= 60) return 'performance-good';
      if (percent >= 40) return 'performance-average';
      return 'performance-poor';
    }
  }
};
</script>

<style scoped>
.top-products {
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
  gap: 15px;
}

.card-title {
  color: #ffffff;
  font-weight: 600;
  margin: 0;
  font-size: 1.1rem;
}

.controls {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.sort-select, .count-select {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 0.9rem;
}

.sort-select option, .count-select option {
  background: #2a2a2a;
  color: white;
}

.refresh-btn {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.refresh-btn:hover {
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

.summary-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  padding: 20px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 25px;
}

.stat-item {
  text-align: center;
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

.products-container {
  min-height: 400px;
}

.loading, .no-data {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 60px 20px;
  text-align: center;
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
}

.products-table {
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.table-header {
  display: grid;
  grid-template-columns: 60px 2fr 1fr 120px 120px 150px 150px;
  background: rgba(255, 255, 255, 0.1);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.header-cell {
  padding: 15px 10px;
  font-weight: 600;
  font-size: 0.9rem;
  color: #ccc;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.table-body {
  max-height: 600px;
  overflow-y: auto;
}

.product-row {
  display: grid;
  grid-template-columns: 60px 2fr 1fr 120px 120px 150px 150px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
  cursor: pointer;
}

.product-row:hover {
  background: rgba(255, 255, 255, 0.05);
}

.product-row.top-performer {
  background: rgba(255, 215, 0, 0.05);
}

.product-row.low-stock {
  border-left: 3px solid #ffc107;
}

.product-row.out-of-stock {
  border-left: 3px solid #dc3545;
  background: rgba(220, 53, 69, 0.05);
}

.cell {
  padding: 15px 10px;
  display: flex;
  align-items: center;
  font-size: 0.9rem;
}

.rank {
  justify-content: center;
}

.rank-badge {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
}

.rank-gold { background: linear-gradient(45deg, #ffd700, #ffed4e); color: #000; }
.rank-silver { background: linear-gradient(45deg, #c0c0c0, #e8e8e8); color: #000; }
.rank-bronze { background: linear-gradient(45deg, #cd7f32, #deb887); color: #fff; }
.rank-default { background: rgba(255, 255, 255, 0.1); color: #ccc; }

.product-info {
  display: flex;
  flex-direction: column;
}

.product-name {
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.product-id {
  font-size: 0.8rem;
  color: #999;
}

.category-badge {
  background: rgba(74, 158, 255, 0.2);
  color: #4a9eff;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.price-display {
  font-weight: 600;
  color: #28a745;
  font-size: 1rem;
}

.stock-info {
  display: flex;
  flex-direction: column;
}

.stock-amount {
  font-weight: 600;
  margin-bottom: 2px;
}

.stock-good { color: #28a745; }
.stock-low { color: #ffc107; }
.stock-out { color: #dc3545; }

.stock-status {
  font-size: 0.8rem;
  color: #999;
}

.sales-info {
  display: flex;
  flex-direction: column;
}

.sales-amount {
  font-weight: 600;
  color: #4a9eff;
  margin-bottom: 2px;
}

.units-sold {
  font-size: 0.8rem;
  color: #999;
}

.performance-indicator {
  width: 100%;
}

.performance-bar {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 4px;
}

.performance-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.performance-excellent { background: linear-gradient(90deg, #28a745, #20c997); }
.performance-good { background: linear-gradient(90deg, #4a9eff, #6f42c1); }
.performance-average { background: linear-gradient(90deg, #ffc107, #fd7e14); }
.performance-poor { background: linear-gradient(90deg, #dc3545, #e83e8c); }

.performance-text {
  font-size: 0.8rem;
  color: #ccc;
  text-align: center;
}

.view-all {
  padding: 20px;
  text-align: center;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.view-all-btn {
  background: rgba(74, 158, 255, 0.2);
  border: 1px solid rgba(74, 158, 255, 0.4);
  color: #4a9eff;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 auto;
}

.view-all-btn:hover {
  background: rgba(74, 158, 255, 0.3);
  border-color: rgba(74, 158, 255, 0.6);
}

/* Scrollbar styling */
.table-body::-webkit-scrollbar {
  width: 6px;
}

.table-body::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.table-body::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
}

.table-body::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Responsive adjustments */
@media (max-width: 1200px) {
  .table-header, .product-row {
    grid-template-columns: 50px 2fr 1fr 100px 100px 130px 120px;
  }
  
  .header-cell, .cell {
    padding: 12px 8px;
    font-size: 0.85rem;
  }
}

@media (max-width: 968px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .controls {
    width: 100%;
    justify-content: space-between;
  }
  
  .table-header, .product-row {
    grid-template-columns: 1fr;
    grid-template-rows: auto;
  }
  
  .product-row {
    padding: 15px;
    display: block;
  }
  
  .cell {
    padding: 5px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  
  .cell:last-child {
    border-bottom: none;
  }
  
  .rank {
    justify-content: flex-start;
  }
  
  .summary-stats {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .summary-stats {
    grid-template-columns: 1fr;
  }
  
  .controls {
    flex-direction: column;
    gap: 8px;
  }
  
  .sort-select, .count-select {
    width: 100%;
  }
}
</style>