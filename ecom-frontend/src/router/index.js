import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Products from '../views/Products.vue'
import Cart from '../views/Cart.vue'
import Chat from '../components/Chat.vue'
import Login from '../views/Login.vue'
import Profile from '../views/Profile.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/profil',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/produits',
    name: 'Products',
    component: Products,
    meta: { requiresAuth: true }
  },
  {
    path: '/panier',
    name: 'Cart',
    component: Cart,
    meta: { requiresAuth: true }
  },
  {
    path: '/chat',
    name: 'Chat',
    component: Chat,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Middleware de protection de route
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const user = localStorage.getItem('user')
  const token = localStorage.getItem('token')

  if (requiresAuth && (!token || !user)) {
    next('/login')
  } else if (to.name === 'Login' && token) {
    // Si déjà connecté, rediriger vers le profil
    next('/profil')
  } else {
    next()
  }
})

export default router
