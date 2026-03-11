<?php
session_start(); // On doit démarrer la session pour pouvoir la détruire

// 1. On vide toutes les variables de session ($_SESSION)
$_SESSION = array();

// 2. On détruit physiquement le fichier de session sur le serveur
session_destroy();

// 3. On redirige vers l'accueil ou le login
header('Location: index.php');
exit;