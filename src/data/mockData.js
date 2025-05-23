// src/data/mockData.js
export default {
  // Sample product data with integrated sold and sales attributes
  products: [
    {
      id: 1,
      name: "Wireless Headphones",
      category: "Electronics",
      price: 89.99,
      stock: 5,
      reorderLevel: 10,
      imageUrl: "headphones.jpg",
      sold: 35,
      sales: 3149.65  // 89.99 * 35
    },
    {
      id: 2,
      name: "Smartphone Case",
      category: "Accessories",
      price: 19.99,
      stock: 128,
      reorderLevel: 30,
      imageUrl: "case.jpg",
      sold: 105,
      sales: 2098.95  // 19.99 * 105
    },
    {
      id: 3,
      name: "4K Smart TV",
      category: "Electronics",
      price: 699.99,
      stock: 12,
      reorderLevel: 5,
      imageUrl: "tv.jpg",
      sold: 8,
      sales: 5599.92  // 699.99 * 8
    },
    {
      id: 4,
      name: "Bluetooth Speaker",
      category: "Electronics",
      price: 79.99,
      stock: 32,
      reorderLevel: 8,
      imageUrl: "speaker.jpg",
      sold: 42,
      sales: 3359.58  // 79.99 * 42
    },
    {
      id: 5,
      name: "Gaming Mouse",
      category: "Gaming",
      price: 49.99,
      stock: 0,
      reorderLevel: 15,
      imageUrl: "mouse.jpg",
      sold: 55,
      sales: 2749.45  // 49.99 * 55
    },
    {
      id: 6,
      name: "Mechanical Keyboard",
      category: "Gaming",
      price: 129.99,
      stock: 3,
      reorderLevel: 7,
      imageUrl: "keyboard.jpg",
      sold: 24,
      sales: 3119.76  // 129.99 * 24
    },
    {
      id: 7,
      name: "Tablet Stand",
      category: "Accessories",
      price: 24.99,
      stock: 85,
      reorderLevel: 20,
      imageUrl: "stand.jpg",
      sold: 77,
      sales: 1924.23  // 24.99 * 77
    }
  ],

  // Enhanced inventory updates with various change types
  inventoryUpdates: [
    // Stock changes (sales and restocks)
    {
      timestamp: "2025-05-01T09:23:44",
      productId: 1,
      oldQuantity: 50,
      newQuantity: 45,
      type: "sale",
      user: "system"
    },
    {
      timestamp: "2025-05-02T10:45:21",
      productId: 2,
      oldQuantity: 120,
      newQuantity: 128,
      type: "restock",
      user: "admin"
    },
    {
      timestamp: "2025-05-03T14:12:33",
      productId: 3,
      oldQuantity: 14,
      newQuantity: 12,
      type: "sale",
      user: "system"
    },
    
    // Name changes
    {
      timestamp: "2025-05-04T11:32:16",
      productId: 5,
      oldQuantity: 60, 
      newQuantity: 60,
      type: "name_change",
      user: "admin",
      oldName: "Wireless Mouse",
      newName: "Gaming Mouse"
    },
    
    // Price changes
    {
      timestamp: "2025-05-05T15:17:42",
      productId: 4,
      oldQuantity: 32,
      newQuantity: 32,
      type: "price_change",
      user: "admin",
      oldPrice: 69.99,
      newPrice: 79.99
    },
    
    // Category changes
    {
      timestamp: "2025-05-06T09:10:22",
      productId: 7,
      oldQuantity: 85,
      newQuantity: 85,
      type: "category_change",
      user: "admin",
      oldCategory: "Mobile",
      newCategory: "Accessories"
    },
    
    // Reorder level changes
    {
      timestamp: "2025-05-07T13:45:30",
      productId: 6,
      oldQuantity: 3,
      newQuantity: 3,
      type: "reorder_change",
      user: "admin",
      oldReorderLevel: 5,
      newReorderLevel: 7
    },
    
    // Product added
    {
      timestamp: "2025-05-08T10:20:15",
      productId: 7,
      oldQuantity: 0,
      newQuantity: 85,
      type: "add",
      user: "admin",
      productName: "Tablet Stand"
    },
    
    // Product deleted (for a non-existent product to simulate deletion)
    {
      timestamp: "2025-05-09T08:45:12",
      productId: 8,
      oldQuantity: 10,
      newQuantity: 0,
      type: "delete",
      user: "admin",
      productName: "Phone Charger"
    }
  ],
  
  // Categories for select dropdown - reduced to only 3 categories
  categories: [
    "Electronics",
    "Gaming",
    "Accessories"
  ],
  
  // Months for charts
  months: ["January", "February", "March", "April", "May"]
};