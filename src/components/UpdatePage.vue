<!-- src/components/UpdatePage.vue -->
<template>
  <div>
    <h1 class="mb-4">FlexStock Inventory Management</h1>
    
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
          :initial-selected-product="selectedProduct"
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
          <div class="card-header text-white">
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
                <div class="item-stock text-center">
                <div class="stock-count" :class="getStockClass(product)">
                  {{ product.stock }}
                </div>
                <small class="d-block mb-2">in stock</small>

                <!-- Delete Button -->
                <button @click="deleteProduct(product)" class="btn btn-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </button>
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
      mode: 'edit',
      selectedProduct: null
    };
  },
  created() {
    // Check if there's a product in localStorage (from restock action)
    const editProduct = localStorage.getItem('editProduct');
    if (editProduct) {
      try {
        const product = JSON.parse(editProduct);
        
        // Find the product in the list of available products by ID
        const foundProduct = this.products.find(p => p.id === product.id);
        
        if (foundProduct) {
          // Set the selected product
          this.selectedProduct = foundProduct;
          
          // Ensure we're in edit mode
          this.mode = 'edit';
        }
      } catch (error) {
        console.error('Error parsing product from localStorage:', error);
      }
      
      // Clear localStorage after using it
      localStorage.removeItem('editProduct');
    }
  },
  methods: {
    updateProduct(product) {
      this.$emit('update-product', product);
    },
    addProduct(product) {
      const maxId = Math.max(...this.products.map(p => p.id), 0);
      const newProduct = {
        ...product,
        id: maxId + 1
      };
      this.$emit('update-product', newProduct);
      this.mode = 'edit';
      alert(`Product "${newProduct.name}" added successfully!`);
    },
    deleteProduct(product) {
      const confirmDelete = confirm(`Are you sure you want to delete "${product.name}"?`);
      if (confirmDelete) {
        this.$emit('delete-product', product);
      }
    },
    getCategories() {
      if (this.products && this.products.length > 0) {
        const uniqueCategories = [...new Set(this.products.map(p => p.category))];
        return uniqueCategories.sort();
      } else {
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

.mb-4{
  color: #ffffff;
}

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
  min-width: 80px;
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
  color: #d3d4d5;
}

.nav-tabs .nav-link.active {
  border-color: #BBA53D;
  color: #BBA53D;
  background-color: transparent;
}

.nav-tabs .nav-link:hover {
  border-color: #dee2e6;
}

.card-header {
  border-bottom: 0;
  background-color:#1d9756 ;
}

.card-header.bg-success {
  border-bottom: 0;
}
</style>