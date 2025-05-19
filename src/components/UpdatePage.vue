<!-- src/components/UpdatePage.vue -->
<template>
  <div>
    <h1 class="mb-4">Update Inventory</h1>
    <div class="row">
      <div class="col-md-7">
        <product-editor :products="products" @update="updateProduct"></product-editor>
      </div>
      <div class="col-md-5">
        <preview-panel :product="selectedProduct"></preview-panel>
      </div>
    </div>
  </div>
</template>

<script>
import ProductEditor from './update/ProductEditor.vue';
import PreviewPanel from './update/PreviewPanel.vue';

export default {
  components: {
    ProductEditor,
    PreviewPanel
  },
  props: {
    products: Array
  },
  data() {
    return {
      selectedProduct: null
    };
  },
  methods: {
    updateProduct(product) {
      this.$emit('update-product', product);
    }
  }
};
</script>