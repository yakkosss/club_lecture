<?php
/**
 * @var array      $session
 * @var bool       $isRegistered
 * @var array|null $attendees
 */
$pageTitle = $session['titre'];
$user      = $_SESSION['user'];
$canMod    = in_array($user['role'], ['admin', 'moderator'], true);
$isPast    = new DateTime($session['date_heure']) < new DateTime();

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

ob_start();
?>
<div style="margin-bottom:16px;">
    <a href="<?= BASE_URL ?>index.php?controller=Session&action=index" style="color:var(--color-text-muted);">← Retour aux sessions</a>
</div>

<?php if ($flashSuccess): ?><div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

<div class="card">
    <h1 class="card__title"><?= htmlspecialchars($session['titre']) ?></h1>
    <p class="card__subtitle">
        📚 <a href="<?= BASE_URL ?>index.php?controller=Book&action=show&id=<?= (int)$session['book_id'] ?>"><?= htmlspecialchars($session['book_title']) ?></a>
        · 📅 <?= htmlspecialchars(substr($session['date_heure'], 0, 16)) ?>
        <?php if ($isPast): ?><span class="badge badge--warning">Passée</span><?php endif; ?>
    </p>

    <?php if (!empty($session['lieu'])): ?>
        <p>📍 <strong>Lieu :</strong> <?= htmlspecialchars($session['lieu']) ?></p>
    <?php endif; ?>
    <?php if (!empty($session['lien'])): ?>
        <p>🔗 <strong>Lien :</strong> <a href="<?= htmlspecialchars($session['lien']) ?>" target="_blank"><?= htmlspecialchars($session['lien']) ?></a></p>
    <?php endif; ?>
    <?php if (!empty($session['description'])): ?>
        <p style="white-space:pre-wrap;"><?= htmlspecialchars($session['description']) ?></p>
    <?php endif; ?>
    <?php if (!empty($session['creator_firstname'])): ?>
        <p style="color:var(--color-text-muted); font-size:13px;">
            Créée par <?= htmlspecialchars($session['creator_firstname'] . ' ' . $session['creator_lastname']) ?>
        </p>
    <?php endif; ?>

    <!-- Inscription -->
    <?php if (!$isPast): ?>
        <div style="margin-top:16px;">
            <?php if ($isRegistered): ?>
                <p style="color:var(--color-success); font-weight:600;">✓ Vous êtes inscrit(e) à cette session.</p>
                <form method="POST" action="<?= BASE_URL ?>index.php?controller=Session&action=unregister">
                    <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                    <button class="btn btn--ghost" type="submit">Se désinscrire</button>
                </form>
            <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>index.php?controller=Session&action=register">
                    <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                    <button class="btn btn--primary" type="submit">S'inscrire à cette session</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($canMod): ?>
        <div style="margin-top:16px; padding-top:14px; border-top:1px solid var(--color-border); display:flex; gap:8px;">
            <form method="POST" action="<?= BASE_URL ?>index.php?controller=Session&action=delete"
                  onsubmit="return confirm('Supprimer cette session ?');">
                <input type="hidden" name="id" value="<?= (int)$session['id'] ?>">
                <button class="btn btn--danger" type="submit">Supprimer la session</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php if ($canMod && $attendees !== null): ?>
<div class="card" style="margin-top:18px;">
    <h2 class="card__title">Inscrits (<?= count($attendees) ?>)</h2>
    <?php if (empty($attendees)): ?>
        <p style="color:var(--color-text-muted);">Aucun inscrit pour le moment.</p>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-border); text-align:left;">
                    <th style="padding:8px 10px;">Nom</th>
                    <th style="padding:8px 10px;">Email</th>
                    <th style="padding:8px 10px;">Statut</th>
                    <th style="padding:8px 10px;">Inscrit le</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendees as $a): ?>
                    <tr style="border-bottom:1px solid var(--color-border);">
                        <td style="padding:8px 10px;"><?= htmlspecialchars($a['firstname'] . ' ' . $a['lastname']) ?></td>
                        <td style="padding:8px 10px; color:var(--color-text-muted);"><?= htmlspecialchars($a['email']) ?></td>
                        <td style="padding:8px 10px;"><span class="badge badge--success"><?= htmlspecialchars($a['statut']) ?></span></td>
                        <td style="padding:8px 10px; color:var(--color-text-muted);"><?= htmlspecialchars(substr($a['created_at'], 0, 10)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
