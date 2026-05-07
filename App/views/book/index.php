<?php
/** @var array $books */
$pageTitle = 'Lectures';
$user = $_SESSION['user'] ?? null;
$canManage = $user && in_array($user['role'], ['admin', 'moderator'], true);

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

ob_start();
?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div>
            <h1 class="card__title">Lectures</h1>
            <p class="card__subtitle">Catalogue des livres suivis par le club.</p>
        </div>
        <?php if ($canManage): ?>
            <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Book&action=displayCreateForm">
                + Nouvelle lecture
            </a>
        <?php endif; ?>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <?php if (empty($books)): ?>
        <p style="color: var(--color-text-muted); margin-top: 16px;">
            Aucune lecture pour le moment.
            <?php if ($canManage): ?>Cliquez sur "+ Nouvelle lecture" pour en ajouter une.<?php endif; ?>
        </p>
    <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:18px; margin-top:18px;">
            <?php foreach ($books as $b): ?>
                <a class="book-card" href="<?= BASE_URL ?>index.php?controller=Book&action=show&id=<?= (int)$b['id'] ?>"
                   style="display:block; border:1px solid var(--color-border); border-radius:var(--radius); overflow:hidden; background:#fff; text-decoration:none; color:inherit; transition:box-shadow .15s, transform .15s;"
                   onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-2px)';"
                   onmouseout="this.style.boxShadow=''; this.style.transform='';">
                    <div style="aspect-ratio: 3/4; background: var(--color-bg-soft); display:flex; align-items:center; justify-content:center;">
                        <?php if (!empty($b['cover_path'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($b['cover_path']) ?>" alt=""
                                 style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span style="color: var(--color-text-muted); font-size: 14px;">Pas de couverture</span>
                        <?php endif; ?>
                    </div>
                    <div style="padding:12px 14px;">
                        <div style="font-weight:600; line-height:1.3;"><?= htmlspecialchars($b['title']) ?></div>
                        <div style="color:var(--color-text-muted); font-size:14px; margin-top:2px;">
                            <?= htmlspecialchars($b['author']) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
