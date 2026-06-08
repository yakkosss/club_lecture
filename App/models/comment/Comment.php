<?php

class Comment {

    private ?int    $id;
    private ?int    $author;
    private ?int    $bookId;
    private string  $commentText;
    private ?int    $parent;
    private string  $state; // APPROVED | WAITING | REJECTED | REPORTED

    public function __construct(
        ?int    $author,
        ?int    $bookId,
        string  $commentText,
        ?int    $parent = null,
        string  $state  = 'WAITING',
        ?int    $id     = null
    ) {
        $this->id          = $id;
        $this->author      = $author;
        $this->bookId      = $bookId;
        $this->commentText = $commentText;
        $this->parent      = $parent;
        $this->state       = $state;
    }

    public function getId(): ?int           { return $this->id; }
    public function getAuthor(): ?int       { return $this->author; }
    public function getBookId(): ?int       { return $this->bookId; }
    public function getCommentText(): string { return $this->commentText; }
    public function getParent(): ?int       { return $this->parent; }
    public function getState(): string      { return $this->state; }
}
