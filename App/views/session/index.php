<?php
$pageTitle = 'Sessions';
$user      = $_SESSION['user'] ?? null;
$canCreate = $user && in_array($user['role'], ['admin', 'moderator'], true);

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

ob_start();
?>
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
    <h1 style="margin:0;">Sessions du club</h1>
    <?php if ($canCreate): ?>
        <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Session&action=displayCreateForm">+ Nouvelle session</a>
    <?php endif; ?>
</div>

<?php if ($flashSuccess): ?><div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

<?php if (empty($sessions)): ?>
    <div class="card"><p style="color:var(--color-text-muted);">Aucune session planifiée pour l'instant.</p></div>
<?php else: ?>
    <?php
    $now = new DateTime();
    $upcoming = array_filter($sessions, fn($s) => new DateTime($s['date_heure']) >= $now);
    $past     = array_filter($sessions, fn($s) => new DateTime($s['date_heure']) < $now);
    ?>
    <?php if ($upcoming): ?>
        <h2 style="margin:0 0 12px;">À venir</h2>
        <?php foreach ($upcoming as $s): ?>
            <div class="card" style="margin-bottom:12px; border-left:4px solid var(--color-primary);">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px; align-items:flex-start;">
                    <div>
                        <h3 style="margin:0 0 4px;"><a href="<?= BASE_URL ?>index.php?controller=Session&action=show&id=<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['titre']) ?></a></h3>
                        <p class="card__subtitle" style="margin:0;">
                            📚 <?= htmlspecialchars($s['book_title']) ?>
                            · 📅 <?= htmlspecialchars(substr($s['date_heure'], 0, 16)) ?>
                            <?php if (!empty($s['lieu'])): ?> · 📍 <?= htmlspecialchars($s['lieu']) ?><?php endif; ?>
                            <?php if (!empty($s['lien'])): ?> · 🔗 <a href="<?= htmlspecialchars($s['lien']) ?>" target="_blank">Lien</a><?php endif; ?>
                        </p>
                    </div>
                    <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Session&action=show&id=<?= (int)$s['id'] ?>">Voir</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($past): ?>
        <h2 style="margin:18px 0 12px; color:var(--color-text-muted);">Passées</h2>
        <?php foreach ($past as $s): ?>
            <div class="card" style="margin-bottom:12px; opacity:.7;">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px; align-items:center;">
                    <div>
                        <h3 style="margin:0 0 4px;"><?= htmlspecialchars($s['titre']) ?></h3>
                        <p class="card__subtitle" style="margin:0;">
                            📚 <?= htmlspecialchars($s['book_title']) ?>
                            · <?= htmlspecialchars(substr($s['date_heure'], 0, 16)) ?>
                        </p>
                    </div>
                    <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Session&action=show&id=<?= (int)$s['id'] ?>">Voir</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
