<template>
  <div class="home">
    <!-- Bannière Hero -->
    <div class="hero-section">
      <div class="container">
        <div class="row align-items-center text-center py-5">
          <div class="col-lg-6">
            <h1 class="display-4 mb-4">Bienvenue dans notre boutique</h1>
            <p class="lead mb-4">
              Découvrez notre collection exclusive de vêtements de qualité. Des
              pièces uniques pour votre style unique.
            </p>
            <div class="d-flex gap-3 justify-content-center">
              <router-link to="/produits" class="btn btn-primary btn-lg">
                <i class="bi bi-bag-plus"></i> Découvrir les produits
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Catégories -->
    <section class="categories-section py-5">
      <div class="container">
        <h2 class="text-center mb-4">Nos catégories</h2>
        <div class="row g-4">
          <div
            class="col-md-4"
            v-for="category in categories"
            :key="category.id"
          >
            <div class="category-card p-4 text-center">
              <i :class="category.icon" class="category-icon"></i>
              <h3 class="mt-3">{{ category.name }}</h3>
              <p class="text-muted">{{ category.description }}</p>
              <router-link
                :to="category.link"
                class="btn btn-outline-primary mt-3"
              >
                Explorer
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Produits en vedette -->
    <section class="featured-products py-5">
      <div class="container">
        <h2 class="text-center mb-4">Produits en vedette</h2>
        <div class="row g-4">
          <div
            class="col-md-4"
            v-for="product in featuredProducts"
            :key="product.id"
          >
            <div class="card h-100 shadow-sm">
              <img
                :src="product.image"
                class="card-img-top"
                :alt="product.name"
              />
              <div class="card-body">
                <h5 class="card-title">{{ product.name }}</h5>
                <p class="card-text text-muted">{{ product.description }}</p>
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="mb-0 text-primary">{{ product.price }}€</h5>
                  <button
                    @click="addToCart(product)"
                    class="btn btn-sm btn-outline-primary"
                  >
                    <i class="bi bi-cart"></i> Ajouter au panier
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter-section py-5 bg-light">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-8 text-center">
            <h3>Souscrivez à notre newsletter</h3>
            <p class="mb-4">
              Restez informé des dernières tendances et promotions exclusives.
            </p>
            <form class="row g-3">
              <div class="col">
                <input
                  type="email"
                  class="form-control"
                  placeholder="Votre email"
                />
              </div>
              <div class="col-auto">
                <button type="submit" class="btn btn-primary">S'abonner</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section py-4 bg-light text-white">
      <div class="container text-center">
        <p>&copy; 2023 Notre Boutique. Tous droits réservés.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, reactive } from "vue";
import { useStore } from "vuex";
import Swal from "sweetalert2";

const store = useStore();
const toast = Swal.fire;

const categories = reactive([
  {
    id: 1,
    name: "Vêtements Homme",
    description: "Collection complète pour les hommes modernes",
    icon: "bi bi-shirt",
    link: "/produits?category=homme",
  },
  {
    id: 2,
    name: "Vêtements Femme",
    description: "Pièces élégantes pour les femmes élégantes",
    icon: "bi bi-dress",
    link: "/produits?category=femme",
  },
  {
    id: 3,
    name: "Accessoires",
    description: "Accessoires tendance pour compléter votre look",
    icon: "bi bi-bag",
    link: "/produits?category=accessoires",
  },
]);

const featuredProducts = computed(() => store.state.products.slice(0, 3));


function addToCart(product) {
  store.commit("addToCart", product)  
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Produit ajouté au panier !",
      toast: true,
      position: "top-right",
      timer: 3000,
      showConfirmButton: false,
    })
}
</script>

<style scoped>
.hero-section,
.newsletter-section,
.footer-section {
  width: 100vw;
  margin-left: calc(-50vw + 50%);
}

.hero-section {
  padding: 80px 0;
  background: linear-gradient(
      rgba(132, 131, 131, 0.5),
      rgba(131, 130, 130, 0.5)
    ),
    url("/images/boutique.jpg") center/cover;
  background-size: cover;
  color: white;
  font-weight: bold;
  position: relative;
}

.hero-section::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
}

.hero-section h1 {
  font-weight: 700;
  line-height: 1.2;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.hero-section p {
  font-size: 1.2rem;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

.category-card {
  background: white;
  border-radius: 10px;
  transition: transform 0.3s ease;
  height: 100%;
}

.category-card:hover {
  transform: translateY(-5px);
}

.category-icon {
  font-size: 3rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.featured-products .card {
  transition: transform 0.3s ease;
}

.featured-products .card:hover {
  transform: translateY(-5px);
}

.newsletter-section {
  background: linear-gradient(rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.05));
}

.newsletter-section h3 {
  color: var(--primary-color);
  margin-bottom: 1.5rem;
}

.footer-section {
  background: linear-gradient(rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.05));
}

.footer-section p {
  color: var(--primary-color);
}

@media (max-width: 768px) {
  .hero-section {
    text-align: center;
  }

  .hero-section .col-lg-6:first-child {
    margin-bottom: 2rem;
  }

  .category-card {
    height: auto;
  }
}
</style>
