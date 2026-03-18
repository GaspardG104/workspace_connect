<?php
/**
 * FRONT CONTROLLER - Point d'entrée unique
 */

// 1. Chargement de l'autoloader de Composer
// On remonte d'un cran (../) car vendor est à la racine du projet
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die("Erreur : L'autoloader est introuvable. As-tu lancé 'composer dump-autoload' ?");
}
require_once $autoloadPath;

// 2. Initialisation de la session et erreurs
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 3. Nettoyage de l'URL (ex: transforme 'reservation/desk/' en 'reservation/desk')
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$urlParts = explode('/', $url);

// 4. Routage (Mapping URL -> Contrôleur)
// On utilise les Namespaces configurés dans ton composer.json (App\Controllers)
switch ($urlParts[0]) {
    case 'home':
        $controller = new \App\Controllers\HomeController();
        $controller->index();
        break;

    case 'login':
        $controller = new \App\Controllers\AuthController();
        if (isset($urlParts[1]) && $urlParts[1] === 'verify') {
            $controller->verify();
        } else {
            $controller->showLogin();
        }
        break;

    case 'reservation':
        $controller = new \App\Controllers\BookingController();
        $action = $urlParts[1] ?? 'parking';

        if ($action === 'parking') {
            $controller->parking();
        } elseif ($action === 'getEvents') {
            $controller->getEvents(); // Est-ce que cette ligne existe bien ?
        } elseif ($action === 'store') {
            $controller->store();
        }
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}