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

    <main class="content">
      <dashboard-page v-if="currentPage === 'dashboard'" 
                     :products="products"
                     :sales="salesHistory"
                     :updates="inventoryUpdates"></dashboard-page>
      <update-page v-if="currentPage === 'update'" 
                  :products="products"
                  @update-product="updateProduct"></update-page>
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
      // Find the product in the array
      const index = this.products.findIndex(p => p.id === updatedProduct.id);
      if (index !== -1) {
        // Create a new update record
        const update = {
          timestamp: new Date().toISOString(),
          productId: updatedProduct.id,
          oldQuantity: this.products[index].stock,
          newQuantity: updatedProduct.stock,
          type: updatedProduct.stock > this.products[index].stock ? 'restock' : 'update',
          user: 'admin'
        };
        
        // Update the product
        this.products[index] = { ...updatedProduct };
        
        // Add the update to inventory updates
        this.inventoryUpdates.unshift(update);
        
        // Show success message
        alert('Product updated successfully!');
      }
    }
  }
};
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