<?php
/**
 * @var array       $myProgress        progressions personnelles
 * @var array|null  $stats             stats globales (admin/mod seulement)
 * @var array       $upcomingSessions  prochaines sessions
 */
$pageTitle = 'Accueil';
$user = $_SESSION['user'] ?? null;

ob_start();
?>
<section class="hero">
    <h1>Bienvenue<?= $user ? ', ' . htmlspecialchars($user['firstname']) : '' ?>&nbsp;!</h1>
    <p>Suivez vos lectures, partagez vos avis et rejoignez les sessions du club.</p>
</section>

<?php if ($user): ?>

<!-- Actions rapides -->
<div class="card">
    <h2 class="card__title">Votre espace</h2>
    <p class="card__subtitle">
        Connecté en tant que <strong><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></strong>
        <span class="badge badge--<?= htmlspecialchars($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>
    </p>
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px;">
        <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Book&action=index">Voir les lectures</a>
        <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Session&action=index">Sessions</a>
        <?php if (in_array($user['role'], ['admin', 'moderator'], true)): ?>
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Book&action=displayCreateForm">+ Nouvelle lecture</a>
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Session&action=displayCreateForm">+ Nouvelle session</a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats globales (admin/mod) -->
<?php if (!empty($stats)): ?>
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:12px; margin-top:18px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:32px; font-weight:700; color:var(--color-primary);"><?= $stats['total_books'] ?></div>
        <div style="color:var(--color-text-muted); font-size:14px;">Lectures</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:32px; font-weight:700; color:var(--color-primary);"><?= $stats['total_members'] ?></div>
        <div style="color:var(--color-text-muted); font-size:14px;">Membres</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:32px; font-weight:700; color:var(--color-primary);"><?= $stats['total_reviews'] ?></div>
        <div style="color:var(--color-text-muted); font-size:14px;">Avis publiés</div>
    </div>
</div>
<?php endif; ?>

<!-- Ma progression -->
<?php if (!empty($myProgress)): ?>
<div class="card" style="margin-top:18px;">
    <h2 class="card__title">Ma progression</h2>
    <?php foreach ($myProgress as $p): ?>
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
            <div style="flex:1; min-width:160px;">
                <div style="font-weight:600; font-size:14px;">
                    <a href="<?= BASE_URL ?>index.php?controller=Book&action=show&id=<?= (int)$p['book_id'] ?>"><?= htmlspecialchars($p['title']) ?></a>
                </div>
                <div style="color:var(--color-text-muted); font-size:13px;"><?= htmlspecialchars($p['author']) ?></div>
            </div>
            <div style="width:180px;">
                <div style="background:var(--color-bg-soft); border-radius:99px; height:8px; overflow:hidden;">
                    <div style="background:var(--color-primary); height:100%; width:<?= (int)$p['pourcentage'] ?>%; border-radius:99px;"></div>
                </div>
                <div style="font-size:12px; color:var(--color-text-muted); margin-top:2px;"><?= (int)$p['pourcentage'] ?> %</div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Prochaines sessions -->
<?php if (!empty($upcomingSessions)): ?>
<div class="card" style="margin-top:18px;">
    <h2 class="card__title">Prochaines sessions</h2>
    <?php foreach ($upcomingSessions as $s): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:8px 0; border-bottom:1px solid var(--color-border);">
            <div>
                <a href="<?= BASE_URL ?>index.php?controller=Session&action=show&id=<?= (int)$s['id'] ?>" style="font-weight:600;"><?= htmlspecialchars($s['titre']) ?></a>
                <span style="color:var(--color-text-muted); font-size:13px; margin-left:8px;">— <?= htmlspecialchars($s['book_title']) ?></span>
                <div style="color:var(--color-text-muted); font-size:13px;">📅 <?= htmlspecialchars(substr($s['date_heure'], 0, 16)) ?></div>
            </div>
            <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Session&action=show&id=<?= (int)$s['id'] ?>">Voir</a>
        </div>
    <?php endforeach; ?>
    <div style="margin-top:12px;">
        <a href="<?= BASE_URL ?>index.php?controller=Session&action=index" style="font-size:14px;">Voir toutes les sessions →</a>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="card">
    <h2 class="card__title">Rejoignez le club !</h2>
    <p>Connectez-vous ou créez un compte pour accéder au club de lecture.</p>
    <div style="display:flex; gap:8px; margin-top:12px;">
        <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Auth&action=displayLoginForm">Se connecter</a>
        <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Auth&action=displayRegisterForm">S'inscrire</a>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
