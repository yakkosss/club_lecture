<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>
<body>

<h1>Bienvenue 👋</h1>

<p>
    Connecté en tant que :
    <strong>
        <?= htmlspecialchars($user['firstname'] ?? $user['email']) ?>
    </strong>
</p>

<p>
    Rôle :
    <?= htmlspecialchars($user['role']) ?>
</p>

<a href="<?= BASE_URL ?>index.php?controller=Auth&action=logout">
    <button>Se déconnecter</button>
</a>

</body>
</html>