<!-- src/components/dashboard/TopProducts.vue -->
<template>
  <div class="card product-card">
    <div class="card-header">
      <h5 class="card-title">Top Products</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-container">
        <table class="table mb-0">
          <thead class="sticky-top bg-white">
            <tr>
              <th>Product Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Sold</th>
              <th>Sales</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in sortedProducts" :key="product.id">
              <td>{{ product.name }}</td>
              <td>{{ product.category }}</td>
              <td>${{ product.price.toFixed(2) }}</td>
              <td>{{ product.stock }}</td>
              <td>{{ product.sold }}</td>
              <td>${{ product.sales.toFixed(2) }}</td>
              <td>
                <span class="badge" :class="getStatusClass(product)">
                  {{ getStatusText(product) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
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
    sortedProducts() {
      // Sort products by sales in descending order by default
      return [...this.products].sort((a, b) => b.sales - a.sales);
    }
  },
  methods: {
    getStatusClass(product) {
      if (product.stock === 0) {
        return 'bg-danger';
      } else if (product.stock <= product.reorderLevel) {
        return 'bg-warning';
      } else {
        return 'bg-success';
      }
    },
    getStatusText(product) {
      if (product.stock === 0) {
        return 'Out of Stock';
      } else if (product.stock <= product.reorderLevel) {
        return 'Low Stock';
      } else {
        return 'In Stock';
      }
    }
  }
};
</script>

<style scoped>
.product-card {
  display: flex;
  flex-direction: column;
  height: 400px; /* Fixed card height */
}

.card-body {
  flex: 1;
  overflow: hidden; /* Prevents content from overflowing */
}

.table-container {
  height: 100%;
  overflow-y: auto; /* Enables vertical scrolling */
}

th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.badge {
  padding: 0.5em 0.75em;
  font-weight: 500;
}

/* Make header sticky */
thead.sticky-top {
  position: sticky;
  top: 0;
  z-index: 1;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Ensure table takes up full space */
.table {
  width: 100%;
  margin: 0;
}

/* Style table rows */
tbody tr:hover {
  background-color: rgba(0,0,0,0.03);
}
</style>