<!-- src/components/dashboard/TopProducts.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Top Products</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Value</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in sortedProducts" :key="product.id">
              <td>{{ product.name }}</td>
              <td>{{ product.category }}</td>
              <td>${{ product.price.toFixed(2) }}</td>
              <td>{{ product.stock }}</td>
              <td>${{ (product.price * product.stock).toFixed(2) }}</td>
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
      return [...this.products].sort((a, b) => {
        return (b.price * b.stock) - (a.price * a.stock);
      });
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