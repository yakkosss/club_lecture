<?php

require_once __DIR__ . '/../models/progress/ProgressDao.php';
require_once __DIR__ . '/../services/AccessGuard.php';

/**
 * ProgressController — EF-06 Suivi de progression (0-100 %).
 */
class ProgressController {

    /**
     * Enregistre ou met à jour la progression d'un utilisateur pour un livre.
     */
    public function update(): void {
        $user        = AccessGuard::requireLogin();
        $bookId      = (int) ($_POST['book_id'] ?? 0);
        $pourcentage = (int) ($_POST['pourcentage'] ?? 0);

        if ($bookId <= 0) {
            $_SESSION['flash_error'] = "Livre introuvable.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=index');
            exit;
        }

        // Borne côté serveur (EF-06 : pas de valeur négative ou >100)
        $pourcentage = max(0, min(100, $pourcentage));

        try {
            ProgressDao::upsert($bookId, (int) $user['id'], $pourcentage);
            $_SESSION['flash_success'] = "Progression mise à jour : {$pourcentage} %.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible d'enregistrer la progression.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId);
        exit;
    }
}
