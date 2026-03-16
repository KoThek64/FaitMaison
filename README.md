<h1 align="center">🍽️ Fait Maison</h1>
<p align="center"><em>Réseau social de partage de recettes, développé en PHP avec Symfony, PostgreSQL et Tailwind CSS.</em></p>

<p align="center">
  <a href="https://faitmaison.up.railway.app">
    <img src="https://img.shields.io/badge/🌐_Voir_le_site-Fait_Maison-C4622D?style=for-the-badge" alt="Voir le site">
  </a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Symfony-8.0-000000?logo=symfony&logoColor=white" alt="Symfony 8.0">
  <img src="https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white" alt="PostgreSQL 16">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS v4">
  <img src="https://img.shields.io/badge/PHPUnit-13-6C757D?logo=php&logoColor=white" alt="PHPUnit 13">
  <img src="https://img.shields.io/badge/Hosted_on-Railway-0B0D0E?logo=railway&logoColor=white" alt="Railway">
</p>

---

## 📋 Contexte du projet

**Fait Maison** est un projet personnel réalisé pour apprendre et pratiquer le développement PHP avec Symfony, en explorant la conception MVC, les bases de données relationnelles avec Doctrine ORM, la gestion d'upload de fichiers et les fonctionnalités sociales.

| | |
|---|---|
| **Type** | Projet personnel |
| **Auteur** | Mattys Lachaise |
| **Année** | 2025–2026 |

---

## 💡 Présentation

**Fait Maison** est une plateforme de partage de recettes inspirée de Marmiton. Les utilisateurs peuvent publier leurs recettes avec photos, les noter, les commenter, s'abonner à d'autres cuisiniers et découvrir un fil d'actualité et des recommandations personnalisées.

### Fonctionnalités

#### Recettes
- **CRUD complet** : créer, lire, modifier, supprimer une recette
- **Photo unique** par recette avec aperçu temps réel et drag & drop
- **Filtres** : catégorie, tag, difficulté, durée — conservés lors de la pagination
- **Pagination** (12 recettes / page) via KnpPaginatorBundle
- **Notation par étoiles** (1–5) avec auto-submit et restauration du scroll

#### Interactions sociales
- **Favoris** : sauvegarder une recette pour la retrouver plus tard
- **Likes** : apprécier une recette avec compteur
- **Commentaires** : laisser un avis avec suppression par l'auteur
- **Abonnements** : suivre / ne plus suivre un cuisinier
- Toutes les actions sociales fonctionnent **sans rechargement de page** (AJAX via `fetch` + `X-Requested-With`)

#### Administration
- **Dashboard** : statistiques globales (utilisateurs, recettes, commentaires, bannis actifs, dépubliées) + sections récentes
- **Gestion des recettes** : liste filtrée par statut (publiée / brouillon / dépubliée par admin), actions dépublier / republier / supprimer
- **Gestion des commentaires** : liste avec suppression via modal de confirmation
- **Gestion des utilisateurs** : liste paginée avec filtres (recherche + statut), actions bannir (durée prédéfinie + raison) / débannir / supprimer
- **Sécurité** : accès réservé à `ROLE_ADMIN` — les utilisateurs bannis sont bloqués à la connexion avec message personnalisé

#### Profil & Découverte
- **Profil utilisateur** : avatar, bio, statistiques, grille de recettes publiées
- **Fil d'actualité** (`/fil`) : recettes des personnes suivies, paginées
- **Recommandations** : suggestions basées sur les catégories likées, avec fallback tendances
- **Inscription / Connexion** sécurisées (Symfony Security + hachage mot de passe)

---

## 🛠️ Stack technique

| Composant | Technologie |
|-----------|-------------|
| **Langage** | PHP 8.4 |
| **Framework** | Symfony 8.0 |
| **Base de données** | PostgreSQL 16 (Docker) |
| **ORM** | Doctrine ORM |
| **Templates** | Twig |
| **CSS** | Tailwind CSS v4 via `symfonycasts/tailwind-bundle` |
| **Pagination** | KnpPaginatorBundle v6 |
| **Tests** | PHPUnit 13 + Symfony BrowserKit |
| **CI/CD** | GitHub Actions |
| **Déploiement** | Railway (FrankenPHP + Caddy) |

---

## 📁 Architecture du projet

```
FaitMaison/
├── .github/workflows/
│   └── ci.yml                          # Pipeline CI (tests + build CSS)
├── migrations/                         # Migrations Doctrine
├── public/
│   └── uploads/recipes/                # Photos de recettes et avatars
├── src/
│   ├── Controller/
│   │   ├── HomeController.php          # GET /
│   │   ├── SecurityController.php      # /login, /logout
│   │   ├── RegistrationController.php  # GET/POST /inscription
│   │   ├── RecipeController.php        # CRUD /recettes, /recette/{id}
│   │   ├── ProfileController.php       # /profil/{id}, /profil/modifier
│   │   ├── FeedController.php          # GET /fil (fil d'actualité)
│   │   ├── FavoriteController.php      # POST /recette/{id}/favori
│   │   ├── LikeController.php          # POST /recette/{id}/liker
│   │   ├── RatingController.php        # POST /recette/{id}/noter
│   │   ├── FollowController.php        # POST /profil/{id}/suivre
│   │   ├── CommentController.php       # POST /recette/{id}/commenter
│   │   └── AdminController.php         # /admin, /admin/recettes, /admin/commentaires, /admin/utilisateurs
│   ├── Entity/
│   │   ├── User.php                    # email, username, bio, avatarName, bannedUntil, banReason
│   │   ├── Recipe.php                  # title, steps, duration, imageName, adminNote…
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Favorite.php
│   │   ├── Rating.php                  # score 1–5
│   │   ├── Like.php                    # table `like` (mot réservé SQL)
│   │   ├── Comment.php                 # content 1000 chars
│   │   └── Follow.php                  # follower + followed
│   ├── Form/
│   │   ├── RegistrationType.php
│   │   ├── RecipeType.php              # imageFile mapped:false
│   │   ├── RecipeFilterType.php        # filtres GET (catégorie, tag, difficulté, durée)
│   │   └── ProfileType.php             # avatarFile mapped:false
│   ├── Repository/
│   │   ├── RecipeRepository.php        # findPublished, findPublishedQuery, findByFollowingQuery, findRecommended, findByAdminFilters, findAdminUnpublished
│   │   ├── RatingRepository.php        # findAverageForRecipe, findAverageForUser
│   │   ├── UserRepository.php          # findByAdminFilters, findBannedUsers
│   │   └── …                           # Repositories standards pour chaque entité
│   ├── EventSubscriber/
│   │   └── BanSubscriber.php           # Bloque la connexion des utilisateurs bannis
│   └── Service/
│       └── ImageUploader.php           # upload(UploadedFile): string + delete(string): void
├── templates/
│   ├── base.html.twig                  # Layout, sidebar rétractable
│   ├── home/index.html.twig            # Hero + recettes récentes
│   ├── recipe/                         # index, show, new, edit
│   ├── profile/                        # show, edit
│   └── feed/index.html.twig
├── assets/styles/app.css               # Tailwind CSS v4 (@theme, palette, polices)
├── tests/
│   ├── Controller/                     # HomeControllerTest, SecurityControllerTest, RecipeControllerTest
│   └── Service/                        # ImageUploaderTest
├── Caddyfile                           # Config FrankenPHP/Caddy pour Railway
└── compose.yaml                        # PostgreSQL via Docker
```

| Fichier / Dossier | Description |
|-------------------|-------------|
| `src/Controller/` | Logique HTTP : requêtes, réponses, redirections |
| `src/Entity/` | Modèles Doctrine : 9 entités, relations ManyToOne / OneToMany |
| `src/Form/` | Formulaires Symfony avec validation intégrée |
| `src/Repository/` | Accès base de données, requêtes DQL personnalisées |
| `src/Service/ImageUploader` | Gestion centralisée de l'upload et suppression de photos |
| `templates/` | Vues Twig avec composants réutilisables |
| `assets/styles/app.css` | Design system Tailwind CSS v4 : palette, typographie, composants |

---

## 🎨 Design System

- **Palette** : espresso `#1A0D06`, terracotta `#C4622D`, crème `#FAF4E8`, gold `#C9963A`
- **Typographie** : Playfair Display (titres) — Crimson Pro (corps) — Cormorant Garamond (accents)
- **Layout** : sidebar rétractable avec `matchMedia` pour desktop / mobile
- **Composants** : `recipe-card`, `btn-primary`, `btn-ghost`, `badge`, `content-card`

---

## 🗄️ Base de données

La base est initialisée via les migrations Doctrine. Elle compte 9 entités :

`user` · `recipe` · `category` · `tag` · `favorite` · `rating` · `like` · `comment` · `follow`

| Entité | États / champs notables |
|--------|------------------------|
| **Recette** | `isPublished: false` (brouillon) → `isPublished: true` (publiée) — `adminNote` non null = dépubliée par admin |
| **Utilisateur** | `bannedUntil` (DateTimeImmutable, nullable) + `banReason` — `isBanned()` calculé dynamiquement |

---

## 🧪 Tests

```bash
# Lancer tous les tests
php bin/phpunit

# Lancer un test spécifique
php bin/phpunit tests/Controller/RecipeControllerTest.php
php bin/phpunit tests/Service/ImageUploaderTest.php
```

### Couverture de tests

| Domaine | État |
|---------|------|
| Upload / suppression d'images | ✅ Couvert |
| Page d'accueil (GET /) | ✅ Couvert |
| Authentification (GET /login) | ✅ Couvert |
| Liste et protection des recettes | ✅ Couvert |
| Interactions sociales (likes, favoris, follows) | ⏳ À venir |
| Profil et fil d'actualité | ⏳ À venir |

Les tests s'exécutent automatiquement à chaque push sur `master` via **GitHub Actions** (build CSS inclus).

---

## 🌐 Déploiement

Le projet est déployé sur **Railway** via **Railpack** (détection automatique PHP).

| Élément | Détail |
|---------|--------|
| **Hébergeur** | Railway |
| **Serveur web** | FrankenPHP + Caddy |
| **Base de données** | Service PostgreSQL Railway |
| **URL** | [faitmaison.up.railway.app](https://faitmaison.up.railway.app) |

La configuration du serveur est définie dans le `Caddyfile` à la racine du projet.

---

## 🧑‍💻 Auteur

Développé par **Mattys Lachaise** — [mattys.contact@gmail.com](mailto:mattys.contact@gmail.com)

> ⚠️ Ce projet est personnel et n'accepte pas de contributions externes.

---

<p align="center">
  Projet réalisé avec ❤️ pour apprendre le développement PHP / Symfony
</p>