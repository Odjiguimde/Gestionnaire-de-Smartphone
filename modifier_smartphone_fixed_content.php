<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est administrateur
requireAdmin('liste_smartphones.php');

// Vérifier si l'ID du smartphone est fourni et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de smartphone invalide";
    header('Location: liste_smartphones.php');
    exit();
}

$smartphone_id = (int)$_GET['id'];
$errors = array();
$success = false;

// Récupérer les données actuelles du smartphone
try {
    $stmt = $pdo->prepare("SELECT * FROM smartphones WHERE id = ?");
    $stmt->execute(array($smartphone_id));
    $smartphone = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
        $nom = ($nom === null) ? '' : $nom;
        
        $marque = filter_input(INPUT_POST, 'marque', FILTER_SANITIZE_STRING);
        $marque = ($marque === null) ? '' : $marque;
        
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $description = ($description === null) ? '' : $description;
        
        $prix = filter_input(INPUT_POST, 'prix', FILTER_SANITIZE_NUMBER_FLOAT, 
                           FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
        $prix = ($prix === null) ? 0 : $prix;
        
        $ram = filter_input(INPUT_POST, 'ram', FILTER_SANITIZE_STRING);
        $ram = ($ram === null) ? '' : $ram;
        
        $rom = filter_input(INPUT_POST, 'rom', FILTER_SANITIZE_STRING);
        $rom = ($rom === null) ? '' : $rom;
        
        $ecran = filter_input(INPUT_POST, 'ecran', FILTER_SANITIZE_STRING);
        $ecran = ($ecran === null) ? '' : $ecran;
        
        $couleurs = filter_input(INPUT_POST, 'couleurs', FILTER_SANITIZE_STRING);
        $couleurs = ($couleurs === null) ? '' : $couleurs;
        
        // Reste du code inchangé...
        
    } catch (Exception $e) {
        error_log("Erreur inattendue: " . $e->getMessage());
        $errors[] = "Une erreur inattendue s'est produite. Veuillez réessayer.";
    }
}

// Inclure le header après le traitement
include 'includes/header.php';
?>

<div class="container mt-4">
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
                            <h5 class="alert-heading">Erreurs :</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="editSmartphoneForm">
                        <div class="text-center mb-4">
                            <?php if(!empty($smartphone['photo'])): ?>
                                <img src="images/<?php echo htmlspecialchars($smartphone['photo']); ?>" 
                                     class="img-fluid rounded smartphone-image" 
                                     alt="<?php echo htmlspecialchars($smartphone['nom']); ?>">
                            <?php else: ?>
                                <div class="bg-light p-5 rounded">
                                    <i class="fas fa-mobile-alt fa-5x text-muted"></i>
                                    <p class="mt-2">Aucune image</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <label for="photo" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-camera"></i> Changer la photo
                                    <input type="file" 
                                           id="photo" 
                                           name="photo" 
                                           class="d-none" 
                                           accept="image/*"
                                           onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
                                </label>
                                <div class="form-text">Formats acceptés: JPG, PNG, GIF, WEBP (max 5MB)</div>
                            </div>
                            
                            <!-- Aperçu de la nouvelle image -->
                            <div id="imagePreview" class="mt-3 image-preview">
                                <h6>Nouvel aperçu :</h6>
                                <img id="preview" class="img-thumbnail preview-image">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nom" 
                                   name="nom" 
                                   value="<?php echo htmlspecialchars($smartphone['nom']); ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="marque" class="form-label">Marque <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="marque" 
                                   name="marque" 
                                   value="<?php echo htmlspecialchars($smartphone['marque']); ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?php echo htmlspecialchars($smartphone['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prix" class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="prix" 
                                           name="prix" 
                                           min="0" 
                                           step="0.01" 
                                           value="<?php echo htmlspecialchars($smartphone['prix']); ?>" 
                                           required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="ram" class="form-label">RAM</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ram" 
                                       name="ram" 
                                       value="<?php echo htmlspecialchars($smartphone['ram']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rom" class="form-label">Stockage</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="rom" 
                                       name="rom" 
                                       value="<?php echo htmlspecialchars($smartphone['rom']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="ecran" class="form-label">Écran</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ecran" 
                                       name="ecran" 
                                       value="<?php echo htmlspecialchars($smartphone['ecran']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="couleurs" class="form-label">Couleurs disponibles</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="couleurs" 
                                   name="couleurs" 
                                   value="<?php echo htmlspecialchars($smartphone['couleurs']); ?>"
                                   placeholder="Séparez les couleurs par des virgules">
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-between">
                            <a href="details_smartphone.php?id=<?php echo $smartphone_id; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                            
                            <div>
                                <button type="button" 
                                        class="btn btn-danger me-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce smartphone ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="supprimer_smartphone.php?id=<?php echo $smartphone_id; ?>" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Supprimer définitivement
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Afficher l'aperçu de l'image sélectionnée
document.getElementById('photo').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(this.files[0]);
    }
});

// Confirmation avant de quitter la page si des modifications ont été effectuées
window.addEventListener('beforeunload', function(e) {
    const form = document.getElementById('editSmartphoneForm');
    const formData = new FormData(form);
    let formChanged = false;
    
    // Vérifier si les champs du formulaire ont été modifiés
    formData.forEach((value, key) => {
        if (key !== 'photo' && value !== '') {
            formChanged = true;
        }
    });
    
    if (formChanged) {
        const confirmationMessage = 'Vous avez des modifications non enregistrées. Êtes-vous sûr de vouloir quitter ?';
        e.returnValue = confirmationMessage; // Pour la compatibilité avec certains navigateurs
        return confirmationMessage;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
