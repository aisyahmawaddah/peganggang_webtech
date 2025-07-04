<template>
  <div class="container-fluid dashboard-container">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="./assets/logo.png" alt="FlexStock Logo" height="60" class="me-3">

        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" :class="{ active: currentPage === 'dashboard' }" href="#"
                @click.prevent="currentPage = 'dashboard'">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: currentPage === 'update' }" href="#"
                @click.prevent="currentPage = 'update'">Update Inventory</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="content">
      <dashboard-page v-if="currentPage === 'dashboard'" :products="products" :sales="salesHistory"
        :updates="inventoryUpdates" />

        <update-page 
      v-if="currentPage === 'update'" 
      :products="products" 
      @update-product="updateProduct"
      @delete-product="deleteProduct" 
    />
    </main>
  </div>
</template>

<script>
import DashboardPage from './components/DashboardPage.vue';
import UpdatePage from './components/UpdatePage.vue';

export default {
  components: {
    DashboardPage,
    UpdatePage
  },
  data() {
    return {
      currentPage: 'dashboard',
      products: [],
      salesHistory: [],
      inventoryUpdates: []
    };
  },

   mounted() {
    this.loadData();
  },
  methods: {

    async loadData() {
      try {
        // Fetch products
        const productsRes = await fetch('https://flexstock-api.duckdns.org/api/products.php');
        const productData = await productsRes.json();
        this.products = productData.data;

        // Fetch inventory updates
        const updatesRes = await fetch('https://flexstock-api.duckdns.org/api/updates.php');
        const updateData = await updatesRes.json();
        this.inventoryUpdates = updateData.data;

        // salesHistory can also be loaded from backend if available
      } catch (err) {
        console.error('Failed to fetch data:', err);
      }
    },

    async addProduct(newProduct) {
  try {
    const response = await fetch('https://flexstock-api.duckdns.org/api/products.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newProduct)
    });

    const result = await response.json();

    if (result.success) {
      alert('Product added successfully!');
      await this.loadData(); // ✅ This is required to update the UI
    } else {
      alert('Failed to add product: ' + result.message);
    }
  } catch (error) {
    console.error('Error adding product:', error);
    alert('Error occurred while adding product.');
  }
},

async updateProduct(updatedProduct) {
  try {
    // 1. Update the product
    const response = await fetch(`https://flexstock-api.duckdns.org/api/products.php?id=${updatedProduct.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: updatedProduct.name,
        category: updatedProduct.category,
        price: updatedProduct.price,
        stock: updatedProduct.stock,
        reorderLevel: updatedProduct.reorderLevel
      })
    });

    const result = await response.json();

    if (result.success) {
      // 2. ALSO log the update activity
      await fetch('https://flexstock-api.duckdns.org/api/updates.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id: updatedProduct.id,
          type: 'update',
          user: 'admin',
          product_name: updatedProduct.name,
          old_quantity: updatedProduct.originalStock || updatedProduct.stock,
          new_quantity: updatedProduct.stock,
          old_price: updatedProduct.originalPrice || updatedProduct.price,
          new_price: updatedProduct.price,
          old_category: updatedProduct.originalCategory || updatedProduct.category,
          new_category: updatedProduct.category,
          old_name: updatedProduct.originalName || updatedProduct.name,
          new_name: updatedProduct.name,
          old_reorder_level: updatedProduct.originalReorderLevel || updatedProduct.reorderLevel,
          new_reorder_level: updatedProduct.reorderLevel
        })
      });

      alert('Product updated successfully!');
      await this.loadData(); 
    } else {
      alert('Update failed: ' + result.message);
    }
  } catch (error) {
    console.error('Error updating product:', error);
    alert('Error occurred while updating product.');
  }
},

     async deleteProduct(product) {
    if (!confirm(`Are you sure you want to delete "${product.name}"?`)) return;

    try {
      const response = await fetch(`https://flexstock-api.duckdns.org/api/products.php?id=${product.id}`, {
        method: 'DELETE',
      });

      const result = await response.json();

      if (result.success) {
        // Log the deletion activity
        await fetch('https://flexstock-api.duckdns.org/api/updates.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id: product.id,
            type: 'delete',
            user: 'admin',
            product_name: product.name
          })
        });

        alert('Product deleted successfully!');
        await this.loadData(); 

      } else {
        alert('Failed to delete: ' + result.message);
      }
    } catch (error) {
      console.error('Error deleting product:', error);
      alert('Error occurred while deleting product.');
    }
  }
}
};
</script>

<style>
.dashboard-container {
  padding: 0;
  min-height: 100vh;
  background-color: rgba(11, 58, 11, 0.888);
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
  color: rgba(238, 255, 6, 0.716);
  border-top-left-radius: 10px !important;
  border-top-right-radius: 10px !important;
}
</style>