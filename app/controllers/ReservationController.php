<?php
namespace App\Controllers;

use PDO;

class ReservationController {
    private $db;

    public function __construct() {
        // Initialisation de la connexion à la base de données
        $this->db = require __DIR__ . '/../../config/db.php';
    }

    /**
     * Affiche la page de gestion des réservations (Vue Admin/Manager)
     */
    public function listAll() {
        // Vérification des droits : Seuls les rôles 1 (Admin) et 2 (Manager) y ont accès
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 3])) {
            header('Location: /workspace_connect/home');
            exit;
        }
        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'reservations/list.php'; 
        include $viewPath . 'layouts/footer.php';
    }


    public function search() {
        $search = $_GET['search'] ?? '';
        $date_filter = $_GET['date'] ?? '';
        $sort_by = $_GET['sort'] ?? 'date_debut';
        $order = $_GET['order'] ?? 'DESC';

       // Remplace la requête SQL dans search() par celle-ci :
$sql = "SELECT b.*, u.nom as user_nom, u.prenom as user_prenom, r.nom as resource_nom, r.type as resource_type,
        -- Récupère les invités séparés par des virgules
        (SELECT STRING_AGG(nom_invite, ', ') FROM attendees WHERE id_booking = b.id) as liste_invites,
        -- Compte le nombre d'invités
        (SELECT COUNT(*) FROM attendees WHERE id_booking = b.id) as nb_invites,
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
            -- Conversion du type ENUM en TEXT pour permettre le ILIKE
            OR r.type::TEXT ILIKE :search 
            OR b.id IN (
                SELECT id_booking FROM attendees 
                WHERE nom_invite ILIKE :search OR email ILIKE :search
            )
        )";

        // Ajout du filtre par date si spécifié
        if (!empty($date_filter)) {
            $sql .= " AND DATE(b.date_debut) = :date_filter";
        }

        // Sécurisation du tri pour éviter les injections SQL
        $allowed_sorts = ['user_nom', 'resource_nom', 'date_debut'];
        $sort_column = in_array($sort_by, $allowed_sorts) ? $sort_by : 'date_debut';
        $sql .= " ORDER BY $sort_column $order";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%");
        if (!empty($date_filter)) $stmt->bindValue(':date_filter', $date_filter);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    /**
     * Supprime une réservation (Action réservée à l'Administrateur)
     */
public function delete($id) {
    header('Content-Type: application/json');

    // 1. Sécurité stricte 
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Autorisation refusée.']);
        exit;
    }

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID manquant.']);
        exit;
    }
        
    try {
        // --- RÉCUPÉRATION DES INFOS AVANT SUPPRESSION ---
        
        // On récupère les choix envoyés par la modale (checkboxes)
        $notifyInvites = isset($_POST['notifyInvites']) && $_POST['notifyInvites'] === 'true';
        $notifyOrganizer = isset($_POST['notifyOrganizer']) && $_POST['notifyOrganizer'] === 'true';

        if ($notifyInvites || $notifyOrganizer) {
            // On récupère l'email de l'organisateur et la liste des emails des invités
            $stmtInfo = $this->db->prepare("
                SELECT b.id_user, u.email as organizer_email, r.nom as resource_name 
                FROM bookings b
                JOIN users u ON b.id_user = u.id
                JOIN resources r ON b.id_resource = r.id
                WHERE b.id = ?
            ");
            $stmtInfo->execute([$id]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                // TODO: Plus tard, développer la logique d'envoi de mail ici
                // Pour l'instant, on se contente de préparer les données
                if ($notifyInvites) {
                    $stmtAtt = $this->db->prepare("SELECT email FROM attendees WHERE id_booking = ?");
                    $stmtAtt->execute([$id]);
                    $emailsInvites = $stmtAtt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Ici on pourra appeler la fonction de mail pour les invités
                    // ex: $this->mailService->sendCancelToInvites($emailsInvites, $info['resource_name']);
                }

                // Logique pour l'organisateur (si c'est un admin qui supprime à sa place)
                if ($notifyOrganizer && $info['id_user'] != $_SESSION['user_id']) {
                    // Ici tu pourras appeler ta fonction de mail pour l'organisateur
                    // ex: $this->mailService->sendCancelToOrganizer($info['organizer_email'], $info['resource_name']);
                }
            }
        }
        // --- FIN DE LA LOGIQUE DE NOTIFICATION ---


        // 2. Suppression (Ta logique d'origine conservée)
        // La suppression en cascade en SQL s'occupera de nettoyer la table attendees
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