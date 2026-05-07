<?php
/** @var array $book */
$pageTitle = $book['title'];
$user = $_SESSION['user'] ?? null;
$canEdit   = $user && in_array($user['role'], ['admin', 'moderator'], true);
$canDelete = $user && $user['role'] === 'admin';

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

ob_start();
?>
<div style="margin-bottom: 18px;">
    <a href="<?= BASE_URL ?>index.php?controller=Book&action=index" style="color: var(--color-text-muted);">← Retour aux lectures</a>
</div>

<?php if ($flashSuccess): ?>
    <div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="card">
    <div style="display:grid; grid-template-columns: 220px 1fr; gap: 24px;" class="book-show">
        <div>
            <div style="aspect-ratio: 3/4; background: var(--color-bg-soft); border-radius: var(--radius); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <?php if (!empty($book['cover_path'])): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($book['cover_path']) ?>" alt=""
                         style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <span style="color: var(--color-text-muted); font-size: 14px;">Pas de couverture</span>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <h1 class="card__title" style="margin-bottom: 4px;"><?= htmlspecialchars($book['title']) ?></h1>
            <p class="card__subtitle" style="margin-bottom: 12px;">
                par <strong><?= htmlspecialchars($book['author']) ?></strong>
                <?php if (!empty($book['release_date'])): ?>
                    · <?= htmlspecialchars(substr($book['release_date'], 0, 10)) ?>
                <?php endif; ?>
            </p>

            <?php if (!empty($book['synopsis'])): ?>
                <p style="white-space: pre-wrap;"><?= htmlspecialchars($book['synopsis']) ?></p>
            <?php else: ?>
                <p style="color: var(--color-text-muted); font-style: italic;">Pas de synopsis renseigné.</p>
            <?php endif; ?>

            <?php if (!empty($book['creator_firstname'])): ?>
                <p style="color: var(--color-text-muted); font-size: 14px; margin-top: 16px;">
                    Ajouté par <?= htmlspecialchars($book['creator_firstname'] . ' ' . $book['creator_lastname']) ?>
                </p>
            <?php endif; ?>

            <?php if ($canEdit || $canDelete): ?>
                <div style="display:flex; gap:8px; margin-top:18px; flex-wrap:wrap;">
                    <?php if ($canEdit): ?>
                        <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Book&action=displayEditForm&id=<?= (int)$book['id'] ?>">Modifier</a>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                        <form method="POST"
                              action="<?= BASE_URL ?>index.php?controller=Book&action=deleteBook"
                              onsubmit="return confirm('Supprimer définitivement cette lecture ?');"
                              style="display:inline;">
                            <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
                            <button class="btn btn--danger" type="submit">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Section documents PDF -->
<?php
/** @var array $documents  liste des documents (passée par BookController::show) */
$canUpload    = $user && in_array($user['role'], ['admin', 'moderator'], true);
$canDeleteDoc = $canUpload;

if (!function_exists('club_format_bytes')) {
    function club_format_bytes(int $bytes): string {
        if ($bytes < 1024) return $bytes . ' o';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' Ko';
        return round($bytes / 1024 / 1024, 1) . ' Mo';
    }
}
?>
<div class="card" style="margin-top:18px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <h2 class="card__title" style="margin:0;">Documents</h2>
    </div>
    <p class="card__subtitle">PDF rattachés à cette lecture (fiches, articles, etc.)</p>

    <?php if ($canUpload): ?>
        <form action="<?= BASE_URL ?>index.php?controller=Document&action=upload"
              method="POST"
              enctype="multipart/form-data"
              style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; margin-bottom: 16px;">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <div class="form__row" style="flex:1; min-width:240px;">
                <label class="form__label" for="document">Ajouter un PDF</label>
                <input class="form__input" type="file" id="document" name="document" accept="application/pdf,.pdf" required>
                <span class="form__hint">Format : PDF uniquement. Taille max : 10 Mo.</span>
            </div>
            <button class="btn btn--primary" type="submit">Uploader</button>
        </form>
    <?php endif; ?>

    <?php if (empty($documents)): ?>
        <p style="color: var(--color-text-muted); margin: 0;">Aucun document pour le moment.</p>
    <?php else: ?>
        <ul style="list-style:none; padding:0; margin:0;">
            <?php foreach ($documents as $d): ?>
                <li style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; border:1px solid var(--color-border); border-radius:var(--radius); margin-bottom:8px; flex-wrap:wrap;">
                    <div style="min-width:0; flex:1;">
                        <div style="font-weight:600; word-break:break-word;"><?= htmlspecialchars($d['filename']) ?></div>
                        <div style="color:var(--color-text-muted); font-size:13px;">
                            <?= club_format_bytes((int)$d['size']) ?>
                            <?php if (!empty($d['uploader_firstname'])): ?>
                                · ajouté par <?= htmlspecialchars($d['uploader_firstname'] . ' ' . $d['uploader_lastname']) ?>
                            <?php endif; ?>
                            <?php if (!empty($d['uploaded_at'])): ?>
                                · <?= htmlspecialchars(substr($d['uploaded_at'], 0, 10)) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a class="btn btn--ghost"
                           href="<?= BASE_URL ?>index.php?controller=Document&action=download&id=<?= (int)$d['id'] ?>">
                            Télécharger
                        </a>
                        <?php if ($canDeleteDoc): ?>
                            <form method="POST"
                                  action="<?= BASE_URL ?>index.php?controller=Document&action=delete"
                                  onsubmit="return confirm('Supprimer ce document ?');"
                                  style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                <button class="btn btn--danger" type="submit">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:18px;">
    <h2 class="card__title">Avis et progression</h2>
    <p class="card__subtitle" style="color: var(--color-text-muted);">
        Sera disponible avec les modules 4 et 5 (avis avec modération + suivi de progression).
    </p>
</div>

<style>
@media (max-width: 640px) {
    .book-show { grid-template-columns: 1fr !important; }
}
</style>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
