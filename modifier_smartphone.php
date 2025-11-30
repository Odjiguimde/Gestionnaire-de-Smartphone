<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est administrateur
require_once 'includes/permissions.php';
checkPageAccess();

// Vérifier si l'ID du smartphone est fourni et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de smartphone invalide";
    header('Location: liste_smartphones.php');
    exit();
}

$smartphone_id = (int)$_GET['id'];
$errors = [];
$success = false;

// Récupérer les données actuelles du smartphone
try {
    $stmt = $pdo->prepare("SELECT * FROM smartphones WHERE id = ?");
    $stmt->execute([$smartphone_id]);
    $smartphone = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérifier si le smartphone existe
    if (!$smartphone) {
        $_SESSION['error_message'] = "Le smartphone demandé n'existe pas";
        header('Location: liste_smartphones.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du smartphone: " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors de la récupération du smartphone";
    header('Location: liste_smartphones.php');
    exit();
}



// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération et validation des données du formulaire
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
        $marque = filter_input(INPUT_POST, 'marque', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $prix = filter_input(INPUT_POST, 'prix', FILTER_SANITIZE_NUMBER_FLOAT, 
                           FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
        $ram = filter_input(INPUT_POST, 'ram', FILTER_SANITIZE_STRING);
        $rom = filter_input(INPUT_POST, 'rom', FILTER_SANITIZE_STRING);
        $ecran = filter_input(INPUT_POST, 'ecran', FILTER_SANITIZE_STRING);
        $couleurs = filter_input(INPUT_POST, 'couleurs', FILTER_SANITIZE_STRING);

        // Initialiser les valeurs par défaut
        if ($nom === false) $nom = '';
        if ($marque === false) $marque = '';
        if ($description === false) $description = '';
        if ($prix === false) $prix = 0;
        if ($ram === false) $ram = '';
        if ($rom === false) $rom = '';
        if ($ecran === false) $ecran = '';
        if ($couleurs === false) $couleurs = '';

        // Validation des champs obligatoires
        if (empty($nom)) {
            $errors[] = "Le nom est obligatoire";
        } elseif (strlen($nom) > 100) {
            $errors[] = "Le nom ne doit pas dépasser 100 caractères";
        }
        
        if (empty($marque)) {
            $errors[] = "La marque est obligatoire";
        } elseif (strlen($marque) > 50) {
            $errors[] = "La marque ne doit pas dépasser 50 caractères";
        }
        
        if (!is_numeric($prix) || $prix <= 0) {
            $errors[] = "Le prix doit être un nombre positif";
        }

        // Gestion de l'upload de la photo si une nouvelle est fournie
        $photo = $smartphone['photo']; // Conserver l'ancienne photo par défaut
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Chemin absolu du dossier images
            $uploadDir = __DIR__ . '/images/';
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new RuntimeException("Impossible de créer le dossier d'upload");
                }
                chmod($uploadDir, 0777);
            }
            
            // Vérifier que le dossier est accessible en écriture
            if (!is_writable($uploadDir)) {
                throw new RuntimeException("Le dossier d'upload n'est pas accessible en écriture. Vérifiez les permissions.");
            }
            
            // Générer un nom de fichier sécurisé
            $originalName = basename($_FILES['photo']['name']);
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $fileName = uniqid('img_', true) . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            // Vérifier l'extension du fichier
            $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "Format de fichier non autorisé. Formats acceptés: " . 
                           implode(', ', $allowedExtensions);
            }
            
            // Vérifier la taille du fichier (max 5MB)
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['photo']['size'] > $maxFileSize) {
                $errors[] = "Le fichier est trop volumineux. La taille maximale autorisée est de 5MB.";
            }
            
            // Si aucune erreur, déplacer le fichier
            if (empty($errors)) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    // Changer les permissions du fichier
                    chmod($targetPath, 0666);
                    
                    // Si une ancienne photo existe, la supprimer
                    if (!empty($photo) && file_exists($uploadDir . $photo)) {
                        @unlink($uploadDir . $photo);
                    }
                    
                    // Stocker le chemin relatif dans la base
                    $photo = $fileName;
                    
                    // Vérifier si le fichier a été correctement déplacé
                    if (!file_exists($targetPath)) {
                        throw new RuntimeException("Le fichier n'a pas été correctement déplacé");
                    }
                } else {
                    throw new RuntimeException("Une erreur est survenue lors du téléchargement du fichier. Vérifiez les permissions du dossier.");
                }
            }
        }
    
        // Si pas d'erreurs, on met à jour dans la base
        if (empty($errors)) {
            try {
                // Démarrer une transaction
                $pdo->beginTransaction();
                
                $updateStmt = $pdo->prepare("UPDATE smartphones SET 
                    nom = ?, 
                    marque = ?, 
                    description = ?, 
                    prix = ?, 
                    ram = ?, 
                    rom = ?, 
                    ecran = ?, 
                    couleurs = ?,
                    photo = ?
                    WHERE id = ?");
                    
                $updateData = [
                    $nom,
                    $marque,
                    $description,
                    (float)$prix,
                    $ram,
                    $rom,
                    $ecran,
                    $couleurs,
                    $photo,
                    $smartphone_id
                ];
                
                if ($updateStmt->execute($updateData)) {
                    // Valider la transaction
                    $pdo->commit();
                    
                    $success = true;
                    $_SESSION['success_message'] = "Le smartphone a été mis à jour avec succès";
                    
                    // Recharger les données mises à jour
                    $stmt = $pdo->prepare("SELECT * FROM smartphones WHERE id = ?");
                    $stmt->execute([$smartphone_id]);
                    $smartphone = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Rediriger pour éviter le rechargement du formulaire
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $smartphone_id);
                    exit();
                } else {
                    throw new PDOException("Échec de la mise à jour du smartphone");
                }
            } catch (Exception $e) {
                // Annuler la transaction en cas d'erreur
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                
                // Journaliser l'erreur avec plus de détails
                error_log("Erreur lors de la mise à jour du smartphone " . $smartphone_id . ": " . 
                         $e->getMessage() . "\n" . 
                         "SQL: " . $updateStmt->queryString . "\n" . 
                         "Params: " . json_encode($updateData));
                
                // Afficher un message d'erreur plus détaillé
                $errors[] = "Une erreur est survenue lors de la mise à jour du smartphone: " . $e->getMessage();
                
                // Si c'est une erreur de contrainte d'unicité
                if ($e->getCode() == '23000') {
                    $errors[] = "Un smartphone avec ce nom existe déjà.";
                }
            }
        }
    } catch (Exception $e) {
        error_log("Erreur inattendue: " . $e->getMessage());
        $errors[] = "Une erreur inattendue s'est produite. Veuillez réessayer.";
    }
}

// Inclure le header après le traitement
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Modifier le smartphone</h2>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Le smartphone a été mis à jour avec succès !
                        <a href="details_smartphone.php?id=<?php echo $smartphone_id; ?>" class="alert-link">
                            Voir les détails
                        </a>
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
                
                <form method="POST" enctype="multipart/form-data" id="editSmartphoneForm">
                    <div class="text-center mb-4">
                        <?php 
                            $photoPath = $smartphone['photo'] ? 'images/' . htmlspecialchars($smartphone['photo']) : '';
                            if (!empty($photoPath) && file_exists(__DIR__ . '/' . $photoPath)):
                        ?>
                            <img src="<?php echo $photoPath; ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?php echo htmlspecialchars($smartphone['nom']); ?>"
                                 style="max-height: 200px;">
                        <?php else: ?>
                            <div class="text-center py-5 bg-light rounded">
                                <i class="bi bi-phone" style="font-size: 5rem; color: #6c757d;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <label for="photo" class="form-label">Changer la photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <div class="form-text">Laissez vide pour conserver l'image actuelle</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du smartphone *</label>
                        <input type="text" class="form-control" id="nom" name="nom" 
                               value="<?php echo htmlspecialchars($smartphone['nom']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="marque" class="form-label">Marque *</label>
                        <input type="text" class="form-control" id="marque" name="marque" 
                               value="<?php echo htmlspecialchars($smartphone['marque']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php 
                            if (isset($smartphone['description'])) {
                                echo htmlspecialchars($smartphone['description']);
                            } else {
                                echo '';
                            }
                        ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prix" class="form-label">Prix (FCFA) *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="prix" name="prix" 
                                       value="<?php echo htmlspecialchars($smartphone['prix']); ?>" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ram" class="form-label">Mémoire RAM (Go)</label>
                            <input type="text" class="form-control" id="ram" name="ram" 
                                   value="<?php echo htmlspecialchars(isset($smartphone['ram']) ? $smartphone['ram'] : ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="rom" class="form-label">Stockage interne (Go)</label>
                            <input type="text" class="form-control" id="rom" name="rom" 
                                   value="<?php echo htmlspecialchars(isset($smartphone['rom']) ? $smartphone['rom'] : ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ecran" class="form-label">Écran</label>
                        <input type="text" class="form-control" id="ecran" name="ecran" 
                               placeholder="Ex: 6.5\" Full HD+" 
                               value="<?php echo htmlspecialchars(isset($smartphone['ecran']) ? $smartphone['ecran'] : ''); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label for="couleurs" class="form-label">Couleurs disponibles</label>
                        <input type="text" class="form-control" id="couleurs" name="couleurs" 
                               placeholder="Séparez les couleurs par des virgules" 
                               value="<?php echo htmlspecialchars(isset($smartphone['couleurs']) ? $smartphone['couleurs'] : ''); ?>">
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-between">
                        <a href="details_smartphone.php?id=<?php echo $smartphone_id; ?>" 
                           class="btn btn-outline-secondary">
                            Annuler
                        </a>
                        
                        <div>
                            <button type="submit" class="btn btn-primary me-md-2">
                                <i class="bi bi-save"></i> Enregistrer les modifications
                            </button>
                            
                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="confirmDelete(<?php echo $smartphone_id; ?>)">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce smartphone ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="supprimer_smartphone.php?id=<?php echo $smartphone_id; ?>" 
                   class="btn btn-danger" id="confirmDeleteBtn">
                    Supprimer définitivement
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(smartphoneId) {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php include 'includes/footer.php'; ?>
