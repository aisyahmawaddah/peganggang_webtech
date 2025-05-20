<!-- src/components/dashboard/RecentActivity.vue -->
<template>
  <div class="card">
    <div class="card-header bg-light">
      <h5 class="card-title mb-0 text-dark">
        <i class="bi bi-clock-history"></i> Recent Activity
      </h5>
    </div>
    <div class="card-body p-0">
      <div class="activity-list">
        <div v-if="combinedUpdates && combinedUpdates.length > 0">
          <div
            v-for="(activity, index) in combinedUpdates"
            :key="index"
            class="activity-item"
            :class="getActivityClass(activity)"
          >
            <div class="activity-icon" :class="activity.iconClass">
              <i :class="activity.icon"></i>
            </div>
            <div class="activity-content">
              <h6 class="mb-1">{{ activity.description }}</h6>
              <div class="activity-meta">
                <span class="time">{{ activity.timeFormatted }}</span>
                <span class="user">{{ activity.user || "admin" }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <p class="text-center text-muted my-4">
            No recent activity to display
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "RecentActivity",
  props: {
    updates: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      previousProducts: {}, // Store previous product states
      productsHistory: [], // Store all product states over time
      changesByTimestamp: new Map(), // Track changes by timestamp
      localUpdates: [], // Store local updates
      productCount: 0, // Track product count to detect adds/deletes
      lastProductIds: new Set(), // Track product IDs to detect adds/deletes
    };
  },
  created() {
    this.initializeTracking();

    // Poll for changes every 500ms
    this.changeDetectionInterval = setInterval(this.detectAllChanges, 500);
  },
  beforeUnmount() {
    clearInterval(this.changeDetectionInterval);
  },
  computed: {
    products() {
      // Find products array in parent component hierarchy
      let parent = this.$parent;
      while (parent) {
        if (parent.products && Array.isArray(parent.products)) {
          return parent.products;
        }
        parent = parent.$parent;
      }
      return [];
    },

    // Combine all updates (prop updates and detected updates)
    combinedUpdates() {
      // Start with the prop updates
      const allUpdates = [...this.updates];

      // Add local updates
      this.localUpdates.forEach((localUpdate) => {
        // Check if this update already exists
        const isDuplicate = allUpdates.some(
          (update) =>
            update.timestamp === localUpdate.timestamp &&
            update.productId === localUpdate.productId &&
            update.type === localUpdate.type
        );

        if (!isDuplicate) {
          allUpdates.push(localUpdate);
        }
      });

      // Process all updates
      return this.formatUpdates(allUpdates);
    },
  },
  methods: {
    initializeTracking() {
      // Store initial state of all products
      this.productsHistory.push({
        timestamp: new Date().toISOString(),
        products: this.deepCloneProducts(this.products),
      });

      // Initialize previous products
      this.previousProducts = this.deepCloneProducts(this.products);

      // Initialize product count and IDs
      this.productCount = this.products.length;
      this.lastProductIds = new Set(this.products.map((p) => p.id));
    },

    deepCloneProducts(products) {
      const clone = {};
      products.forEach((product) => {
        clone[product.id] = { ...product };
      });
      return clone;
    },

    detectAllChanges() {
      // Check for product additions or deletions
      this.detectProductAddDelete();

      // Check for field changes in existing products
      this.detectProductFieldChanges();

      // Store current state for next comparison
      this.productsHistory.push({
        timestamp: new Date().toISOString(),
        products: this.deepCloneProducts(this.products),
      });

      // Keep history to a reasonable size
      if (this.productsHistory.length > 10) {
        this.productsHistory.shift();
      }

      // Update previous products for next comparison
      this.previousProducts = this.deepCloneProducts(this.products);
      this.productCount = this.products.length;
      this.lastProductIds = new Set(this.products.map((p) => p.id));
    },

    getDeletedProductName(productId) {
      // Try to find the name from previous products if it's not in current products
      const previousProduct = this.previousProducts[productId];
      return previousProduct ? previousProduct.name : null;
    },

    detectProductAddDelete() {
      // Track the current product IDs
      const currentProductIds = new Set(this.products.map((p) => p.id));

      // Check for product additions
      if (this.products.length > this.productCount) {
        // Find the new product(s)
        const newProducts = this.products.filter(
          (p) => !this.lastProductIds.has(p.id)
        );

        newProducts.forEach((newProduct) => {
          const timestamp = new Date().toISOString();
          this.addLocalUpdate({
            productId: newProduct.id,
            timestamp,
            type: "add",
            user: "admin",
            productName: newProduct.name,
            category: newProduct.category,
            price: newProduct.price,
            stock: newProduct.stock,
            reorderLevel: newProduct.reorderLevel,
          });
        });
      }

      // Check for product deletions - more robust approach
      // Compare sets of IDs instead of just length
      const previousIds = [...this.lastProductIds];
      const currentIds = [...currentProductIds];

      if (previousIds.length > currentIds.length) {
        const deletedProductIds = previousIds.filter(
          (id) => !currentProductIds.has(id)
        );

        // Log the detection to help debug
        console.log("Detected deleted product IDs:", deletedProductIds);

        deletedProductIds.forEach((deletedId) => {
          const deletedProduct = this.previousProducts[deletedId];
          if (deletedProduct) {
            const timestamp = new Date().toISOString();
            // Create a more explicit delete update
            this.addLocalUpdate({
              productId: deletedId,
              timestamp,
              type: "delete",
              user: "admin",
              productName: deletedProduct.name,
              oldQuantity: deletedProduct.stock || 0,
              newQuantity: 0,
            });
          }
        });
      }

      // Update tracking variables
      this.productCount = this.products.length;
      this.lastProductIds = currentProductIds;
    },

    detectProductFieldChanges() {
      // Check for changes in each product's fields
      this.products.forEach((currentProduct) => {
        const id = currentProduct.id;
        const previousProduct = this.previousProducts[id];

        if (!previousProduct) {
          return; // New product, already handled in detectProductAddDelete
        }

        // Check for field changes
        const changes = {};
        let hasChanges = false;

        if (previousProduct.name !== currentProduct.name) {
          changes.nameChanged = true;
          changes.oldName = previousProduct.name;
          changes.newName = currentProduct.name;
          hasChanges = true;
        }

        if (previousProduct.category !== currentProduct.category) {
          changes.categoryChanged = true;
          changes.oldCategory = previousProduct.category;
          changes.newCategory = currentProduct.category;
          hasChanges = true;
        }

        if (previousProduct.price !== currentProduct.price) {
          changes.priceChanged = true;
          changes.oldPrice = previousProduct.price;
          changes.newPrice = currentProduct.price;
          hasChanges = true;
        }

        if (previousProduct.reorderLevel !== currentProduct.reorderLevel) {
          changes.reorderLevelChanged = true;
          changes.oldReorderLevel = previousProduct.reorderLevel;
          changes.newReorderLevel = currentProduct.reorderLevel;
          hasChanges = true;
        }

        if (previousProduct.stock !== currentProduct.stock) {
          changes.stockChanged = true;
          changes.oldStock = previousProduct.stock;
          changes.newStock = currentProduct.stock;
          hasChanges = true;
        }

        if (hasChanges) {
          // Check if there's a recent update in the props that we can associate this with
          const now = new Date();
          const recentUpdates = [...this.updates].filter(
            (update) =>
              update.productId === id && now - new Date(update.timestamp) < 3000 // Within last 3 seconds
          );

          if (recentUpdates.length > 0) {
            // Found a recent update to associate with
            recentUpdates.sort(
              (a, b) => new Date(b.timestamp) - new Date(a.timestamp)
            );
            const mostRecentUpdate = recentUpdates[0];

            // Store the changes with this timestamp
            changes.productId = id;
            this.changesByTimestamp.set(mostRecentUpdate.timestamp, changes);
          } else {
            // Create our own update
            const timestamp = new Date().toISOString();

            // Determine the most significant change
            let changeType = "update";
            if (changes.nameChanged) {
              changeType = "name_change";
            } else if (changes.categoryChanged) {
              changeType = "category_change";
            } else if (changes.priceChanged) {
              changeType = "price_change";
            } else if (changes.reorderLevelChanged) {
              changeType = "reorder_change";
            } else if (changes.stockChanged) {
              changeType =
                currentProduct.stock > previousProduct.stock
                  ? "restock"
                  : "sale";
            }

            // Create a manual update for this change
            this.addLocalUpdate({
              productId: id,
              timestamp,
              type: changeType,
              user: "admin",
              productName: currentProduct.name,
              ...changes,
            });
          }
        }
      });
    },

    addLocalUpdate(updateData) {
      // Create a standard format update
      const update = {
        timestamp: updateData.timestamp,
        productId: updateData.productId,
        oldQuantity: updateData.oldStock || 0,
        newQuantity: updateData.newStock || 0,
        type: updateData.type,
        user: updateData.user || "admin",
        _additionalData: { ...updateData }, // Store all data for reference
      };

      // Add to local updates
      this.localUpdates.push(update);

      // Limit local updates to prevent memory issues
      if (this.localUpdates.length > 50) {
        this.localUpdates.shift();
      }
    },

    formatUpdates(updates) {
      if (!updates || updates.length === 0) {
        return [];
      }

      return updates
        .map((update) => {
          const productId = update.productId;
          const productName = this.getProductNameById(productId);

          // Get additional data if available
          const additionalData = update._additionalData || {};

          // Get detected changes for this timestamp
          const detectedChanges = this.changesByTimestamp.get(update.timestamp);

          let description = "";
          let iconClass = "icon-blue";
          let icon = "bi bi-pencil-square";
          let type = update.type || "update";

          // Check for specific changes, regardless of update type
          // This ensures we catch name changes even if they're not explicitly marked
          if (detectedChanges && detectedChanges.nameChanged) {
            description = `Product name changed to ${detectedChanges.newName}`;
            iconClass = "icon-blue";
            icon = "bi bi-tag";
            type = "name_change";
          } else if (detectedChanges && detectedChanges.categoryChanged) {
            description = `${detectedChanges.oldCategory} changes to ${detectedChanges.newCategory}`;
            iconClass = "icon-blue";
            icon = "bi bi-folder";
            type = "category_change";
          } else if (detectedChanges && detectedChanges.priceChanged) {
            const priceChange =
              detectedChanges.newPrice > detectedChanges.oldPrice
                ? "increased"
                : "decreased";
            description = `$${detectedChanges.oldPrice} ${priceChange} to $${detectedChanges.newPrice}`;
            iconClass = "icon-orange";
            icon = "bi bi-currency-dollar";
            type = "price_change";
          } else if (detectedChanges && detectedChanges.reorderLevelChanged) {
            const levelChange =
              detectedChanges.newReorderLevel > detectedChanges.oldReorderLevel
                ? "increased"
                : "decreased";
            description = `${detectedChanges.oldReorderLevel} ${levelChange} to ${detectedChanges.newReorderLevel}`;
            iconClass = "icon-blue";
            icon = "bi bi-arrow-repeat";
            type = "reorder_change";
          }
          // Only if no other changes are detected, then process based on type
          else {
            // Process based on explicit type
            switch (type) {
              case "add":
                description = `New product added: ${
                  additionalData.productName || productName
                }`;
                iconClass = "icon-green";
                icon = "bi bi-plus-circle";
                break;

              case "delete":
                // Use additionalData or stored product name, ensuring we display name not ID
                description = `Product deleted: ${
                  update.productName || additionalData.productName || this.getDeletedProductName(update.productId) || `Product #${update.productId}`
                }`;
                iconClass = "icon-red";
                icon = "bi bi-trash";
                break;

              case "name_change":
                if (update.oldName && update.newName) {
                  description = `Changed name: ${update.oldName} changes to ${update.newName}`;
                } else if (additionalData.oldName && additionalData.newName) {
                  description = `Changed name: ${additionalData.oldName} changes to ${additionalData.newName}`;
                } else {
                  description = `Product name updated: ${productName}`;
                }
                iconClass = "icon-blue";
                icon = "bi bi-tag";
                break;

              case "category_change":
                if (update.oldCategory && update.newCategory) {
                  description = `Category: ${update.oldCategory} changes to ${update.newCategory}`;
                } else if (detectedChanges && detectedChanges.categoryChanged) {
                  description = `Category: ${detectedChanges.oldCategory} changes to ${detectedChanges.newCategory}`;
                } else if (
                  additionalData.oldCategory &&
                  additionalData.newCategory
                ) {
                  description = `Category: ${additionalData.oldCategory} changes to ${additionalData.newCategory}`;
                } else {
                  description = `Category updated for ${productName}`;
                }
                iconClass = "icon-blue";
                icon = "bi bi-folder";
                break;

              case "price_change":
                if (
                  update.oldPrice !== undefined &&
                  update.newPrice !== undefined
                ) {
                  const priceChange =
                    update.newPrice > update.oldPrice
                      ? "increased"
                      : "decreased";
                  description = `Price: RM${update.oldPrice} ${priceChange} to RM${update.newPrice}`;
                } else if (detectedChanges && detectedChanges.priceChanged) {
                  const priceChange =
                    detectedChanges.newPrice > detectedChanges.oldPrice
                      ? "increased"
                      : "decreased";
                  description = `Price: RM${detectedChanges.oldPrice} ${priceChange} to RM${detectedChanges.newPrice}`;
                } else if (
                  additionalData.oldPrice !== undefined &&
                  additionalData.newPrice !== undefined
                ) {
                  const priceChange =
                    additionalData.newPrice > additionalData.oldPrice
                      ? "increased"
                      : "decreased";
                  description = `Price: RM${additionalData.oldPrice} ${priceChange} to RM${additionalData.newPrice}`;
                } else {
                  description = `Price updated for ${productName}`;
                }
                iconClass = "icon-orange";
                icon = "bi bi-currency-dollar";
                break;

              case "reorder_change":
                if (
                  update.oldReorderLevel !== undefined &&
                  update.newReorderLevel !== undefined
                ) {
                  const levelChange =
                    update.newReorderLevel > update.oldReorderLevel
                      ? "increased"
                      : "decreased";
                  description = `Reorder level: ${update.oldReorderLevel} ${levelChange} to ${update.newReorderLevel}`;
                } else if (
                  detectedChanges &&
                  detectedChanges.reorderLevelChanged
                ) {
                  const levelChange =
                    detectedChanges.newReorderLevel >
                    detectedChanges.oldReorderLevel
                      ? "increased"
                      : "decreased";
                  description = `Reorder level: ${detectedChanges.oldReorderLevel} ${levelChange} to ${detectedChanges.newReorderLevel}`;
                } else if (
                  additionalData.oldReorderLevel !== undefined &&
                  additionalData.newReorderLevel !== undefined
                ) {
                  const levelChange =
                    additionalData.newReorderLevel >
                    additionalData.oldReorderLevel
                      ? "increased"
                      : "decreased";
                  description = `Reorder level: ${additionalData.oldReorderLevel} ${levelChange} to ${additionalData.newReorderLevel}`;
                } else {
                  description = `Reorder level updated for ${productName}`;
                }
                iconClass = "icon-blue";
                icon = "bi bi-arrow-repeat";
                break;

              case "sale":
                description = `Sale: ${productName} quantity decreased from ${update.oldQuantity} to ${update.newQuantity}`;
                iconClass = "icon-red";
                icon = "bi bi-cart";
                break;

              case "restock":
                description = `Restock: ${productName} quantity increased from ${update.oldQuantity} to ${update.newQuantity}`;
                iconClass = "icon-green";
                icon = "bi bi-truck";
                break;

              default:
                // For general updates or when type is not specific
                if (
                  update.oldQuantity !== undefined &&
                  update.newQuantity !== undefined
                ) {
                  if (update.oldQuantity === update.newQuantity) {
                    description = `Inventory check: ${productName} quantity verified (${update.newQuantity})`;
                    iconClass = "icon-blue";
                    icon = "bi bi-clipboard-check";
                  } else if (update.newQuantity > update.oldQuantity) {
                    description = `Restock: ${productName} quantity increased from ${update.oldQuantity} to ${update.newQuantity}`;
                    iconClass = "icon-green";
                    icon = "bi bi-plus-circle";
                    type = "restock";
                  } else {
                    description = `Sale: ${productName} quantity decreased from ${update.oldQuantity} to ${update.newQuantity}`;
                    iconClass = "icon-red";
                    icon = "bi bi-dash-circle";
                    type = "sale";
                  }
                } else {
                  description = `Updated: ${productName}`;
                }
            }
          }

          return {
            ...update,
            productName,
            description,
            iconClass,
            icon,
            type,
            timeFormatted: this.formatTimeAgo(update.timestamp),
          };
        })
        .sort((a, b) => {
          // Sort by timestamp, newest first
          return new Date(b.timestamp) - new Date(a.timestamp);
        });
    },

    getProductNameById(id) {
      const product = this.products.find((p) => p.id === id);
      if (product) {
        return product.name;
      }

      // If not found in current products, check previous products
      const previousProduct = this.previousProducts[id];
      if (previousProduct) {
        return previousProduct.name;
      }

      return `Product #${id}`;
    },

    getActivityClass(activity) {
      switch (activity.type) {
        case "sale":
          return "sale-item";
        case "restock":
          return "restock-item";
        case "add":
          return "add-item";
        case "delete":
          return "delete-item";
        case "price_change":
          return "price-item";
        case "name_change":
        case "category_change":
          return "update-item";
        default:
          return "";
      }
    },

    formatTimeAgo(timestamp) {
      if (!timestamp) return "Just now";

      const now = new Date();
      const activityTime = new Date(timestamp);
      const diffInMilliseconds = now - activityTime;
      const diffInMinutes = Math.floor(diffInMilliseconds / (1000 * 60));
      const diffInHours = Math.floor(diffInMilliseconds / (1000 * 60 * 60));
      const diffInDays = Math.floor(diffInMilliseconds / (1000 * 60 * 60 * 24));

      if (diffInMinutes < 60) {
        return diffInMinutes <= 1 ? "Just now" : `${diffInMinutes} minutes ago`;
      } else if (diffInHours < 24) {
        return `${diffInHours} ${diffInHours === 1 ? "hour" : "hours"} ago`;
      } else if (diffInDays === 1) {
        return (
          "Yesterday, " +
          activityTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          })
        );
      } else {
        return activityTime.toLocaleString([], {
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });
      }
    },
  },
};
</script>

<style scoped>
.card-header.bg-primary {
  border-bottom: 0;
}

.card {
  height: 380px; /* Adjust this value to match your Sales Performance chart height */
}

.activity-list {
  max-height: 330px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-bottom: 1px solid #e9ecef;
  transition: background-color 0.2s;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-item:hover {
  background-color: #f8f9fa;
}

.activity-item.sale-item {
  background-color: rgba(220, 53, 69, 0.05);
}

.activity-item.restock-item {
  background-color: rgba(40, 167, 69, 0.05);
}

.activity-item.add-item {
  background-color: rgba(0, 123, 255, 0.05);
}

.activity-item.delete-item {
  background-color: rgba(108, 117, 125, 0.05);
}

.activity-item.price-item {
  background-color: rgba(255, 193, 7, 0.05);
}

.activity-item.update-item {
  background-color: rgba(23, 162, 184, 0.05);
}

.activity-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin-right: 12px;
  flex-shrink: 0;
}

.icon-blue {
  background-color: rgba(13, 110, 253, 0.1);
  color: #0d6efd;
}

.icon-green {
  background-color: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.icon-red {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.icon-orange {
  background-color: rgba(255, 193, 7, 0.1);
  color: #ffc107;
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-content h6 {
  margin: 0;
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.activity-meta {
  display: flex;
  font-size: 0.75rem;
  color: #6c757d;
  margin-top: 4px;
}

.activity-meta .time {
  margin-right: 15px;
}

.activity-meta .user {
  padding: 1px 6px;
  background-color: #e9ecef;
  border-radius: 12px;
  font-size: 0.7rem;
}

.empty-state {
  padding: 30px 15px;
  text-align: center;
}
</style>
