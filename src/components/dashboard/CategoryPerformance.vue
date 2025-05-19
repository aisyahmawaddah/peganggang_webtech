<!-- src/components/dashboard/CategoryPerformance.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Category Performance</h5>
    </div>
    <div class="card-body">
      <div class="chart-container">
        <!-- Chart will be rendered here -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    products: Array
  }
};
</script>

<style scoped>
.chart-container {
  height: 300px;
}
</style>