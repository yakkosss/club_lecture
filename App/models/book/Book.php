<?php

class Book {

    private ?int    $id;
    private string  $title;
    private string  $author;
    private ?string $synopsis;
    private ?string $coverPath;     // chemin relatif (ex: uploads/covers/abc.jpg)
    private ?string $releaseDate;   // ISO string ou null
    private ?int    $createdBy;     // id de l'utilisateur qui a créé l'entrée

    public function __construct(
        string  $title,
        string  $author,
        ?string $synopsis    = null,
        ?string $coverPath   = null,
        ?string $releaseDate = null,
        ?int    $createdBy   = null,
        ?int    $id          = null
    ) {
        $this->id          = $id;
        $this->title       = $title;
        $this->author      = $author;
        $this->synopsis    = $synopsis;
        $this->coverPath   = $coverPath;
        $this->releaseDate = $releaseDate;
        $this->createdBy   = $createdBy;
    }

    public function getId(): ?int           { return $this->id; }
    public function getTitle(): string      { return $this->title; }
    public function getAuthor(): string     { return $this->author; }
    public function getSynopsis(): ?string  { return $this->synopsis; }
    public function getCoverPath(): ?string { return $this->coverPath; }
    public function getReleaseDate(): ?string { return $this->releaseDate; }
    public function getCreatedBy(): ?int    { return $this->createdBy; }

    public function setCoverPath(?string $path): void { $this->coverPath = $path; }
}
