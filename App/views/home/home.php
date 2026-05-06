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
        <?= htmlspecialchars($_SESSION['user']['firstname']) ?>
    </strong>
</p>

<p>
    Rôle :
    <?= htmlspecialchars($_SESSION['user']['role']->value) ?>
</p>

<a href="<?= BASE_URL ?>index.php?controller=Auth&action=logout">
    <button>Se déconnecter</button>
</a>

</body>
</html>