<?php

session_start(); // Ouvre la session pour "retenir" l'utilisateur

// Vérifier si connecté ET si admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: login.php?redirect=inscription.php');
    exit;
}

// On récupère la connexion PDO
$pdo = require_once __DIR__ . '/../config/db.php';
$message = "";

// RECUPERATION DE L'ID RÉEL
$id_user = $_SESSION['user_id']; 
$nom_user = $_SESSION['user_nom']; // Optionnel pour la partie user experience


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_role = $_POST['id_role'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $immatriculation = $_POST['immatriculation'];
    $password = $_POST['password'];

    // 1. On hache le mot de passe (Sécurité !)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 2. On insère dans la base (id_role = 3 par défaut pour "collaborateur")
        $sql = "INSERT INTO users (id_role, nom, prenom, email, immatriculation, password_hash) 
                VALUES (:id_role, :nom, :prenom, :email, :imma, :pass)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_role'   => $id_role,
            'nom'   => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'imma'  => $immatriculation,
            'pass'  => $password_hash
        ]);

        $message = "✅" . htmlspecialchars($nom_user) . " a créer le compte de " . htmlspecialchars($nom) . " avec succès ! 
        <a href='login.php'>Connectez-vous ici</a>";
    } catch (Exception $e) {
        $message = "❌ Erreur : Cet email est peut-être déjà utilisé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription - Workspace Connect</title>
</head>
<body>
    <h1>Créer un compte</h1>
    <p><?= $message ?></p>

    <form method="POST">
        <input type="text" name="nom" placeholder="Nom" required><br><br>
        <input type="text" name="prenom" placeholder="Prénom" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="text" name="immatriculation" placeholder="Immatriculation (ex: AA-123-BB)"><br><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br><br>
        
        <button type="submit">Valider</button>
    </form>
    <a href="logout.php" style="color: red;">Se déconnecter</a>
</body>
</html>