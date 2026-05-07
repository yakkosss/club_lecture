<?php
$pageTitle = 'Page introuvable';
ob_start();
?>
<div class="card">
    <div class="error-page">
        <p class="error-page__code">404</p>
        <h1 class="error-page__title">Page introuvable</h1>
        <p class="error-page__text">
            La page que vous cherchez n'existe pas ou a été déplacée.
        </p>
        <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Home&action=index">Retour à l'accueil</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
