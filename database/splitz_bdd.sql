-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 21 mai 2025 à 23:17
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `splitz_bdd`
--

-- --------------------------------------------------------

--
-- Structure de la table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
CREATE TABLE IF NOT EXISTS `budgets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `budgets`
--

INSERT INTO `budgets` (`id`, `title`, `price`, `created_by`, `created_at`) VALUES
(35, 'Voyage', 228.00, 14, '2025-05-19 22:42:09');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `budget_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_id` (`budget_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `type`, `price`, `budget_id`) VALUES
(111, 'Essence', 'car', 228.00, 35);

-- --------------------------------------------------------

--
-- Structure de la table `category_participants`
--

DROP TABLE IF EXISTS `category_participants`;
CREATE TABLE IF NOT EXISTS `category_participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `participant_id` int NOT NULL,
  `category_id` int NOT NULL,
  `participant_amount` int NOT NULL DEFAULT '0',
  `invitation` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'En attente',
  `payment` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'En attente',
  PRIMARY KEY (`id`,`participant_id`,`category_id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`participant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `category_participants`
--

INSERT INTO `category_participants` (`id`, `participant_id`, `category_id`, `participant_amount`, `invitation`, `payment`) VALUES
(123, 16, 111, 76, 'Confirmé', 'Confirmé'),
(124, 17, 111, 76, 'Confirmé', 'Confirmé');

-- --------------------------------------------------------

--
-- Structure de la table `contacts_list`
--

DROP TABLE IF EXISTS `contacts_list`;
CREATE TABLE IF NOT EXISTS `contacts_list` (
  `user_id` int NOT NULL,
  `contact_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`contact_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contacts_list`
--

INSERT INTO `contacts_list` (`user_id`, `contact_id`) VALUES
(14, 16),
(14, 17);

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `recipient_id` (`recipient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `subject`, `content`, `is_read`, `created_at`) VALUES
(128, 14, 16, 'Invitation à participer au budget: Voyage', 'Vous êtes invité à participer à la catégorie \"Essence\" du budget \"Voyage\".<br><br>Pour confirmer votre participation, veuillez cliquer sur le lien suivant :<br><a href=\"index.php?route=confirm-participation&category_id=111&user_id=16\">Confirmer ma participation</a><br><br>Ou copiez-collez ce lien dans votre navigateur :<br>index.php?route=confirm-participation&category_id=111&user_id=16<br><br>Créer par tekass13500', 0, '2025-05-20 00:42:09'),
(129, 14, 17, 'Invitation à participer au budget: Voyage', 'Vous êtes invité à participer à la catégorie \"Essence\" du budget \"Voyage\".<br><br>Pour confirmer votre participation, veuillez cliquer sur le lien suivant :<br><a href=\"index.php?route=confirm-participation&category_id=111&user_id=17\">Confirmer ma participation</a><br><br>Ou copiez-collez ce lien dans votre navigateur :<br>index.php?route=confirm-participation&category_id=111&user_id=17<br><br>Créer par tekass13500', 0, '2025-05-20 00:42:09');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'USER',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unique_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `unique_id` (`unique_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`, `unique_id`) VALUES
(14, 'tekass13500', '$2y$10$UP.IKbEtQ.itUxdGWGFiiu4w5.9eQy4op/uXlJlOYB24Wu4EAMwnS', 'tekass56800@gmail.com', 'USER', '2025-05-14 22:22:33', 1199),
(16, 'user1', '$2y$10$6dWVZN540IGZh.iU2crxKO5cNsedAFrmAL8E2podfDtYbJdnsQg2O', 'user1@exemple.com', 'USER', '2025-05-19 21:55:58', 1138),
(17, 'user2', '$2y$10$vUuPGb/UBkt7LmXnPyFgpeztZRMn1m7SZHALm8RKLt1LwP6OLRa7m', 'user2@exemple.com', 'USER', '2025-05-19 21:56:27', 1217);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `category_participants`
--
ALTER TABLE `category_participants`
  ADD CONSTRAINT `category_participants_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `category_participants_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `contacts_list`
--
ALTER TABLE `contacts_list`
  ADD CONSTRAINT `contacts_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contacts_list_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
