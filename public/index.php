<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Workspace Connect</title>
    <link rel="stylesheet" href="styles/style_index">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <h1>Bienvenue sur Workspace Connect</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>Bonjour, <strong><?= htmlspecialchars($_SESSION['user_nom']) ?></strong> ! <i class="fa-regular fa-circle-user"></i></p>
                <a href="parking/reserver.php">Réserver une place</a> | 
                
                <?php if ($_SESSION['user_role'] == 1): ?>
                    <a href="inscription.php">Administration (Créer un compte)</a> |
                <?php endif; ?>
                
                <a href="logout.php">Se déconnecter</a>
            <?php else: ?>
                <a href="login.php">Se connecter</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2>Simplifiez vos réservations de parking et de bureaux.</h2>
        <p>Notre plateforme permet aux collaborateurs de réserver leurs ressources en quelques clics.</p>
    </main>
</body>
</html>