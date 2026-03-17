<?php
namespace App\Controllers;

class HomeController {
    public function index() {
        // Définition de la racine des vues pour simplifier
        $viewPath = __DIR__ . '/../views/';

        // Chargement des composants
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'home.php';
        include $viewPath . 'layouts/footer.php';
    }
}