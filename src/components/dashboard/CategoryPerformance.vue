<!-- src/components/dashboard/CategoryPerformance.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Sales Performance by Category</h5>
    </div>
    <div class="card-body">
      <div class="chart-container">
        <Bar 
          :data="chartData" 
          :options="chartOptions"
          v-if="chartLoaded"
        />
        <div v-else class="loading">Loading chart...</div>
      </div>
    </div>
  </div>
</template>

<script>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

// Register the components
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export default {
  components: {
    Bar
  },
  props: {
    products: {
      type: Array,
      required: true
    },
    months: {
      type: Array,
      default: () => ["January", "February", "March", "April", "May"]
    }
  },
  data() {
    return {
      chartLoaded: true,
      chartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Monthly Sales by Category'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Sales ($)'
            }
          }
        }
      }
    };
  },
  computed: {
    chartData() {
      // Group sales by category
      const categories = [...new Set(this.products.map(product => product.category))];
      
      // Generate data for each category
      const datasets = categories.map((category, index) => {
        const colors = [
          'rgba(54, 162, 235, 0.8)', // blue for Electronics
          'rgba(20, 30, 90, 0.8)',   // dark blue for Gaming
          'rgba(255, 183, 0, 0.8)'   // gold for Accessories
        ];
        
        // Sum the sales for products in this category
        const totalSales = this.products
          .filter(product => product.category === category)
          .reduce((sum, product) => sum + product.sales, 0);
          
        // Create simulated monthly data based on the total
        // In a real app, you'd use actual monthly data
        const monthlyData = this.months.map(() => {
          // Generate plausible monthly variations with some randomness
          const factor = 0.6 + Math.random() * 0.8;
          return Math.round(totalSales * factor / this.months.length);
        });
        
        return {
          label: category,
          backgroundColor: colors[index % colors.length],
          data: monthlyData
        };
      });

      return {
        labels: this.months,
        datasets
      };
    }
  }
};
</script>

<style scoped>
.chart-container {
  height: 300px;
  position: relative;
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: #666;
}
</style>