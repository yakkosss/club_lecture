<?php
$pageTitle = 'Créer un utilisateur';
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

ob_start();
?>
<div class="card card--narrow">
    <h1 class="card__title">Créer un utilisateur</h1>
    <p class="card__subtitle">Création d'un compte (réservé aux administrateurs).</p>

    <?php if ($flashError): ?>
        <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <form class="form" action="<?= BASE_URL ?>index.php?controller=User&action=createUser" method="POST">
        <div class="form__row">
            <label class="form__label" for="firstname">Prénom</label>
            <input class="form__input" type="text" id="firstname" name="firstname" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="lastname">Nom</label>
            <input class="form__input" type="text" id="lastname" name="lastname" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="email">Email</label>
            <input class="form__input" type="email" id="email" name="email" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="password">Mot de passe</label>
            <input class="form__input" type="password" id="password" name="password" required minlength="6">
        </div>
        <div class="form__row">
            <label class="form__label" for="role">Rôle</label>
            <select class="form__select" id="role" name="role" required>
                <option value="admin">Administrateur</option>
                <option value="moderator">Modérateur</option>
                <option value="member" selected>Membre</option>
            </select>
        </div>
        <div class="form__actions">
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Home&action=index">Annuler</a>
            <button class="btn btn--primary" type="submit">Créer</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
