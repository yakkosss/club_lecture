<?php

class Review {

    private ?int    $id;
    private int     $bookId;
    private ?int    $userId;
    private int     $note;
    private ?string $commentaire;
    private bool    $hidden;

    public function __construct(
        int     $bookId,
        ?int    $userId,
        int     $note,
        ?string $commentaire = null,
        bool    $hidden      = false,
        ?int    $id          = null
    ) {
        $this->id          = $id;
        $this->bookId      = $bookId;
        $this->userId      = $userId;
        $this->note        = max(1, min(5, $note));
        $this->commentaire = $commentaire;
        $this->hidden      = $hidden;
    }

    public function getId(): ?int             { return $this->id; }
    public function getBookId(): int          { return $this->bookId; }
    public function getUserId(): ?int         { return $this->userId; }
    public function getNote(): int            { return $this->note; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function isHidden(): bool          { return $this->hidden; }
}
