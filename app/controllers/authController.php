<?php
namespace App\Controllers;
use PDO;

class AuthController {
    
    public function showLogin() {
        $error = null; // On définit la variable par défaut (vide)
        $redirect = $_GET['redirect'] ?? '';
        
        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'auth/login.php';
        include $viewPath . 'layouts/footer.php';
    }

    public function verify() {
        // On utilise bien db.php qui est configuré pour Postgres sans le charset
        $db = require __DIR__ . '/../../config/db.php'; 

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // On appelle le modèle
        $user = \App\Models\User::findByEmail($db, $email);

        // password_verify compare le texte clair avec le hachage de la BDD
        if ($user && password_verify($password, $user['password_hash'])) {
            // On remplit la session avec les noms de colonnes de ton fichier SQL
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_role'] = $user['id_role'];

            header('Location: /workspace_connect/home');
            exit();
        } else {
            $error = "Email ou mot de passe incorrect.";
            $viewPath = __DIR__ . '/../views/';
            include $viewPath . 'layouts/header.php';
            include $viewPath . 'auth/login.php';
            include $viewPath . 'layouts/footer.php';
        }
    }
}