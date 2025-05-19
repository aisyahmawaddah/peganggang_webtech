<!-- src/components/update/AddProductForm.vue -->
<template>
  <div class="card">
    <div class="card-header bg-success text-white">
      <h5 class="card-title mb-0"><i class="bi bi-plus-circle"></i> Add New Product</h5>
    </div>
    <div class="card-body">
      <form @submit.prevent="submitForm">
        <div class="mb-3">
          <label for="productName" class="form-label">Product Name</label>
          <input type="text" class="form-control" id="productName" v-model="form.name" required>
        </div>
        
        <div class="mb-3">
          <label for="productCategory" class="form-label">Category</label>
          <select class="form-select" id="productCategory" v-model="form.category" required>
            <option value="" disabled>Select a category</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="productPrice" class="form-label">Price ($)</label>
            <input type="number" step="0.01" min="0.01" class="form-control" id="productPrice" v-model="form.price" required>
          </div>
          <div class="col-md-6">
            <label for="productStock" class="form-label">Initial Stock</label>
            <input type="number" min="0" class="form-control" id="productStock" v-model="form.stock" required>
          </div>
        </div>
        
        <div class="mb-3">
          <label for="reorderLevel" class="form-label">Reorder Level</label>
          <input type="number" min="1" class="form-control" id="reorderLevel" v-model="form.reorderLevel" required>
        </div>
        
        <div class="d-grid">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add Product
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
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
      }
    };
  },
  methods: {
    submitForm() {
      // Convert string values to numbers
      const newProduct = {
        ...this.form,
        price: parseFloat(this.form.price),
        stock: parseInt(this.form.stock),
        reorderLevel: parseInt(this.form.reorderLevel),
        sold: 0,
        sales: 0
      };
      
      this.$emit('add', newProduct);
      
      // Reset the form after submission
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