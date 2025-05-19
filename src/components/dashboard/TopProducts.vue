<!-- src/components/dashboard/TopProducts.vue -->
<template>
  <div class="top-products">
    <h3 class="card-title">Top Products</h3>
    
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
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
          <tr v-for="product in topProducts" :key="product.id">
            <td>{{ product.name }}</td>
            <td>{{ product.category }}</td>
            <td>${{ product.price.toFixed(2) }}</td>
            <td>{{ product.stock }}</td>
            <td>{{ product.sold }}</td>
            <td>${{ product.sales.toFixed(2) }}</td>
            <td>
              <span class="status-badge" :class="getStatusClass(product)">
                {{ getStatusText(product) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TopProducts',
  data() {
    return {
      products: []
    };
  },
  computed: {
    topProducts() {
      // Sort products by sales value (highest first) and return top 5
      return [...this.products]
        .sort((a, b) => b.sales - a.sales)
        .slice(0, 5);
    }
  },
  created() {
    // Fetch product data from mockData
    this.fetchProducts();
  },
  methods: {
    fetchProducts() {
      // In a real application, this would be an API call
      // Now we directly use the products with their sold and sales attributes
      const mockData = require('@/data/mockData').default;
      this.products = mockData.products;
    },
    getStatusClass(product) {
      if (product.stock <= 0) {
        return 'out-of-stock';
      } else if (product.stock <= product.reorderLevel) {
        return 'low-stock';
      } else {
        return 'in-stock';
      }
    },
    getStatusText(product) {
      if (product.stock <= 0) {
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
.top-products {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 20px;
  height: 100%;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 20px;
  color: #333;
}

.table-responsive {
  overflow-y: auto;
  flex-grow: 1;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  text-align: left;
  padding: 12px 16px;
  border-bottom: 1px solid #eee;
  font-weight: 600;
  color: #555;
}

td {
  padding: 12px 16px;
  border-bottom: 1px solid #eee;
}

tr:hover {
  background-color: #f9fafb;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.in-stock {
  background-color: #e6f4ea;
  color: #137333;
}

.low-stock {
  background-color: #fef7e0;
  color: #b06000;
}

.out-of-stock {
  background-color: #fce8e6;
  color: #c5221f;
}
</style>