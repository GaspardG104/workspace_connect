<?php
namespace App\Controllers;

class OutController {
    public function logout() {
        // 1. On vide la session
        $_SESSION = [];

        // 2. On détruit la session
        session_destroy();

        // 3. On redirige vers la page de login ou l'accueil
        header('Location: /workspace_connect/login');
        exit;
    }
}