<?php

namespace app\controllers;

class HomeController {
    
    public function index() {
        // On définit les variables dont la vue aura besoin
        $pageTitle = "Accueil - Workspace Connect";
        $isLoggedIn = isset($_SESSION['user_id']);

        // On appelle la vue
        require_once __DIR__ . '/../../views/home.php';
    }
}