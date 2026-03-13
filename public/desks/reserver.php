<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Vérifie bien le chemin vers ton login
    exit;
}

$pdo = require_once __DIR__ . '/../../config/db.php';
$id_user = $_SESSION['user_id']; 
$nom_user = $_SESSION['user_nom'];

$resources = $pdo->query("SELECT id, nom FROM resources WHERE type = 'salle' AND type = 'bureau' ORDER BY nom")->fetchAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Open Space - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/../styles/style_desks.css">
    <link rel="stylesheet" href="/../styles/main_theme.css">
</head>
<body class="bg-light">

<?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="fw-bold text-white">Réserver un espace de travail</h1>
            <p class="text-white-50">Sélectionnez votre bureau ou une salle de réunion</p>
        </div>

        <div class="card shadow p-3 mx-auto" style="max-width: 800px; border-radius: 15px;">
            <div class="plan-container">
                <button class="btn btn-meeting meeting-1 shadow-sm text-primary">Salle de réunion 1</button>
                <button class="btn btn-meeting meeting-2 shadow-sm text-primary">Salle de réunion 2</button>

                <div class="desk-group-v v-desk-1">
                    <?php for($i=1; $i<=6; $i++) echo '<button class="desk-unit" title="Poste V1-'.$i.'"></button>'; ?>
                </div>
                <div class="desk-group-v v-desk-2">
                    <?php for($i=1; $i<=6; $i++) echo '<button class="desk-unit" title="Poste V2-'.$i.'"></button>'; ?>
                </div>

                <div class="desk-group-h h-1">
                    <?php for($i=1; $i<=6; $i++) echo '<button class="desk-unit" title="Poste H1-'.$i.'"></button>'; ?>
                </div>
                <div class="desk-group-h h-2">
                    <?php for($i=1; $i<=6; $i++) echo '<button class="desk-unit" title="Poste H2-'.$i.'"></button>'; ?>
                </div>
                <div class="desk-group-h h-3">
                    <?php for($i=1; $i<=6; $i++) echo '<button class="desk-unit" title="Poste H3-'.$i.'"></button>'; ?>
                </div>
                <div class="desk-group-h h-4">
                    <?php for($i=1; $i<=6; $i++) echo '<button class="desk-unit" title="Poste H4-'.$i.'"></button>'; ?>
                </div>

                <button class="btn btn-box box-1 text-dark">Box 1</button>
                <button class="btn btn-box box-2 text-dark">Box 2</button>
            </div>
            
            <div class="mt-4 d-flex justify-content-center gap-4">
                <small><i class="fa-solid fa-square text-primary"></i> Salles</small>
                <small><i class="fa-solid fa-square" style="color: #00a2e8;"></i> Bureaux</small>
                <small><i class="fa-solid fa-square text-dark"></i> Box</small>
            </div>
            <div class="text-center mt-4">
                <a href="/../index.php" class="btn btn-outline-secondary px-4">Retour à l'accueil</a>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>