<?php
namespace App\Models;
use PDO;

class Booking {
    /**
     * 1. VÉRIFICATION : Est-ce que la ressource est libre ?
     */
    public static function isAvailable($db, $resourceId, $start, $end) {
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE id_resource = ? 
                AND (date_debut < ? AND date_fin > ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$resourceId, $end, $start]);
        return $stmt->fetchColumn() == 0;
    }

    /**
     * 2. CRÉATION : Insérer une nouvelle réservation
     */
    public static function create($db, $userId, $resourceId, $start, $end, $idSeries = null) {
        $sql = "INSERT INTO bookings (id_user, id_resource, date_debut, date_fin, id_series) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$userId, $resourceId, $start, $end, $idSeries]);
    }

    /**
     * 3. RECHERCHE : Ta requête SQL GÉANTE (Optimisée MVC)
     */
    public static function search($db, $searchTerm) {
        $sql = "SELECT b.*, u.nom as user_nom, u.prenom as user_prenom, r.nom as resource_nom, r.type as resource_type, b.id_series,
                (SELECT STRING_AGG(nom_invite, ', ') FROM attendees WHERE id_booking = b.id) as liste_invites,
                (SELECT COUNT(*) FROM attendees WHERE id_booking = b.id) as nb_invites,
                CASE 
                    WHEN (u.nom ILIKE :search OR u.prenom ILIKE :search) THEN 'Organisateur'
                    ELSE 'Invité'
                END as role_label
                FROM bookings b
                JOIN users u ON b.id_user = u.id
                JOIN resources r ON b.id_resource = r.id
                WHERE (
                    u.nom ILIKE :search OR u.prenom ILIKE :search OR r.nom ILIKE :search
                    OR r.type::TEXT ILIKE :search 
                    OR b.id IN (
                        SELECT id_booking FROM attendees 
                        WHERE nom_invite ILIKE :search OR email ILIKE :search
                    )
                )
                ORDER BY b.date_debut DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute(['search' => "%$searchTerm%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 4. SUPPRESSION : Unitaire ou par série
     */
    public static function delete($db, $id, $deleteAllSeries = false) {
        if ($deleteAllSeries) {
            // On récupère d'abord l'ID de la série
            $stmt = $db->prepare("SELECT id_series FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            $seriesId = $stmt->fetchColumn();

            if ($seriesId) {
                $stmtDel = $db->prepare("DELETE FROM booking_series WHERE id = ?");
                return $stmtDel->execute([$seriesId]);
            }
        }

        // Sinon, suppression unitaire
        $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getEventsByResource($db, $id_resource) {
        $stmt = $db->prepare("SELECT b.id, b.date_debut as start, b.date_fin as end, 
                            CONCAT(u.prenom, ' ', u.nom) as title 
                            FROM bookings b 
                            JOIN users u ON b.id_user = u.id 
                            WHERE b.id_resource = ?");
        $stmt->execute([$id_resource]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}