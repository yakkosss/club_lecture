<?php

require_once __DIR__ . '/../config/Db.php';
require_once __DIR__ . '/../models/book/BookDao.php';
require_once __DIR__ . '/../models/progress/ProgressDao.php';
require_once __DIR__ . '/../models/review/ReviewDao.php';
require_once __DIR__ . '/../models/session/SessionDao.php';
require_once __DIR__ . '/../models/user/UserDao.php';
require_once __DIR__ . '/../services/AccessGuard.php';

class HomeController {

    public function index(): void {
        $user = AccessGuard::requireLogin();

        // Statistiques personnelles
        $myProgress = ProgressDao::getAllByUser((int) $user['id']);
        $myReview   = null; // résumé utilisé dans le dashboard

        // Statistiques globales (admin/modérateur)
        $stats = null;
        if (in_array($user['role'], ['admin', 'moderator'], true)) {
            $pdo   = Db::getConnection();
            $stats = [
                'total_books'   => (int) $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn(),
                'total_members' => (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'total_reviews' => (int) $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
            ];
        }

        // Prochaines sessions
        $upcomingSessions = SessionDao::getUpcoming(3);

        require __DIR__ . '/../views/home/home.php';
    }
}
