<template>
  <div class="products-container">
    <div class="products-header">
      <h2>Produits</h2>
      <button @click="showForm = true; formMode = 'create'" class="btn btn-primary">
        <i class="fas fa-plus"></i> Créer un produit
      </button>
    </div>
    
    <div class="products-list">
      <div v-if="products.length > 0">
        <div v-for="product in products" :key="product.id" class="product-card">
          <div class="product-image">
            <img :src="product.image_url" :alt="product.name" v-if="product.image_url" />
            <span class="no-image" v-else>Aucune image</span>
          </div>
          <div class="product-details">
            <h3>{{ product.name }}</h3>
            <p>{{ product.description }}</p>
            <p class="product-price">Prix: {{ product.price }} €</p>
            <p class="product-stock">Stock: {{ product.stock }}</p>
            <div class="product-actions">
              <button @click="addToCart(product)" class="btn btn-success">
                <i class="fas fa-shopping-cart"></i> Ajouter au panier
              </button>
              <button @click="editProduct(product)" class="btn btn-warning">
                <i class="fas fa-edit"></i> Modifier
              </button>
              <button @click="deleteProduct(product)" class="btn btn-danger">
                <i class="fas fa-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="no-products">
        <i class="fas fa-box-open"></i>
        <h3>Aucun produit disponible</h3>
        <p>Vous pouvez créer votre premier produit en cliquant sur le bouton "Créer un produit" ci-dessus.</p>
      </div>
    </div>
    
    <!-- Panier -->
    <div v-if="cart.length > 0" class="cart-section">
      <h3>Panier</h3>
      <div class="cart-items">
        <div v-for="item in cart" :key="item.product_id" class="cart-item">
          <div>{{ item.product_name }}</div>
          <div>{{ item.product_price }} FCFA</div>
          <div>Quantité: {{ item.quantity }}</div>
          <button @click="removeFromCart(item)" class="btn btn-danger">Supprimer</button>
        </div>
      </div>
    </div>
    
    <div v-if="showForm" class="product-form-overlay">
      <div class="product-form-container">
        <div class="product-form-header">
          <div class="form-title">
            <h2>{{ formMode === 'edit' ? 'Modifier' : 'Créer' }} un produit</h2>
            <p class="form-subtitle">{{ formMode === 'edit' ? 'Modifier les informations du produit' : 'Ajouter un nouveau produit' }}</p>
          </div>
          <button type="button" @click="resetForm" class="close-form">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <form @submit.prevent="formMode === 'edit' ? updateProduct() : createProduct()" class="product-form-content">
          <div class="form-grid">
            <div class="form-group">
              <label for="name">Nom</label>
              <input v-model="form.name" type="text" id="name" required>
            </div>
            
            <div class="form-group">
              <label for="price">Prix</label>
              <input v-model="form.price" type="number" id="price" step="0.01" required>
            </div>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea v-model="form.description" id="description" required></textarea>
          </div>
          
          <div class="form-grid">
            <div class="form-group">
              <label for="stock">Stock</label>
              <input v-model="form.stock" type="number" id="stock" required>
            </div>
            
            <div class="form-group">
              <label for="image">Image</label>
              <input type="file" id="image" @change="handleImageUpload" accept="image/*">
              <div v-if="form.image" class="image-preview">
                <img :src="form.image" alt="Aperçu">
              </div>
            </div>
          </div>
          
          <div class="form-actions">
            <div class="action-buttons">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ formMode === 'edit' ? 'Modifier' : 'Créer' }}
              </button>
            </div>
            <div class="action-buttons">
              <button type="button" @click="resetForm" class="btn btn-secondary">
                <i class="fas fa-times"></i> Annuler
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import Echo from 'laravel-echo'
import Swal from 'sweetalert2'
import { computed } from 'vue'

// Configuration de base pour axios
axios.defaults.baseURL = 'http://localhost:5173/api'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const products = ref([])
const cart = ref([])
const loading = ref(false)
const showForm = ref(false)
const formMode = ref('create')
const form = ref({
  id: null,
  name: '',
  description: '',
  price: 0,
  stock: 0,
  image: null,
  imagePreview: null
})

const sweetAlert = (title, text, icon) => {
  Swal.fire({
    title: title,
    text: text,
    icon: icon,
    confirmButtonText: 'OK'
  })
}

const initializeWebSocket = async () => {
  try {
    window.Echo = new Echo({
      broadcaster: 'socket.io',
      client: window.io,
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      host: window.location.hostname + ':6001',
      enabledTransports: ['ws', 'wss']
    })

    window.Echo.private('products')
      .listen('ProductCreated', (e) => {
        products.value.unshift(e.product)
      })
      .listen('ProductUpdated', (e) => {
        const index = products.value.findIndex(p => p.id === e.product.id)
        if (index !== -1) {
          products.value[index] = e.product
        }
      })
      .listen('ProductDeleted', (e) => {
        const index = products.value.findIndex(p => p.id === e.product.id)
        if (index !== -1) {
          products.value.splice(index, 1)
        }
      })

    window.Echo.private('cart-updates')
      .listen('CartUpdated', (event) => {
        cart.value = event.cart
      })
  } catch (error) {
    console.error('Erreur lors de l\'initialisation de WebSocket:', error)
  }
}

const getProducts = async () => {
  try {
    await axios.get('/products')
      .then((response) => {
        if (response.data && response.data.success && Array.isArray(response.data.products)) {
          products.value = response.data.products
        } else {
          sweetAlert('Erreur', 'Format de réponse invalide', 'error')
        }
      })
      .catch((error) => {
        console.error('Error fetching products:', error)
        sweetAlert('Erreur', 'Erreur lors du chargement des produits', 'error')
      })
  } catch (error) {
    console.error('Error in getProducts:', error)
    sweetAlert('Erreur', 'Erreur lors du chargement des produits', 'error')
  }
}

const createProduct = async () => {
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('price', form.value.price)
    formData.append('stock', form.value.stock)
    if (form.value.image) {
      formData.append('image', form.value.image)
    }

    await axios.post('/products', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    .then((response) => {
      sweetAlert('Succès', 'Produit créé avec succès', 'success')
      resetForm()
      getProducts() // Rafraîchir la liste
    })
    .catch((error) => {
      console.error('Erreur lors de la création du produit:', error)
      sweetAlert('Erreur', 'Erreur lors de la création du produit', 'error')
    })
  } catch (error) {
    console.error('Erreur lors de la création du produit:', error)
    sweetAlert('Erreur', 'Erreur lors de la création du produit', 'error')
  }
}

const updateProduct = async () => {
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('price', form.value.price)
    formData.append('stock', form.value.stock)
    if (form.value.image) {
      formData.append('image', form.value.image)
    }

    await axios.post(`/products/${form.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    .then((response) => {
      sweetAlert('Succès', 'Produit mis à jour avec succès', 'success')
      resetForm()
      getProducts() // Rafraîchir la liste
    })
    .catch((error) => {
      console.error('Erreur lors de la mise à jour du produit:', error)
      sweetAlert('Erreur', 'Erreur lors de la mise à jour du produit', 'error')
    })
  } catch (error) {
    console.error('Erreur lors de la mise à jour du produit:', error)
    sweetAlert('Erreur', 'Erreur lors de la mise à jour du produit', 'error')
  }
}

const deleteProduct = async (product) => {
  try {
    const confirmed = await Swal.fire({
      title: 'Confirmer la suppression',
      text: `Voulez-vous supprimer le produit "${product.name}" ?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non'
    })

    if (confirmed.isConfirmed) {
      await axios.delete(`/products/${product.id}`)
        .then((response) => {
          sweetAlert('Succès', 'Produit supprimé avec succès', 'success')
          getProducts() // Rafraîchir la liste
        })
        .catch((error) => {
          console.error('Erreur lors de la suppression:', error)
          sweetAlert('Erreur', 'Erreur lors de la suppression', 'error')
        })
    }
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    sweetAlert('Erreur', 'Erreur lors de la suppression', 'error')
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.value.image = file
    form.value.imagePreview = URL.createObjectURL(file)
  }
}

const resetForm = () => {
  form.value = {
    id: null,
    name: '',
    description: '',
    price: 0,
    stock: 0,
    image: null,
    imagePreview: null
  }
  showForm.value = false
  formMode.value = 'create'
}

const editProduct = (product) => {
  form.value = { ...product }
  showForm.value = true
  formMode.value = 'edit'
}

const addToCart = (product) => {
  try {
    axios.post('/cart', {
      product_id: product.id,
      quantity: 1
    })
    .then((response) => {
      if (response.data.success) {
        sweetAlert('Succès', 'Produit ajouté au panier', 'success')
        // Mettre à jour le stock si nécessaire
        const index = products.value.findIndex(p => p.id === product.id)
        if (index !== -1) {
          products.value[index].stock = response.data.stock
        }
      } else {
        sweetAlert('Erreur', response.data.message || 'Erreur lors de l\'ajout au panier', 'error')
      }
    })
    .catch((error) => {
      console.error('Erreur lors de l\'ajout au panier:', error)
      sweetAlert('Erreur', 'Erreur lors de l\'ajout au panier', 'error')
    })
  } catch (error) {
    console.error('Erreur lors de l\'ajout au panier:', error)
    sweetAlert('Erreur', 'Erreur lors de l\'ajout au panier', 'error')
  }
}

const removeFromCart = (product) => {
  try {
    axios.delete(`/removeFromCart/${product.id}`)
      .then((response) => {
        if (response.data.success) {
          sweetAlert('Succès', 'Produit supprimé du panier', 'success')
          // Mettre à jour le stock si nécessaire
          const index = products.value.findIndex(p => p.id === product.id)
          if (index !== -1) {
            products.value[index].stock = response.data.stock
          }
        } else {
          sweetAlert('Erreur', response.data.message || 'Erreur lors de la suppression du panier', 'error')
        }
      })
      .catch((error) => {
        console.error('Erreur lors de la suppression du panier:', error)
        sweetAlert('Erreur', 'Erreur lors de la suppression du panier', 'error')
      })
  } catch (error) {
    console.error('Erreur lors de la suppression du panier:', error)
    sweetAlert('Erreur', 'Erreur lors de la suppression du panier', 'error')
  }
}

onMounted(async () => {
  await initializeWebSocket()
  await getProducts()
  
  // Récupérer le panier initial
  try {
    const response = await axios.get('/cart')
    if (response.data.success) {
      cart.value = response.data.cart || []
    }
  } catch (error) {
    console.error('Erreur lors de la récupération du panier:', error)
  }
})

onUnmounted(() => {
  window.Echo.leave('products')
  window.Echo.leave('cart-updates')
})
</script>

<style scoped>
:root {
  --primary-color: #2563eb;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --background-color: #f8fafc;
  --text-color: #1f2937;
  --border-color: #e5e7eb;
}

.products-container {
  padding: 20px;
  min-height: 100vh;
  background-color: var(--background-color);
  color: var(--text-color);
}

.products-header {
  display: flex;
  justify-content: space-between;
  /* align-items: center; */
  margin-bottom: 2rem;
}

.products-list {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  padding: 1rem;
}

.product-card {
  flex: 1 1 300px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s;
}

.product-card:hover {
  transform: translateY(-5px);
}

.product-image {
  width: 100%;
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
}

.product-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.product-details {
  padding: 1.5rem;
}

.product-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 15px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 120px;
}

.btn-primary {
  background: #4f46e5;
  color: white;
}

.btn-primary:hover {
  background: #4338ca;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover {
  background: #d97706;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

.no-products {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.no-products i {
  font-size: 4rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.no-products h3 {
  margin: 0 0 1rem 0;
  color: var(--text-color);
}

.no-products p {
  color: var(--text-color);
}

.product-form-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  backdrop-filter: blur(5px);
  z-index: 1000;
}

.product-form-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.product-form-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.form-title {
  flex: 1;
}

.form-title h2 {
  margin: 0 0 0.5rem 0;
  color: var(--text-color);
}

.form-subtitle {
  color: var(--text-color);
  opacity: 0.8;
  font-size: 0.9rem;
}

.product-form-content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea {
  padding: 0.5rem 0.75rem;
  border: 2px solid black;
  border-radius: 6px;
  font-size: 0.9rem;
  background: white;
  transition: all 0.2s;
  width: 100%;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: black;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-group textarea {
  min-height: 80px;
  resize: vertical;
}

.form-group input:invalid,
.form-group textarea:invalid {
  border-color: black;
}

.form-group input:required:valid,
.form-group textarea:required:valid {
  border-color: black;
}

.image-preview {
  width: 100%;
  height: 100px;
  margin-top: 0.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 2px dashed black;
  border-radius: 6px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.image-preview:hover {
  border-color: black;
  background: rgba(37, 99, 235, 0.05);
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1rem;  
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.cart-section {
  margin-top: 20px;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.cart-items {
  margin-top: 10px;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border-bottom: 1px solid #eee;
}

@media (max-width: 768px) {
  .products-list {
    padding: 0.5rem;
  }
  
  .product-card {
    flex: 1 1 100%;
  }
  
  .product-actions {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .btn {
    width: 100%;
  }
}
</style>
