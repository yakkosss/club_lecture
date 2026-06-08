<?php

class Session {

    private ?int    $id;
    private int     $bookId;
    private string  $titre;
    private string  $dateHeure;
    private ?string $lien;
    private ?string $lieu;
    private ?string $description;
    private ?int    $createdBy;

    public function __construct(
        int     $bookId,
        string  $titre,
        string  $dateHeure,
        ?string $lien        = null,
        ?string $lieu        = null,
        ?string $description = null,
        ?int    $createdBy   = null,
        ?int    $id          = null
    ) {
        $this->id          = $id;
        $this->bookId      = $bookId;
        $this->titre       = $titre;
        $this->dateHeure   = $dateHeure;
        $this->lien        = $lien;
        $this->lieu        = $lieu;
        $this->description = $description;
        $this->createdBy   = $createdBy;
    }

    public function getId(): ?int             { return $this->id; }
    public function getBookId(): int          { return $this->bookId; }
    public function getTitre(): string        { return $this->titre; }
    public function getDateHeure(): string    { return $this->dateHeure; }
    public function getLien(): ?string        { return $this->lien; }
    public function getLieu(): ?string        { return $this->lieu; }
    public function getDescription(): ?string { return $this->description; }
    public function getCreatedBy(): ?int      { return $this->createdBy; }
}
