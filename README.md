# Splitz

This project was developed for certification for [Title RNCP 37674] (https://www.france incompetences.fr/recherche/rncp/37674/) Mobile web developer with the collaboration of [3wacademy] (https://3wacademy.fr/).

## Link of the online site:

[e-project.alwaysdata.net] (https://e-project.alwaysdata.net)

# Description

Splitz is a simple and intuitive application allowing to organize and follow a personal or collective budget with ease.
No means of payment or external messaging, everything goes in the application.

# Local installation

## Prerequis

- ** PHP **> = 7.4
- ** MySQL **> = 5.7 or ** Mariadb **> = 10.2
- ** Composer ** (to manage PHP dependencies)
- ** Local web server ** (Xampp, Wamp, Mamp, or Integrated PHP server)

## Facility

### 1. Clone Restitory

`bash
git clone https://github.com/tekass13/splitz.git
CD Splitz` `

### 2. Install PHP outbuildings

`bash
Install compose` `

### 3. Configuration of the environment

`` bash

# Copy the example file

cp .Well.
`` `

Modify the file `. Bid with your database settings:

`sending
Db_host = localhost
Db_name = nom_de_re_base
Db_user = your_Ustilizer
Db_password = your_mot_de_passe
Db_port = 3306` `

### 4. Database

#### Create the database

`sql
Create database nom_de_tre_base character set utf8mb4 collate utf8mb4_unicode_ci;` `

#### Import the structure/data

`` bash

# If you have a SQL file

MySQL -U your_Utilizer -p Splitz_Bdd.sql

# Or via phpmyadmin: import the SQL file provided

`` `

### 5. Launch of the server

#### Option A: Integrated PHP server

`bash
PHP -s Localhost: 8000` `

#### Option B: Xampp/Wamp/Mamp

1. Place the project in the HTDOCS`(XAMPP) or`www` (WAMP) folder
2. Start Apache and MySQL
3. Access `http: // localhost/nom-du-projet`

## Access to application

-** Main URL **: `http: // Localhost: 8000` (PHP server) or` http: // Localhost/Nom-du-Projet` (Xampp/Wamp)

- ** phpmyadmin **: `http: // localhost/phpmyadmin` (if xampp/wamp)

## Project structure

`` `
your project/
├├ index.php # Home page
├├ Config/
│ └ └ Database.Php # BDD configuration
├ asse Assets/
│ ├ ├ CSS/ # CSS files
│ ├ ├ JS/ # JavaScript files
│ └ └ Images/ # Images
├ ─ Includes/ # reusable PHP files
├ Database/
│ └ └ Schema.sql # Database structure
├ ─. Welcome # environment variables (example)
├├. ENV # Environment variables (to be created)
├├ compose.json # PHP outbuildings
└└ Readme.md `` `

## Current problems

### Connection error to the database

- Check that MySQL is started
- Check the identifiers in the file `.
- Make sure the database exists

### 500 error (internal server error)

- Check the PHP error logs
- Make sure all the outbuildings are installed with `Settle

### File permissions (Linux/Mac)

`bash
sudo chmod -r 755 your project/
sudo chown -r www-data: www-data your project/` `

## Environment variables available

Here are the variables you can configure in your file `.

`` sending

# Database

Db_host = localhost
Db_name = nom_base
Db_user = user
Db_password = word_de_passe
Db_port = 3306

# Application configuration

App_env = development
App_debug = True
App_URL = http: // localhost: 8000

# Email (if applicable)

Mail_host = smtp.gmail.com
Mail_port = 587
Mail_username=votre-mail@gmail.com
Mail_password = your-a-one-pass
`` `

## Contribution

1. Forke the project
2. Create a branch for your functionality (`checkout git -b feature/new -functionalite`)
3. Commit your changes (`git commit -am 'Addition of a new feature')
4. Pushez to the branch (`Git Push Origin Feature/New Function Note
5. Open a Request sweater
