<!-- src/components/dashboard/InventoryOverview.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Inventory Overview</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-6 mb-3">
          <div class="stat-item">
            <h3>{{ totalProducts }}</h3>
            <p>Total Products</p>
          </div>
        </div>
        <div class="col-6 mb-3">
          <div class="stat-item warning">
            <h3>{{ lowStockCount }}</h3>
            <p>Low Stock</p>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-item danger">
            <h3>{{ outOfStockCount }}</h3>
            <p>Out of Stock</p>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-item success">
            <h3>${{ totalValue.toLocaleString() }}</h3>
            <p>Inventory Value</p>
          </div>
        </div>
      </div>
      <div class="chart-container mt-3">
        <!-- Chart will be rendered here -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    products: Array
  },
  computed: {
    totalProducts() {
      return this.products.length;
    },
    lowStockCount() {
      return this.products.filter(p => p.stock > 0 && p.stock <= p.reorderLevel).length;
    },
    outOfStockCount() {
      return this.products.filter(p => p.stock === 0).length;
    },
    totalValue() {
      return this.products.reduce((total, product) => {
        return total + (product.price * product.stock);
      }, 0);
    }
  }
};
</script>

<style scoped>
.stat-item {
  padding: 10px;
  border-radius: 5px;
  background-color: #f8f9fa;
  text-align: center;
}

.stat-item h3 {
  margin-bottom: 5px;
  font-weight: 700;
}

.stat-item p {
  margin-bottom: 0;
  color: #6c757d;
}

.stat-item.warning h3 {
  color: #ffc107;
}

.stat-item.danger h3 {
  color: #dc3545;
}

.stat-item.success h3 {
  color: #28a745;
}

.chart-container {
  height: 200px;
}
</style>