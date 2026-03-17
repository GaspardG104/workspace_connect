<?php
namespace App\Controllers;

class HomeController {
    public function index() {
        // Définit le chemin vers le dossier des vues
        $viewPath = __DIR__ . '/../views/';

        // On inclut les fichiers dans l'ordre
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'home.php';
        include $viewPath . 'layouts/footer.php';
    }
}