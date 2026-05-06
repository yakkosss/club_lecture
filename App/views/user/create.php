<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création utilisateur</title>
</head>
<body>
    <h2>Créer un utilisateur</h2>
    <form action="<?= BASE_URL ?>index.php?controller=User&action=createUser" method="POST">
        
        <label for="firstname">Prénom</label>
        <input type="text" name="firstname" id="firstname" required>

        <label for="lastname">Nom</label>
        <input type="text" name="lastname" id="lastname" required>

        <label for="email">email</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required>

        <label for="role_id">Rôle</label>
        <select name="role_id" id="role_id" required>
            <option value="admin">Administrateur</option>
            <option value="moderator">Moderateur</option>
            <option value="member">Membre</option>

        </select>

        <button type="submit">créer</button>
    </form>
</body>
</html>
