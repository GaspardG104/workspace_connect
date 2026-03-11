<?php

// Indispensable pour lire les infos du login
session_start(); 

// Si pas de session, retour au login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// On récupère la connexion PDO
$pdo = require_once __DIR__ . '/../config/db.php';
$message = "";

// RECUPERATION DE L'ID RÉEL
$id_user = $_SESSION['user_id']; 
$nom_user = $_SESSION['user_nom']; // Optionnel pour la partie user experience

// Si le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_resource = $_POST['resource'];
    $date_debut = $_POST['debut'];
    $date_fin = $_POST['fin'];

    try {
        $sql = "INSERT INTO bookings (id_user, id_resource, date_debut, date_fin) 
                VALUES (:user, :res, :debut, :fin)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user'  => $id_user,
            'res'   => $id_resource,
            'debut' => $date_debut,
            'fin'   => $date_fin
        ]);

        $message = "✅ Réservation réussie pour " . htmlspecialchars($nom_user) . " !";

    } catch (Exception $e) {
        // Si la contrainte 'no_overlap' est déclenchée, ça tombe ici !
        $message = "❌ Erreur : Cette place est déjà réservée sur ce créneau.";
    }
}

// On récupère la liste des parkings pour le menu déroulant
$resources = $pdo->query("SELECT id, nom FROM resources WHERE type = 'parking'")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Réserver un parking</title>
</head>
<body>
    <h1>Réserver ma place</h1>
    
    <?php if($message) echo "<p>$message</p>"; ?>

    <form method="POST">
        <label>Choisir la place :</label>
        <select name="resource" required>
            <?php foreach($resources as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Début :</label>
        <input type="datetime-local" name="debut" required>
        <br><br>

        <label>Fin :</label>
        <input type="datetime-local" name="fin" required>
        <br><br>

        <button type="submit">Confirmer la réservation</button>
    </form>
</body>
</html>