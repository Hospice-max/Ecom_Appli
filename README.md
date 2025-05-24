# E-commerce Application

Une application moderne d'e-commerce construite avec Laravel pour le backend et Vue.js pour le frontend.

## 🚀 Technologies Utilisées

- **Backend**:
  - Laravel
  - PHP
  - MySQL

- **Frontend**:
  - Vue.js
  - Vite
  - JavaScript/TypeScript
  - CSS/SCSS

## 📋 Prérequis

Pour exécuter ce projet localement, vous devez avoir :

- PHP 8.0 ou supérieur
- Node.js 14.17 ou supérieur
- MySQL 5.7 ou supérieur
- Composer
- NPM ou Yarn

## 🛠️ Installation

1. Clonez le dépôt :
   ```bash
   git clone https://github.com/Hospice-max/Ecom_Appli.git
   cd Ecom_Appli
   ```

2. Installez les dépendances backend :
   ```bash
   cd ecom-backend
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. Installez les dépendances frontend :
   ```bash
   cd ../ecom-frontend
   npm install
   ```

4. Configurez la base de données dans le fichier `.env`

5. Lancez les migrations :
   ```bash
   cd ../ecom-backend
   php artisan migrate
   ```

6. Lancez les serveurs :
   - Backend : `php artisan serve` (par défaut sur http://localhost:8000)
   - Frontend : `npm run dev` (par défaut sur http://localhost:5173)

## 📦 Structure du Projet

```
EcomApp/
├── ecom-backend/      # Backend Laravel
│   ├── app/          # Logique métier
│   ├── config/       # Configuration
│   ├── database/     # Migrations et seeders
│   └── routes/       # Routes API
├── ecom-frontend/    # Frontend Vue.js
│   ├── src/         # Code source
│   ├── public/      # Assets statiques
│   └── package.json
└── README.md
```

## 🛠️ Fonctionnalités

- Authentification des utilisateurs
- Catalogue de produits
- Panier d'achat
- Système de paiement
- Gestion des commandes
- Interface d'administration

## 📝 Contribuer

1. Clonez le dépôt
2. Créez une branche pour votre fonctionnalité :
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. Committez vos changements :
   ```bash
   git commit -m 'Add some amazing feature'
   ```
4. Poussez la branche :
   ```bash
   git push origin feature/amazing-feature
   ```
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👥 Contact

Pour toute question ou suggestion, contactez-nous à :
- Email : contact@ecomapp.com
- GitHub : @Hospice-max
