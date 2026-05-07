<?php
/** @var array $users  rangées de la table users avec colonne 'role' */
$pageTitle = 'Utilisateurs';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

ob_start();
?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div>
            <h1 class="card__title">Utilisateurs</h1>
            <p class="card__subtitle">Liste de tous les comptes du club.</p>
        </div>
        <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=User&action=displayCreateForm">
            + Nouvel utilisateur
        </a>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <table style="width:100%; border-collapse:collapse; margin-top:16px;">
        <thead>
            <tr style="text-align:left; border-bottom:1px solid var(--color-border);">
                <th style="padding:10px 8px;">Nom</th>
                <th style="padding:10px 8px;">Email</th>
                <th style="padding:10px 8px;">Rôle</th>
                <th style="padding:10px 8px;">Créé le</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
            <tr><td colspan="4" style="padding:14px 8px; color:var(--color-text-muted);">Aucun utilisateur.</td></tr>
        <?php else: foreach ($users as $u): ?>
            <tr style="border-bottom:1px solid var(--color-border);">
                <td style="padding:10px 8px;"><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?></td>
                <td style="padding:10px 8px;"><?= htmlspecialchars($u['email']) ?></td>
                <td style="padding:10px 8px;">
                    <span class="badge badge--<?= htmlspecialchars($u['role']) ?>">
                        <?= htmlspecialchars($u['role']) ?>
                    </span>
                </td>
                <td style="padding:10px 8px; color:var(--color-text-muted); font-size:14px;">
                    <?= htmlspecialchars($u['created_at']) ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
