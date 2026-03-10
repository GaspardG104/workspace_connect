<?php
// config/db.php

// On charge le fichier ignoré par Git
$settings = require 'settings.php';

try {
    $dsn = "pgsql:host={$settings['host']};dbname={$settings['db']}";
    $pdo = new PDO($dsn, $settings['user'], $settings['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion sécurisée.");
}