<?php
/** @var array $book */
$pageTitle = 'Modifier — ' . $book['title'];
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$dateValue = !empty($book['release_date']) ? substr($book['release_date'], 0, 10) : '';

ob_start();
?>
<div class="card card--narrow" style="max-width: 620px;">
    <h1 class="card__title">Modifier la lecture</h1>
    <p class="card__subtitle">Mettez à jour les informations du livre.</p>

    <?php if ($flashError): ?>
        <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <form class="form"
          action="<?= BASE_URL ?>index.php?controller=Book&action=updateBook"
          method="POST"
          enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">

        <div class="form__row">
            <label class="form__label" for="title">Titre *</label>
            <input class="form__input" type="text" id="title" name="title"
                   value="<?= htmlspecialchars($book['title']) ?>" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="author">Auteur *</label>
            <input class="form__input" type="text" id="author" name="author"
                   value="<?= htmlspecialchars($book['author']) ?>" required>
        </div>
        <div class="form__row">
            <label class="form__label" for="synopsis">Synopsis</label>
            <textarea class="form__textarea" id="synopsis" name="synopsis" rows="5"><?= htmlspecialchars($book['synopsis'] ?? '') ?></textarea>
        </div>
        <div class="form__row">
            <label class="form__label" for="release_date">Date de parution</label>
            <input class="form__input" type="date" id="release_date" name="release_date"
                   value="<?= htmlspecialchars($dateValue) ?>">
        </div>
        <div class="form__row">
            <label class="form__label" for="cover">Couverture</label>
            <?php if (!empty($book['cover_path'])): ?>
                <div style="margin-bottom: 8px;">
                    <img src="<?= BASE_URL . htmlspecialchars($book['cover_path']) ?>" alt=""
                         style="max-width:120px; height:auto; border-radius:var(--radius); border:1px solid var(--color-border);">
                </div>
            <?php endif; ?>
            <input class="form__input" type="file" id="cover" name="cover" accept=".jpg,.jpeg,.png,.webp">
            <span class="form__hint">Laissez vide pour conserver la couverture actuelle. Formats : jpg, png, webp. Max 2 Mo.</span>
        </div>

        <div class="form__actions">
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Book&action=show&id=<?= (int)$book['id'] ?>">Annuler</a>
            <button class="btn btn--primary" type="submit">Enregistrer</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
