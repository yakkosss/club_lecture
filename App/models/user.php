<?php
class User {
        
    private int $id;
    private string $name;
    private string $firstname;
    private string $email;
    private string $password;
    private int $role;
    private int $created_at;

    public function __construct(int $id, string $name, string $firstname, string $email, string $password, int $role) {
        $this->id = $id;
        $this->name = $name;
        $this->firstname = $firstname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function getIdUser() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getFirstName() {
        return $this->firstname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getRole() {
        return $this->role;
    }

}




?>