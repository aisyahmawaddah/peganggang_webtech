<template>
  <div class="card recent-activity">
    <div class="card-header">
      <div class="header-content">
        <h5 class="card-title">Recent Activity</h5>
        <div class="activity-controls">
          <select v-model="filterType" class="filter-select">
            <option value="all">All Activities</option>
            <option value="sale">Sales</option>
            <option value="restock">Restocks</option>
            <option value="edit">Edits</option>
            <option value="add">Additions</option>
            <option value="delete">Deletions</option>
          </select>
          <button @click="refreshActivity" class="refresh-btn" title="Refresh activity">
            <i class="bi bi-arrow-clockwise" :class="{ 'spinning': isRefreshing }"></i>
          </button>
        </div>
      </div>
    </div>
    
    <div class="card-body">
      <!-- Activity Stats -->
      <div class="activity-stats" v-if="hasData">
        <div class="stat-item">
          <div class="stat-value">{{ totalActivities }}</div>
          <div class="stat-label">Total Activities</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ todayActivities }}</div>
          <div class="stat-label">Today</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ activeUsers }}</div>
          <div class="stat-label">Active Users</div>
        </div>
      </div>

      <!-- Activity Feed -->
      <div class="activity-feed">
        <div v-if="isLoading || isRefreshing" class="loading">
          <div class="spinner"></div>
          <p>Loading activities...</p>
        </div>
        
        <div v-else-if="!hasData" class="no-data">
          <i class="bi bi-clock-history"></i>
          <p>No recent activity</p>
          <small>Activity will appear here when products are updated</small>
        </div>
        
        <div v-else-if="filteredActivities.length === 0" class="no-data">
          <i class="bi bi-filter"></i>
          <p>No activities for selected filter</p>
          <small>Try selecting a different filter option</small>
        </div>
        
        <div v-else class="activity-list">
          <div
            v-for="activity in filteredActivities"
            :key="`${activity.id}-${activity.timestamp}`"
            class="activity-item"
            :class="{ 'highlight': isRecent(activity.timestamp) }"
          >
            <div class="activity-icon" :class="getActivityIconClass(activity.type)">
              <i :class="getActivityIcon(activity.type)"></i>
            </div>
            
            <div class="activity-content">
              <div class="activity-main">
                <div class="activity-text">{{ getActivityText(activity) }}</div>
                <div class="activity-meta">
                  <span class="activity-user">{{ activity.user || 'system' }}</span>
                  <span class="activity-time">{{ formatTimeAgo(activity.timestamp) }}</span>
                </div>
              </div>
              
              <div class="activity-details" v-if="getActivityDetails(activity)">
                <small class="activity-detail-text">{{ getActivityDetails(activity) }}</small>
              </div>
            </div>
            
            <div class="activity-badge" v-if="getActivityBadge(activity)">
              <span class="badge" :class="getBadgeClass(activity.type)">
                {{ getActivityBadge(activity) }}
              </span>
            </div>
          </div>
          
          <!-- Load More Button -->
          <div v-if="hasMoreActivities" class="load-more">
            <button @click="loadMoreActivities" class="load-more-btn">
              <i class="bi bi-arrow-down"></i>
              Load More Activities
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RecentActivity',
  props: {
    updates: {
      type: Array,
      default: () => []
    },
    products: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      filterType: 'all',
      isLoading: false,
      isRefreshing: false,
      displayLimit: 10,
      maxDisplayLimit: 50
    };
  },
  computed: {
    hasData() {
      return this.updates && this.updates.length > 0;
    },
    totalActivities() {
      return this.updates.length;
    },
    todayActivities() {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      return this.updates.filter(activity => {
        const activityDate = new Date(activity.timestamp);
        return activityDate >= today;
      }).length;
    },
    activeUsers() {
      const users = new Set(this.updates.map(activity => activity.user || 'system'));
      return users.size;
    },
    filteredActivities() {
      let activities = this.updates;
      
      // Apply type filter
      if (this.filterType !== 'all') {
        activities = activities.filter(activity => activity.type === this.filterType);
      }
      
      // Sort by timestamp (newest first)
      activities = activities.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
      
      // Limit display
      return activities.slice(0, this.displayLimit);
    },
    hasMoreActivities() {
      const totalFiltered = this.filterType === 'all' 
        ? this.updates.length 
        : this.updates.filter(activity => activity.type === this.filterType).length;
      
      return totalFiltered > this.displayLimit && this.displayLimit < this.maxDisplayLimit;
    }
  },
  watch: {
    updates: {
      handler() {
        // Reset display limit when updates change
        this.displayLimit = 10;
      },
      deep: true
    },
    filterType() {
      // Reset display limit when filter changes
      this.displayLimit = 10;
    }
  },
  methods: {
    async refreshActivity() {
      this.isRefreshing = true;
      try {
        // Emit event to parent to refresh data
        this.$emit('refresh-data');
        
        // Wait a bit for the refresh to complete
        await new Promise(resolve => setTimeout(resolve, 1000));
      } catch (error) {
        console.error('Error refreshing activity:', error);
      } finally {
        this.isRefreshing = false;
      }
    },
    
    loadMoreActivities() {
      this.displayLimit = Math.min(this.displayLimit + 10, this.maxDisplayLimit);
    },
    
    isRecent(timestamp) {
      const now = new Date();
      const activityTime = new Date(timestamp);
      const diffMinutes = (now - activityTime) / (1000 * 60);
      return diffMinutes < 5; // Consider activities in last 5 minutes as recent
    },
    
    getActivityText(activity) {
      const productName = this.getProductName(activity);
      
      if (activity.type === 'sale') {
        const quantitySold = (activity.old_quantity || 0) - (activity.new_quantity || 0);
        return `${productName} - ${quantitySold} units sold`;
      }
      
      if (activity.type === 'restock') {
        const quantityAdded = (activity.new_quantity || 0) - (activity.old_quantity || 0);
        return `${productName} restocked with ${quantityAdded} units`;
      }
      
      if (activity.type === 'edit' || activity.type === 'update') {
        return `${productName} details updated`;
      }
      
      if (activity.type === 'add') {
        return `${productName} added to inventory`;
      }
      
      if (activity.type === 'delete') {
        return `${productName} removed from inventory`;
      }
      
      if (activity.type === 'name_change') {
        return `Product renamed to ${activity.new_name || productName}`;
      }
      
      if (activity.type === 'price_change') {
        return `${productName} price updated`;
      }
      
      if (activity.type === 'category_change') {
        return `${productName} moved to ${activity.new_category || 'new category'}`;
      }
      
      if (activity.type === 'reorder_change') {
        return `${productName} reorder level changed`;
      }
      
      return `${productName} was modified`;
    },
    
    getActivityDetails(activity) {
      if (activity.type === 'price_change' && activity.old_price && activity.new_price) {
        return `Price changed from RM${activity.old_price} to RM${activity.new_price}`;
      }
      
      if (activity.type === 'category_change' && activity.old_category && activity.new_category) {
        return `Category: ${activity.old_category} → ${activity.new_category}`;
      }
      
      if (activity.type === 'name_change' && activity.old_name && activity.new_name) {
        return `Name: ${activity.old_name} → ${activity.new_name}`;
      }
      
      if (activity.type === 'reorder_change' && activity.old_reorder_level && activity.new_reorder_level) {
        return `Reorder level: ${activity.old_reorder_level} → ${activity.new_reorder_level}`;
      }
      
      if ((activity.type === 'sale' || activity.type === 'restock') && 
          activity.old_quantity !== null && activity.new_quantity !== null) {
        return `Stock: ${activity.old_quantity} → ${activity.new_quantity}`;
      }
      
      return null;
    },
    
    getActivityBadge(activity) {
      if (activity.type === 'sale') return 'SALE';
      if (activity.type === 'restock') return 'RESTOCK';
      if (activity.type === 'add') return 'NEW';
      if (activity.type === 'delete') return 'DELETED';
      if (activity.type === 'edit' || activity.type === 'update') return 'EDIT';
      return null;
    },
    
    getBadgeClass(type) {
      const classes = {
        sale: 'badge-sale',
        restock: 'badge-restock',
        add: 'badge-add',
        delete: 'badge-delete',
        edit: 'badge-edit',
        update: 'badge-edit'
      };
      return classes[type] || 'badge-default';
    },
    
    getProductName(activity) {
      // Try different name sources
      return activity.product_name || 
             activity.current_product_name || 
             activity.new_name || 
             `Product ${activity.product_id}`;
    },
    
    getActivityIcon(type) {
      const icons = {
        sale: 'bi bi-cart-dash',
        restock: 'bi bi-cart-plus',
        edit: 'bi bi-pencil-square',
        update: 'bi bi-pencil-square',
        add: 'bi bi-plus-circle',
        delete: 'bi bi-trash',
        name_change: 'bi bi-tag',
        price_change: 'bi bi-currency-dollar',
        category_change: 'bi bi-folder',
        reorder_change: 'bi bi-arrow-repeat'
      };
      return icons[type] || 'bi bi-arrow-clockwise';
    },
    
    getActivityIconClass(type) {
      const classes = {
        sale: 'icon-red',
        restock: 'icon-green',
        edit: 'icon-blue',
        update: 'icon-blue',
        add: 'icon-green',
        delete: 'icon-red',
        name_change: 'icon-blue',
        price_change: 'icon-yellow',
        category_change: 'icon-purple',
        reorder_change: 'icon-orange'
      };
      return classes[type] || 'icon-blue';
    },
    
    formatTimeAgo(timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diff = now - date;
      
      const minutes = Math.floor(diff / 60000);
      const hours = Math.floor(diff / 3600000);
      const days = Math.floor(diff / 86400000);
      
      if (minutes < 1) return 'Just now';
      if (minutes < 60) return `${minutes}m ago`;
      if (hours < 24) return `${hours}h ago`;
      if (days < 7) return `${days}d ago`;
      
      return date.toLocaleDateString();
    }
  }
};
</script>

<style scoped>
.recent-activity {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  height: 100%;
}

.card-header {
  background: rgba(255, 255, 255, 0.08);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.card-title {
  color: #ffffff;
  font-weight: 600;
  margin: 0;
  font-size: 1.1rem;
}

.activity-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-select {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 0.9rem;
}

.filter-select option {
  background: #2a2a2a;
  color: white;
}

.refresh-btn {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.refresh-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.activity-stats {
  display: flex;
  justify-content: space-around;
  text-align: center;
  padding: 15px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 20px;
}

.stat-item {
  flex: 1;
}

.stat-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: #4a9eff;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.85rem;
  color: #ccc;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.activity-feed {
  max-height: 500px;
  overflow-y: auto;
}

.loading, .no-data {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px 20px;
  text-align: center;
  color: #666;
}

.spinner {
  width: 30px;
  height: 30px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-left: 3px solid #4a9eff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

.no-data i {
  font-size: 2.5rem;
  margin-bottom: 15px;
  opacity: 0.5;
}

.no-data small {
  color: #999;
  margin-top: 5px;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 15px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  border-left: 3px solid transparent;
  transition: all 0.3s ease;
}

.activity-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.activity-item.highlight {
  border-left-color: #4a9eff;
  background: rgba(74, 158, 255, 0.1);
}

.activity-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.icon-blue { background: rgba(74, 158, 255, 0.2); color: #4a9eff; }
.icon-green { background: rgba(40, 167, 69, 0.2); color: #28a745; }
.icon-red { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
.icon-yellow { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
.icon-purple { background: rgba(111, 66, 193, 0.2); color: #6f42c1; }
.icon-orange { background: rgba(255, 159, 64, 0.2); color: #ff9f40; }

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-main {
  margin-bottom: 6px;
}

.activity-text {
  font-size: 0.95rem;
  color: white;
  margin-bottom: 6px;
  line-height: 1.4;
}

.activity-meta {
  display: flex;
  gap: 12px;
  font-size: 0.8rem;
  color: #999;
}

.activity-user {
  font-weight: 500;
  color: #ccc;
}

.activity-details {
  margin-top: 6px;
}

.activity-detail-text {
  color: #aaa;
  font-style: italic;
}

.activity-badge {
  flex-shrink: 0;
}

.badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-sale { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
.badge-restock { background: rgba(40, 167, 69, 0.2); color: #28a745; }
.badge-add { background: rgba(40, 167, 69, 0.2); color: #28a745; }
.badge-delete { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
.badge-edit { background: rgba(74, 158, 255, 0.2); color: #4a9eff; }
.badge-default { background: rgba(255, 255, 255, 0.1); color: #ccc; }

.load-more {
  text-align: center;
  padding: 20px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: 15px;
}

.load-more-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 auto;
}

.load-more-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
}

/* Scrollbar styling */
.activity-feed::-webkit-scrollbar {
  width: 6px;
}

.activity-feed::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 3px;
}

.activity-feed::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
}

.activity-feed::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .activity-controls {
    width: 100%;
    justify-content: space-between;
  }
  
  .activity-stats {
    flex-direction: column;
    gap: 15px;
    text-align: left;
  }
  
  .activity-item {
    padding: 12px;
  }
  
  .activity-meta {
    flex-direction: column;
    gap: 4px;
  }
  
  .activity-icon {
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .activity-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .activity-badge {
    align-self: flex-end;
  }
  
  .filter-select {
    width: 100%;
  }
  
  .activity-controls {
    flex-direction: column;
    gap: 8px;
  }
}
</style>