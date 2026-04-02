<?php
namespace App\Controllers;

// Importation de la classe PDO globale
use PDO;

class UserController {

    /**
     * Affiche la page "Mon Compte" avec les réservations (Organisateur + Invité)
     */
    public function account() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /workspace_connect/login');
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';
        $userId = $_SESSION['user_id'];
        
        // 1. Récupération des types de ressources pour le filtre (Parking, Salle, etc.)
        $stmtTypes = $db->query("SELECT DISTINCT type FROM resources ORDER BY type");
        $types_uniques = $stmtTypes->fetchAll(PDO::FETCH_COLUMN);

        // 2. Gestion du filtre par type
        $filterType = $_GET['type'] ?? 'all';
        $typeCondition = ($filterType !== 'all') ? " AND res.type = ?" : "";

        // 3. Infos de l'utilisateur connecté (pour obtenir son email notamment)
        $stmtUser = $db->prepare("SELECT u.*, r.nom as role_nom FROM users u LEFT JOIN roles r ON u.id_role = r.id WHERE u.id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            die("Utilisateur introuvable.");
        }
        
        $userEmail = $user['email'];

        // 4. Requête UNION : On fusionne les résas créées par moi ET celles où je suis invité (via mon email)
        // Note : On utilise 'as debut' et 'as fin' pour que la vue s'y retrouve
        $queryBookings = "
            (SELECT bk.id, bk.statut, res.nom as resource_name, res.type as resource_type, 
                    bk.date_debut as debut, bk.date_fin as fin, 'Organisateur' as role_dans_resa,
                    bk.id_series -- <--- AJOUT ICI
            FROM bookings bk
            JOIN resources res ON bk.id_resource = res.id
            WHERE bk.id_user = ? $typeCondition)
            UNION
            (SELECT bk.id, bk.statut, res.nom as resource_name, res.type as resource_type, 
                    bk.date_debut as debut, bk.date_fin as fin, 'Invité' as role_dans_resa,
                    bk.id_series -- <--- AJOUT ICI
            FROM bookings bk
            JOIN resources res ON bk.id_resource = res.id
            JOIN attendees att ON bk.id = att.id_booking
            WHERE att.email = ? $typeCondition)
            ORDER BY debut DESC";
        // 5. Préparation rigoureuse des paramètres pour éviter les décalages
        $params = [];
        
        // Paramètres pour la 1ère partie (Organisateur)
        $params[] = $userId; 
        if ($filterType !== 'all') {
            $params[] = $filterType;
        }

        // Paramètres pour la 2ème partie (Invité)
        $params[] = $userEmail;
        if ($filterType !== 'all') {
            $params[] = $filterType;
        }

        $stmtBookings = $db->prepare($queryBookings);
        $stmtBookings->execute($params);
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        // 6. Inclusion des vues
        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'user/account.php';
        include $viewPath . 'layouts/footer.php';
    }

    /**
     * Met à jour le mot de passe
     */
    public function updatePassword() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Session expirée']);
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';
        $userId = $_SESSION['user_id'];
        $old_pwd = $_POST['old_pwd'] ?? '';
        $new_pwd = $_POST['new_pwd'] ?? '';

        if (strlen($new_pwd) < 8) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit faire au moins 8 caractères.']);
            exit;
        }

        // Vérification de l'ancien mot de passe
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($old_pwd, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => "L'ancien mot de passe est incorrect."]);
            exit;
        }

        // Mise à jour
        $new_hash = password_hash($new_pwd, PASSWORD_BCRYPT);
        $update = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        
        if ($update->execute([$new_hash, $userId])) {
            echo json_encode(['success' => true, 'message' => "✅ Mot de passe mis à jour !"]);
        } else {
            echo json_encode(['success' => false, 'message' => "❌ Erreur lors de la mise à jour."]);
        }
        exit;
    }

}