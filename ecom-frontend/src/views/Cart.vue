<template>
  <div class="cart">
    <h2 class="mb-4">Votre Panier</h2>
    
    <div v-if="cart.length === 0" class="text-center">
      <p>Votre panier est vide</p>
      <router-link to="/produits" class="btn btn-secondary">
        Retour aux produits
      </router-link>
    </div>

    <div v-else class="row">
      <div class="col-md-8">
        <div class="card mb-4" v-for="item in cart" :key="item.id">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title">{{ item.name }}</h5>
                <p class="card-text">{{ item.description }}</p>
                <p class="card-text">
                  Prix unitaire: {{ item.price }}€ x {{ item.quantity }}
                </p>
                <div class="input-group mb-3">
                  <button 
                    @click="decrementQuantity(item.id)" 
                    class="btn btn-outline-secondary"
                  >-</button>
                  <input 
                    type="number" 
                    class="form-control"
                    :value="item.quantity"
                    @change="updateQuantity(item.id, $event.target.value)"
                  >
                  <button 
                    @click="incrementQuantity(item.id)" 
                    class="btn btn-outline-secondary"
                  >+</button>
                </div>
              </div>
              <button 
                @click="removeFromCart(item.id)" 
                class="btn btn-danger"
              >
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Récapitulatif</h5>
            <p class="card-text">
              Total: {{ cartTotal }}€
            </p>
            <button class="btn btn-success w-100">Passer commande</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Cart',
  computed: {
    cart() {
      return this.$store.state.cart
    },
    cartTotal() {
      return this.$store.getters.cartTotal
    }
  },
  methods: {
    removeFromCart(productId) {
      this.$store.dispatch('removeFromCart', productId)
    },
    updateQuantity(productId, quantity) {
      this.$store.dispatch('updateQuantity', { productId, quantity: parseInt(quantity) })
    },
    incrementQuantity(productId) {
      this.$store.dispatch('updateQuantity', { productId, quantity: this.cart.find(item => item.id === productId).quantity + 1 })
    },
    decrementQuantity(productId) {
      const item = this.cart.find(item => item.id === productId)
      if (item.quantity > 1) {
        this.$store.dispatch('updateQuantity', { productId, quantity: item.quantity - 1 })
      }
    }
  }
}
</script>

<style scoped>
.card {
  transition: var(--transition);
  overflow: hidden;
  position: relative;
  border: none;
  box-shadow: var(--shadow-sm);
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.card-body {
  padding: 1.5rem;
}

.card-title {
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: var(--text-primary);
}

.card-text {
  color: var(--text-secondary);
  margin-bottom: 1rem;
}

.card-text strong {
  color: var(--primary-color);
  font-weight: 600;
}

.input-group {
  margin-bottom: 1rem;
}

.btn {
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  border-radius: var(--border-radius);
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn:hover {
  transform: translateY(-1px);
}

.btn-outline-secondary {
  border: 2px solid var(--text-muted);
  color: var(--text-muted);
}

.btn-outline-secondary:hover {
  background-color: var(--text-muted);
  color: white;
}

.btn-danger {
  background: var(--danger-color);
  border: none;
}

.btn-danger:hover {
  background: darken(var(--danger-color), 10%);
}

/* Style pour le récapitulatif */
.card:last-child {
  border-top: 2px solid var(--primary-color);
}

.card:last-child .card-body {
  padding: 2rem;
  text-align: center;
}

.card:last-child .card-title {
  color: var(--primary-color);
  margin-bottom: 1.5rem;
}

.card:last-child .card-text {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 1.5rem;
}

.btn-success {
  background: var(--success-color);
  border: none;
  padding: 1rem 2rem;
  font-size: 1.1rem;
  font-weight: 600;
}

.btn-success:hover {
  background: darken(var(--success-color), 10%);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
}

/* Animations pour les quantités */
.input-group input[type="number"] {
  text-align: center;
  font-weight: 600;
  transition: var(--transition);
}

.input-group input[type="number"]:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Effet de survol sur les prix */
.card-text strong {
  position: relative;
  display: inline-block;
}

.card-text strong::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--primary-color);
  transition: var(--transition);
}

.card-text strong:hover::after {
  width: 100%;
}
</style>
