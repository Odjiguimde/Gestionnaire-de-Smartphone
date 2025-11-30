<?php
session_start();
require_once 'config/database.php';

// Vérifier si l'ID du smartphone est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste_smartphones.php');
    exit();
}

$smartphone_id = (int)$_GET['id'];

// Récupérer les détails du smartphone
$stmt = $pdo->prepare("SELECT * FROM smartphones WHERE id = ?");
$stmt->execute([$smartphone_id]);
$smartphone = $stmt->fetch();

// Si le smartphone n'existe pas, rediriger
if (!$smartphone) {
    header('Location: liste_smartphones.php');
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <?php if(!empty($smartphone['photo'])): ?>
                <div class="details-image-container">
                    <img src="images/<?php echo htmlspecialchars($smartphone['photo']); ?>" 
                         class="details-smartphone-image" 
                         alt="<?php echo htmlspecialchars($smartphone['nom']); ?>">
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-light">
                    <i class="bi bi-phone large"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h1 class="h2"><?php echo htmlspecialchars($smartphone['nom']); ?></h1>
                <p class="h4 text-muted mb-4"><?php echo htmlspecialchars($smartphone['marque']); ?></p>
                
                <div class="d-flex align-items-center mb-4">
                    <span class="display-5 text-primary fw-bold me-3">
                        <?php echo number_format($smartphone['prix'], 2, ',', ' '); ?> FCFA
                    </span>
                    <span class="badge bg-success">En stock</span>
                </div>
                
                <?php if(!empty($smartphone['description'])): ?>
                    <div class="mb-4">
                        <h3 class="h5">Description</h3>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($smartphone['description'])); ?></p>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <?php if(!empty($smartphone['ram'])): ?>
                        <div class="col-6 mb-3">
                            <h4 class="h6 text-muted mb-1">Mémoire RAM</h4>
                            <p class="mb-0"><?php echo htmlspecialchars($smartphone['ram']); ?> Go</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($smartphone['rom'])): ?>
                        <div class="col-6 mb-3">
                            <h4 class="h6 text-muted mb-1">Stockage interne</h4>
                            <p class="mb-0"><?php echo htmlspecialchars($smartphone['rom']); ?> Go</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($smartphone['ecran'])): ?>
                        <div class="col-6 mb-3">
                            <h4 class="h6 text-muted mb-1">Écran</h4>
                            <p class="mb-0"><?php echo htmlspecialchars($smartphone['ecran']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($smartphone['couleurs'])): ?>
                        <div class="col-12 mb-3">
                            <h4 class="h6 text-muted mb-1">Couleurs disponibles</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <?php 
                                $couleurs = explode(',', $smartphone['couleurs']);
                                foreach ($couleurs as $couleur): 
                                    $couleur = trim($couleur);
                                    if (!empty($couleur)):
                                ?>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($couleur); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-between mt-4">
                    <div>
                        <a href="liste_smartphones.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                    
                    <?php if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <div class="btn-group">
                        <a href="modifier_smartphone.php?id=<?php echo $smartphone['id']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <button type="button" 
                                class="btn btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal"
                                data-id="<?php echo $smartphone['id']; ?>"
                                data-nom="<?php echo htmlspecialchars($smartphone['nom']); ?>">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
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
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Supprimer</a>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion de la modale de suppression
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        const modal = new bootstrap.Modal(deleteModal);
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const modalTitle = deleteModal.querySelector('.modal-title');
        
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const smartphoneId = button.getAttribute('data-id');
            const smartphoneName = button.getAttribute('data-nom');
            
            modalTitle.textContent = `Supprimer ${smartphoneName} ?`;
            confirmDeleteBtn.href = `supprimer_smartphone.php?id=${smartphoneId}`;
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
