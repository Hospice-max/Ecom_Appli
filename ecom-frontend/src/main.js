import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'
import './assets/styles/global.css'
import Echo from 'laravel-echo'
import io from 'socket.io-client'
import axios from 'axios'
import Pusher from 'pusher-js'

// Configuration de l'interception des requêtes axios
axios.defaults.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:5173'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Intercepteur pour ajouter le token d'authentification
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Intercepteur pour gérer les erreurs d'authentification
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      store.dispatch('logout')
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

// Récupérer le token CSRF
axios.get('/csrf-token')
    .then(response => {
        // Créer le meta tag avec le token CSRF
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = response.data.csrf_token;
        document.head.appendChild(meta);

        // Stocker le token CSRF dans la fenêtre
        window.csrfToken = response.data.csrf_token;
    })
    .catch(error => {
        console.error('Erreur lors de la récupération du token CSRF:', error);
        throw new Error('Impossible de récupérer le token CSRF');
    });

const app = createApp(App)
app.use(router)
app.use(store)
app.mount('#app')

window.io = io;
window.Echo = new Echo({
    broadcaster: 'socket.io',
    client: io,
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    host: window.location.hostname + ':6001',
    auth: {
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    },
    enabledTransports: ['ws', 'wss']
})