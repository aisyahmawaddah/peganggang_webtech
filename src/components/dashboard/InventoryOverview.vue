<template>
  <div class="inventory-overview">
    <div class="overview-header">
      <h2 class="section-title">Inventory Overview</h2>
      <button 
        @click="refreshData" 
        class="refresh-btn"
        :disabled="isRefreshing"
        title="Refresh data"
      >
        <i class="bi bi-arrow-clockwise" :class="{ 'spinning': isRefreshing }"></i>
      </button>
    </div>

    <!-- Show either stats grid OR product panel, not both -->
    <div v-if="!showPanel" class="stats-grid">
      <!-- Row 1: Total Products | Categories -->
      <div
        class="stat-card"
        @click="showProductList('all')"
        :class="{ active: activeCard === 'all' }"
      >
        <div class="stat-info">
          <div class="stat-value blue">{{ totalProducts }}</div>
          <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-trend" v-if="totalProductsTrend !== 0">
          <i
            :class="
              totalProductsTrend > 0
                ? 'bi bi-arrow-up trend-up'
                : 'bi bi-arrow-down trend-down'
            "
          ></i>
          <span :class="totalProductsTrend > 0 ? 'trend-up' : 'trend-down'">
            {{ Math.abs(totalProductsTrend) }}%
          </span>
        </div>
      </div>

      <div
        class="stat-card"
        @click="showProductList('categories')"
        :class="{ active: activeCard === 'categories' }"
      >
        <div class="stat-info">
          <div class="stat-value blue">{{ categories.length }}</div>
          <div class="stat-label">Categories</div>
        </div>
      </div>

      <!-- Row 2: Low Stock | Out of Stock -->
      <div
        class="stat-card alert-card"
        @click="showProductList('low')"
        :class="{ active: activeCard === 'low' }"
      >
        <div class="stat-info">
          <div class="stat-value yellow">{{ lowStockCount }}</div>
          <div class="stat-label">Low Stock</div>
        </div>
        <div class="stat-trend" v-if="lowStockTrend !== 0">
          <i
            :class="
              lowStockTrend < 0
                ? 'bi bi-arrow-up trend-up'
                : 'bi bi-arrow-down trend-down'
            "
          ></i>
          <span :class="lowStockTrend < 0 ? 'trend-up' : 'trend-down'">
            {{ Math.abs(lowStockTrend) }}%
          </span>
        </div>
      </div>

      <div
        class="stat-card alert-card"
        @click="showProductList('out')"
        :class="{ active: activeCard === 'out' }"
      >
        <div class="stat-info">
          <div class="stat-value red">{{ outOfStockCount }}</div>
          <div class="stat-label">Out of Stock</div>
        </div>
        <div class="stat-trend" v-if="outOfStockTrend !== 0">
          <i
            :class="
              outOfStockTrend < 0
                ? 'bi bi-arrow-up trend-up'
                : 'bi bi-arrow-down trend-down'
            "
          ></i>
          <span :class="outOfStockTrend < 0 ? 'trend-up' : 'trend-down'">
            {{ Math.abs(outOfStockTrend) }}%
          </span>
        </div>
      </div>

      <!-- Row 3: Total Sales | Inventory Value -->
      <div
        class="stat-card"
        @click="showProductList('sales')"
        :class="{ active: activeCard === 'sales' }"
      >
        <div class="stat-info">
          <div class="stat-value green">
            RM{{ totalSales.toLocaleString("en-US", {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            }) }}
          </div>
          <div class="stat-label">Total Sales</div>
        </div>
        <div class="stat-trend" v-if="salesTrend !== 0">
          <i
            :class="
              salesTrend > 0
                ? 'bi bi-arrow-up trend-up'
                : 'bi bi-arrow-down trend-down'
            "
          ></i>
          <span :class="salesTrend > 0 ? 'trend-up' : 'trend-down'">
            {{ Math.abs(salesTrend) }}%
          </span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-value purple">
            RM{{ inventoryValue.toLocaleString("en-US", {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            }) }}
          </div>
          <div class="stat-label">Inventory Value</div>
        </div>
        <div class="inventory-detail">
          <div class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: inventoryHealthPercent + '%' }"
            ></div>
          </div>
          <div class="inventory-health">
            {{ inventoryHealthPercent }}% Health
          </div>
        </div>
      </div>
    </div>

    <!-- Product List Panel - Now appears inside the card -->
    <div v-if="showPanel" class="product-panel-inside">
      <div class="panel-header">
        <h3 class="panel-title">{{ panelTitle }}</h3>
        <div class="panel-actions">
          <!-- Show "Back to Categories" if viewing products within a category -->
          <button 
            v-if="activeCard === 'categories' && selectedCategory" 
            @click="backToCategories" 
            class="back-to-categories-btn"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Back to Categories</span>
          </button>
          <button @click="closePanel" class="close-btn">
            <i class="bi bi-arrow-left"></i>
            <span>Back</span>
          </button>
        </div>
      </div>
      <div class="panel-content">
        <div v-if="filteredProducts.length === 0" class="no-data">
          <i class="bi bi-inbox"></i>
          <p>No products found</p>
        </div>
        <div v-else class="product-list">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="product-item"
            @click="navigateToUpdatePage(product)"
          >
            <div class="product-info">
              <div class="product-name">{{ product.name }}</div>
              <div class="product-category">{{ product.category }}</div>
            </div>
            <div class="product-stats">
              <div class="product-price" v-if="!product.isCategory">RM{{ product.price }}</div>
              <div class="product-stock" :class="getStockClass(product)">
                {{ product.isCategory ? `${product.stock} products` : `${product.stock} units` }}
              </div>
            </div>
            <div class="product-action">
              <i class="bi bi-chevron-right"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity Section - Only show when panel is closed -->
    <div class="recent-activity" v-if="!showPanel && recentUpdates.length > 0">
      <div class="section-title">Recent Activity</div>
      <div class="activity-list">
        <div
          v-for="(update, index) in recentUpdates"
          :key="index"
          class="activity-item"
        >
          <div
            class="activity-icon"
            :class="getActivityIconClass(update.type)"
          >
            <i :class="getActivityIcon(update.type)"></i>
          </div>
          <div class="activity-content">
            <div class="activity-text">{{ getActivityText(update) }}</div>
            <div class="activity-time">
              {{ formatTimeAgo(update.timestamp) }}
            </div>
          </div>
        </div>
        <div v-if="recentUpdates.length === 0" class="no-activity">
          <i class="bi bi-clock-history"></i>
          <p>No recent activity</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'InventoryOverview',
  props: {
    products: {
      type: Array,
      default: () => []
    },
    updates: {
      type: Array,
      default: () => []
    },
    onNavigateToUpdate: {
      type: Function,
      default: null,
    },
  },
  emits: ['navigate-to-update'],
  data() {
    return {
      activeCard: null,
      showPanel: false,
      isRefreshing: false,
      totalProductsTrend: 0,
      lowStockTrend: 0,
      outOfStockTrend: 0,
      salesTrend: 0,
      lastUpdated: new Date(),
      selectedCategory: null, // For tracking which category is selected
    };
  },
  computed: {
    totalProducts() {
      return this.products.length;
    },
    lowStockCount() {
      return this.products.filter(
        (product) => product.stock > 0 && product.stock <= product.reorder_level
      ).length;
    },
    outOfStockCount() {
      return this.products.filter((product) => product.stock <= 0).length;
    },
    totalSales() {
      return this.products.reduce((total, product) => total + (product.sales || 0), 0);
    },
    inventoryValue() {
      return this.products.reduce(
        (total, product) => total + product.price * product.stock,
        0
      );
    },
    categories() {
      return [...new Set(this.products.map((product) => product.category))];
    },
    inventoryHealthPercent() {
      if (this.products.length === 0) return 100;
      const healthy = this.products.filter(
        (p) => p.stock > p.reorder_level * 1.5
      ).length;
      return Math.round((healthy / this.products.length) * 100);
    },
    filteredProducts() {
      if (this.activeCard === "all") return [...this.products];
      if (this.activeCard === "low") {
        return this.products.filter(
          (p) => p.stock > 0 && p.stock <= p.reorder_level
        );
      }
      if (this.activeCard === "out") {
        return this.products.filter((p) => p.stock <= 0);
      }
      if (this.activeCard === "sales") {
        return [...this.products].sort((a, b) => (b.sales || 0) - (a.sales || 0));
      }
      if (this.activeCard === "categories") {
        // If a specific category is selected, show products from that category
        if (this.selectedCategory) {
          return this.products.filter((p) => p.category === this.selectedCategory);
        }
        // Otherwise, show list of categories with product counts
        return this.categories.map((category) => ({
          id: `category-${category}`,
          name: category,
          category: "Category",
          price: "-",
          stock: this.getProductCountByCategory(category),
          isCategory: true,
        }));
      }
      if (this.activeCard === "category-products") {
        // Show products from selected category
        return this.products.filter((p) => p.category === this.selectedCategory);
      }
      return [];
    },
    panelTitle() {
      const titles = {
        all: "All Products",
        low: "Low Stock Products",
        out: "Out of Stock Products",
        sales: "Top Selling Products",
        categories: this.selectedCategory ? `${this.selectedCategory} Products` : "Categories",
        "category-products": `${this.selectedCategory} Products`,
      };
      return titles[this.activeCard] || "";
    },
    lastUpdatedText() {
      return this.formatTime(this.lastUpdated);
    },
    recentUpdates() {
      return this.updates.slice(0, 5);
    },
  },
  watch: {
    // Update lastUpdated when props change
    products: {
      handler() {
        this.lastUpdated = new Date();
      },
      deep: true
    },
    updates: {
      handler() {
        this.lastUpdated = new Date();
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
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        this.lastUpdated = new Date();
      } catch (error) {
        console.error('Error refreshing data:', error);
      } finally {
        this.isRefreshing = false;
      }
    },
    showProductList(type) {
      this.activeCard = type;
      this.showPanel = true;
    },
    backToCategories() {
      this.selectedCategory = null; // Clear selected category to show category list
    },
    closePanel() {
      this.showPanel = false;
      this.activeCard = null;
      this.selectedCategory = null; // Reset selected category
    },
    navigateToUpdatePage(product) {
      // If it's a category item, show products in that category
      if (product.isCategory) {
        this.selectedCategory = product.name;
        this.activeCard = "categories"; // Stay in categories mode but show products
        return;
      }
      
      // Store the product to edit
      localStorage.setItem("editProduct", JSON.stringify(product));
      
      // Use the prop function if available
      if (this.onNavigateToUpdate) {
        this.onNavigateToUpdate(product);
        return;
      }
      
      // Emit event to parent
      this.$emit('navigate-to-update', product);
      
      // Fallback: Find the root component with currentPage
      let appComponent = this.$root;
      if (appComponent && typeof appComponent.currentPage !== "undefined") {
        appComponent.currentPage = "update";
      } else {
        console.error("Could not find App component with currentPage property");
      }
    },
    getProductCountByCategory(category) {
      return this.products.filter((p) => p.category === category).length;
    },
    getStockClass(product) {
      if (product.isCategory) return '';
      if (product.stock === 0) return 'stock-out';
      if (product.stock <= product.reorder_level) return 'stock-low';
      return 'stock-good';
    },
    formatTime(date) {
      const now = new Date();
      const diff = now - new Date(date);
      const min = Math.round(diff / 60000);
      if (min < 1) return "Just now";
      if (min < 60) return `${min} minute${min !== 1 ? "s" : ""} ago`;
      const hr = Math.round(min / 60);
      if (hr < 24) return `${hr} hour${hr !== 1 ? "s" : ""} ago`;
      const day = Math.round(hr / 24);
      return `${day} day${day !== 1 ? "s" : ""} ago`;
    },
    getActivityText(update) {
      const productName = update.product_name || update.current_product_name || `Product ${update.product_id}`;
      
      switch (update.type) {
        case 'sale':
          return `${productName} sold ${update.old_quantity - update.new_quantity} units`;
        case 'restock':
          return `${productName} restocked with ${update.new_quantity - update.old_quantity} units`;
        case 'edit':
        case 'update':
          return `${productName} details updated`;
        case 'add':
          return `${productName} added to inventory`;
        case 'delete':
          return `${productName} removed from inventory`;
        case 'name_change':
          return `Product renamed to ${update.new_name || productName}`;
        case 'price_change':
          return `${productName} price updated`;
        case 'category_change':
          return `${productName} category changed`;
        default:
          return `${productName} updated`;
      }
    },
    getActivityIcon(type) {
      const icons = {
        sale: 'bi bi-cart-dash',
        restock: 'bi bi-cart-plus',
        edit: 'bi bi-pencil-square',
        update: 'bi bi-pencil-square',
        add: 'bi bi-plus-circle',
        delete: 'bi bi-trash',
        name_change: 'bi bi-tag',
        price_change: 'bi bi-currency-dollar',
        category_change: 'bi bi-folder'
      };
      return icons[type] || 'bi bi-arrow-clockwise';
    },
    getActivityIconClass(type) {
      const classes = {
        sale: 'icon-red',
        restock: 'icon-green',
        edit: 'icon-blue',
        update: 'icon-blue',
        add: 'icon-green',
        delete: 'icon-red',
        name_change: 'icon-blue',
        price_change: 'icon-yellow',
        category_change: 'icon-purple'
      };
      return classes[type] || 'icon-blue';
    },
    formatTimeAgo(timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diff = now - date;
      const minutes = Math.floor(diff / 60000);
      
      if (minutes < 1) return 'Just now';
      if (minutes < 60) return `${minutes}m ago`;
      
      const hours = Math.floor(minutes / 60);
      if (hours < 24) return `${hours}h ago`;
      
      const days = Math.floor(hours / 24);
      return `${days}d ago`;
    }
  }
};
</script>

<style scoped>
.inventory-overview {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  color: white;
  height: 100%;
}

.overview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-title {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0;
  color: #ffffff;
}

.refresh-btn {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.refresh-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr; /* 2 columns */
  grid-template-rows: repeat(3, 1fr); /* 3 rows */
  gap: 15px;
  margin-bottom: 20px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.stat-card:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.stat-card.active {
  background: rgba(0, 123, 255, 0.2);
  border-color: #007bff;
}

/* Alert cards with pulsing red border animation */
.alert-card {
  border: 2px solid #ff4757;
  box-shadow: 0 0 20px rgba(255, 71, 87, 0.3);
  animation: pulseAlert 2s infinite;
}

@keyframes pulseAlert {
  0% {
    border-color: #ff4757;
    box-shadow: 0 0 20px rgba(255, 71, 87, 0.3);
  }
  50% {
    border-color: #ff6b7a;
    box-shadow: 0 0 30px rgba(255, 71, 87, 0.6);
  }
  100% {
    border-color: #ff4757;
    box-shadow: 0 0 20px rgba(255, 71, 87, 0.3);
  }
}

.alert-card:hover {
  background: rgba(255, 255, 255, 0.12);
  transform: translateY(-2px);
  /* Keep the alert animation even on hover */
  animation: pulseAlert 2s infinite;
}

.stat-info {
  margin-bottom: 8px;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-value.blue { color: #87CEEB; } /* Sky Blue - light and visible */
.stat-value.yellow { color: #FFE135; } /* Bright Yellow - stands out well */
.stat-value.red { color: #FF6B6B; } /* Coral Red - soft but visible */
.stat-value.green { color: #98FB98; } /* Pale Green - light pastel */
.stat-value.purple { color: #DDA0DD; } /* Plum - soft purple pastel */

.stat-label {
  font-size: 0.85rem;
  color: #ccc;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
}

.trend-up { color: #28a745; }
.trend-down { color: #dc3545; }

.inventory-detail {
  margin-top: 8px;
}

.progress-bar {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  height: 6px;
  margin-bottom: 6px;
}

.progress-fill {
  background: linear-gradient(90deg, #28a745, #20c997);
  border-radius: 4px;
  height: 100%;
  transition: width 0.3s ease;
}

.inventory-health {
  font-size: 0.75rem;
  color: #ccc;
}

/* NEW: Product Panel Inside Styles - Replace the old fixed sidebar */
.product-panel-inside {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  min-height: 400px;
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px 8px 0 0;
}

.panel-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.back-to-categories-btn {
  background: rgba(74, 158, 255, 0.2);
  border: 1px solid rgba(74, 158, 255, 0.3);
  color: #4a9eff;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.85rem;
  transition: all 0.3s ease;
}

.back-to-categories-btn:hover {
  background: rgba(74, 158, 255, 0.3);
  border-color: rgba(74, 158, 255, 0.4);
}

.panel-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  color: white;
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.panel-content {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
  max-height: 350px;
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.product-item:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.15);
  transform: translateX(4px);
}

.product-info {
  flex: 1;
}

.product-name {
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
  font-size: 0.95rem;
}

.product-category {
  font-size: 0.8rem;
  color: #ccc;
}

.product-stats {
  text-align: right;
}

.product-price {
  font-weight: 600;
  color: #4a9eff;
  margin-bottom: 4px;
  font-size: 0.9rem;
}

.product-stock {
  font-size: 0.8rem;
}

.stock-good { color: #28a745; }
.stock-low { color: #ffc107; }
.stock-out { color: #dc3545; }

.product-action {
  color: #ccc;
  font-size: 0.9rem;
}

/* Recent Activity Styles */
.recent-activity {
  margin-top: 20px;
}

.activity-list {
  margin-top: 12px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

.icon-blue { background: rgba(74, 158, 255, 0.2); color: #4a9eff; }
.icon-green { background: rgba(40, 167, 69, 0.2); color: #28a745; }
.icon-red { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
.icon-yellow { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
.icon-purple { background: rgba(111, 66, 193, 0.2); color: #6f42c1; }

.activity-content {
  flex: 1;
}

.activity-text {
  font-size: 0.9rem;
  color: white;
  margin-bottom: 2px;
}

.activity-time {
  font-size: 0.75rem;
  color: #ccc;
}

.no-data, .no-activity {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.no-data i, .no-activity i {
  font-size: 2rem;
  margin-bottom: 10px;
  display: block;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-value {
    font-size: 1.4rem;
  }
  
  .product-panel-inside {
    min-height: 300px;
  }
  
  .panel-content {
    max-height: 250px;
  }
}
</style>