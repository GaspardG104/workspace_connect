<?php
namespace App\Controllers;

use PDO;

class BookingController {
    
    // Affiche la page de réservation du parking
    public function parking() {
        // Sécurité : Vérification session
        if (!isset($_SESSION['user_id'])) {
            header('Location: /workspace_connect/login');
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';
        
        // On récupère les places de parking 
        $resources = $db->query("SELECT id, nom FROM resources WHERE type = 'parking' ORDER BY nom")->fetchAll();

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'reservations/parking.php'; 
        include $viewPath . 'layouts/footer.php';
    }

        // Affiche la page de réservation des bureaux
    public function desk() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /workspace_connect/login');
            exit;
        }

        // On utilise directement le pont de connexion sans passer par un Model pour l'instant
        $db = require __DIR__ . '/../../config/db.php';
        
        // 1. Récupération des bureaux et salles
        $stmt = $db->query("SELECT id, nom, capacite FROM resources WHERE type != 'parking' ORDER BY nom ASC");
        $resources = $stmt->fetchAll();

        // 2. Préparation du mapping pour le plan (Nécessaire pour les boutons S1, S2, etc.)
        $res_map = [];
        $capacities = [];
        foreach ($resources as $r) {
            $res_map[$r['nom']] = $r['id'];
            $capacities[$r['id']] = $r['capacite'];
        }

        // 3. Récupération des autres utilisateurs (pour les invitations)
        $stmtUsers = $db->prepare("SELECT id, prenom, nom FROM users WHERE id != :me ORDER BY nom ASC");
        $stmtUsers->execute(['me' => $_SESSION['user_id']]);
        $all_users = $stmtUsers->fetchAll(); 

        // 4. Chargement de la vue
        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'reservations/desk.php'; 
        include $viewPath . 'layouts/footer.php';
    }


    // Gère l'enregistrement 
public function store() {
    $db = require __DIR__ . '/../../config/db.php';
    
    // On vérifie la session
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => "Session expirée."]);
        exit;
    }

    $id_user = $_SESSION['user_id'];
    
    // Correction des noms : Ton formulaire semble envoyer 'resource' et non 'id_resource'
    $id_resource = $_POST['resource'] ?? $_POST['id_resource'] ?? null;
    $date_debut = $_POST['debut'] ?? null;
    $date_fin = $_POST['fin'] ?? null;
    $invites = $_POST['invites'] ?? []; // Tableau d'IDs d'utilisateurs

    if (!$id_resource || !$date_debut || !$date_fin) {
        echo json_encode(['success' => false, 'message' => "❌ Données manquantes (Ressource ou Dates)."]);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Insertion du Booking principal
        $sql = "INSERT INTO bookings (id_user, id_resource, date_debut, date_fin, statut) 
                VALUES (:user, :res, :debut, :fin, 'confirme')";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user'  => $id_user,
            'res'   => $id_resource,
            'debut' => $date_debut,
            'fin'   => $date_fin
        ]);

        $bookingId = $db->lastInsertId();

        // 2. Insertion dans 'attendees' (on transforme les IDs en Emails/Noms)
        if (!empty($invites) && is_array($invites)) {
            $stmtUser = $db->prepare("SELECT email, nom, prenom FROM users WHERE id = ?");
            $stmtAt = $db->prepare("INSERT INTO attendees (id_booking, email, nom_invite) VALUES (?, ?, ?)");

            foreach ($invites as $id_invite) {
                $stmtUser->execute([$id_invite]);
                $u = $stmtUser->fetch(PDO::FETCH_ASSOC);
                
                if ($u) {
                    $nomComplet = $u['prenom'] . ' ' . $u['nom'];
                    $stmtAt->execute([$bookingId, $u['email'], $nomComplet]);
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => "✅ Réservation réussie !"]);

    } catch (\Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log("Erreur Booking : " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => "❌ Erreur : " . $e->getMessage()]);
    }
}

    public function getEvents() {
        $db = require __DIR__ . '/../../config/db.php';
        $id_resource = isset($_GET['id_resource']) ? intval($_GET['id_resource']) : 0;

        $stmt = $db->prepare("SELECT b.id, b.date_debut as start, b.date_fin as end, 
                            CONCAT(u.prenom, ' ', u.nom) as title 
                            FROM bookings b 
                            JOIN users u ON b.id_user = u.id 
                            WHERE b.id_resource = :id");
        $stmt->execute(['id' => $id_resource]);
        
        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($events);
        exit; // Très important pour ne pas envoyer de HTML parasite
    }
    
}