<?php
$pageTitle = 'Accès refusé';
ob_start();
?>
<div class="card">
    <div class="error-page">
        <p class="error-page__code">403</p>
        <h1 class="error-page__title">Accès refusé</h1>
        <p class="error-page__text">
            Vous n'avez pas les droits nécessaires pour accéder à cette page.
            Si vous pensez qu'il s'agit d'une erreur, contactez un administrateur.
        </p>
        <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Home&action=index">Retour à l'accueil</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
