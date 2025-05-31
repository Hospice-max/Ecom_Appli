<template>
  <div class="chat-container">
    <!-- Liste des utilisateurs -->
    <div class="users-list">
      <h4>Utilisateurs en ligne</h4>
      <div class="users-list-container">
        <div 
          class="user-item active"
          @click="selectUser(userData)">
          <div class="user-avatar">
            <img :src="userData.avatar" :alt="userData.name" v-if="userData.avatar" />
          </div>
          <div class="user-info">
            <span class="user-name">Vous</span>
            <span class="user-status online">En ligne</span>
          </div>
        </div>
        <div v-if="users.length > 0">
          <div v-for="user in users" :key="user.id" 
               class="user-item" 
               :class="{'active': selectedUser?.id === user.id}"
               @click="selectUser(user)">
            <div class="user-avatar">
              <img :src="user.avatar" :alt="user.name" v-if="user.avatar" />
            </div>
            <div class="user-info">
              <span class="user-name">{{ user.name }}</span>
              <span class="user-status" :class="{'online': user.online}">{{ user.online ? 'En ligne' : 'Hors ligne' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fenêtre de chat -->
    <div class="chat-window" v-if="selectedUser">
      <div class="chat-header">
        <button class="close-button" @click="closeChat">
          <i class="bi bi-x-lg"></i>
        </button>
        <div class="user-avatar">
          <img :src="selectedUser.avatar" :alt="selectedUser.name" v-if="selectedUser.avatar" />
          <span v-else>{{ selectedUser.name.charAt(0) }}</span>
        </div>
        <div class="user-info">
          <h4>{{ selectedUser.name }}</h4>
          <span class="status">{{ selectedUser.online ? 'En ligne' : 'Hors ligne' }}</span>
        </div>
      </div>
      <div class="chat-messages" ref="messagesContainer">
        <div v-for="(message, index) in messages" :key="index" 
             :class="['message', message.sender_id === userData.id ? 'user-message' : 'other-message']">
          <div class="message-content">
            <span class="message-text">{{ message.message }}</span>
            <span class="message-time">{{ formatTime(message.created_at) }}</span>
          </div>
        </div>
      </div>
      <div class="chat-input">
        <input v-model="newMessage" @keyup.enter="sendMessage" 
               type="text" placeholder="Tapez votre message..." class="form-control" />
        <button @click="sendMessage" class="btn btn-primary">Envoyer</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Echo from 'laravel-echo'
import axios from 'axios'
import Swal from 'sweetalert2'
import 'bootstrap-icons/font/bootstrap-icons.css'

const messages = ref([])
const newMessage = ref('')
const messagesContainer = ref(null)
const notifications = ref([])
const users = ref([])
const selectedUser = ref(null)
const userData = ref(JSON.parse(localStorage.getItem("user")))
const pollingInterval = ref(null)

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

const startPolling = () => {
  if (selectedUser.value) {
    pollingInterval.value = setInterval(async () => {
      await loadMessages(selectedUser.value.id)
    }, 3000) // Rafraîchir toutes les 3 secondes
  }
}

const stopPolling = () => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
}

const selectUser = async (user) => {
  selectedUser.value = user
  await loadMessages(user.id)
  startPolling()
}

const loadMessages = async (userId) => {
  try {
    const response = await axios.get(`/api/messages/${userId}`)
    messages.value = [...response.data.messages].reverse()
    scrollToBottom()
  } catch (error) {
    console.error('Erreur lors du chargement des messages:', error)
    sweetAlert('Erreur', 'Erreur lors du chargement des messages', 'error')
  }
}

const closeChat = () => {
  selectedUser.value = null
  stopPolling()
}

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/users')
    users.value = response.data.users
  } catch (error) {
    console.error('Erreur lors du chargement des utilisateurs:', error)
    sweetAlert('Erreur', 'Erreur lors du chargement des utilisateurs', 'error')
  }
}

const sendMessage = async () => {
  if (newMessage.value.trim() && selectedUser.value) {
    try {
      await axios.post('/api/messages', {
        recipient_id: selectedUser.value.id,
        content: newMessage.value
      }, {
        headers: {
          'X-CSRF-TOKEN': window.csrfToken
        }
      }).then((response) => {
        console.log('Message envoyé avec succès:', response.data)        
        newMessage.value = ''
        loadMessages(selectedUser.value.id)
      }).catch((error) => {
        console.error('Erreur lors de l\'envoi du message:', error)
        sweetAlert('Erreur', 'Erreur lors de l\'envoi du message', 'error')
      })
    } catch (error) {
      console.error('Erreur lors de l\'envoi du message:', error)
    } 
  }
}

const initializeWebSocket = async () => {
  window.Echo.private(`user.${userData.value.id}`)
    .listen('.App\\Events\\MessageSent', (event) => {
      if (event.message.recipient_id === userData.value.id || event.message.sender_id === userData.value.id) {
        messages.value.push(event.message);
        scrollToBottom();
      }
    })
    .listen('.App\\Events\\UsersUpdated', (event) => {
      users.value = event.users;
    });

  window.Echo.channel('notifications')
    .listen('.App\\Events\\NewNotification', (event) => {
      notifications.value.push(event.notification);
      sweetAlert('Nouvelle notification', event.notification.message, 'info');
    });
}

onMounted(async () => {
  const echoConfigured = await configEcho()
  if (echoConfigured) {
    await loadUsers()
    await initializeWebSocket()
  } 
})

onUnmounted(() => {
  window.Echo.leave(`user.${userData.value.id}`)
  stopPolling()
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
  display: flex;
  height: 100vh;
  background: var(--background-light);
}

.users-list {
  width: 300px;
  padding: 1.5rem;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.users-list-container {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem 0;
}

.user-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
}

.user-item:hover {
  background-color: #f1f5f9;
}

.user-item.active {
  background-color: #e2e8f0;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: var(--secondary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.user-info {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: var(--text-primary);
  display: block;
}

.user-status {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.user-status.online {
  color: #10b981;
}

.chat-window {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  margin: 1rem;
}

.chat-header {
  display: flex;
  align-items: center;
  padding: 1rem;
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  position: relative;
}

.close-button {
  position: absolute;
  right: 1rem;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  color: #6c757d;
  font-size: 1.2rem;
}

.close-button:hover {
  color: #495057;
}

.chat-header .user-avatar {
  width: 48px;
  height: 48px;
}

.status {
  font-size: 0.875rem;
  color: #3b82f6;
}

.chat-messages {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message {
  max-width: 80%;
  padding: 1rem;
  border-radius: var(--border-radius);
  background: white;
  box-shadow: var(--shadow-sm);
}

.message.user-message {
  margin-left: auto;
  background: var(--primary-color);
  color: white;
}

.message-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.message-text {
  font-size: 1rem;
  line-height: 1.5;
}

.message-time {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.chat-input {
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 1rem;
}

.form-control {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: var(--transition);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: var(--border-radius);
  font-weight: 600;
  transition: var(--transition);
}

.btn-primary {
  background: var(--primary-color);
  color: white;
  border: none;
}

.btn-primary:hover {
  background: var(--secondary-color);
}
</style>