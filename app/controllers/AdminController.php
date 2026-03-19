<?php
namespace App\Controllers;

class AdminController {

    public function __construct() {
        // Double sécurité : on vérifie que l'utilisateur est admin (ID de rôle 1)
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
            header('Location: /workspace_connect/login');
            exit;
        }
    }

    // Affiche le formulaire d'inscription
    public function register() {
        $db = require __DIR__ . '/../../config/db.php';

        // 1. On va chercher tous les rôles en base de données
        $stmtRoles = $db->query("SELECT id, nom FROM roles ORDER BY nom ASC");
        $roles = $stmtRoles->fetchAll(\PDO::FETCH_ASSOC);

        $message = $_SESSION['msg'] ?? "";
        $message_type = (strpos($message, '✅') !== false) ? 'success' : 'danger';
        unset($_SESSION['msg']);
        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'admin/inscription.php';
        include $viewPath . 'layouts/footer.php';
    }

    // Traite la création du compte
    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /workspace_connect/admin/inscription');
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';

        $id_role = $_POST['id_role'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $immatriculation = $_POST['immatriculation'];
        $password = $_POST['password'];

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (id_role, nom, prenom, email, immatriculation, password_hash) 
                    VALUES (:id_role, :nom, :prenom, :email, :imma, :pass)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_role' => $id_role,
                ':nom'     => $nom,
                ':prenom'  => $prenom,
                ':email'   => $email,
                ':imma'    => $immatriculation,
                ':pass'    => $password_hash
            ]);

            $_SESSION['msg'] = "✅ L'utilisateur $prenom $nom a été créé avec succès !";
        } catch (\Exception $e) {
            $_SESSION['msg'] = "❌ Erreur : " . $e->getMessage();
        }

        header('Location: /workspace_connect/admin/register');
        exit;
    }

        // --- Affiche la liste des utilisateurs ---
    public function usersList() {
        $db = require __DIR__ . '/../../config/db.php';
        
        // On récupère les users avec le nom de leur rôle
        $sql = "SELECT u.*, r.nom as role_nom 
                FROM users u 
                JOIN roles r ON u.id_role = r.id 
                ORDER BY u.nom ASC";
        $users = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'admin/users_list.php';
        include $viewPath . 'layouts/footer.php';
    }

    // --- Modifie un utilisateur ---
    public function editUser($id) {
        $db = require __DIR__ . '/../../config/db.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Logique de mise à jour
            $id_role = $_POST['id_role'];
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $email = $_POST['email'];
            $immat = $_POST['immatriculation'];
            
            // Si un nouveau mot de passe est saisi, on le hache, sinon on garde l'ancien
            if (!empty($_POST['new_password'])) {
                $pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET id_role=?, nom=?, prenom=?, email=?, immatriculation=?, password_hash=? WHERE id=?";
                $params = [$id_role, $nom, $prenom, $email, $immat, $pass, $id];
            } else {
                $sql = "UPDATE users SET id_role=?, nom=?, prenom=?, email=?, immatriculation=? WHERE id=?";
                $params = [$id_role, $nom, $prenom, $email, $immat, $id];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $_SESSION['msg'] = "✅ Utilisateur mis à jour !";
            header('Location: /workspace_connect/admin/usersList');
            exit;
        }

        // Affichage du formulaire d'édition
        $user = $db->prepare("SELECT * FROM users WHERE id = ?");
        $user->execute([$id]);
        $userData = $user->fetch(\PDO::FETCH_ASSOC);
        
        $roles = $db->query("SELECT * FROM roles")->fetchAll(\PDO::FETCH_ASSOC);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'admin/edit_user.php';
        include $viewPath . 'layouts/footer.php';
    }

    public function deleteUser($id) {
    $db = require __DIR__ . '/../../config/db.php';

    // Sécurité supplémentaire : on ne s'auto-supprime pas
    if ($id == $_SESSION['user_id']) {
        $_SESSION['msg'] = "❌ Vous ne pouvez pas supprimer votre propre compte admin !";
        header('Location: /workspace_connect/admin/usersList');
        exit;
    }

    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['msg'] = "✅ Utilisateur supprimé avec succès.";
    } catch (\Exception $e) {
        $_SESSION['msg'] = "❌ Impossible de supprimer : l'utilisateur est peut-être lié à des réservations.";
    }

    header('Location: /workspace_connect/admin/usersList');
    exit;
}


}