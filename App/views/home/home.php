<?php
$pageTitle = 'Accueil';
$user = $_SESSION['user'] ?? null;

ob_start();
?>
<section class="hero">
    <h1>Bienvenue<?= $user ? ', ' . htmlspecialchars($user['firstname']) : '' ?>&nbsp;!</h1>
    <p>Suivez vos lectures, partagez vos avis et rejoignez les sessions du club.</p>
</section>

<?php if ($user): ?>
    <div class="card">
        <h2 class="card__title">Votre espace</h2>
        <p class="card__subtitle">
            Connecté en tant que <strong><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></strong>
            <span class="badge badge--<?= htmlspecialchars($user['role']) ?>">
                <?= htmlspecialchars($user['role']) ?>
            </span>
        </p>
        <p>
            Explorez le catalogue de lectures du club, suivez votre progression
            et participez aux discussions.
        </p>
        <p>
            <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Book&action=index">Voir les lectures</a>
            <?php if (in_array($user['role'], ['admin', 'moderator'], true)): ?>
                <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Book&action=displayCreateForm">+ Nouvelle lecture</a>
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div class="card">
        <h2 class="card__title">Vous n'êtes pas connecté</h2>
        <p>Connectez-vous ou créez un compte pour accéder au club.</p>
        <p>
            <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Auth&action=displayLoginForm">Se connecter</a>
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Auth&action=displayRegisterForm">S'inscrire</a>
        </p>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
