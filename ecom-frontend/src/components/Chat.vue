<template>
  <div class="chat-container">
    <div class="chat-header">
      <h4>Messages</h4>
    </div>
    <div class="chat-messages" ref="messagesContainer">
      <div v-for="(message, index) in messages" :key="index" 
           :class="['message', message.from === 'user' ? 'user-message' : 'admin-message']">
        <div class="message-content">
          <span class="message-text">{{ message.content }}</span>
          <span class="message-time">{{ formatTime(message.time) }}</span>
        </div>
      </div>
    </div>
    <div class="chat-input">
      <input v-model="newMessage" @keyup.enter="sendMessage" 
             type="text" placeholder="Tapez votre message..." class="form-control" />
      <button @click="sendMessage" class="btn btn-primary">Envoyer</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Echo from 'laravel-echo'
import axios from 'axios'
import Swal from 'sweetalert2'

const messages = ref([])
const newMessage = ref('')
const messagesContainer = ref(null)
const notifications = ref([])

const sweetAlert = (title, text, icon) => {
  Swal.fire({
    title: title,
    text: text,
    icon: icon,
    confirmButtonText: 'OK'
  })
}

const configEcho = async () => {
  try {
    // Vérifier si le token CSRF existe    
    if (!window.csrfToken) {
      console.error('Token CSRF non défini');
      return false;
    }

    window.Echo = new Echo({
      broadcaster: 'socket.io',
      client: window.io,
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      host: window.location.hostname + ':6001',
      auth: {
        headers: {
          'X-CSRF-TOKEN': window.csrfToken
        }
      },
      enabledTransports: ['ws', 'wss']
    })
    
    console.log('Echo configuré avec succès')
    return true
  } catch (error) {
    console.error('Erreur lors de la configuration de Echo:', error)
    return false
  }
}

const formatTime = (time) => {
  return new Date(time).toLocaleTimeString()
}

const scrollToBottom = () => {
  messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
}

const sendMessage = async () => {
  if (newMessage.value.trim()) {
    try {
      await axios.post('/api/postMessages', {
        from: 'user',
        content: newMessage.value
      }, {
        headers: {
          'X-CSRF-TOKEN': window.csrfToken
        }
      }).then((response) => {
        console.log('Message envoyé avec succès:', response.data)        
        newMessage.value = ''
        getMessages()
      }).catch((error) => {
        console.error('Erreur lors de l\'envoi du message:', error)
        sweetAlert('Erreur', 'Erreur lors de l\'envoi du message', 'error')
      })
    } catch (error) {
      console.error('Erreur lors de l\'envoi du message:', error)
    } 
  }
}

const handleNotification = (notification) => {
  notifications.value.unshift(notification)  // Ajouter au début
  // Afficher une notification visuelle
  sweetAlert('Notification', notification.message, 'info')
}

const initializeWebSocket = async () => {
  if (await configEcho()) {
    window.Echo.private('chat')
      .listen('NewMessage', (e) => {
        messages.value.unshift(e.message)  // Ajouter au début
        scrollToBottom()
      })
      .listen('NewNotification', (e) => {
        handleNotification(e.notification)
      })
  }
}

const getMessages = async () => {
  try {
    await axios.get('/api/getMessages').then((response) => {      
      messages.value = [...response.data.messages].reverse()
    }).catch((error) => {
      console.error('Erreur lors du chargement des messages:', error)
      sweetAlert('Erreur', 'Erreur lors du chargement des messages', 'error')
    })
    scrollToBottom()
  } catch (error) {
    console.error('Erreur lors du chargement des messages:', error)
    sweetAlert('Erreur', 'Erreur lors du chargement des messages', 'error')
  }
}
onMounted(async () => {
  setTimeout(async () => {
    await initializeWebSocket()
  }, 5000);
  
  // Charger les messages existants
  await getMessages()
})

onUnmounted(() => {
  window.Echo.leave('chat')
})
</script>

<style scoped>
:root {
  --primary-color: #2563eb;
  --secondary-color: #1e40af;
  --background-light: #f8fafc;
  --text-primary: #1e293b;
  --text-secondary: #475569;
  --text-muted: #94a3b8;
  --border-radius: 1rem;
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --transition: all 0.2s ease-in-out;
}

.chat-container {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
  background: white;
  border-radius: 1rem;
  box-shadow: var(--shadow-md);
  height: 600px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.chat-header {
  padding: 1.5rem;
  background: var(--primary-color);
  color: white;
  border-radius: 1rem 1rem 0 0;
  position: relative;
  overflow: hidden;
  font-weight: 600;
  text-align: center;
}

.chat-header::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0.1) 100%);
  animation: shine 2s infinite;
}

@keyframes shine {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background: white;
  border-bottom: 1px solid var(--text-muted);
}

.message {
  margin-bottom: 1.5rem;
  max-width: 80%;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.user-message {
  margin-left: auto;
  background: var(--primary-color);
  color: white;
  border-radius: 1rem 1rem 0 1rem;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.admin-message {
  background: var(--background-light);
  color: var(--text-primary);
  border-radius: 1rem 1rem 1rem 0;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.message-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.message-text {
  font-weight: 500;
  flex: 1;
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.7;
  font-weight: 300;
}

.chat-input {
  padding: 1.5rem;
  border-top: 1px solid var(--text-muted);
  display: flex;
  gap: 1rem;
  background: white;
}

input[type="text"] {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid var(--text-muted);
  border-radius: 1rem;
  font-family: inherit;
  transition: var(--transition);
  font-size: 1rem;
}

input[type="text"]:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

button {
  padding: 0.75rem 1.5rem;
  border-radius: 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  font-weight: 600;
  transition: var(--transition);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
}

button:hover {
  background: var(--secondary-color);
  transform: translateY(-1px);
}

button:active {
  transform: translateY(0);
}

/* Animations et transitions */
.message {
  transition: transform 0.2s ease-in-out;
}

.message:hover {
  transform: translateY(-2px);
}

/* Style pour les messages longs */
.message-content {
  word-wrap: break-word;
  max-width: 100%;
  width: fit-content;
}

/* Style pour les petites écrans */
@media (max-width: 480px) {
  .chat-container {
    max-width: 100%;
    border-radius: 0;
  }
  
  .chat-header {
    padding: 1rem;
  }
  
  .chat-input {
    padding: 1rem;
  }
}
</style>