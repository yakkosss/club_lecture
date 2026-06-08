<?php
/**
 * Layout principal — utilisé par toutes les vues.
 *
 * Variables attendues :
 *   - $pageTitle (string, optionnel) : titre de la page
 *   - $content   (string)            : HTML rendu de la vue (capturé via ob_get_clean)
 *
 * Les vues "renderent" leur contenu dans une variable $content puis
 * font require de ce layout.
 */

require_once __DIR__ . '/../../services/AuthService.php';

$currentUser = AuthService::getSessionUser();
$pageTitle = $pageTitle ?? 'Club de Lecture';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Club de Lecture</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="site">
    <header class="site-header">
        <div class="site-header__inner">
            <a class="brand" href="<?= BASE_URL ?>index.php?controller=Home&action=index">
                <span class="brand__dot"></span>
                <span>Club de Lecture</span>
            </a>
            <nav class="nav">
                <?php if ($currentUser): ?>
                    <a href="<?= BASE_URL ?>index.php?controller=Home&action=index">Accueil</a>
                    <a href="<?= BASE_URL ?>index.php?controller=Book&action=index">Lectures</a>
                    <a href="<?= BASE_URL ?>index.php?controller=Session&action=index">Sessions</a>
                    <?php if (in_array($currentUser['role'], ['admin'], true)): ?>
                        <a href="<?= BASE_URL ?>index.php?controller=User&action=index">Utilisateurs</a>
                    <?php endif; ?>
                    <span class="nav__user">
                        <?= htmlspecialchars($currentUser['firstname']) ?>
                        <span class="badge badge--<?= htmlspecialchars($currentUser['role']) ?>">
                            <?= htmlspecialchars($currentUser['role']) ?>
                        </span>
                    </span>
                    <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Auth&action=logout">Déconnexion</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>index.php?controller=Auth&action=displayLoginForm">Connexion</a>
                    <a class="btn btn--primary" href="<?= BASE_URL ?>index.php?controller=Auth&action=displayRegisterForm">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="site-footer__inner">
            &copy; <?= date('Y') ?> Club de Lecture
        </div>
    </footer>
</div>
</body>
</html>
