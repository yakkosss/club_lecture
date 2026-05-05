<?php
class User {
        
    private ?int $id;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $passwordHash;
    private int $role;
    private ?DateTime $createdAt;

    public function __construct(string $firstname, string $lastname, string $email, string $passwordHash, int $role, ?int $id = null, ?DateTime $createdAt = null) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;  
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRoleId() {
        return $this->role;
    }

    public function getPasswordHash(): string {
        return $this->passwordHash;
    }

}




?>