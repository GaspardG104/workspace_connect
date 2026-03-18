<?php
namespace App\Controllers;

class UserController {
    // Affiche la page du compte connécté
    public function user() {
        // Sécurité : Vérification session
        if (!isset($_SESSION['user_id'])) {
            header('Location: /workspace_connect/login');
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';
        
        // On récupère les infos de l'utilisateur 
        $queryUser = "SELECT u.nom, u.prenom, u.email, u.immatriculation, r.nom as role_nom 
              FROM users u LEFT JOIN roles r ON u.id_role = r.id WHERE u.id = ?";
        $stmt = $pdo->prepare($queryUser);
        $stmt->execute([$userId]);
        $user = $stmt->fetch();



        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'user/account.php'; 
        include $viewPath . 'layouts/footer.php';
    }



}