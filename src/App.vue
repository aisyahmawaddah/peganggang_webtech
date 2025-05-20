<template>
  <div class="container-fluid dashboard-container">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Smart Inventory Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" :class="{ active: currentPage === 'dashboard' }" 
                 href="#" @click.prevent="currentPage = 'dashboard'">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: currentPage === 'update' }" 
                 href="#" @click.prevent="currentPage = 'update'">Update Inventory</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- <main class="content">
      <dashboard-page v-if="currentPage === 'dashboard'" 
                     :products="products"
                     :sales="salesHistory"
                     :updates="inventoryUpdates"></dashboard-page>
      <update-page v-if="currentPage === 'update'" 
                  :products="products"
                  @update-product="updateProduct"></update-page>
    </main> -->
    <main class="content">
      <dashboard-page 
        v-if="currentPage === 'dashboard'" 
        :products="products"
        :sales="salesHistory"
        :updates="inventoryUpdates" />
        
      <update-page 
        v-if="currentPage === 'update'" 
        :products="products"
        @update-product="updateProduct"
        @delete-product="deleteProduct" />
    </main>
  </div>
</template>

<script>
import DashboardPage from './components/DashboardPage.vue';
import UpdatePage from './components/UpdatePage.vue';
import mockData from './data/mockData.js';

export default {
  components: {
    DashboardPage,
    UpdatePage
  },
  data() {
    return {
      currentPage: 'dashboard',
      products: mockData.products,
      salesHistory: mockData.salesHistory,
      inventoryUpdates: mockData.inventoryUpdates
    };
  },
  methods: {
    updateProduct(updatedProduct) {
  // Check if this is a new product (no existing ID in the array)
  const index = this.products.findIndex(p => p.id === updatedProduct.id);
  
  if (index === -1) {
    // This is a new product, add it to the array
    this.products.push(updatedProduct);
    
    // Add a new inventory update record
    const update = {
      timestamp: new Date().toISOString(),
      productId: updatedProduct.id,
      oldQuantity: 0,
      newQuantity: updatedProduct.stock,
      type: 'add',
      user: 'admin',
      productName: updatedProduct.name
    };
    
    this.inventoryUpdates.unshift(update);
  } else {
    // This is an existing product - get the old product
    const oldProduct = this.products[index];
    
    // Compare each field to detect what changed
    const changes = {};
    
    // Check for name changes
    if (oldProduct.name !== updatedProduct.name) {
      changes.oldName = oldProduct.name;
      changes.newName = updatedProduct.name;
      changes.type = 'name_change';
    }
    
    // Check for category changes
    if (oldProduct.category !== updatedProduct.category) {
      changes.oldCategory = oldProduct.category;
      changes.newCategory = updatedProduct.category;
      changes.type = changes.type || 'category_change';
    }
    
    // Check for price changes
    if (oldProduct.price !== updatedProduct.price) {
      changes.oldPrice = oldProduct.price;
      changes.newPrice = updatedProduct.price;
      changes.type = changes.type || 'price_change';
    }
    
    // Check for reorder level changes
    if (oldProduct.reorderLevel !== updatedProduct.reorderLevel) {
      changes.oldReorderLevel = oldProduct.reorderLevel;
      changes.newReorderLevel = updatedProduct.reorderLevel;
      changes.type = changes.type || 'reorder_change';
    }
    
    // Check for stock changes
    if (oldProduct.stock !== updatedProduct.stock) {
      changes.oldQuantity = oldProduct.stock;
      changes.newQuantity = updatedProduct.stock;
      changes.type = changes.type || (updatedProduct.stock > oldProduct.stock ? 'restock' : 'sale');
    } else {
      // If stock didn't change, but we're tracking other changes, still record the quantities
      changes.oldQuantity = oldProduct.stock;
      changes.newQuantity = updatedProduct.stock;
    }
    
    // If no specific change was detected, mark as generic update
    if (!changes.type) {
      changes.type = 'update';
    }
    
    // Create the update record with all detected changes
    const update = {
      timestamp: new Date().toISOString(),
      productId: updatedProduct.id,
      oldQuantity: changes.oldQuantity || oldProduct.stock,
      newQuantity: changes.newQuantity || updatedProduct.stock,
      type: changes.type,
      user: 'admin',
      
      // Include all specific change fields
      ...(changes.oldName && { oldName: changes.oldName }),
      ...(changes.newName && { newName: changes.newName }),
      ...(changes.oldCategory && { oldCategory: changes.oldCategory }),
      ...(changes.newCategory && { newCategory: changes.newCategory }),
      ...(changes.oldPrice && { oldPrice: changes.oldPrice }),
      ...(changes.newPrice && { newPrice: changes.newPrice }),
      ...(changes.oldReorderLevel && { oldReorderLevel: changes.oldReorderLevel }),
      ...(changes.newReorderLevel && { newReorderLevel: changes.newReorderLevel })
    };
    
    // Update the product
    this.products[index] = { ...updatedProduct };
    
    // Add the update to inventory updates
    this.inventoryUpdates.unshift(update);
  }
  
  // Show success message
  alert('Inventory updated successfully!');
},

// Also improve the deleteProduct method to track deletions
deleteProduct(productToDelete) {
  const index = this.products.findIndex(p => p.id === productToDelete.id);
  if (index !== -1) {
    // Create a delete update record before removing the product
    const update = {
      timestamp: new Date().toISOString(),
      productId: productToDelete.id,
      oldQuantity: productToDelete.stock,
      newQuantity: 0,
      type: 'delete',
      user: 'admin',
      productName: productToDelete.name
    };
    
    // Add the update to inventory updates
    this.inventoryUpdates.unshift(update);
    
    // Remove the product
    this.products.splice(index, 1);
    alert(`"${productToDelete.name}" has been deleted.`);
  }
}}};
</script>

<style>
.dashboard-container {
  padding: 0;
  min-height: 100vh;
  background-color: #f8f9fa;
}

.content {
  padding: 20px;
}

.card {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}

.card-header {
  background-color: #343a40;
  color: white;
  border-top-left-radius: 10px !important;
  border-top-right-radius: 10px !important;
}
</style>