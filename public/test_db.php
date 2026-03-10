<?php
// public/test_db.php
require_once '../config/db.php';

try {
    // On récupère les ressources de type 'parking'
    $stmt = $pdo->prepare("SELECT nom, localisation FROM resources WHERE type = :type");
    $stmt->execute(['type' => 'parking']);
    $parkings = $stmt->fetchAll();

    echo "<h1>✅ Connexion réussie à PostgreSQL !</h1>";
    echo "<h3>Liste des places de parking :</h3>";
    echo "<ul>";
    foreach ($parkings as $p) {
        echo "<li><strong>" . htmlspecialchars($p['nom']) . "</strong> - Localisation : " . htmlspecialchars($p['localisation']) . "</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<h1>❌ Erreur</h1>";
    echo "Détails : " . $e->getMessage();
}

try {
    // On récupère les ressources de type 'parking'
    $stmt = $pdo->prepare("SELECT *  FROM users");
    $stmt->execute();
    $parkings = $stmt->fetchAll();

    echo "<h1>✅ Connexion réussie à PostgreSQL !</h1>";
    echo "<h3>Liste des places de parking :</h3>";
    echo "<ul>";
    foreach ($parkings as $p) {
        echo "<li><strong>" . htmlspecialchars($p['nom']) . "</strong> - Localisation : " . htmlspecialchars($p['localisation']) . "</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<h1>❌ Erreur</h1>";
    echo "Détails : " . $e->getMessage();
}