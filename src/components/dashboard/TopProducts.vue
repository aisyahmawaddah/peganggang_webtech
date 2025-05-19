<!-- src/components/dashboard/TopProducts.vue -->
<template>
  <div class="card product-card">
    <div class="card-header">
      <h5 class="card-title">Top Products</h5>
    </div>
    <div class="card-body p-0"> <!-- Removed padding for better table appearance -->
      <div class="table-container">
        <table class="table mb-0"> <!-- Removed bottom margin -->
          <thead class="sticky-top bg-white">
            <tr>
              <th @click="sortBy('name')">
                Product Name
                <i v-if="sortKey === 'name'" :class="getSortIconClass()"></i>
              </th>
              <th @click="sortBy('category')">
                Category
                <i v-if="sortKey === 'category'" :class="getSortIconClass()"></i>
              </th>
              <th @click="sortBy('price')">
                Price
                <i v-if="sortKey === 'price'" :class="getSortIconClass()"></i>
              </th>
              <th @click="sortBy('stock')">
                Stock
                <i v-if="sortKey === 'stock'" :class="getSortIconClass()"></i>
              </th>
              <th @click="sortBy('sold')">
                Sold
                <i v-if="sortKey === 'sold'" :class="getSortIconClass()"></i>
              </th>
              <th @click="sortBy('sales')">
                Sales
                <i v-if="sortKey === 'sales'" :class="getSortIconClass()"></i>
              </th>
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
  data() {
    return {
      sortKey: 'sales', // Default sort by sales
      sortDir: -1 // -1 for descending, 1 for ascending
    };
  },
  computed: {
    sortedProducts() {
      return [...this.products].sort((a, b) => {
        let aValue = a[this.sortKey];
        let bValue = b[this.sortKey];
        
        // Handle string values (case-insensitive sorting)
        if (typeof aValue === 'string' && typeof bValue === 'string') {
          aValue = aValue.toLowerCase();
          bValue = bValue.toLowerCase();
        }
        
        if (aValue < bValue) return -1 * this.sortDir;
        if (aValue > bValue) return 1 * this.sortDir;
        return 0;
      });
    }
  },
  methods: {
    sortBy(key) {
      // If the same key is clicked, toggle the direction
      if (this.sortKey === key) {
        this.sortDir *= -1;
      } else {
        this.sortKey = key;
        // Default to descending for sales and sold, ascending for others
        this.sortDir = (key === 'sales' || key === 'sold') ? -1 : 1;
      }
    },
    getSortIconClass() {
      return this.sortDir === 1 
        ? 'bi bi-caret-up-fill' 
        : 'bi bi-caret-down-fill';
    },
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
  cursor: pointer;
  position: relative;
  background-color: #f8f9fa;
  padding-right: 20px; /* Space for sort icon */
}

th:hover {
  background-color: #e9ecef;
}

th i {
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.75rem;
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