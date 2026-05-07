<?php
$pageTitle = 'Inscription';
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
$old = $_SESSION['flash_old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['flash_old']);

ob_start();
?>
<div class="card card--narrow">
    <h1 class="card__title">Créer un compte</h1>
    <p class="card__subtitle">Rejoignez le club et suivez vos lectures.</p>

    <?php if ($flashError): ?>
        <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <form class="form" action="<?= BASE_URL ?>index.php?controller=Auth&action=register" method="POST">
        <div class="form__row">
            <label class="form__label" for="firstname">Prénom</label>
            <input class="form__input" type="text" id="firstname" name="firstname"
                   value="<?= htmlspecialchars($old['firstname'] ?? '') ?>" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="lastname">Nom</label>
            <input class="form__input" type="text" id="lastname" name="lastname"
                   value="<?= htmlspecialchars($old['lastname'] ?? '') ?>" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="email">Email</label>
            <input class="form__input" type="email" id="email" name="email"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="password">Mot de passe</label>
            <input class="form__input" type="password" id="password" name="password" required minlength="6">
            <span class="form__hint">Au moins 6 caractères.</span>
        </div>
        <div class="form__actions">
            <a href="<?= BASE_URL ?>index.php?controller=Auth&action=displayLoginForm">J'ai déjà un compte</a>
            <button class="btn btn--primary" type="submit">Créer mon compte</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
