<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT username, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur non trouvé";
        header('Location: /projet_dev_web/index.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du profil: " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors de la récupération de votre profil";
    header('Location: /projet_dev_web/index.php');
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">Mon profil</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Nom d'utilisateur</h5>
                            <p class="text-muted"><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <h5>Email</h5>
                            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <h5>Rôle</h5>
                            <p class="text-muted"><?php 
                                echo htmlspecialchars($user['role']);
                                if ($user['role'] === 'admin') {
                                    echo ' <span class="badge bg-danger">Admin</span>';
                                }
                            ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="modifier_profil.php" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Modifier mon profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
