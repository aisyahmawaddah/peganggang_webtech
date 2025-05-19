<!-- src/components/UpdatePage.vue -->
<template>
  <div>
    <h1 class="mb-4">Inventory Management</h1>
    
    <!-- Tabs for switching between Edit and Add modes -->
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <a class="nav-link" :class="{ active: mode === 'edit' }" href="#" @click.prevent="mode = 'edit'">
          <i class="bi bi-pencil-square"></i> Edit Existing Item
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{ active: mode === 'add' }" href="#" @click.prevent="mode = 'add'">
          <i class="bi bi-plus-circle"></i> Add New Item
        </a>
      </li>
    </ul>
    
    <div class="row">
      <!-- Left side: Edit/Add Form -->
      <div class="col-md-7">
        <!-- Edit Product Form -->
        <product-editor 
          v-if="mode === 'edit'" 
          :products="products" 
          @update="updateProduct"
        ></product-editor>
        
        <!-- Add Product Form -->
        <add-product-form
          v-if="mode === 'add'" 
          :categories="getCategories()" 
          @add="addProduct"
        ></add-product-form>
      </div>
      
      <!-- Right side: Products List -->
      <div class="col-md-5">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
              <i class="bi bi-list-ul"></i> Product Inventory
              <span class="badge bg-light text-dark float-end">{{ products.length }} items</span>
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="inventory-list">
              <div v-for="product in products" :key="product.id" 
                   class="inventory-item" 
                   :class="{ 'low-stock': product.stock <= product.reorderLevel, 'out-of-stock': product.stock === 0 }">
                <div class="item-details">
                  <h6 class="mb-1">{{ product.name }}</h6>
                  <div class="item-meta">
                    <span class="category">{{ product.category }}</span>
                    <span class="price">${{ product.price.toFixed(2) }}</span>
                  </div>
                </div>
                <div class="item-stock">
                  <div class="stock-count" :class="getStockClass(product)">
                    {{ product.stock }}
                  </div>
                  <small>in stock</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ProductEditor from './update/ProductEditor.vue';
import AddProductForm from './update/AddProductForm.vue';

export default {
  components: {
    ProductEditor,
    AddProductForm
  },
  props: {
    products: Array
  },
  data() {
    return {
      mode: 'edit', // 'edit' or 'add'
      selectedProduct: null
    };
  },
  methods: {
    updateProduct(product) {
      this.$emit('update-product', product);
    },
    addProduct(product) {
      // Generate a new unique ID (highest ID + 1)
      const maxId = Math.max(...this.products.map(p => p.id), 0);
      const newProduct = {
        ...product,
        id: maxId + 1
      };
      
      // Emit event to parent component
      this.$emit('update-product', newProduct);
      
      // Switch back to edit mode after adding
      this.mode = 'edit';
      
      // Show success message
      alert(`Product "${newProduct.name}" added successfully!`);
    },
    getCategories() {
      // Get unique categories from products or use mockData
      if (this.products && this.products.length > 0) {
        const uniqueCategories = [...new Set(this.products.map(p => p.category))];
        return uniqueCategories.sort();
      } else {
        // Fallback to common categories
        return ["Electronics", "Accessories", "Gaming"];
      }
    },
    getStockClass(product) {
      if (product.stock === 0) {
        return 'text-danger';
      } else if (product.stock <= product.reorderLevel) {
        return 'text-warning';
      } else {
        return 'text-success';
      }
    }
  }
};
</script>

<style scoped>
.inventory-list {
  max-height: 500px;
  overflow-y: auto;
}

.inventory-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  border-bottom: 1px solid #e9ecef;
  transition: background-color 0.2s;
}

.inventory-item:last-child {
  border-bottom: none;
}

.inventory-item:hover {
  background-color: #f8f9fa;
}

.inventory-item.low-stock {
  background-color: rgba(255, 193, 7, 0.1);
}

.inventory-item.out-of-stock {
  background-color: rgba(220, 53, 69, 0.1);
}

.item-details {
  flex: 1;
}

.item-meta {
  display: flex;
  font-size: 0.85rem;
  color: #6c757d;
}

.item-meta .category {
  margin-right: 15px;
  padding: 2px 8px;
  background-color: #e9ecef;
  border-radius: 12px;
}

.item-meta .price {
  font-weight: 600;
}

.item-stock {
  text-align: center;
  min-width: 60px;
}

.stock-count {
  font-size: 1.25rem;
  font-weight: 700;
}

.text-success {
  color: #28a745 !important;
}

.text-warning {
  color: #ffc107 !important;
}

.text-danger {
  color: #dc3545 !important;
}

/* Tab styling */
.nav-tabs .nav-link {
  border: none;
  border-bottom: 3px solid transparent;
  padding: 0.75rem 1rem;
  color: #495057;
}

.nav-tabs .nav-link.active {
  border-color: #007bff;
  color: #007bff;
  background-color: transparent;
}

.nav-tabs .nav-link:hover {
  border-color: #dee2e6;
}

.card-header.bg-primary {
  border-bottom: 0;
}

.card-header.bg-success {
  border-bottom: 0;
}
</style>