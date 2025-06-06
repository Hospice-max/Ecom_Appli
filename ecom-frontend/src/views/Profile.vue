<template>
  <div class="profile-container">
    <div class="profile-header">
      <div class="profile-avatar">
        <div class="profile-avatar">
          <img
            v-if="avatar"
            :src="avatar"
            alt="Avatar"
            class="rounded-circle w-100 h-100"
          />
          <span v-else
            ><svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 30 30"
              width="100"
              height="100"
            >
              <path fill="currentColor" d="M16 8a5 5 0 1 0 5 5a5 5 0 0 0-5-5" />
              <path
                fill="currentColor"
                d="M16 2a14 14 0 1 0 14 14A14.016 14.016 0 0 0 16 2m7.993 22.926A5 5 0 0 0 19 20h-6a5 5 0 0 0-4.992 4.926a12 12 0 1 1 15.985 0"
              />
            </svg> </span
          ><br />
          <span class="editBtn" @click="openFileInput">
            <i class="bi bi-pencil">Modifier</i>
          </span>
        </div>

        <div id="imagePreviewModal">
          <span
            style="
              position: absolute;
              top: 10px;
              right: 20px;
              font-size: 30px;
              font-weight: bold;
              color: white;
              cursor: pointer;
            "
            onclick="closePreview()"
            >&times;</span
          >
          <div
            style="
              display: flex;
              justify-content: center;
              align-items: center;
              height: 100%;
            "
          >
            <img id="previewImage" style="max-width: 90%; max-height: 90%" />
          </div>
          <button @click="confirmImage()" class="ConfirmBtn">Confirm</button>
        </div>
      </div>
      <div class="profile-info">
        <h1>{{ user.name }}</h1>
        <p class="text-muted">{{ user.email }}</p>
        <div class="stats mt-3">
          <div class="stat-item">
            <span class="stat-number">{{ ordersCount }}</span>
            <span class="stat-label text-white">Commandes</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">{{ wishlistCount }}</span>
            <span class="stat-label text-white">Favoris</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">{{ reviewsCount }}</span>
            <span class="stat-label text-white">Avis</span>
          </div>
        </div>
      </div>
    </div>

    <div class="profile-tabs mt-4">
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'profile' }"
        @click="activeTab = 'profile'"
      >
        <i class="bi bi-person"></i>
        Profil
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'orders' }"
        @click="activeTab = 'orders'"
      >
        <i class="bi bi-box"></i>
        Commandes
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'wishlist' }"
        @click="activeTab = 'wishlist'"
      >
        <i class="bi bi-heart"></i>
        Favoris
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'reviews' }"
        @click="activeTab = 'reviews'"
      >
        <i class="bi bi-star"></i>
        Avis
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'security' }"
        @click="activeTab = 'security'"
      >
        <i class="bi bi-shield-lock"></i>
        Sécurité
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
            <input
              v-model="profileForm.address"
              placeholder="inscrivez votre adresse"
              type="text"
            />
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input
              v-model="profileForm.phone"
              placeholder="inscrivez votre numero"
              type="tel"
            />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-success" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              <span v-else>Mettre à jour</span>
            </button>
          </div>
        </form>
      </div>

      <div v-if="activeTab === 'orders'" class="profile-section">
        <h2>Historique des commandes</h2>
        <div class="orders-list">
          <div v-for="order in orders" :key="order.id" class="order-item">
            <div class="order-header">
              <span class="order-date">{{ formatDate(order.date) }}</span>
              <span class="order-status" :class="order.status">{{
                order.status
              }}</span>
            </div>
            <div class="order-items">
              <div
                v-for="item in order.items"
                :key="item.id"
                class="order-item"
              >
                <img
                  :src="item.image"
                  :alt="item.name"
                  class="order-item-img"
                />
                <div class="order-item-info">
                  <h5>{{ item.name }}</h5>
                  <p class="text-muted">
                    {{ item.quantity }} x {{ item.price }}€
                  </p>
                </div>
              </div>
            </div>
            <div class="order-total">Total: {{ order.total }}€</div>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'wishlist'" class="profile-section">
        <h2>Favoris</h2>
        <div class="wishlist-grid">
          <div
            v-for="product in wishlist"
            :key="product.id"
            class="wishlist-item"
          >
            <img
              :src="product.image"
              :alt="product.name"
              class="wishlist-img"
            />
            <div class="wishlist-info">
              <h5>{{ product.name }}</h5>
              <p class="text-muted">{{ product.price }}€</p>
              <button
                @click="removeFromWishlist(product.id)"
                class="btn btn-danger btn-sm"
              >
                <i class="bi bi-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'reviews'" class="profile-section">
        <h2>Mes avis</h2>
        <div class="reviews-list">
          <div v-for="review in reviews" :key="review.id" class="review-item">
            <div class="review-header">
              <h5>{{ review.productName }}</h5>
              <div class="rating">
                <i
                  v-for="n in review.rating"
                  :key="n"
                  class="bi bi-star-fill text-warning"
                ></i>
              </div>
            </div>
            <p class="review-text">{{ review.text }}</p>
            <div class="review-footer">
              <span class="text-muted">{{ formatDate(review.date) }}</span>
              <button
                @click="deleteReview(review.id)"
                class="btn btn-sm btn-outline-danger ms-2"
              >
                <i class="bi bi-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </div>
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
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              <span v-else>Changer le mot de passe</span>
            </button>
          </div>
        </form>

        <div class="delete-account mt-4">
          <h3 class="text-danger">Supprimer le compte</h3>
          <p class="text-muted mb-3">
            Attention : Cette action est irréversible et supprimera
            définitivement votre compte.
          </p>
          <button @click="confirmDeleteAccount" class="btn btn-danger">
            <i class="bi bi-trash"></i> Supprimer mon compte
          </button>
        </div>
      </div>
    </div>

    <div class="profile-actions mt-4">
      <button @click="logout" class="btn btn-outline-danger">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import axios from "axios";
import store from "../store";

const router = useRouter();
const user = JSON.parse(localStorage.getItem("user"));
const activeTab = ref("profile");

// Formulaires
const profileForm = {
  name: user.name || "",
  email: user.email || "",
  address: user.address || "",
  phone: user.phone || "",
};

const passwordForm = {
  current: "",
  new: "",
  confirm: "",
};

const loading = ref(false);

// Ajoutez une référence pour l'avatar
const avatar = ref();

let selectedImageDataUrl = null;

// Fonction pour ouvrir le sélecteur de fichier
function openFileInput() {
  const fileInput = document.createElement("input");
  fileInput.type = "file";
  fileInput.accept = "image/*";
  fileInput.style.display = "none";

  fileInput.addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        selectedImageDataUrl = e.target.result;
        document.getElementById("previewImage").src = selectedImageDataUrl;
        document.getElementById("imagePreviewModal").style.display = "block";
      };
      reader.readAsDataURL(file);
    }
  });

  fileInput.click();
}

function closePreview() {
  document.getElementById("imagePreviewModal").style.display = "none";
}

function confirmImage() {
  if (selectedImageDataUrl) {
    const formData = new FormData();
    formData.append("avatar", selectedImageDataUrl);

    axios
      .post(`/api/update-avatar/${user.id}`, formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((response) => {
        if (response.data.success === true) {
          avatar.value = response.data.avatar;
          user.avatar = avatar.value;
          localStorage.setItem("user", JSON.stringify(user));
          Swal.fire({
            icon: "success",
            title: "Succès",
            text: response.data.message,
            timer: 2000,
            showConfirmButton: false,
          });
        }
      })
      .catch((error) => {
        console.error(error);
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text: error.response?.data?.message || "Une erreur est survenue",
          timer: 2000,
          showConfirmButton: false,
        });
      })
      .finally(() => {
        closePreview();
      });
  }
}

// Données simulées
const orders = ref([
  {
    id: 1,
    date: "2023-05-25",
    status: "livré",
    items: [{ id: 1, name: "Veste en cuir", price: 199.99, quantity: 1 }],
    total: 199.99,
  },
]);

const wishlist = ref([
  { id: 1, name: "Jean slim", price: 79.99, image: "/images/jean.jpg" },
]);

const reviews = ref([
  {
    id: 1,
    productName: "Veste en cuir",
    rating: 5,
    text: "Produit de très bonne qualité, correspond parfaitement à la description.",
    date: "2023-05-25",
  },
]);

// Computed
const ordersCount = computed(() => orders.length);
const wishlistCount = computed(() => wishlist.length);
const reviewsCount = computed(() => reviews.length);

// Fonctions
function formatDate(date) {
  return new Date(date).toLocaleDateString();
}

// TODO: Mettre à jour le profil
function updateProfile() {
  loading.value = true;
  // Logique de mise à jour du profil
  Swal.fire({
    icon: "success",
    title: "Succès",
    text: "Profil mis à jour avec succès !",
    timer: 2000,
    showConfirmButton: false,
  });
  loading.value = false;
}

// TODO: Changer le mot de passe
function changePassword() {
  if (passwordForm.new !== passwordForm.confirm) {
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: "Les mots de passe ne correspondent pas.",
    });
    return;
  }

  loading.value = true;
  // Logique de changement de mot de passe
  Swal.fire({
    icon: "success",
    title: "Succès",
    text: "Mot de passe mis à jour avec succès !",
    timer: 2000,
    showConfirmButton: false,
  });
  loading.value = false;
}

// Supprimer un article des favoris
function removeFromWishlist(productId) {
  // Logique de suppression de l'article des favoris
  const index = wishlist.findIndex((item) => item.id === productId);
  if (index > -1) {
    wishlist.splice(index, 1);
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Article retiré des favoris !",
      timer: 2000,
      showConfirmButton: false,
    });
  }
}

// Supprimer un avis
function deleteReview(reviewId) {
  const index = reviews.findIndex((r) => r.id === reviewId);
  if (index > -1) {
    reviews.splice(index, 1);
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Avis supprimé avec succès !",
      timer: 2000,
      showConfirmButton: false,
    });
  }
}

// Supprimer le compte
async function confirmDeleteAccount() {
  await Swal.fire({
    title: "Êtes-vous sûr ?",
    text: "Cette action est irréversible. Votre compte sera définitivement supprimé.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Oui, supprimer !",
    cancelButtonText: "Annuler",
  }).then((result) => {
    if (result.isConfirmed) {
      axios.delete("/api/delete-account").then((response) => {
        if (response.data.success === true) {
          logout();
          Swal.fire({
            icon: "success",
            title: "Supprimé",
            text: "Votre compte a été supprimé avec succès.",
            timer: 2000,
            showConfirmButton: false,
          });
        }
      });
    }
  });
}

const logout = async () => {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;
    await fetch("/api/logout", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    })
      .then((response) => {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        router.push("/login");
        return response.ok;
      })
      .catch((error) => {
        console.error("Erreur lors de la déconnexion:", error);
      });
  } catch (error) {
    console.error("Erreur lors de la déconnexion:", error);
  }
};

onMounted(() => {
  // Récupérer l'utilisateur depuis le store Vuex
  const currentUser = user;
  if (currentUser && currentUser.id) {
    // Utiliser l'ID de l'utilisateur connecté pour récupérer ses données
    axios
      .get(`/api/users/${currentUser.id}`)
      .then((response) => {
        user.value = response.data;
        avatar.value = user.value.avatar;
      })
      .catch((error) => {
        console.error(
          "Erreur lors de la récupération des données utilisateur:",
          error
        );
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text: "Impossible de récupérer les données utilisateur",
          timer: 3000,
          showConfirmButton: false,
        });
      });
  } else {
    console.error("Aucun utilisateur connecté trouvé dans le store");
    router.push("/login");
  }
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
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  background: linear-gradient(135deg, #6c757d, #d2d2d2);
}

.profile-avatar {
  width: 120px;
  height: 120px;
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-info h1 {
  font-size: 2rem;
  margin: 0;
}

.stats {
  display: flex;
  gap: 1.5rem;
  margin-top: 1rem;
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--primary-color);
}

.stat-label {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.profile-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.tab-btn {
  flex: 1;
  min-width: 120px;
  padding: 0.75rem;
  border: none;
  border-radius: 0.5rem;
  background: #cccccc;
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-btn.active {
  background: #93c47d;
  color: white;
}

.tab-btn:hover {
  opacity: 0.9;
}

.profile-section {
  background: var(--background-color);
  padding: 2rem;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-form {
  display: flex;
  flex-direction: column;
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
}

.form-group input {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
}

.form-actions {
  margin-top: 1rem;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.order-item {
  background: var(--background-color);
  padding: 1.5rem;
  border-radius: 0.5rem;
}

.order-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.order-status {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.8rem;
}

.order-status.livré {
  background: #d4edda;
  color: #155724;
}

.order-status.en-cours {
  background: #fff3cd;
  color: #856404;
}

.order-items {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.order-item-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 0.25rem;
}

.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
}

.wishlist-item {
  background: var(--background-color);
  padding: 1rem;
  border-radius: 0.5rem;
}

.wishlist-img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 0.25rem;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.review-item {
  background: var(--background-color);
  padding: 1.5rem;
  border-radius: 0.5rem;
}

.rating {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
}

.delete-account {
  background: #fff3f3;
  padding: 1.5rem;
  border-radius: 0.5rem;
  border: 1px solid #f8d7da;
}

.profile-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.profile-avatar {
  width: 100px; /* Set a fixed width */
  height: 100px; /* Set a fixed height */
  overflow: hidden; /* Prevent any overflow */
  position: relative; /* For positioning the edit button */
}

.profile-avatar img {
  width: 100%; /* Ensure the image fills the container */
  height: 100%; /* Ensure the image fills the container */
  object-fit: cover; /* Cover the area without stretching */
}

.editBtn {
  position: absolute;
  bottom: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
  color: white;
  padding: 5px;
  cursor: pointer;
  border-radius: 5px;
}

.editBtn i {
  font-size: 12px;
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

.editBtn {
  cursor: pointer;
}

.editBtn:hover {
  color: rgb(32, 129, 209);
  transform: scale(1.1);
  font-weight: bold;
}

#imagePreviewModal {
  display: none;
  position: fixed;
  z-index: 1;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
}
.ConfirmBtn {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 10px 20px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
</style>
