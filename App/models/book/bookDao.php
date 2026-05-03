<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/book.php';

class BookDao{

    public static function getAllBooks(){

        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM books");

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;

    }

    public static function getBookByID($book_id){

        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = :book_id");

        $stmt->bindValue(":book_id", $book_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    }
}

public static function createBook($book){

    $pdo = Db::getConnection();

    $stmt = $pdo->prepare("INSERT INTO books (title, author, synopsis, release_date, created_by) VALUES(:title, :author, :synopsis, :release_date, :created_by)");
    
    $stmt->bindValue(":title", $book->getTitle());
    $stmt->bindValue(":author", $book->getAuthor());
    $stmt->bindValue(":synopsis", $book->getSynopsis());
    $stmt->bindValue(":release_date", $book->getReleaseDate());
    $stmt->bindValue(":created_by", $book->getCreatedBy());
    $stmt->execute();

}
//$stmt = $pdo->prepare("INSERT INTO books (title, author, synopsis, release_date, created_by, created_at) VALUES(:title, :author, :synopsis, :release_date, :created_by, :created_at)");
?>