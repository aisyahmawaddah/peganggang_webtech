<template>
  <div class="dashboard">
    <h1 class="page-title mb-4">FlexStock Dashboard</h1>
    
    <div class="row mb-4">
      <div class="col-md-4">
        <inventory-overview :products="products"></inventory-overview>
      </div>
      <div class="col-md-8">
        <sales-trend :sales="sales"></sales-trend>
      </div>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-6">
        <category-performance :products="products"></category-performance>
      </div>
      <div class="col-md-6">
        <recent-activity :updates="updates"></recent-activity>
      </div>
    </div>
    
    <div class="row">
      <div class="col-12">
        <top-products :products="products"></top-products>
      </div>
    </div>
  </div>
</template>

<script>
import InventoryOverview from './dashboard/InventoryOverview.vue';
import SalesTrend from './dashboard/SalesTrend.vue';
import CategoryPerformance from './dashboard/CategoryPerformance.vue';
import RecentActivity from './dashboard/RecentActivity.vue';
import TopProducts from './dashboard/TopProducts.vue';

export default {
  components: {
    InventoryOverview,
    SalesTrend,
    CategoryPerformance,
    RecentActivity,
    TopProducts
  },
  props: {
    products: Array,
    sales: Array,
    updates: Array
  }
};
</script>

<style scoped>
.page-title {
  color: #ffffff;
  font-weight: 600;
}
</style>