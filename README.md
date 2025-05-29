# SplitZ

Ce projet a été développer pour l'otention de la certification au [titre RNCP 37674](https://www.francecompetences.fr/recherche/rncp/37674/) développeur web et web mobile avec la collaboration de la [3WAcademy](https://3wacademy.fr/).

## Lien du site en ligne:

[e-project.alwaysdata.net](https://e-project.alwaysdata.net)

# Description

SplitZ est une application simple et intuitive permettant d'organiser et suivre un budget personnel ou collectif en toute simplicité.
Pas de moyen de paiement ni de messagerie externe, tout ce passe dans l'application.

# Installation en local

## Prérequis

- **PHP** >= 7.4
- **MySQL** >= 5.7 ou **MariaDB** >= 10.2
- **Composer** (pour gérer les dépendances PHP)
- **Serveur web local** (XAMPP, WAMP, MAMP, ou serveur PHP intégré)

## Installation

### 1. Cloner le repository

```bash
git clone https://github.com/Tekass13/SplitZ.git
cd SplitZ
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Configuration de l'environnement

```bash
# Copier le fichier d'exemple
cp .env.example .env
```

Modifiez le fichier `.env` avec vos paramètres de base de données :

```env
DB_HOST=localhost
DB_NAME=nom_de_votre_base
DB_USER=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
DB_PORT=3306
```

### 4. Base de données

#### Créer la base de données

```sql
CREATE DATABASE nom_de_votre_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Importer la structure/données

```bash
# Si vous avez un fichier SQL
mysql -u votre_utilisateur -p splitz_bdd.sql

# Ou via phpMyAdmin : importer le fichier SQL fourni
```

### 5. Lancement du serveur

#### Option A : Serveur PHP intégré

```bash
php -S localhost:8000
```

#### Option B : XAMPP/WAMP/MAMP

1. Placez le projet dans le dossier `htdocs` (XAMPP) ou `www` (WAMP)
2. Démarrez Apache et MySQL
3. Accédez à `http://localhost/nom-du-projet`

## Accès à l'application

- **URL principale** : `http://localhost:8000` (serveur PHP) ou `http://localhost/nom-du-projet` (XAMPP/WAMP)
- **phpMyAdmin** : `http://localhost/phpmyadmin` (si XAMPP/WAMP)

## Structure du projet

```
votre-projet/
├── index.php          # Page d'accueil
├── config/
│   └── database.php   # Configuration BDD
├── assets/
│   ├── css/          # Fichiers CSS
│   ├── js/           # Fichiers JavaScript
│   └── images/       # Images
├── includes/         # Fichiers PHP réutilisables
├── database/
│   └── schema.sql    # Structure de la base de données
├── .env.example      # Variables d'environnement (exemple)
├── .env              # Variables d'environnement (à créer)
├── composer.json     # Dépendances PHP
└── README.md
```

## Problèmes courants

### Erreur de connexion à la base de données

- Vérifiez que MySQL est démarré
- Vérifiez les identifiants dans le fichier `.env`
- Assurez-vous que la base de données existe

### Erreur 500 (Internal Server Error)

- Vérifiez les logs d'erreur PHP
- Assurez-vous que toutes les dépendances sont installées avec `composer install`

### Permissions de fichiers (Linux/Mac)

```bash
sudo chmod -R 755 votre-projet/
sudo chown -R www-data:www-data votre-projet/
```

## Variables d'environnement disponibles

Voici les variables que vous pouvez configurer dans votre fichier `.env` :

```env
# Base de données
DB_HOST=localhost
DB_NAME=nom_base
DB_USER=utilisateur
DB_PASSWORD=mot_de_passe
DB_PORT=3306

# Configuration application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Email (si applicable)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe
```

## Contribution

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -am 'Ajout d'une nouvelle fonctionnalité'`)
4. Pushez vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request
