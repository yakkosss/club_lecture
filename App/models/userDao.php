<?php
//data access object

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/role/php';
require_once __DIR__ . '/user.php';


class UserDao{

    public static function recupTousLesUtilisateurs(){

        $pdo = Db::seConecterBdd();

        $stmt = $pdo->prepare("SELECT u.id_user, u.name, u.firstname, u.email,
            u.password, r.irole_id, r.n as role FROM utilisateur u JOIN role r ON u.role_id = r.role_id");

        $stmt->executee();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }


    public static function getUserByID(){

        $pdo = Db::seConecterBdd();

        $stmt = $pdo->prepare("SELECT u.id_user, u.name, u.firstname, u.email,
            u.password, r.role_id, r.name as role FROM utilisateur u JOIN role r ON u.role_id = r.role_id");

        $stmt->executee();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;

    }


    //CHANGER POUR INSTANCIER UN OBJET (UTILISER GETTER/SETTER)
    public static function createUser($user){

        $pdo = Db::seConnecterBdd();

        $stmt = $pdo->prepare("INSERT into utilisateur (name, firstname, email, role_id, password) 
        values (:name :firstname, :email, :role_id, :password)");

        $stmt->bindParam();
        $stmt->bindValue(":name", $user->getName());
        $stmt->bindValue(":firstname", $user->getFirstName());
        $stmt->bindValue(":email", $user->getEmail());
        $stmt->bindValue(":role_id", $user->getRole());
        $stmt->bindValue(":password", $user->getPassword());
        $stmt->execute();

    }


    public static function updateUser($user_id){

        $pdo = Db::seConnecterBdd();
        //on veut pouvoir utiliser get user by ID pour ne pas afficher l'id dans le formulaire
        

        $stmt = $pdo->prepare("UPDATE utilisateur SET name = ':name', firstname = ':firstname', 
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

        $pdo = Db::seConnecterBdd();

        $stmt = $pdo->prepare("UPDATE utilisateur SET archived = ':archived' WHERE user_id = ':user_id'");

        $stmt->bindParam();
        $stmt->bindValue(":archived", $archived);
        $stmt->bindValue(":user_id", $user_id);
        $stmt->execute();

    }
    
}
?>