import { createStore } from 'vuex'

export default createStore({
  state: {
    products: [
      {
        id: 1,
        name: 'T-shirt Classic',
        price: 29.99,
        description: 'T-shirt en coton biologique',
        image: '/images/products/tshirt.jpg',
        quantity: 1
      },
      {
        id: 2,
        name: 'Pantalon Casual',
        price: 49.99,
        description: 'Pantalon en jeans',
        image: '/images/products/pantalon.jpg',
        quantity: 1
      },
      {
        id: 3,
        name: 'Veste Sport',
        price: 79.99,
        description: 'Veste en cuir',
        image: '/images/products/veste.jpg',
        quantity: 1
      }
    ],
    cart: []
  },
  mutations: {
    addToCart(state, product) {
      const existingProduct = state.cart.find(item => item.id === product.id)
      if (existingProduct) {
        existingProduct.quantity += 1
      } else {
        state.cart.push({ ...product, quantity: 1 })
      }
    },
    removeFromCart(state, productId) {
      state.cart = state.cart.filter(item => item.id !== productId)
    },
    updateQuantity(state, { productId, quantity }) {
      const product = state.cart.find(item => item.id === productId)
      if (product) {
        product.quantity = quantity
      }
    }
  },
  actions: {
    addToCart({ commit }, product) {
      commit('addToCart', product)
    },
    removeFromCart({ commit }, productId) {
      commit('removeFromCart', productId)
    },
    updateQuantity({ commit }, { productId, quantity }) {
      commit('updateQuantity', { productId, quantity })
    }
  },
  getters: {
    cartTotal: state => {
      return state.cart.reduce((total, item) => total + (item.price * item.quantity), 0)
    }
  }
})