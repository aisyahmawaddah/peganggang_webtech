// src/data/mockData.js
export default {
  // Sample product data
  products: [
    {
      id: 1,
      name: "Wireless Headphones",
      category: "Electronics",
      price: 89.99,
      stock: 45,
      reorderLevel: 10,
      imageUrl: "headphones.jpg"
    },
    {
      id: 2,
      name: "Smartphone Case",
      category: "Accessories",
      price: 19.99,
      stock: 128,
      reorderLevel: 30,
      imageUrl: "case.jpg"
    },
    {
      id: 3,
      name: "4K Smart TV",
      category: "Electronics",
      price: 699.99,
      stock: 12,
      reorderLevel: 5,
      imageUrl: "tv.jpg"
    },
    // Add more products as needed...
  ],

  // Sample sales data by month
  salesHistory: [
    {
      date: "2025-01-01",
      productId: 1,
      quantity: 12,
      revenue: 1079.88
    },
    {
      date: "2025-02-01",
      productId: 2,
      quantity: 28,
      revenue: 559.72
    },
    // Add more sales history as needed...
  ],

  // Sample inventory updates
  inventoryUpdates: [
    {
      timestamp: "2025-05-01T09:23:44",
      productId: 1,
      oldQuantity: 50,
      newQuantity: 45,
      type: "sale",
      user: "system"
    },
    {
      timestamp: "2025-05-03T14:12:33",
      productId: 3,
      oldQuantity: 14,
      newQuantity: 12,
      type: "sale",
      user: "system"
    },
    // Add more inventory updates as needed...
  ],
  
  // Categories for select dropdown
  categories: [
    "Electronics",
    "Computer Peripherals",
    "Accessories",
    "Storage",
    "Audio",
    "Wearables"
  ],
  
  // Months for charts
  months: ["January", "February", "March", "April", "May"]
};