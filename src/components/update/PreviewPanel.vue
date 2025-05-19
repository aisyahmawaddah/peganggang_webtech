<!-- src/components/update/PreviewPanel.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Preview</h5>
    </div>
    <div class="card-body">
      <p class="text-muted">Select and edit a product to see changes</p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    product: Object
  }
};
</script>