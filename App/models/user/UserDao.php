<?php
//data access object

require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../role/Role.php';


class UserDao {

    /**
     * Liste l'ensemble des utilisateurs avec leur rôle.
     * Retourne un tableau associatif (utilisé directement par la vue).
     */
    public static function getAllUsers(): array {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare(
            "SELECT u.id, u.firstname, u.lastname, u.email, u.created_at, r.name AS role
             FROM users u
             JOIN roles r ON u.role_id = r.id
             ORDER BY u.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son id.
     */
    public static function findById(int $id): ?User {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare(
            "SELECT u.*, r.name AS role
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? self::hydrate($result) : null;
    }

    /**
     * Récupère un utilisateur par son email (utilisé à la connexion).
     */
    public static function findByEmail(string $email): ?User {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare(
            "SELECT u.*, r.name AS role
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.email = :email"
        );
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? self::hydrate($result) : null;
    }

    /**
     * Crée un nouvel utilisateur. Le rôle est résolu via la table 'roles'
     * pour ne pas dépendre des id auto-incrémentés côté applicatif.
     */
    public static function createUser(User $user): int {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare(
            "INSERT INTO users (firstname, lastname, email, password_hash, role_id)
             VALUES (
                :firstname,
                :lastname,
                :email,
                :password_hash,
                (SELECT id FROM roles WHERE name = :role_name)
             )"
        );
        $stmt->bindValue(':firstname',     $user->getFirstname());
        $stmt->bindValue(':lastname',      $user->getLastname());
        $stmt->bindValue(':email',         $user->getEmail());
        $stmt->bindValue(':password_hash', $user->getPasswordHash());
        $stmt->bindValue(':role_name',     $user->getRole()->value);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour le rôle d'un utilisateur (admin uniquement, EF-02).
     */
    public static function updateRole(int $userId, Role $role): void {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare(
            "UPDATE users
             SET role_id = (SELECT id FROM roles WHERE name = :role_name)
             WHERE id = :id"
        );
        $stmt->bindValue(':role_name', $role->value);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Supprime un utilisateur par son id.
     */
    public static function deleteById(int $id): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Construit un User à partir d'une ligne de la BDD.
     */
    private static function hydrate(array $row): User {
        return new User(
            $row['firstname'],
            $row['lastname'],
            $row['email'],
            $row['password_hash'],
            Role::from($row['role']),
            (int) $row['id'],
            new DateTime($row['created_at'])
        );
    }
}
