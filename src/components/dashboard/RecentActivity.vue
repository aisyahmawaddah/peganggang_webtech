<!-- src/components/dashboard/RecentActivity.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Recent Activity</h5>
    </div>
    <div class="card-body">
      <ul class="activity-list">
        <li v-for="(update, index) in recentUpdates" :key="index" class="activity-item">
          <div class="activity-content">
            <p>{{ getActivityText(update) }}</p>
            <p class="activity-time">{{ formatTime(update.timestamp) }}</p>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    updates: Array
  },
  computed: {
    recentUpdates() {
      return this.updates.slice(0, 5);
    }
  },
  methods: {
    getActivityText(update) {
      return `Updated product #${update.productId} from ${update.oldQuantity} to ${update.newQuantity} units`;
    },
    formatTime(timestamp) {
      const date = new Date(timestamp);
      return date.toLocaleString();
    }
  }
};
</script>

<style scoped>
.activity-list {
  list-style: none;
  padding: 0;
}

.activity-item {
  padding: 10px 0;
  border-bottom: 1px solid #eee;
}

.activity-time {
  font-size: 0.8rem;
  color: #6c757d;
}
</style>