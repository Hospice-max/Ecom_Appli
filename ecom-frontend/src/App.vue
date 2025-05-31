<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
      <div class="container">
        <router-link to="/" class="navbar-brand">Mon E-commerce</router-link>

        <button
          class="navbar-toggler"
          type="button"
          @click="toggleMenu"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div
          class="collapse navbar-collapse"
          :class="{ show: isMenuOpen }"
          id="navbarNav"
        >
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link to="/" class="nav-link">Accueil</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/produits" class="nav-link">Produits</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/panier" class="nav-link">Panier</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/chat" class="nav-link">Messages</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/profil" class="nav-link">
                <i class="bi bi-person"></i> Paramètres
              </router-link>
            </li>
          </ul>
          <div class="nav-item">
            <router-link to="/panier" class="btn btn-light">
              <i class="bi bi-cart"></i> Panier ({{ cartCount }})
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <div class="container mt-4">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import { ref, computed } from "vue";
import { useStore } from "vuex";

export default {
  name: "App",
  setup() {
    const store = useStore();
    const cartCount = computed(() => store.state.cart.length);
    const isMenuOpen = ref(false);

    const toggleMenu = () => {
      isMenuOpen.value = !isMenuOpen.value;
    };

    return {
      cartCount,
      isMenuOpen,
      toggleMenu
    };
  }
};
</script>
<style>
:root {
  --primary-color: #2563eb;
  --secondary-color: #1e40af;
  --accent-color: #3b82f6;
  --background-color: #f8fafc;
  --text-color: #1f2937;
}

#app {
  font-family: "Inter", system-ui, -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: var(--text-color);
  background-color: var(--background-color);
  min-height: 100vh;
}

.navbar {
  background: linear-gradient(
    135deg,
    var(--primary-color),
    var(--secondary-color)
  );
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.navbar-brand {
  font-weight: 700;
  font-size: 1.5rem;
  color: white !important;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.navbar-brand:hover {
  transform: scale(1.05);
  text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
}

.nav-link {
  color: white !important;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
  padding: 0.5rem 1rem !important;
}

.nav-link::after {
  content: "";
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 0;
  left: 0;
  background-color: white;
  transition: width 0.3s ease;
}

.nav-link:hover::after {
  width: 100%;
}

.nav-link:hover {
  color: black !important;
  transform: translateY(-2px);
}

.btn-light {
  background: white;
  border: none;
  border-radius: 50px;
  padding: 0.5rem 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.btn-light:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.router-view {
  animation: fadeIn 0.5s ease-out;
}

/* Responsive Design */
@media (max-width: 768px) {
  .navbar-brand {
    font-size: 1.2rem;
  }

  .nav-link {
    padding: 0.3rem 0.8rem !important;
  }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #D2D2D2;
}

::-webkit-scrollbar-thumb {
  background: #D2D2D2;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #D2D2D2;
}

.new-navbar-collapse {
  display: none;
  flex-direction: column;
}

.new-navbar-collapse.show {
  display: flex;
}

/* Optionnel : comportement desktop */
@media (min-width: 992px) {
  .new-navbar-collapse {
    display: flex !important;
    flex-direction: row;
  }

  .new-navbar-toggler {
    display: none;
  }
}
</style>
