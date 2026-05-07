<?php
$pageTitle = 'Connexion';
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

ob_start();
?>
<div class="card card--narrow">
    <h1 class="card__title">Connexion</h1>
    <p class="card__subtitle">Accédez à votre espace de lecture.</p>

    <?php if ($flashError): ?>
        <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <form class="form" action="<?= BASE_URL ?>index.php?controller=Auth&action=login" method="POST">
        <div class="form__row">
            <label class="form__label" for="email">Email</label>
            <input class="form__input" type="email" id="email" name="email" required autofocus>
        </div>
        <div class="form__row">
            <label class="form__label" for="password">Mot de passe</label>
            <input class="form__input" type="password" id="password" name="password" required>
        </div>
        <div class="form__actions">
            <a href="<?= BASE_URL ?>index.php?controller=Auth&action=displayRegisterForm">Pas encore inscrit&nbsp;?</a>
            <button class="btn btn--primary" type="submit">Se connecter</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
