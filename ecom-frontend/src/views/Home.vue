<template>
  <div class="home">
    <div class="hero-section text-center py-5">
      <h1 class="display-4">Bienvenue dans notre boutique</h1>
      <p class="lead">Découvrez notre collection exclusive</p>
    </div>

    <div class="row mt-5">
      <div class="col-md-4 mb-4" v-for="product in featuredProducts" :key="product.id">
        <div class="card h-100">
          <img :src="product.image" class="card-img-top" :alt="product.name">
          <div class="card-body">
            <h5 class="card-title">{{ product.name }}</h5>
            <p class="card-text">{{ product.description }}</p>
            <p class="card-text"><strong>{{ product.price }}€</strong></p>
            <button class="btn btn-outline-primary" @click="addToCart(product)">
              Ajouter au panier
            </button>
            <router-link :to="{ name: 'Products' }" class="btn btn-secondary">
              Voir tous les produits
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home',
  computed: {
    featuredProducts() {
      return this.$store.state.products.slice(0, 3)
    }
  },
  methods: {
    addToCart(product) {
      this.$store.commit('addToCart', product)
      this.$toast.success('Produit ajouté au panier !')
    }
  }
}
</script>

<style scoped>
.hero-section {
  background-color: #f8f9fa;
  border-radius: 10px;
  padding: 40px 20px;
}

.card {
  transition: transform 0.3s ease;
  border: 1px solid #e9ecef;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-img-top {
  height: 200px;
  object-fit: cover;
}

.card-title {
  font-weight: 600;
  color: #333;
}

.card-text {
  color: #6c757d;
}

.btn-outline-primary {
  padding: 6px 12px;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .card {
    margin-bottom: 20px;
  }
  
  .card-img-top {
    height: 180px;
  }
}
</style>
