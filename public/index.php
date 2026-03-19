<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
            // L'action est la deuxième partie de l'URL (ex: /reservation/desk)
            $action = $urlParts[1] ?? 'parking'; 

            if ($action === 'parking') {
                $controller->parking();
            } elseif ($action === 'desk') {
                $controller->desk(); // <-- ICI : appeler la méthode desk() !
            } elseif ($action === 'getEvents') {
                $controller->getEvents();
            } elseif ($action === 'store') {
                $controller->store();
            } else {
                http_response_code(404);
                echo "Action de réservation inconnue";
            }
            break;

    case 'user':
        $controller = new \App\Controllers\UserController();
        $action = $urlParts[1] ?? 'account'; // Par défaut, on affiche le compte

        if ($action === 'account') {
            $controller->account();
        } elseif ($action === 'updateImmat') {
            $controller->updateImmat();
        } elseif ($action === 'updatePassword') {
            $controller->updatePassword();
        }
        break;

    case 'logout':
        $controller = new \App\Controllers\OutController();
        $controller->logout();
        break;

    case 'admin':
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
        header('Location: /workspace_connect/login');
        exit;
    }

    $controller = new \App\Controllers\AdminController();
    $action = $urlParts[1] ?? 'dashboard';

    if ($action === 'register') {
        $controller->register();
    } elseif ($action === 'storeUser') {
        $controller->storeUser();
    }
    break;

    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}