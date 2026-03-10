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
    echo "<h1>❌ Erreur places de parkings</h1>";
    echo "Détails : " . $e->getMessage();
}

try {
    // On récupère les ressources de type 'parking'
    $stmt = $pdo->prepare("SELECT *  FROM users"); // oui après je demande qu'une partie des infos mais au final entre temps j'ai changer d'avis
    $stmt->execute();
    $users = $stmt->fetchAll();

    echo "<h3>Liste des utilisateurs :</h3>";
    echo "<ul>";
    foreach ($users as $u) {
        echo "<li><strong>" . htmlspecialchars($u['nom']) . "</strong> - Prénom : ". htmlspecialchars($u['prenom']) . " - 
        Poste : " . htmlspecialchars($u['id_role']) .  "</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<h1>❌ Erreur utilisateurs</h1>";
    echo "Détails : " . $e->getMessage();
}