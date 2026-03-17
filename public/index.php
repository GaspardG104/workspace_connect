<?php
// 1. Initialisation de la session et des erreurs
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Chargement automatique (Autoload)
// Si tu n'as pas encore Composer, on peut simuler un petit autoloader
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// 3. Récupération de l'URL demandée
$url = $_GET['url'] ?? 'home'; // Par défaut, on va sur la page d'accueil
$url = rtrim($url, '/');

// 4. Système de routage très simple (Mapping URL -> Contrôleur)
// Dans un vrai projet, on utiliserait un objet Router
switch ($url) {
    case 'home':
        require_once '../app/Controllers/HomeController.php';
        $controller = new \Controllers\HomeController();
        $controller->index();
        break;

    case 'login':
        require_once '../app/Controllers/AuthController.php';
        $controller = new \Controllers\AuthController();
        $controller->showLogin();
        break;

    case 'reservation':
        require_once '../app/Controllers/BookingController.php';
        $controller = new \Controllers\BookingController();
        $controller->index();
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}