<?php
/**
 * Vérifie si l'utilisateur a accès à une page spécifique
 * @param string $page La page à vérifier
 * @return bool Retourne true si l'utilisateur a accès, false sinon
 */
function hasAccess($page) {
    // Pages accessibles à tous
    $publicPages = [
        'index.php',
        'liste_smartphones.php',
        'details_smartphone.php'
    ];

    // Pages accessibles uniquement aux administrateurs
    $adminPages = [
        'ajouter_smartphone.php',
        'modifier_smartphone.php',
        'supprimer_smartphone.php'
    ];

    // Vérifier si c'est une page publique
    if (in_array($page, $publicPages)) {
        return true;
    }

    // Vérifier si c'est une page admin et si l'utilisateur est admin
    if (in_array($page, $adminPages) && isAdmin()) {
        return true;
    }

    // Par défaut, les pages nécessitent une authentification
    return isLoggedIn();
}

/**
 * Vérifie l'accès à la page actuelle et redirige si nécessaire
 * @param string $redirectUrl URL de redirection si l'accès est refusé
 */
function checkPageAccess($redirectUrl = '/projet_dev_web/index.php') {
    $currentPage = basename($_SERVER['PHP_SELF']);
    
    if (!hasAccess($currentPage)) {
        $_SESSION['error_message'] = "Accès refusé. Vous n'avez pas les droits nécessaires pour accéder à cette page.";
        header("Location: $redirectUrl");
        exit();
    }
}
