<?php
// config/db.php

// On récupère les identifiants via le require
$config = require __DIR__ . '/db_config.php';

try {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // On retourne l'objet PDO pour qu'il soit stocké dans $db dans l'AuthController
    return new PDO($dsn, $config['user'], $config['password'], $options);

} catch (PDOException $e) {
    // En production, on évite d'afficher $e->getMessage() pour ne pas dévoiler d'infos sensibles
    die("Erreur de connexion à la base de données.");
}