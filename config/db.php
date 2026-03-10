<?php
// config/db.php

// On charge le fichier ignoré par Git
$db_config = require 'db_config.php';

try {
    $dsn = "pgsql:host={$db_config['host']};dbname={$db_config['db']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion sécurisée.");
}