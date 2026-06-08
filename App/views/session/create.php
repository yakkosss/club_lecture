<?php
$pageTitle = 'Nouvelle session';
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

ob_start();
?>
<div style="margin-bottom:16px;">
    <a href="<?= BASE_URL ?>index.php?controller=Session&action=index" style="color:var(--color-text-muted);">← Retour aux sessions</a>
</div>
<div class="card" style="max-width:600px;">
    <h1 class="card__title">Créer une session</h1>
    <?php if ($flashError): ?><div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>index.php?controller=Session&action=create"
          style="display:flex; flex-direction:column; gap:14px;">

        <div class="form__row">
            <label class="form__label" for="book_id">Lecture associée *</label>
            <select class="form__input" id="book_id" name="book_id" required>
                <option value="">-- Choisir un livre --</option>
                <?php foreach ($books as $b): ?>
                    <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['title']) ?> — <?= htmlspecialchars($b['author']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form__row">
            <label class="form__label" for="titre">Titre de la session *</label>
            <input class="form__input" type="text" id="titre" name="titre" required>
        </div>

        <div class="form__row">
            <label class="form__label" for="date_heure">Date et heure *</label>
            <input class="form__input" type="datetime-local" id="date_heure" name="date_heure" required>
        </div>

        <div class="form__row">
            <label class="form__label" for="lieu">Lieu (physique)</label>
            <input class="form__input" type="text" id="lieu" name="lieu" placeholder="Ex: Médiathèque centrale">
        </div>

        <div class="form__row">
            <label class="form__label" for="lien">Lien (en ligne)</label>
            <input class="form__input" type="url" id="lien" name="lien" placeholder="https://meet.google.com/...">
        </div>

        <div class="form__row">
            <label class="form__label" for="description">Description</label>
            <textarea class="form__input" id="description" name="description" rows="3"></textarea>
        </div>

        <div style="display:flex; gap:8px;">
            <button class="btn btn--primary" type="submit">Créer la session</button>
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Session&action=index">Annuler</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
