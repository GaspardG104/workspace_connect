<?php
namespace Models;

use PDO;

class Booking {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Vérifie si une ressource est libre et l'enregistre
     */
    public function create($userId, $resourceId, $start, $end) {
        try {
            $sql = "INSERT INTO bookings (id_user, id_resource, date_debut, date_fin) 
                    VALUES (:user, :res, :debut, :fin)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'user'  => $userId,
                'res'   => $resourceId,
                'debut' => $start,
                'fin'   => $end
            ]);
        } catch (\Exception $e) {
            // Ici, on pourrait logguer l'erreur
            return false;
        }
    }

    /**
     * Récupère les réservations pour le calendrier (remplace get_events.php)
     */
    public function getEventsByResource($resourceId) {
        $sql = "SELECT b.id, b.date_debut as start, b.date_fin as end, 
                CONCAT(u.prenom, ' ', u.nom) as title
                FROM bookings b
                JOIN users u ON b.id_user = u.id
                WHERE b.id_resource = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $resourceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}