<?php
/** @var array $users */
$pageTitle   = 'Utilisateurs';
$currentUser = $_SESSION['user'];
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
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

    <!-- Recherche instantanée -->
    <input type="search" id="user-search" class="form__input" placeholder="Rechercher par nom ou email…"
           style="margin-top:14px; max-width:340px;">

    <table style="width:100%; border-collapse:collapse; margin-top:14px;" id="users-table">
        <thead>
            <tr style="text-align:left; border-bottom:1px solid var(--color-border);">
                <th style="padding:10px 8px;">Nom</th>
                <th style="padding:10px 8px;">Email</th>
                <th style="padding:10px 8px;">Rôle</th>
                <th style="padding:10px 8px;">Créé le</th>
                <th style="padding:10px 8px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
            <tr><td colspan="5" style="padding:14px 8px; color:var(--color-text-muted);">Aucun utilisateur.</td></tr>
        <?php else: foreach ($users as $u):
            $isSelf = (int)$u['id'] === (int)$currentUser['id'];
        ?>
            <tr style="border-bottom:1px solid var(--color-border);" class="user-row"
                data-search="<?= htmlspecialchars(strtolower($u['firstname'] . ' ' . $u['lastname'] . ' ' . $u['email'])) ?>">
                <td style="padding:10px 8px;"><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?><?= $isSelf ? ' <span style="color:var(--color-text-muted); font-size:12px;">(vous)</span>' : '' ?></td>
                <td style="padding:10px 8px;"><?= htmlspecialchars($u['email']) ?></td>
                <td style="padding:10px 8px;">
                    <span class="badge badge--<?= htmlspecialchars($u['role']) ?>">
                        <?= htmlspecialchars($u['role']) ?>
                    </span>
                </td>
                <td style="padding:10px 8px; color:var(--color-text-muted); font-size:14px;">
                    <?= htmlspecialchars(substr($u['created_at'], 0, 10)) ?>
                </td>
                <td style="padding:10px 8px;">
                    <?php if (!$isSelf): ?>
                        <!-- Changer le rôle -->
                        <form method="POST" action="<?= BASE_URL ?>index.php?controller=User&action=updateRole"
                              style="display:inline-flex; gap:4px; align-items:center;">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <select name="role" style="font-size:12px; padding:3px 6px; border-radius:var(--radius); border:1px solid var(--color-border);">
                                <option value="member"    <?= $u['role'] === 'member'    ? 'selected' : '' ?>>Membre</option>
                                <option value="moderator" <?= $u['role'] === 'moderator' ? 'selected' : '' ?>>Modérateur</option>
                                <option value="admin"     <?= $u['role'] === 'admin'     ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button class="btn btn--ghost" type="submit" style="font-size:12px; padding:4px 8px;">Changer</button>
                        </form>
                        <!-- Supprimer -->
                        <form method="POST" action="<?= BASE_URL ?>index.php?controller=User&action=deleteUser"
                              onsubmit="return confirm('Supprimer cet utilisateur ?');"
                              style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <button class="btn btn--danger" type="submit" style="font-size:12px; padding:4px 8px;">Supprimer</button>
                        </form>
                    <?php else: ?>
                        <span style="color:var(--color-text-muted); font-size:13px;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('user-search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.user-row').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
