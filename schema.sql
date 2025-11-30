-- Création de la base de données
CREATE DATABASE IF NOT EXISTS projet_dev_web;
USE projet_dev_web;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des smartphones
CREATE TABLE IF NOT EXISTS smartphones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    marque VARCHAR(50) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    photo VARCHAR(255),
    ram VARCHAR(20),
    rom VARCHAR(20),
    ecran VARCHAR(50),
    couleurs TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de l'administrateur par défaut (mot de passe: admin123)
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertion d'un utilisateur de test (mot de passe: user123)
INSERT INTO users (username, password, role) 
VALUES ('utilisateur', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insertion de quelques smartphones de test
INSERT INTO smartphones (nom, marque, description, prix, ram, rom, ecran, couleurs) VALUES
('Galaxy S21', 'Samsung', 'Smartphone haut de gamme avec écran AMOLED 120Hz', 849.99, '8', '128', '6.2" Dynamic AMOLED 2X', 'Noir, Blanc, Violet'),
('iPhone 13', 'Apple', 'Dernier iPhone avec puce A15 Bionic', 909.00, '4', '128', '6.1" Super Retina XDR', 'Bleu, Rose, Minuit, Étoilé'),
('Redmi Note 11', 'Xiaomi', 'Excellent rapport qualité-prix avec écran AMOLED', 249.99, '6', '128', '6.43" AMOLED DotDisplay', 'Gris Graphite, Bleu Saphir, Vert Forêt'),
('Pixel 6', 'Google', 'Avec l\'IA Google et l\'appareil photo révolutionnaire', 649.00, '8', '128', '6.4" OLED 90Hz', 'Noir, Vert, Rose'),
('OnePlus 10 Pro', 'OnePlus', 'Performance fluide avec charge rapide', 899.00, '8', '256', '6.7" Fluid AMOLED 120Hz', 'Noir, Vert, Bleu');
