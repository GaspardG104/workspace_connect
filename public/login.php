<?php
session_start(); // Ouvre la session pour "retenir" l'utilisateur
$pdo = require_once __DIR__ . '/../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. On cherche l'utilisateur par son email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // 2. On vérifie si l'utilisateur existe ET si le mot de passe est bon
    if ($user && password_verify($password, $user['password_hash'])) {
        // Succès ! On stocke les infos utiles en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role'] = $user['id_role'];

        // Redirection vers la page de réservation
        header('Location: reserver.php');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Connexion - Workspace Connect</title>
</head>
<body>
    <h2>Connexion</h2>
    
    <?php if($error): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>