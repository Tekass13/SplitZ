<?php
// Utilisation des constantes de configuration
$dsn = "mysql:host=localhost" . DB_HOST . ";dbname=splitz_bdd" . DB_NAME . ";charset=utf8mb4";

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Exemple de structure de la table users

/*
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NULL,
    name VARCHAR(255) NULL,
    picture VARCHAR(255) NULL,
    google_id VARCHAR(255) NULL UNIQUE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
);
*/