-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 27 juil. 2025 à 18:06
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet_dev_web`
--

-- --------------------------------------------------------

--
-- Structure de la table `smartphones`
--

CREATE TABLE `smartphones` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `marque` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `ram` varchar(20) DEFAULT NULL,
  `rom` varchar(20) DEFAULT NULL,
  `ecran` varchar(50) DEFAULT NULL,
  `couleurs` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `smartphones`
--

INSERT INTO `smartphones` (`id`, `nom`, `marque`, `description`, `prix`, `photo`, `ram`, `rom`, `ecran`, `couleurs`, `created_at`) VALUES
(1, 'Galaxy S21', 'Samsung', 'Smartphone haut de gamme avec écran AMOLED 120Hz', 552493.50, 'img_687eba93756629.96241125.png', '8', '128', '6.2&#34; Dynamic AMOLED ', 'Noir, Blanc, Violet', '2025-07-21 22:07:18'),
(2, 'iPhone 13', 'Apple', 'Dernier iPhone avec puce A15 Bionic', 590850.00, 'img_687ebaa8cfe3b5.47957825.jpg', '4', '128', '6.1&#34; Super Retina XDR', 'Bleu, Rose, Minuit, Étoilé', '2025-07-21 22:07:18'),
(3, 'Redmi Note 11', 'Xiaomi', 'Excellent rapport qualité-prix avec écran AMOLED', 192999.50, 'img_687ebe108abee1.03711576.png', '6', '128', '6.43&#34; AMOLED DotDisplay', 'Gris Graphite, Bleu Saphir, Vert Forêt', '2025-07-21 22:07:18');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin', '2025-07-21 22:07:18'),
(2, 'utilisateur', 'user', 'user', '2025-07-21 22:07:18'),
(3, 'Titans', '123456', 'user', '2025-07-22 18:38:05'),
(4, 'alpha', '$2y$10$khLo3Eto4/nXR2ArMNSBT.RQ5MPaG2lksh6WksTZ/n.FSeHbGZtyC', 'user', '2025-07-26 16:46:35'),
(5, 'Titan', '$2y$10$JacrnENeSW1D2jRuC4PYjudhHheYDzAsD5kULoBC.xdZSjZD7vzBO', 'user', '2025-07-26 20:30:11');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `smartphones`
--
ALTER TABLE `smartphones`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `smartphones`
--
ALTER TABLE `smartphones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
