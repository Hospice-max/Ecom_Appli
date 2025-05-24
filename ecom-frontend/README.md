# E-Commerce Application

Une application e-commerce moderne construite avec Vue 3 et Vite.

## Fonctionnalités

- Système d'authentification complet (inscription, connexion, déconnexion)
- Profil utilisateur avec gestion de la photo de profil
- Gestion des commandes
- Interface de chat en temps réel
- Catalogue de produits
- Système de sécurité robuste

## Technologies Utilisées

- **Frontend**:
  - Vue 3 avec Composition API
  - Vite comme bundler
  - Bootstrap Icons pour les icônes
  - Axios pour les requêtes HTTP

- **Backend**:
  - PHP avec Composer
  - API RESTful

## Installation

1. Cloner le repository:
```bash
git clone [URL_DU_REPO]
cd windsurf-project
```

2. Installer les dépendances frontend:
```bash
npm install
```

3. Installer les dépendances backend:
```bash
composer install
```

4. Copier le fichier .env.example en .env et configurer les variables d'environnement

5. Démarrer le serveur de développement:
```bash
npm run dev
```

## Structure du Projet

```
windsurf-project/
├── public/           # Assets statiques
├── src/
│   ├── components/   # Composants réutilisables
│   ├── views/        # Pages principales
│   ├── App.vue       # Composant racine
│   └── main.js       # Point d'entrée
└── package.json      # Dépendances et scripts
```

## Scripts Disponibles

- `npm run dev` - Démarrer le serveur de développement
- `npm run build` - Créer une build de production
- `npm run preview` - Prévisualiser la build de production

## Contribution

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/amazing-feature`)
3. Commit vos changements (`git commit -m 'Add some amazing feature'`)
4. Push vers la branche (`git push origin feature/amazing-feature`)
5. Ouvrez une Pull Request

## License

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.
