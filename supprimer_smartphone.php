<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

try {
    // Vérifier si l'utilisateur est administrateur
    requireAdmin('liste_smartphones.php');

    // Vérifier si l'ID du smartphone est fourni
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("ID de smartphone invalide");
    }

    $smartphone_id = (int)$_GET['id'];

    // Démarrer une transaction
    $pdo->beginTransaction();

    // Récupérer les informations du smartphone pour supprimer sa photo
    $stmt = $pdo->prepare("SELECT photo FROM smartphones WHERE id = ?");
    $stmt->execute([$smartphone_id]);
    $smartphone = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$smartphone) {
        throw new Exception("Smartphone introuvable");
    }

    // Supprimer la photo du serveur si elle existe
    if (!empty($smartphone['photo'])) {
        $photo_path = __DIR__ . '/images/' . $smartphone['photo'];
        if (file_exists($photo_path) && is_file($photo_path)) {
            if (!unlink($photo_path)) {
                throw new Exception("Impossible de supprimer le fichier image");
            }
        }
    }

    // Supprimer le smartphone de la base de données
    $stmt = $pdo->prepare("DELETE FROM smartphones WHERE id = ?");
    $stmt->execute([$smartphone_id]);

    // Valider la transaction
    $pdo->commit();

    $_SESSION['success_message'] = "Le smartphone a été supprimé avec succès";
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
}

// Rediriger vers la liste des smartphones
header('Location: liste_smartphones.php');
exit();
?>
