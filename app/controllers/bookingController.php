<?php
namespace Controllers;

use models\booking;

class BookingController {
    private $bookingModel;

    public function __construct($db) {
        $this->bookingModel = new Booking($db);
    }

    // Affiche la page de réservation
    public function index() {
        $pageTitle = "Réserver - Workspace Connect";
        // On pourrait récupérer ici la liste des bureaux depuis un ResourceModel
        require_once '../views/booking/reserver.php';
    }

    // API : Traite la réservation (remplace process_reservation.php)
    public function store() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }

        $success = $this->bookingModel->create(
            $_SESSION['user_id'],
            $_POST['resource'],
            $_POST['debut'],
            $_POST['fin']
        );

        if ($success) {
            echo json_encode(['success' => true, 'message' => '✅ Réservation réussie !']);
        } else {
            echo json_encode(['success' => false, 'message' => '❌ Ressource déjà prise ou erreur technique.']);
        }
    }
}