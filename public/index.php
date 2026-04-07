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
                $controller->desk(); 
            } elseif ($action === 'getEvents') {
                $controller->getEvents();
            } elseif ($action === 'store') {
                $controller->store();
            } elseif ($action === 'delete') {
                $id = $urlParts[2] ?? null;
                $controller->delete($id);
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
        // Autorise Admin (1) ET Manager (2)
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 2])) {
            header('Location: /workspace_connect/login');
            exit;
        }

        $controller = new \App\Controllers\AdminController();
        $action = $urlParts[1] ?? 'dashboard';

        if ($action === 'register') {
            $controller->register();
        } elseif ($action === 'storeUser') {
            $controller->storeUser();
        } elseif ($action === 'users_list') {
            $controller->users_list();
        } elseif ($action === 'editUser') {
            $id = $urlParts[2] ?? null;
            $controller->editUser($id);
        } elseif ($action === 'import') {
            $controller->import();
        } elseif ($action === 'deleteUser') {
        $id = $urlParts[2] ?? null;
        $controller->deleteUser($id);
        } else {
            // Redirection par défaut ou message
            echo "Dashboard Admin";
        }
        break;

    case 'chat':
        $controller = new \App\Controllers\ChatController();
        if ($urlParts[1] === 'process') {
        $controller->process();
        }
        break;

    case 'reservations':
        $controller = new \App\Controllers\ReservationController();
        if ($urlParts[1] === 'all') {
            $controller->listAll();
        } elseif ($urlParts[1] === 'search') {
            $controller->search();
        } elseif ($urlParts[1] === 'delete') { // <--- AJOUTE CECI
            $id = $urlParts[2] ?? null;
            $controller->delete($id);
        }
        break;
        
    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}