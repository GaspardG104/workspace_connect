<?php
namespace App\Controllers;

use App\Models\Booking;
use PDO;

class ReservationController {
    private $db;

    public function __construct() {
        // Initialisation de la connexion
        $this->db = require __DIR__ . '/../../config/db.php';
    }

    /**
     * Affiche la page de gestion des réservations (Vue Admin/Manager)
     */
    public function listAll() {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 2])) {
            header('Location: /workspace_connect/home');
            exit;
        }
        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'reservations/list.php'; 
        include $viewPath . 'layouts/footer.php';
    }

    /**
     * Recherche via le Modèle (La requête géante est maintenant dans Booking::search)
     */
    public function search() {
        $search = $_GET['search'] ?? '';
        
        // Appel au modèle
        $reservations = Booking::search($this->db, $search);

        header('Content-Type: application/json');
        echo json_encode($reservations);
    }

    /**
     * Suppression (Unique ou Série) avec gestion des notifications
     */
    public function delete($id) {

        header('Content-Type: application/json');
        try {
            $userId = $_SESSION['user_id'];
            $notifyInvites = isset($_POST['notifyInvites']) && $_POST['notifyInvites'] === 'true';
            $notifyOrganizer = isset($_POST['notifyOrganizer']) && $_POST['notifyOrganizer'] === 'true';
            $deleteAllSeries = isset($_POST['deleteAllSeries']) && $_POST['deleteAllSeries'] === 'true';

            // 1. Récupération des infos avant suppression pour les mails
            // On peut utiliser une petite requête ici ou ajouter une méthode getById dans le modèle
            $stmtInfo = $this->db->prepare("SELECT b.*, u.email as organizer_email FROM bookings b JOIN users u ON b.id_user = u.id WHERE b.id = ?");
            $stmtInfo->execute([$id]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            if (!$info) {
                echo json_encode(['success' => false, 'message' => 'Réservation introuvable.']);
                return;
            }

            // 2. Logique des notifications (on garde ton code tel quel)
            if ($notifyInvites) {
                $stmtAtt = $this->db->prepare("SELECT email FROM attendees WHERE id_booking = ?");
                $stmtAtt->execute([$id]);
                $emailsInvites = $stmtAtt->fetchAll(PDO::FETCH_COLUMN);
                // Logique d'envoi de mail aux invités...
            }

            // 3. Exécution de la suppression via le Modèle
            if ($deleteAllSeries && !empty($info['id_series'])) {
                // On délègue la suppression de la série au modèle
                $result = Booking::delete($this->db, $id, true); // true pour supprimer toute la série
                $message = "Toute la série de réservations a été supprimée.";
            } else {
                // Suppression unitaire
                $result = Booking::delete($this->db, $id);
                $message = "La réservation a été supprimée avec succès.";
            }

            echo json_encode(['success' => $result, 'message' => $message]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}