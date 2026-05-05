<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form action="<?= BASE_URL ?>index.php?controller=Auth&action=login" method="POST">

    <input type="email" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Mot de passe">

    <button type="submit">Connexion</button>
</form>
</body>
</html>