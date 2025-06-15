<!-- src/components/update/AddProductForm.vue -->
<template>
  <div class="card">
    <div class="card-header bg-success text-white">
      <h5 class="card-title mb-0"><i class="bi bi-plus-circle"></i> Add New Product</h5>
    </div>
    <div class="card-body">
      <!-- Success Message -->
      <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ successMessage }}
        <button type="button" class="btn-close" @click="successMessage = ''"></button>
      </div>
      
      <!-- Error Message -->
      <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ errorMessage }}
        <button type="button" class="btn-close" @click="errorMessage = ''"></button>
      </div>
      
      <form @submit.prevent="submitForm">
        <div class="mb-3">
          <label for="productName" class="form-label">Product Name</label>
          <input 
            type="text" 
            class="form-control" 
            id="productName" 
            v-model="form.name" 
            :disabled="isSubmitting"
            required
          >
        </div>
        
        <div class="mb-3">
          <label for="productCategory" class="form-label">Category</label>
          <select 
            class="form-select" 
            id="productCategory" 
            v-model="form.category" 
            :disabled="isSubmitting"
            required
          >
            <option value="" disabled>Select a category</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="productPrice" class="form-label">Price ($)</label>
            <input 
              type="number" 
              step="0.01" 
              min="0.01" 
              class="form-control" 
              id="productPrice" 
              v-model="form.price" 
              :disabled="isSubmitting"
              required
            >
          </div>
          <div class="col-md-6">
            <label for="productStock" class="form-label">Initial Stock</label>
            <input 
              type="number" 
              min="0" 
              class="form-control" 
              id="productStock" 
              v-model="form.stock" 
              :disabled="isSubmitting"
              required
            >
          </div>
        </div>
        
        <div class="mb-3">
          <label for="reorderLevel" class="form-label">Reorder Level</label>
          <input 
            type="number" 
            min="1" 
            class="form-control" 
            id="reorderLevel" 
            v-model="form.reorderLevel" 
            :disabled="isSubmitting"
            required
          >
        </div>
        
        <div class="d-grid">
          <button type="submit" class="btn btn-success" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="bi bi-plus-circle"></i> 
            {{ isSubmitting ? 'Adding Product...' : 'Add Product' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  props: {
    categories: Array
  },
  data() {
    return {
      form: {
        name: '',
        category: '',
        price: 0,
        stock: 0,
        reorderLevel: 5,
        sold: 0,
        sales: 0
      },
      isSubmitting: false,
      successMessage: '',
      errorMessage: ''
    };
  },
  methods: {
    async submitForm() {
      // Clear previous messages
      this.successMessage = '';
      this.errorMessage = '';
      this.isSubmitting = true;

      try {
        // Prepare the data for API
        const productData = {
          name: this.form.name.trim(),
          category: this.form.category,
          price: parseFloat(this.form.price),
          stock: parseInt(this.form.stock),
          reorder_level: parseInt(this.form.reorderLevel), // Note: API expects reorder_level
          image_url: '', // Empty for now
          sold: 0, // New product starts with 0 sold
        };

        console.log('Sending product data to API:', productData);

        // Make API call to create product
        const response = await axios.post('/products', productData);

        console.log('API Response:', response.data);

        if (response.data.success) {
          // Success - show message and emit event
          this.successMessage = `Product "${productData.name}" added successfully!`;
          
          // Emit the new product data to parent component
          this.$emit('add', response.data.data);
          
          // Reset the form
          this.resetForm();
          
          // Hide success message after 5 seconds
          setTimeout(() => {
            this.successMessage = '';
          }, 5000);
          
        } else {
          this.errorMessage = response.data.message || 'Failed to add product';
        }

      } catch (error) {
        console.error('Error adding product:', error);
        
        if (error.response) {
          // Server responded with error status
          this.errorMessage = error.response.data.message || `Server error: ${error.response.status}`;
        } else if (error.request) {
          // Request was made but no response received
          this.errorMessage = 'Cannot connect to server. Make sure your backend is running.';
        } else {
          // Something else happened
          this.errorMessage = 'An unexpected error occurred';
        }
      } finally {
        this.isSubmitting = false;
      }
    },

    resetForm() {
      this.form = {
        name: '',
        category: '',
        price: 0,
        stock: 0,
        reorderLevel: 5,
        sold: 0,
        sales: 0
      };
    }
  }
};
</script>