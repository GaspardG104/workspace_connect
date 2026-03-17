<?php
namespace Controllers;

class HomeController {
    public function index() {
        // Logique métier : Est-ce que l'utilisateur est connecté ?
        $isLoggedIn = isset($_SESSION['user_id']);
        
        // On "rend" la vue en lui passant les infos
        require_once '../views/home.php';
    }
}