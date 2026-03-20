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


    public function register() {
        $db = require __DIR__ . '/../../config/db.php';

        // 1. On récupère les rôles pour le menu déroulant
        $roles = $db->query("SELECT * FROM roles")->fetchAll(\PDO::FETCH_ASSOC);

        // 2. On récupère la liste des utilisateurs pour le tableau (L'OUBLI ÉTAIT ICI)
        $sql = "SELECT u.*, r.nom as role_nom 
                FROM users u 
                JOIN roles r ON u.id_role = r.id 
                ORDER BY u.nom ASC";
        $users = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        // On passe $roles et $users à la vue
        include $viewPath . 'admin/users_list.php'; 
        include $viewPath . 'layouts/footer.php';
    }

    // Traite la création du compte
    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /workspace_connect/admin/users_list');
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
    public function users_list() {
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

        // 1. Traitement de la mise à jour (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id_role = $_POST['id_role'];
                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $email = $_POST['email'];
                $immat = $_POST['immatriculation'];
                
                if (!empty($_POST['password'])) {
                    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET id_role=?, nom=?, prenom=?, email=?, immatriculation=?, password_hash=? WHERE id=?";
                    $params = [$id_role, $nom, $prenom, $email, $immat, $pass, $id];
                } else {
                    $sql = "UPDATE users SET id_role=?, nom=?, prenom=?, email=?, immatriculation=? WHERE id=?";
                    $params = [$id_role, $nom, $prenom, $email, $immat, $id];
                }

            $db->prepare($sql)->execute($params);
            $_SESSION['msg'] = "✅ Utilisateur $prenom $nom mis à jour avec succès !";
            } catch (\Exception $e) {
                $_SESSION['msg'] = "❌ Erreur lors de la modification : " . $e->getMessage();
            }
            header('Location: /workspace_connect/admin/register'); // Retour à la liste
            exit;
        }

        // 2. Affichage du formulaire (GET)
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            header('Location: /workspace_connect/admin/register');
            exit;
        }

        $roles = $db->query("SELECT * FROM roles")->fetchAll(\PDO::FETCH_ASSOC);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'admin/edit_user.php'; // On appelle la nouvelle vue
        include $viewPath . 'layouts/footer.php';
    }

    public function deleteUser($id) {
        $db = require __DIR__ . '/../../config/db.php';

        // Sécurité supplémentaire : on ne s'auto-supprime pas
        if ($id == $_SESSION['user_id']) {
            $_SESSION['msg'] = "❌ Vous ne pouvez pas supprimer votre propre compte admin !";
            header('Location: /workspace_connect/admin/users_list');
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['msg'] = "✅ Utilisateur supprimé avec succès.";
        } catch (\Exception $e) {
            $_SESSION['msg'] = "❌ Impossible de supprimer : l'utilisateur est peut-être lié à des réservations.";
        }

    header('Location: /workspace_connect/admin/users_list');
    exit;
}


}