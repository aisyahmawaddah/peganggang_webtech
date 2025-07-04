import { createApp } from 'vue'
import App from './App.vue'
import axios from 'axios'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'

// Configure axios defaults - CORRECT URL for your setup
axios.defaults.baseURL = 'https://flexstock-api.duckdns.org/api'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = false

// Add request interceptor for debugging
axios.interceptors.request.use(
  config => {
    // Automatically add .php extension if not present
    if (config.url && !config.url.includes('.php') && !config.url.includes('?')) {
      config.url = config.url + '.php';
    }
    console.log('Making request to:', config.baseURL + '/' + config.url);
    console.log('Request config:', config);
    return config;
  },
  error => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

// Add response interceptor for debugging
axios.interceptors.response.use(
  response => {
    console.log('Response received:', response);
    return response;
  },
  error => {
    console.error('Response error:', error);
    return Promise.reject(error);
  }
);

// Make axios available globally
const app = createApp(App)
app.config.globalProperties.$http = axios

app.mount('#app')