<?php
namespace App\Controllers;

class UserController {
    // Affiche la page du compte connécté
    public function account() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /workspace_connect/login');
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';
        $userId = $_SESSION['user_id'];
        
        // 1. Récupérer les infos de l'utilisateur
        $stmtUser = $db->prepare("SELECT u.*, r.nom as role_nom FROM users u LEFT JOIN roles r ON u.id_role = r.id WHERE u.id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(\PDO::FETCH_ASSOC);

        // 2. RÉCUPÉRER LES RÉSERVATIONS (La partie manquante !)
        // On joint la table 'resources' pour avoir le nom de la salle/bureau/place
        $queryBookings = "SELECT b.*, res.nom as resource_name, res.type as resource_type 
                        FROM bookings b 
                        JOIN resources res ON b.id_resource = res.id 
                        WHERE b.id_user = ? 
                        ORDER BY b.date_debut DESC";
        $stmtBookings = $db->prepare($queryBookings);
        $stmtBookings->execute([$userId]);
        $bookings = $stmtBookings->fetchAll(\PDO::FETCH_ASSOC);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'user/account.php'; // Ici, $user ET $bookings seront disponibles
        include $viewPath . 'layouts/footer.php';
    }

    // Remplace process_update_immat.php
    public function updateImmat() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Session expirée']);
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';
        $userId = $_SESSION['user_id'];
        $nouvelle_immat = isset($_POST['immatriculation']) ? trim($_POST['immatriculation']) : '';

        try {
            $stmt = $db->prepare("UPDATE users SET immatriculation = :immat WHERE id = :id");
            $stmt->execute(['immat' => $nouvelle_immat, 'id' => $userId]);
            
            $msg = empty($nouvelle_immat) ? "Préférence mise à jour." : "✅ Plaque mise à jour !";
            echo json_encode(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => "❌ Erreur technique."]);
        }
        exit;
    }

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

        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($old_pwd, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => "L'ancien mot de passe est incorrect."]);
            exit;
        }

        $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->execute([$new_hash, $userId]);

        echo json_encode(['success' => true, 'message' => '✅ Mot de passe modifié !']);
        exit;
    }
}