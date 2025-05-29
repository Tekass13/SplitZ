# Splitz

This project was developed for certification for [Title RNCP 37674] (https://www.france incompetences.fr/recherche/rncp/37674/) Mobile web developer with the collaboration of [3wacademy] (https://3wacademy.fr/).

## Link of the online site:

[e-project.alwaysdata.net] (https://e-project.alwaysdata.net)

# Description

Splitz is a simple and intuitive application allowing to organize and follow a personal or collective budget with ease.
No means of payment or external messaging, everything goes in the application.

# Local installation

## Prerequis

- **PHP**> = 7.4
- **MySQL**> = 5.7 or **Mariadb**> = 10.2
- [**Composer**](https://getcomposer.org/doc/00-intro.md) (to manage PHP dependencies)
- **Local web server** (Xampp, Wamp, Mamp, or Integrated PHP server)

## Facility

### 1. Clone Restitory

```bash
git clone https://github.com/Tekass13/SplitZ.git
```

### 2. Navigate to the repository

```bash
cd SplitZ
```

### 3. Install PHP outbuildings

```bash
composer install
```

### 4. Configuration of the environment

```bash
# Copy exemple file
cp .env.example .env
```

Modify the file `.env` with your database settings:

```env
DB_HOST=localhost
DB_NAME=nom_de_votre_base
DB_USER=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
DB_PORT=3306
```

### 5. Database

#### Create the database

```sql
CREATE DATABASE splitz_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Import the structure/data

```bash
# If you have a SQL file
mysql -u your_username -p splitz_bdd.sql
# Or via phpmyadmin: import the SQL file provided
```

### 6. Launch of the server

#### Option A: Integrated PHP server

```bash
php -S localhost:8000
```

#### Option B: Xampp/Wamp/Mamp

1. Place the project in the `HTDOCS`(XAMPP) or `www` (WAMP) folder
2. Start Apache and MySQL
3. Access `http://localhost/SplitZ`

## Access to application

-** Main URL **: `http://Localhost:8000` (PHP server) or `http://Localhost/SplitZ` (Xampp/Wamp)

- ** phpmyadmin **: `http://localhost/phpmyadmin` (if xampp/wamp)
