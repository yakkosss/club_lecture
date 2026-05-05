<?php
//data access object

require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/User.php';


class UserDao{

    public static function getAllUsers(){

        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("SELECT u.id_users, u.name, u.firstname, u.email,
            u.password, r.irole_id, r.n as role FROM users u JOIN role r ON u.role_id = r.role_id");

        $stmt->executee();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }


    public static function getUserByID(){

        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("SELECT u.id_users, u.name, u.firstname, u.email,
            u.password, r.role_id, r.name as role FROM users u JOIN role r ON u.role_id = r.role_id");

        $stmt->executee();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;

    }

    public static function findByEmail($email) {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("SELECT u.id, u.firstname, u.lastname, u.email,
            u.password_hash, r.role_id, r.name as roles FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.email = :email");

        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    //CHANGER POUR INSTANCIER UN OBJET (UTILISER GETTER/SETTER)
    public static function createUser($user){

        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("INSERT into users (firstname, lastname, email, role_id, password_hash) 
        values (:firstname, :lastname, :email, :role_id, :password_hash)");

        $stmt->bindValue(":firstname", $user->getFirstName());
        $stmt->bindValue(":lastname", $user->getLastName());
        $stmt->bindValue(":email", $user->getEmail());
        $stmt->bindValue(":role_id", $user->getRoleId());
        $stmt->bindValue(":password_hash", $user->getPasswordHash());
        $stmt->execute();

    }


    public static function updateUser($user_id){

        $pdo = Db::getConnection();
        //on veut pouvoir utiliser get user by ID pour ne pas afficher l'id dans le formulaire
        

        $stmt = $pdo->prepare("UPDATE users SET name = ':name', firstname = ':firstname', 
            email = ':email', role_id = ':role_id', password = ':password' WHERE user_id = ':user_id'");

        $stmt->bindValue(":name", $name);
        $stmt->bindValue(":firstname", $firstname);
        $stmt->bindValue(":email", $email);
        $stmt->bindValue(":role_id", $role_id);
        $stmt->bindValue(":password", $password);
        $stmt->bindValue(":user_id", $user_id);
        $stmt->execute();

    }


    public static function archiveUser($archived, $user_id){

        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("UPDATE users SET archived = ':archived' WHERE id = ':user_id'");

        $stmt->bindParam();
        $stmt->bindValue(":archived", $archived);
        $stmt->bindValue(":user_id", $user_id);
        $stmt->execute();

    }
    
}
?>