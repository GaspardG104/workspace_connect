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

$sql = "SELECT b.*, u.nom as user_nom, u.prenom as user_prenom, r.nom as resource_nom, r.type as resource_type, b.id_series,
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
     * Supprime une réservation (Action réservée à l'Administrateur et l'organisateur)
     */
public function delete($id) {
    header('Content-Type: application/json');

    // 1. Vérification de la session de base
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Session expirée.']);
        exit;
    }

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID manquant.']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    // On définit ce qu'est un admin (selon tes variables de session)
    $isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1); 
    
    try {
        // --- NOUVEAU : VÉRIFICATION DE PROPRIÉTÉ ---
        // On récupère les infos nécessaires pour la sécurité ET les notifications d'un coup
        $stmtCheck = $this->db->prepare("
            SELECT b.id_user, b.id_series, u.email as organizer_email, r.nom as resource_name 
            FROM bookings b
            JOIN users u ON b.id_user = u.id
            JOIN resources r ON b.id_resource = r.id
            WHERE b.id = ?
        ");
        $stmtCheck->execute([$id]);
        $info = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$info) {
            echo json_encode(['success' => false, 'message' => 'Réservation introuvable.']);
            exit;
        }

        // LA CONDITION CLÉ : Si pas admin ET pas le propriétaire -> Refus
        if (!$isAdmin && $info['id_user'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Autorisation refusée : ce n\'est pas votre réservation.']);
            exit;
        }

        // --- 1. LOGIQUE DE NOTIFICATION (TON CODE PRÉSERVÉ) ---
        $notifyInvites = isset($_POST['notifyInvites']) && $_POST['notifyInvites'] === 'true';
        $notifyOrganizer = isset($_POST['notifyOrganizer']) && $_POST['notifyOrganizer'] === 'true';

        if ($notifyInvites || $notifyOrganizer) {
            if ($notifyInvites) {
                $stmtAtt = $this->db->prepare("SELECT email FROM attendees WHERE id_booking = ?");
                $stmtAtt->execute([$id]);
                $emailsInvites = $stmtAtt->fetchAll(PDO::FETCH_COLUMN);
                // Ta logique mail invités ici... (ex: $this->mailService->send(...))
            }
            // NotifyOrganizer : seulement si celui qui supprime n'est PAS l'organisateur (donc un admin)
            if ($notifyOrganizer && $info['id_user'] != $userId) {
                // Ta logique mail organisateur ici...
            }
        }

        // --- 2. LOGIQUE DE SUPPRESSION (TON CODE FUSIONNÉ) ---
        $deleteAllSeries = isset($_POST['deleteAllSeries']) && $_POST['deleteAllSeries'] === 'true';

        if ($deleteAllSeries && !empty($info['id_series'])) {
            // Suppression de la série (le ON DELETE CASCADE s'occupe du reste)
            $stmt = $this->db->prepare("DELETE FROM booking_series WHERE id = ?");
            $result = $stmt->execute([$info['id_series']]);
            $message = "Toute la série de réservations a été supprimée.";
        } else {
            // Suppression unique
            $stmt = $this->db->prepare("DELETE FROM bookings WHERE id = ?");
            $result = $stmt->execute([$id]);
            $message = "La réservation a été supprimée avec succès.";
        }

        // UN SEUL ET UNIQUE ECHO JSON À LA FIN
        echo json_encode(['success' => $result, 'message' => $message]);

    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur technique : ' . $e->getMessage()]);
    }
    exit;
}
       
}