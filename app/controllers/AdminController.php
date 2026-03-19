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

        header('Location: /workspace_connect/admin/inscription');
        exit;
    }
}