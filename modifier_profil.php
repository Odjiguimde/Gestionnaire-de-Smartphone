<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$errors = [];
$success = false;

// Récupérer les informations actuelles de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
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

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        // Validation
        if (empty($username)) {
            $errors[] = "Le nom d'utilisateur est obligatoire";
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères";
        }
        
        if (empty($email)) {
            $errors[] = "L'email est obligatoire";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide";
        }
        
        // Vérifier si le nom d'utilisateur ou l'email existe déjà pour un autre utilisateur
        if (empty($errors)) {
            $stmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE (username = ? OR email = ?) 
                AND id != ?
            ");
            $stmt->execute([$username, $email, $_SESSION['user_id']]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                if ($existingUser['username'] === $username) {
                    $errors[] = "Ce nom d'utilisateur est déjà utilisé";
                }
                if ($existingUser['email'] === $email) {
                    $errors[] = "Cette adresse email est déjà utilisée";
                }
            }
        }
        
        // Si aucune erreur, mettre à jour le profil
        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET username = ?, email = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$username, $email, $_SESSION['user_id']])) {
                $_SESSION['username'] = $username;
                $success = true;
                $user = [
                    'username' => $username,
                    'email' => $email
                ];
            }
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du profil: " . $e->getMessage());
        $errors[] = "Une erreur est survenue lors de la mise à jour de votre profil";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">Modifier mon profil</h2>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Votre profil a été mis à jour avec succès !
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Sauvegarder les modifications
                            </button>
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour au profil
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
