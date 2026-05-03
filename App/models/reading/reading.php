<?php 
class reading {
    private int $id;
    private int $user_id;
    private int $book_id;
    private string $date_read;

    public function __construct($Id, $User_id, $Book_id, $Date_read) {
        $this->id = $Id;
        $this->user_id = $User_id;
        $this->book_id = $Book_id;
        $this->date_read = $Date_read;
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getBookId() {
        return $this->book_id;
    }

    public function getDateRead() {
        return $this->date_read;
    }
}
?>