<?php
session_start();
require_once 'config/database.php';
?>
<?php include 'includes/header.php'; ?>

<div class="jumbotron text-center">
    <h1 class="display-4">Bienvenue sur le Gestionnaire de Smartphones</h1>
    <p class="lead">Gérez facilement votre inventaire de smartphones</p>
    <hr class="my-4">
    <p>Découvrez nos fonctionnalités :</p>
    <div class="row mt-4 justify-content-center">
        <div class="col-md-5 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Liste des smartphones</h5>
                    <p class="card-text">Consultez tous les smartphones disponibles en stock.</p>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="liste_smartphones.php" class="btn btn-primary">Voir la liste</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-secondary">Connectez-vous pour voir la liste</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Ajouter un smartphone</h5>
                    <p class="card-text">Ajoutez un nouveau smartphone à votre inventaire.</p>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="ajouter_smartphone.php" class="btn btn-success">Ajouter</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-secondary">Connectez-vous pour ajouter</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
