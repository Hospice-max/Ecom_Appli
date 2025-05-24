<template>
  <div class="profile-container">
    <div class="profile-header">
      <div class="profile-avatar">
        <img
          :src="user.avatar || 'https://via.placeholder.com/150'"
          alt="Avatar"
        />
        <button class="avatar-upload-btn" @click="showUploadModal = true">
          <i class="bi bi-camera"></i>
        </button>
      </div>
      <div class="profile-info">
        <h1>{{ user.name }}</h1>
        <p class="email">{{ user.email }}</p>
        <div class="stats">
          <div class="stat-item">
            <span class="number">{{ ordersCount }}</span>
            <span class="label">Commandes</span>
          </div>
          <div class="stat-item">
            <span class="number">{{ productsCount }}</span>
            <span class="label">Produits</span>
          </div>
        </div>
      </div>
    </div>

    <div class="profile-tabs">
      <button
        :class="['tab-btn', { active: activeTab === 'profile' }]"
        @click="activeTab = 'profile'"
      >
        <i class="bi bi-person"></i>
        Profil
      </button>
      <button
        :class="['tab-btn', { active: activeTab === 'security' }]"
        @click="activeTab = 'security'"
      >
        <i class="bi bi-shield-lock"></i>
        Sécurité
      </button>
      <button
        :class="['tab-btn', { active: activeTab === 'orders' }]"
        @click="activeTab = 'orders'"
      >
        <i class="bi bi-cart-check"></i>
        Commandes
      </button>
    </div>

    <div class="profile-content">
      <div v-if="activeTab === 'profile'" class="profile-section">
        <h2>Informations personnelles</h2>
        <form @submit.prevent="updateProfile" class="profile-form">
          <div class="form-group">
            <label>Nom complet</label>
            <input v-model="profileForm.name" type="text" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="profileForm.email" type="email" required />
          </div>
          <div class="form-group">
            <label>Adresse</label>
            <input v-model="profileForm.address" type="text" />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              <span v-else>Mettre à jour</span>
            </button>
          </div>
        </form>
      </div>

      <div v-if="activeTab === 'security'" class="profile-section">
        <h2>Changer le mot de passe</h2>
        <form @submit.prevent="changePassword" class="profile-form">
          <div class="form-group">
            <label>Mot de passe actuel</label>
            <input v-model="passwordForm.current" type="password" required />
          </div>
          <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input v-model="passwordForm.new" type="password" required />
          </div>
          <div class="form-group">
            <label>Confirmer le nouveau mot de passe</label>
            <input v-model="passwordForm.confirm" type="password" required />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              <span v-else>Changer le mot de passe</span>
            </button>
          </div>
        </form>
        <div class="danger-zone">
          <button class="btn-danger" @click="confirmLogout">
            <i class="bi bi-box-arrow-right"></i>
            Déconnexion
          </button>
          <button class="btn-danger" @click="confirmDelete">
            <i class="bi bi-trash"></i>
            Supprimer le compte
          </button>
        </div>
      </div>

      <div v-if="activeTab === 'orders'" class="profile-section">
        <h2>Mes commandes</h2>
        <div class="orders-list">
          <div v-if="orders.length > 0">
              <div v-for="order in orders" :key="order.id" class="order-card">
                <div class="order-info">
                  <span class="order-number">Commande #{{ order.id }}</span>
                  <span class="order-date">{{ formatDate(order.date) }}</span>
                </div>
                <div class="order-status" :class="order.status">
                  {{ order.status }}
                </div>
              </div>
          </div>
          <div v-else>
            <p>Vous n'avez pas de commandes</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal pour le changement de photo -->
    <div v-if="showUploadModal" class="modal-overlay">
      <div class="modal-content">
        <h3>Changer la photo de profil</h3>
        <form @submit.prevent="uploadAvatar" class="upload-form">
          <input type="file" @change="handleFileChange" accept="image/*" />
          <div class="preview" v-if="previewImage">
            <img :src="previewImage" alt="Preview" />
          </div>
          <div class="form-actions">
            <button
              type="button"
              @click="showUploadModal = false"
              class="btn-outline"
            >
              Annuler
            </button>
            <button type="submit" class="btn-primary">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRouter } from 'vue-router';

const router = useRouter();

const user = ref({
  name: "John Doe",
  email: "john@example.com",
  avatar: "",
});

const orders = ref([]);

const ordersCount = ref();
const productsCount = ref(10); // Exemple

const activeTab = ref("profile");
const loading = ref(false);
const showUploadModal = ref(false);
const previewImage = ref(null);

const profileForm = ref({
  name: "",
  email: "",
  address: "",
});

const passwordForm = ref({
  current: "",
  new: "",
  confirm: "",
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    previewImage.value = URL.createObjectURL(file);
  }
};

    // Commandes
const getOrders = async () => {
    try {
    axios.get('/api/getOrders', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
        }
    }).then(response => {        
        orders.value = response.data;
        ordersCount.value = orders.value.length;
    }).catch(error => {
        console.error('Erreur lors de la récupération des commandes:', error);
        showErrorMessage('Erreur lors de la récupération des commandes')
    })
    } catch (error) {
        console.error('Erreur lors de la récupération des commandes:', error);
        showErrorMessage('Erreur lors de la récupération des commandes')
    }
}

const uploadAvatar = async () => {
  // Logique pour l'upload de l'avatar
  try {
    await axios.post(
      "/api/uploadAvatar",
      {
        avatar: previewImage.value,
      },
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Avatar mis à jour avec succès",
      toast: true,
      position: "top-right",
      timer: 3000,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text:
        error.response?.data?.message || "Erreur lors de l'upload de l'avatar",
      toast: true,
      position: "top-right",
      timer: 3000,
    });
  }
  showUploadModal.value = false;
};

const updateProfile = async () => {
  loading.value = true;
  try {
    // Logique pour la mise à jour du profil
    await axios.post(
      "/api/updateProfile",
      {
        name: profileForm.value.name,
        email: profileForm.value.email,
        password: profileForm.value.password,
        password_confirmation: profileForm.value.password_confirmation,
      },
      {
        headers: {
          "X-CSRF-TOKEN": window.csrfToken,
        },
      }
    );
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Profil mis à jour avec succès",
      toast: true,
      position: "top-right",
      timer: 3000,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: error.response?.data?.message || "Erreur lors de la mise à jour",
      toast: true,
      position: "top-right",
      timer: 3000,
    });
  } finally {
    loading.value = false;
  }
};

const changePassword = async () => {
  if (passwordForm.value.new !== passwordForm.value.confirm) {
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: "Les mots de passe ne correspondent pas",
      toast: true,
      position: "top-right",
      timer: 3000,
    });
    return;
  }

  loading.value = true;
  try {
    // Logique pour le changement de mot de passe
    await axios.post(
      "/api/changePassword",
      {
        current: passwordForm.value.current,
        new: passwordForm.value.new,
        confirm: passwordForm.value.confirm,
      },
      {
        headers: {
          "X-CSRF-TOKEN": window.csrfToken,
        },
      }
    );
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Mot de passe changé avec succès",
      toast: true,
      position: "top-right",
      timer: 3000,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text:
        error.response?.data?.message ||
        "Erreur lors du changement de mot de passe",
      toast: true,
      position: "top-right",
      timer: 3000,
    });
  } finally {
    loading.value = false;
  }
};

const confirmLogout = () => {
  Swal.fire({
    title: "Déconnexion",
    text: "Êtes-vous sûr de vouloir vous déconnecter ?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Déconnexion",
    cancelButtonText: "Annuler",
  }).then((result) => {
    if (result.isConfirmed) {
      try {
        axios.post("/api/logout", {
          headers: {
            "X-CSRF-TOKEN": window.csrfToken,
          },
        });
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        router.push("/login");
        Swal.fire({
          icon: "success",
          title: "Succès",
          text: "Déconnexion effectuée avec succès",
          toast: true,
          position: "top-right",
          timer: 3000,
        });
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text:
            error.response?.data?.message || "Erreur lors de la déconnexion",
          toast: true,
          position: "top-right",
          timer: 3000,
        });
      }
    }
  });
};

const confirmDelete = () => {
  Swal.fire({
    title: "Supprimer le compte",
    text: "Cette action est irréversible. Voulez-vous vraiment supprimer votre compte ?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Supprimer",
    cancelButtonText: "Annuler",
    confirmButtonColor: "#dc2626",
  }).then((result) => {
    if (result.isConfirmed) {
      // Logique pour la suppression du compte
      try {
        axios.delete("/api/deleteAccount", {
          headers: {
            "X-CSRF-TOKEN": window.csrfToken,
          },
        });
        Swal.fire({
          icon: "success",
          title: "Succès",
          text: "Compte supprimé avec succès",
          toast: true,
          position: "top-right",
          timer: 3000,
        });
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text:
            error.response?.data?.message ||
            "Erreur lors de la suppression du compte",
          toast: true,
          position: "top-right",
          timer: 3000,
        });
      }
    }
  });
};

onMounted(() => {
  // Charger les données utilisateur
  const userData = JSON.parse(localStorage.getItem("user"));
  if (userData) {
    user.value = userData;
    profileForm.value = { ...userData };
  }

  getOrders();
});
</script>

<style scoped>
.profile-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-bottom: 3rem;
  padding: 2rem;
  background: linear-gradient(
    135deg,
    var(--primary-color),
    var(--secondary-color)
  );
  border-radius: 16px;
  color: white;
}

.profile-avatar {
  position: relative;
}

.profile-avatar img {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-upload-btn {
  position: absolute;
  bottom: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.avatar-upload-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.profile-info {
  flex: 1;
}

.profile-info h1 {
  font-size: 2rem;
  margin: 0 0 0.5rem 0;
}

.email {
  font-size: 1.1rem;
  opacity: 0.9;
}

.stats {
  display: flex;
  gap: 2rem;
  margin-top: 1.5rem;
}

.stat-item {
  text-align: center;
}

.stat-item .number {
  font-size: 1.5rem;
  font-weight: bold;
}

.stat-item .label {
  font-size: 0.9rem;
  opacity: 0.8;
}

.profile-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1rem;
  border-radius: 12px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.tab-btn {
  flex: 1;
  padding: 0.75rem;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  border-radius: 8px;
}

.tab-btn:hover {
  background: rgba(37, 99, 235, 0.1);
}

.tab-btn.active {
  background: var(--primary-color);
  color: white;
}

.profile-section {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.profile-section h2 {
  margin: 0 0 1.5rem 0;
  color: var(--text-color);
}

.profile-form {
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
  font-weight: 500;
  color: var(--text-color);
}

.form-group input {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary:hover {
  background: var(--secondary-color);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-danger {
  background: #ef4444;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-danger:hover {
  background: #dc2626;
  transform: translateY(-2px);
}

.btn-outline {
  background: none;
  border: 1px solid #e5e7eb;
  color: var(--text-color);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-outline:hover {
  background: #f3f4f6;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.order-card {
  padding: 1rem;
  border-radius: 8px;
  background: #f9fafb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.order-number {
  font-weight: 500;
  color: var(--text-color);
}

.order-date {
  font-size: 0.9rem;
  color: #6b7280;
}

.order-status {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.order-status.en_attente {
  background: #e5e7eb;
  color: #6b7280;
}

.order-status.livree {
  background: #dcfce7;
  color: #166534;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  padding: 2rem;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.upload-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.preview {
  width: 100%;
  max-height: 200px;
  overflow: hidden;
  border-radius: 8px;
}

.preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
  display: inline-block;
  vertical-align: middle;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .profile-container {
    padding: 1rem;
  }

  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }

  .profile-info h1 {
    font-size: 1.5rem;
  }

  .stats {
    justify-content: center;
  }

  .profile-tabs {
    flex-direction: column;
    gap: 0.5rem;
  }

  .tab-btn {
    flex: none;
  }
}
</style>
