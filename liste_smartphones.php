<?php
session_start();  // La première instruction
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/permissions.php';
checkPageAccess();
// Récupérer la liste des smartphones
$sql = "SELECT id, nom, marque, prix, photo FROM smartphones ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$smartphones = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<h1 class="mb-4 page-title-centered">Liste des smartphones</h1>

<div class="row">
    <?php if (count($smartphones) > 0): ?>
        <?php foreach($smartphones as $smartphone): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-img-container">
                        <?php 
                        $imagePath = 'images/' . htmlspecialchars($smartphone['photo']);
                        $fullPath = __DIR__ . '/' . $imagePath;
                        if(!empty($smartphone['photo']) && file_exists($fullPath)): 
                        ?>
                            <img src="<?php echo $imagePath; ?>" 
                                 class="smartphone-image" 
                                 alt="<?php echo htmlspecialchars($smartphone['nom']); ?>">
                        <?php else: ?>
                            <i class="bi bi-phone"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($smartphone['nom']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($smartphone['marque']); ?></p>
                        <h6 class="text-primary"><?php echo number_format($smartphone['prix'], 2, ',', ' '); ?> FCFA</h6>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between">
                        <a href="details_smartphone.php?id=<?php echo $smartphone['id']; ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Détails
                        </a>
                        
                        <?php if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <div class="btn-group">
                            <a href="modifier_smartphone.php?id=<?php echo $smartphone['id']; ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm" 
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
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                Aucun smartphone n'a été trouvé dans la base de données.
            </div>
        </div>
    <?php endif; ?>
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
