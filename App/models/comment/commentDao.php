<?php

require_once __DIR__ . '/../config/Db.php';
require_once __DIR__ . '/../models/user/User.php';
require_once __DIR__ . '/../models/book/Book.php';
require_once __DIR__ . '/../models/reading/Reading.php';

class commentsDao {

    public static function create_comment(int $author, int $reading_id, string $comment_text, int $parent_comment_id) {
        $pdo = Db::seConnecterBdd();

        $stmt = $pdo->prepare("INSERT INTO comments (author, reading, comment_text, parent) VALUES (:author, :reading, :comment_text, :parent)");
        $stmt->bindValue(":author", $author);
        $stmt->bindValue(":reading", $reading_id);
        $stmt->bindValue(":comment_text", $comment_text);
        $stmt->bindValue(":parent", $parent_comment_id);
        $stmt->execute();
    }
}
?>