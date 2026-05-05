<?php
require_once __DIR__ . '/../models/user/userDao.php';
require_once __DIR__ . '/../models/user/User.php';

class UserController{

    public static function index(){

    //récupérer et lister les données
        $users = UserDao::getAllUsers();
        require_once __DIR__ . '/../views/users/index.php';

    }

    public static function displayCreateForm(){
        require_once __DIR__ . '/../views/user/create.php';
    }
    
    public static function displayUpdateForm(){
        $id_user = $_GET["id_user"];

        if($id_user) {
            //ajouter la possiilité de récupérer

            //trasmettre les donées de user à la vue
            require_once __DIR__ .'/../views/users/update.php';
            
        }
    }
    public function createUser(){
      
        $firstname = trim($_POST['firstname']) ?? '';
        $lastname = trim($_POST['lastname']) ?? '';
        $email =trim($_POST['email']) ?? '';
        $role =(int)$_POST['role_id'] ?? 0;
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

        userDao::createUser(new User($firstname, $lastname, $email, $password, $role));

        header('Location: index.php?controller=User&action=displayCreateForm');

        exit;
    }
    
    public function updateUser(){
        $nom = trim($_POST['name']) ?? '';
        $prenom = trim($_POST['firstname']) ?? '';
        $email =trim($_POST['email']) ?? '';
        $role =(int)$_POST['role_id'] ?? 0;
        $password = password_hash("test", PASSWORD_DEFAULT);
        $user_id = (int)($_POST['user_id']);

        if($id_user){
            userDao::updateUser($user_id);
        }

        //redirection
        header('Location: index.php?controller=UserController&action=index');

        exit;
    }
   
 
   
}
?>