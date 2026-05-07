<?php

class Document {

    private ?int      $id;
    private int       $bookId;
    private string    $filename;     // nom original (affichage)
    private string    $filepath;     // chemin relatif au storage (ex: 'documents/abc.pdf')
    private string    $mime;
    private int       $size;
    private ?int      $uploadedBy;
    private ?DateTime $uploadedAt;

    public function __construct(
        int       $bookId,
        string    $filename,
        string    $filepath,
        string    $mime,
        int       $size,
        ?int      $uploadedBy = null,
        ?int      $id         = null,
        ?DateTime $uploadedAt = null
    ) {
        $this->id         = $id;
        $this->bookId     = $bookId;
        $this->filename   = $filename;
        $this->filepath   = $filepath;
        $this->mime       = $mime;
        $this->size       = $size;
        $this->uploadedBy = $uploadedBy;
        $this->uploadedAt = $uploadedAt;
    }

    public function getId():          ?int      { return $this->id; }
    public function getBookId():      int       { return $this->bookId; }
    public function getFilename():    string    { return $this->filename; }
    public function getFilepath():    string    { return $this->filepath; }
    public function getMime():        string    { return $this->mime; }
    public function getSize():        int       { return $this->size; }
    public function getUploadedBy():  ?int      { return $this->uploadedBy; }
    public function getUploadedAt():  ?DateTime { return $this->uploadedAt; }
}
