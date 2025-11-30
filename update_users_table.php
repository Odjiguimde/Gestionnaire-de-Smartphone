<?php
require_once 'config/database.php';

try {
    // Vérifier si la colonne 'role' existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        // Ajouter la colonne 'role' avec une valeur par défaut 'user'
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
        echo "La colonne 'role' a été ajoutée à la table 'users'.<br>";
        
        // Mettre à jour le premier utilisateur comme admin
        $pdo->exec("UPDATE users SET role = 'admin' LIMIT 1");
        echo "Le premier utilisateur a été défini comme administrateur.<br>";
    } else {
        echo "La colonne 'role' existe déjà dans la table 'users'.<br>";
    }
    
    // Afficher la structure mise à jour
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Structure de la table 'users'</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Valeur par défaut</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Afficher les utilisateurs avec leurs rôles
    $stmt = $pdo->query("SELECT id, email, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Utilisateurs et leurs rôles</h2>";
    if (count($users) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Email</th><th>Rôle</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Aucun utilisateur trouvé dans la base de données.";
    }
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
