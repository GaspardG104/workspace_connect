<?php
session_start(); // Ouvre la session pour "retenir" l'utilisateur
$pdo = require_once __DIR__ . '/../config/db.php';

$error = "";

//Pour la redirection si non identifié
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

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
        $target = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'reserver.php';
        header("Location: $target");
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
    <link rel="stylesheet" href="styles/style_login/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

</head>
<body>
    <h2>Connexion <i class="fa-regular fa-id-card"></i></h2>
    
    <?php if($error): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect) ?>">

        <label> <i class="fa-solid fa-envelope"></i> Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label> <i class="fa-solid fa-lock"></i> Mot de passe :</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Se connecter </button>  <i class="fa-solid fa-arrow-right-to-bracket"></i>
    </form>
</body>
</html>