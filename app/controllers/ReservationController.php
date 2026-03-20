<?php
namespace App\Controllers;

use PDO;

class ReservationController {
    private $db;

    public function __construct() {
        $this->db = require __DIR__ . '/../../config/db.php';
    }

    // Affiche la structure de la page
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

    // API AJAX pour la recherche
    public function search() {
        $search = $_GET['search'] ?? '';
        $date_filter = $_GET['date'] ?? '';
        $sort_by = $_GET['sort'] ?? 'date_debut';
        $order = $_GET['order'] ?? 'DESC';

        $sql = "SELECT b.*, u.nom as user_nom, u.prenom as user_prenom, r.nom as resource_nom, r.type as resource_type,
                CASE 
                    WHEN (u.nom ILIKE :search OR u.prenom ILIKE :search) THEN 'Organisateur'
                    ELSE 'Invité'
                END as role_label
                FROM bookings b
                JOIN users u ON b.id_user = u.id
                JOIN resources r ON b.id_resource = r.id
                WHERE (
                    u.nom ILIKE :search 
                    OR u.prenom ILIKE :search 
                    OR r.nom ILIKE :search
                    OR b.id IN (
                        SELECT bi.id_booking FROM booking_invites bi 
                        JOIN users ui ON bi.id_user = ui.id 
                        WHERE ui.nom ILIKE :search OR ui.prenom ILIKE :search
                    )
                )";

        if (!empty($date_filter)) {
            $sql .= " AND DATE(b.date_debut) = :date_filter";
        }

        $allowed_sorts = ['user_nom', 'resource_nom', 'date_debut'];
        $sort_column = in_array($sort_by, $allowed_sorts) ? $sort_by : 'date_debut';
        $sql .= " ORDER BY $sort_column $order";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%");
        if (!empty($date_filter)) $stmt->bindValue(':date_filter', $date_filter);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json'); // Indique au navigateur que c'est du JSON
    echo json_encode($results);
    exit;
    }

    public function delete($id) {
        header('Content-Type: application/json');

        // Sécurité : Seul l'admin (role 1) peut supprimer n'importe quelle résa
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Autorisation refusée.']);
            exit;
        }

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
            exit;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM bookings WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Réservation supprimée avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur technique : ' . $e->getMessage()]);
        }
    }
}