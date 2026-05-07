<?php
/**
 * UploadService — gestion centralisée des uploads de fichiers.
 *
 * Toutes les règles de validation (type MIME réel, taille max, extensions
 * autorisées) sont au même endroit afin que les contrôleurs n'aient pas
 * à les réimplémenter et que les futures extensions (PDF) réutilisent
 * la même base.
 */
class UploadService {

    /** Tailles maximales par défaut (en octets). */
    public const MAX_IMAGE_SIZE    = 2 * 1024 * 1024;    // 2 Mo
    public const MAX_DOCUMENT_SIZE = 10 * 1024 * 1024;   // 10 Mo

    /** Mappings extensions => type MIME attendu. */
    private const IMAGE_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    private const DOCUMENT_TYPES = [
        'pdf' => 'application/pdf',
    ];

    /**
     * Sauvegarde une image uploadée (couverture) dans le dossier public/uploads/covers.
     *
     * @param array $file  Tableau issu de $_FILES['nom_du_champ'].
     * @return array       [string|null $relativePath, string|null $error]
     *                     - $relativePath : chemin relatif à BASE_URL (ex: 'uploads/covers/abc.jpg')
     *                     - $error : message d'erreur si échec, sinon null
     */
    public static function saveCoverImage(array $file): array {
        if (!isset($file['error'])) {
            return [null, "Fichier invalide."];
        }

        // Pas de fichier envoyé : on retourne null sans erreur (champ optionnel)
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, "Échec de l'upload (code " . (int)$file['error'] . ")."];
        }

        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            return [null, "Image trop volumineuse (max " . (self::MAX_IMAGE_SIZE / 1024 / 1024) . " Mo)."];
        }

        // Extension par nom + vérification du contenu
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::IMAGE_TYPES[$ext])) {
            return [null, "Format non autorisé. Formats acceptés : " . implode(', ', array_keys(self::IMAGE_TYPES)) . "."];
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        if ($realMime !== self::IMAGE_TYPES[$ext]) {
            return [null, "Le contenu du fichier ne correspond pas à son extension."];
        }

        // Vérifie que c'est bien une image lisible
        if (@getimagesize($file['tmp_name']) === false) {
            return [null, "Le fichier n'est pas une image valide."];
        }

        // Nom unique pour éviter les collisions / les noms exotiques
        $filename  = bin2hex(random_bytes(16)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $targetDir = self::publicUploadsDir() . '/covers';

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return [null, "Impossible de créer le dossier de stockage."];
        }

        $absoluteTarget = $targetDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $absoluteTarget)) {
            return [null, "Échec de l'enregistrement du fichier."];
        }

        return ['uploads/covers/' . $filename, null];
    }

    /**
     * Sauvegarde un document PDF dans App/storage/documents (HORS docroot
     * web). Le fichier ne sera accessible que via DocumentController::download
     * qui vérifie les droits avant de le servir.
     *
     * @param array $file  Tableau issu de $_FILES['nom_du_champ'].
     * @return array       [array|null $info, string|null $error]
     *   $info : ['filename' => 'nom_original.pdf',
     *            'filepath' => 'documents/abc.pdf',
     *            'mime'     => 'application/pdf',
     *            'size'     => 12345]
     */
    public static function savePdfDocument(array $file): array {
        if (!isset($file['error'])) {
            return [null, "Fichier invalide."];
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return [null, "Aucun fichier sélectionné."];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, "Échec de l'upload (code " . (int)$file['error'] . ")."];
        }
        if ($file['size'] > self::MAX_DOCUMENT_SIZE) {
            return [null, "Document trop volumineux (max " . (self::MAX_DOCUMENT_SIZE / 1024 / 1024) . " Mo)."];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::DOCUMENT_TYPES[$ext])) {
            return [null, "Format non autorisé. Seuls les fichiers PDF sont acceptés."];
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        if ($realMime !== self::DOCUMENT_TYPES[$ext]) {
            return [null, "Le contenu du fichier ne correspond pas à son extension."];
        }

        // Nom de stockage aléatoire (pour éviter les collisions et les noms exotiques)
        $storedName = bin2hex(random_bytes(16)) . '.pdf';
        $targetDir  = self::storageDir() . DIRECTORY_SEPARATOR . 'documents';

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return [null, "Impossible de créer le dossier de stockage."];
        }

        $absoluteTarget = $targetDir . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $absoluteTarget)) {
            return [null, "Échec de l'enregistrement du fichier."];
        }

        // Nettoie le nom original pour l'affichage / téléchargement.
        $cleanName = self::sanitizeFilename($file['name']);

        return [[
            'filename' => $cleanName,
            'filepath' => 'documents/' . $storedName,
            'mime'     => $realMime,
            'size'     => (int) $file['size'],
        ], null];
    }

    /**
     * Supprime un fichier de storage. Le chemin doit être relatif à App/storage
     * (ex: 'documents/abc.pdf'). Vérifie que le chemin résolu reste dans le
     * dossier de storage (anti path-traversal).
     */
    public static function deleteStorageFile(?string $relativePath): void {
        $absolute = self::resolveStoragePath($relativePath);
        if ($absolute !== null && is_file($absolute)) {
            @unlink($absolute);
        }
    }

    /**
     * Résout un chemin relatif de storage en chemin absolu, en s'assurant
     * que le résultat reste dans le dossier App/storage. Retourne null si
     * le chemin est invalide ou s'il échappe du dossier autorisé.
     */
    public static function resolveStoragePath(?string $relativePath): ?string {
        if (!$relativePath || strpos($relativePath, '..') !== false) {
            return null;
        }
        $root     = self::storageDir();
        $absolute = $root . DIRECTORY_SEPARATOR . $relativePath;

        // dirname() est utilisé sur le fichier potentiel : si le fichier
        // n'existe pas encore, realpath() renvoie false. On vérifie donc
        // le dossier parent.
        $resolvedRoot = realpath($root);
        $resolvedDir  = realpath(dirname($absolute));
        if ($resolvedRoot === false || $resolvedDir === false) {
            return null;
        }
        if (strpos($resolvedDir, $resolvedRoot) !== 0) {
            return null;
        }
        return $resolvedDir . DIRECTORY_SEPARATOR . basename($absolute);
    }

    /**
     * Supprime un fichier (couverture) à partir de son chemin relatif.
     * Silencieux si le fichier n'existe pas.
     */
    public static function deleteRelativeFile(?string $relativePath): void {
        if (!$relativePath) {
            return;
        }
        // Sécurise : on n'autorise que les fichiers sous /uploads/
        if (strpos($relativePath, 'uploads/') !== 0 || strpos($relativePath, '..') !== false) {
            return;
        }
        $absolute = self::publicUploadsDir() . '/' . substr($relativePath, strlen('uploads/'));
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    /**
     * Nettoie un nom de fichier pour affichage / Content-Disposition.
     */
    private static function sanitizeFilename(string $name): string {
        $name = basename($name);
        $name = preg_replace('/[^A-Za-z0-9._\- ]/', '_', $name);
        $name = trim($name, ' .');
        return $name === '' ? 'document.pdf' : $name;
    }

    /**
     * Chemin absolu vers public/uploads.
     */
    private static function publicUploadsDir(): string {
        return realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR . 'uploads';
    }

    /**
     * Chemin absolu vers App/storage (hors docroot web).
     */
    private static function storageDir(): string {
        $base = realpath(__DIR__ . '/..');
        if ($base === false) {
            // Fallback sans realpath si le dossier est tout neuf
            $base = __DIR__ . '/..';
        }
        $dir = $base . DIRECTORY_SEPARATOR . 'storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }
}
