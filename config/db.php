<?php
// config/db.php

$host = 'localhost';
$port = '5432';
$db   = 'workspace_connect';
$user = 'postgres'; 
$pass = 'MOT_DE_PASSE'; 

try {
    // Le DSN (Data Source Name) pour PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    
    // Création de la connexion avec des options de sécurité
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Affiche les erreurs SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupère les données sous forme de tableaux
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Sécurité renforcée
    ]);

    // echo "Connexion réussie !"; // Décommenter pour tester
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}