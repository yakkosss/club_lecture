<?php 

class book {
    private int $id;
    private string $title;
    private string $author;
    private string $synopsis;
    private int $release_date;


    public function __construct($Id, $Title, $Author, $release_date) {
        $this->id = $Id;
        $this->title = $Title;
        $this->author = $Author;
        $this->year = $Year;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getYear() {
        return $this->year;
    }
}
?>
