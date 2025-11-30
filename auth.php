<?php
/**
 * Vérifie si l'utilisateur est connecté
 * @return bool Retourne true si l'utilisateur est connecté, false sinon
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est un administrateur
 * @return bool Retourne true si l'utilisateur est un administrateur, false sinon
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirige l'utilisateur vers la page de connexion s'il n'est pas connecté
 * @param string $redirectAfterLogin URL de redirection après la connexion
 */
function requireLogin($redirectAfterLogin = '') {
    if (!isLoggedIn()) {
        if (!empty($redirectAfterLogin)) {
            $_SESSION['redirect_after_login'] = $redirectAfterLogin;
        }
        header('Location: /projet_dev_web/login.php');
        exit();
    }
}

/**
 * Redirige l'utilisateur s'il n'est pas administrateur
 * @param string $redirectUrl URL de redirection si l'utilisateur n'est pas admin (peut être un chemin relatif ou absolu)
 */
function requireAdmin($redirectUrl = 'index.php') {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error_message'] = "Accès refusé. Vous n'avez pas les droits d'administration.";
        // Vérifier si c'est un chemin relatif
        if (strpos($redirectUrl, '/') !== 0) {
            // C'est un chemin relatif, on ajoute le chemin de base
            $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/');
            $redirectUrl = $basePath . '/' . ltrim($redirectUrl, '/');
        }
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Affiche un message d'erreur s'il existe
 */
function displayError() {
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['error_message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['error_message']);
    }
}

/**
 * Affiche un message de succès s'il existe
 */
function displaySuccess() {
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['success_message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['success_message']);
    }
}
?>
