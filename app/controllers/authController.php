<?php
namespace App\Controllers;

class AuthController {
    // Affiche le formulaire
    public function showLogin() {
        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'auth/login.php';
        include $viewPath . 'layouts/footer.php';
    }

    // Vérifie les identifiants
    public function verify() {
        // C'est ici que tu mettras ta logique SQL plus tard
        // Pour tester la navbar, on simule une connexion réussie :
        session_start();
        $_SESSION['user_id'] = 1;
        $_SESSION['user_nom'] = "Admin";
        $_SESSION['user_role'] = 1;

        header('Location: /workspace_connect/home');
        exit();
    }
}