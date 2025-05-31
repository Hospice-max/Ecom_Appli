<template>
    <div class="login-container">
      <div class="login-box">
        <div class="login-header">
          <h1>{{ isLogin ? "Bienvenue" : "Inscription" }}</h1>
          <p>
            {{
              isLogin
                ? "Connectez-vous pour accéder à l'application"
                : "Créez votre compte"
            }}
          </p>
        </div>
  
        <form @submit.prevent="handleSubmit" class="login-form">
          <div v-if="!isLogin" class="form-group">
            <label for="name">Nom d'utilisateur</label>
            <input
              id="name"
              type="text"
              v-model="formData.name"
              required
              placeholder="Votre nom d'utilisateur"
            />
          </div>
  
          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              type="email"
              v-model="formData.email"
              required
              placeholder="Votre email"
            />
          </div>
  
          <div class="form-group">
            <label for="password">Mot de passe</label>
            <input
              id="password"
              type="password"
              v-model="formData.password"
              required
              placeholder="Mot de passe"
            />
          </div>
  
          <div v-if="!isLogin" class="form-group">
            <label for="confirm-password">Confirmer le mot de passe</label>
            <input
              id="confirm-password"
              type="password"
              v-model="formData.confirmPassword"
              required
              placeholder="Confirmez le mot de passe"
            />
          </div>
  
          <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
  
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <i :class="isLogin ? 'fas fa-sign-in-alt' : 'fas fa-user-plus'"></i>
            {{
              loading ? "Chargement..." : isLogin ? "Se connecter" : "S'inscrire"
            }}
          </button>
        </form>
  
        <div class="login-footer">
          <p>
            {{ isLogin ? "Pas de compte ?" : "Déjà un compte ?" }}
            <a href="#" @click.prevent="toggleForm">
              {{ isLogin ? "Créer un compte" : "Se connecter" }}
            </a>
          </p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { useRouter } from 'vue-router'
  import axios from 'axios'
  import Swal from 'sweetalert2'
  
  const router = useRouter()
  const isLogin = ref(true)
  const loading = ref(false)
  const errorMessage = ref('')
  
  const formData = ref({
    name: '',
    email: '',
    password: '',
    confirmPassword: ''
  })
  
  const toggleForm = () => {
    isLogin.value = !isLogin.value
    errorMessage.value = ''
    formData.value.password = ''
    formData.value.confirmPassword = ''
  }
  
  const validateForm = () => {
    if (!isLogin.value && formData.value.password !== formData.value.confirmPassword) {
      errorMessage.value = 'Les mots de passe ne correspondent pas.'
      return false
    }
    return true
  }
  
  const handleLogin = async () => {
    try {
      const { data } = await axios.post('/api/login', {
        email: formData.value.email,
        password: formData.value.password
      })
      
      if (data.success) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        showSuccessMessage('Connexion réussie')
        router.push('/profil')
      } else {
        throw new Error(data.message || 'Échec de la connexion')
      }
    } catch (error) {
      showErrorMessage(error.response?.data?.message || error.message)
    }
  }
  
  const handleRegister = async () => {
    try {
      const { data } = await axios.post('/api/register', {
        name: formData.value.name,
        email: formData.value.email,
        password: formData.value.password,
        password_confirmation: formData.value.confirmPassword
      })
      
      if (data.success) {
        router.push('/welcome')
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        showSuccessMessage('Inscription réussie')
      } else {
        throw new Error(data.message || 'Échec de l\'inscription')
      }
    } catch (error) {
      showErrorMessage(error.response?.data?.message || error.message)
    }
  }
  
  const handleLogout = async () => {
    try {
      const token = localStorage.getItem('token');
        if (!token) return;
    await fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        }).then(response => {
        
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        return response.ok;
      }).catch(error => {
        console.error('Erreur lors de la déconnexion:', error);
        showErrorMessage('Erreur lors de la déconnexion')
      });
      router.push('/login')
      showSuccessMessage('Déconnexion réussie')
    } catch (error) {
      showErrorMessage(error.response?.data?.message || error.message)
    }
  }
  
  const showSuccessMessage = (message) => {
    Swal.fire({
      icon: 'success',
      title: 'Succès',
      text: message,
      showConfirmButton: false,
      timer: 1500
    })
  }
  
  const showErrorMessage = (message) => {
    Swal.fire({
      icon: 'error',
      title: 'Erreur',
      text: message,
      showConfirmButton: false,
      timer: 1500
    })
  }
  
  const handleSubmit = async () => {
    if (!validateForm()) {
      return
    }
  
    loading.value = true
    errorMessage.value = ''
  
    try {
      if (isLogin.value) {
        await handleLogin()
      } else {
        await handleRegister()
      }
    } finally {
      loading.value = false
    }
  }
  </script>
<style scoped>
:root {
  --primary-color: #2563eb;
  --secondary-color: #1e40af;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --text-color: #1f2937;
  --text-light: #6b7280;
  --background: #f3f4f6;
  --card-bg: #ffffff;
  --shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
}

.login-container {
  position: relative;
  min-height: 100vh;
  overflow: hidden;
}

.login-box {
  position: relative;
  z-index: 1;
  background: rgba(255, 255, 255, 0.95);
  padding: 2.5rem;
  border-radius: 16px;
  box-shadow: var(--shadow);
  width: 100%;
  max-width: 400px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.login-header h1 {
  color: var(--text-color);
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
}

.login-header p {
  color: var(--text-light);
  margin: 0;
  font-size: 1.1rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: var(--text-color);
  font-weight: 500;
  font-size: 0.95rem;
}

.form-group input {
  padding: 0.875rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.2s;
  background: #f9fafb;
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.btn {
  padding: 0.875rem 1.5rem;
  border-radius: 10px;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
  border: none;
  font-weight: 600;
}

.btn-primary:hover {
  background: var(--secondary-color);
  transform: translateY(-1px);
}

.login-footer {
  text-align: center;
  margin-top: 1.5rem;
  color: var(--text-light);
}

.login-footer a {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.login-footer a:hover {
  color: var(--secondary-color);
  text-decoration: underline;
}

@media (max-width: 640px) {
  .login-box {
    padding: 2rem;
  }

  .login-header h1 {
    font-size: 1.75rem;
  }

  .form-group input {
    padding: 0.75rem 1rem;
  }

  .error-message {
    color: var(--danger-color);
    text-align: center;
    font-weight: 500;
    margin-top: -1rem;
  }
}
</style>
