<!-- src/components/update/ProductEditor.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Edit Product</h5>
    </div>
    <div class="card-body">
      <div class="form-group mb-3">
        <label for="productSelect">Select Product</label>
        <select id="productSelect" class="form-control" v-model="selectedProductId">
          <option v-for="product in products" :key="product.id" :value="product.id">
            {{ product.name }}
          </option>
        </select>
      </div>
      
      <form v-if="selectedProduct" @submit.prevent="submitForm">
        <div class="form-group mb-3">
          <label for="productName">Product Name</label>
          <input type="text" class="form-control" id="productName" v-model="form.name" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="productCategory">Category</label>
          <input type="text" class="form-control" id="productCategory" v-model="form.category" required>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="productPrice">Price ($)</label>
            <input type="number" step="0.01" class="form-control" id="productPrice" v-model="form.price" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="productStock">Stock</label>
            <input type="number" class="form-control" id="productStock" v-model="form.stock" required>
          </div>
        </div>
        
        <div class="form-group mb-3">
          <label for="reorderLevel">Reorder Level</label>
          <input type="number" class="form-control" id="reorderLevel" v-model="form.reorderLevel" required>
        </div>
        
        <div class="text-end">
          <button type="submit" class="btn btn-primary">Update Product</button>
        </div>
      </form>
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
      selectedProductId: null,
      form: {
        id: null,
        name: '',
        category: '',
        price: 0,
        stock: 0,
        reorderLevel: 0
      }
    };
  },
  computed: {
    selectedProduct() {
      return this.products.find(p => p.id === this.selectedProductId);
    }
  },
  watch: {
    selectedProduct(product) {
      if (product) {
        this.form = { ...product };
      }
    }
  },
  methods: {
    submitForm() {
      this.$emit('update', { ...this.form });
    }
  }
};
</script>