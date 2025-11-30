<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est administrateur
requireAdmin('liste_smartphones.php');

$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $marque = isset($_POST['marque']) ? trim($_POST['marque']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $prix = isset($_POST['prix']) ? trim($_POST['prix']) : '';
    $ram = isset($_POST['ram']) ? trim($_POST['ram']) : '';
    $rom = isset($_POST['rom']) ? trim($_POST['rom']) : '';
    $ecran = isset($_POST['ecran']) ? trim($_POST['ecran']) : '';
    $couleurs = isset($_POST['couleurs']) ? trim($_POST['couleurs']) : '';
    
    // Validation
    if (empty($nom)) $errors[] = "Le nom est obligatoire";
    if (empty($marque)) $errors[] = "La marque est obligatoire";
    if (!is_numeric($prix) || $prix <= 0) $errors[] = "Le prix doit être un nombre positif";
    
    // Gestion de l'upload de la photo
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/images/';
        // Créer le dossier s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filetype = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = $uploadDir . $new_filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                $photo = $new_filename;
            } else {
                $errors[] = "Erreur lors de l'upload de la photo. Vérifiez les permissions du dossier.";
            }
        } else {
            $errors[] = "Format de fichier non autorisé. Formats acceptés: " . implode(', ', $allowed);
        }
    } else {
        $errors[] = "Veuillez sélectionner une photo valide";
    }
    
    // Si pas d'erreurs, on insère dans la base
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO smartphones (nom, marque, description, prix, photo, ram, rom, ecran, couleurs) 
                                 VALUES (:nom, :marque, :description, :prix, :photo, :ram, :rom, :ecran, :couleurs)");
            
            $result = $stmt->execute([
                ':nom' => $nom,
                ':marque' => $marque,
                ':description' => $description,
                ':prix' => $prix,
                ':photo' => $photo,
                ':ram' => $ram,
                ':rom' => $rom,
                ':ecran' => $ecran,
                ':couleurs' => $couleurs
            ]);
            
            if ($result) {
                $success = true;
                // Réinitialiser les champs du formulaire
                $nom = $marque = $description = $prix = $ram = $rom = $ecran = $couleurs = '';
                $photo = '';
            } else {
                $errors[] = "Erreur lors de l'ajout du smartphone";
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Ajouter un nouveau smartphone</h2>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Le smartphone a été ajouté avec succès !
                        <a href="liste_smartphones.php" class="alert-link">Voir la liste</a>
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
                
                <form method="POST" enctype="multipart/form-data" id="smartphoneForm">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du smartphone *</label>
                        <input type="text" class="form-control" id="nom" name="nom" 
                               value="<?php echo htmlspecialchars(isset($_POST['nom']) ? $_POST['nom'] : ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="marque" class="form-label">Marque *</label>
                        <input type="text" class="form-control" id="marque" name="marque" 
                               value="<?php echo htmlspecialchars(isset($_POST['marque']) ? $_POST['marque'] : ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" style="height: 100px;" ><?php 
                            echo htmlspecialchars(isset($_POST['description']) ? $_POST['description'] : ''); 
                        ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prix" class="form-label">Prix (FCFA) *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="prix" name="prix" 
                                       value="<?php echo htmlspecialchars(isset($_POST['prix']) ? $_POST['prix'] : ''); ?>" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Photo *</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                            <div class="form-text">Formats acceptés: JPG, JPEG, PNG, GIF</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ram" class="form-label">Mémoire RAM (Go)</label>
                            <input type="text" class="form-control" id="ram" name="ram" 
                                   value="<?php echo htmlspecialchars((isset($_POST['ram']) && $_POST['ram'] != '') ? $_POST['ram'] : ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="rom" class="form-label">Stockage interne (Go)</label>
                            <input type="text" class="form-control" id="rom" name="rom" 
                                   value="<?php echo htmlspecialchars((isset($_POST['rom']) && $_POST['rom'] != '') ? $_POST['rom'] : ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ecran" class="form-label">Écran</label>
                        <input type="text" class="form-control" id="ecran" name="ecran" 
                               placeholder="Ex: 6.5\" Full HD+" 
                               value="<?php echo htmlspecialchars((isset($_POST['ecran']) && $_POST['ecran'] != '') ? $_POST['ecran'] : ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="couleurs" class="form-label">Couleurs disponibles</label>
                        <input type="text" class="form-control" id="couleurs" name="couleurs" 
                               placeholder="Séparez les couleurs par des virgules" 
                               value="<?php echo htmlspecialchars((isset($_POST['couleurs']) && $_POST['couleurs'] != '') ? $_POST['couleurs'] : ''); ?>">
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="liste_smartphones.php" class="btn btn-secondary me-md-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">Ajouter le smartphone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
