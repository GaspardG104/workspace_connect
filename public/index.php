<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="styles/style_index.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fa-solid fa-car-side me-2"></i> Workspace Connect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link text-white me-3">
                                <i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['user_nom']) ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-dark px-3 me-2 btn-sm" href="parking/reserver.php">Réserver</a>
                        </li>
                        <?php if ($_SESSION['user_role'] == 1): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="inscription.php">Inscription</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold" href="logout.php">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light px-4" href="login.php">Se connecter</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-8 text-center text-white">
                <h1 class="display-4 fw-bold mb-4">Simplifiez vos réservations de parking et de bureaux.</h1>
                <p class="lead mb-5">Notre plateforme permet aux collaborateurs de réserver leurs ressources en quelques clics, en toute sécurité.</p>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-lg btn-light fw-bold px-5 py-3 shadow">Commencer maintenant</a>
                <?php else: ?>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="parking/reserver.php" class="btn btn-lg btn-primary fw-bold px-4 shadow">Réserver un Parking</a>
                        <a href="desks/reserver.php" class="btn btn-lg btn-outline-light fw-bold px-4">Réserver un Bureau</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>