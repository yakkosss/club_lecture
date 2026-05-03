<?php
require_once __DIR__ . '/../models/bookDao.php';

class BookController{

    public static function index(){};

    public function createBook(){
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $synopsis = trim($_POST['synopsis'] ?? '');
        $release_date = (int)($_POST['release_date'] ?? 0);
        $created_by = (int)($_POST['created_by'] ?? 0);

        $book = new Book($title, $author, $synopsis, $release_date, $created_by);
    }
}

?>