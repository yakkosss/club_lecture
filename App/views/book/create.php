<?php
$pageTitle = 'Nouvelle lecture';
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

ob_start();
?>
<div class="card card--narrow" style="max-width: 620px;">
    <h1 class="card__title">Nouvelle lecture</h1>
    <p class="card__subtitle">Renseignez les informations du livre.</p>

    <?php if ($flashError): ?>
        <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <form class="form"
          action="<?= BASE_URL ?>index.php?controller=Book&action=createBook"
          method="POST"
          enctype="multipart/form-data">

        <div class="form__row">
            <label class="form__label" for="title">Titre *</label>
            <input class="form__input" type="text" id="title" name="title" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="author">Auteur *</label>
            <input class="form__input" type="text" id="author" name="author" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="synopsis">Synopsis</label>
            <textarea class="form__textarea" id="synopsis" name="synopsis" rows="5"></textarea>
        </div>
        <div class="form__row">
            <label class="form__label" for="release_date">Date de parution</label>
            <input class="form__input" type="date" id="release_date" name="release_date">
        </div>
        <div class="form__row">
            <label class="form__label" for="cover">Couverture</label>
            <input class="form__input" type="file" id="cover" name="cover" accept=".jpg,.jpeg,.png,.webp">
            <span class="form__hint">Formats acceptés : jpg, png, webp. Taille max : 2 Mo.</span>
        </div>

        <div class="form__actions">
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Book&action=index">Annuler</a>
            <button class="btn btn--primary" type="submit">Créer la lecture</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
