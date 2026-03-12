<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Open Space - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="/../styles/style_desks.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: #1e3c72;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">
            <i class="fa-solid fa-building-user me-2"></i> Workspace Connect
        </a>
        <a href="../index.php" class="btn btn-outline-light btn-sm">Retour</a>
    </div>
</nav>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-white">Réserver un espace de travail</h1>
        <p class="text-white-50">Sélectionnez votre bureau ou une salle de réunion</p>
    </div>

    <div class="card shadow p-4 mx-auto" style="max-width: 900px;">
        <div class="plan-container">
            <button class="btn btn-office meeting-1 shadow-sm text-dark">Salle de réunion 1</button>
            <button class="btn btn-office meeting-2 shadow-sm text-dark">Salle de réunion 2</button>

            <button class="btn-office desk-group v-desk-1"></button>
            <button class="btn-office desk-group v-desk-2"></button>

            <button class="btn-office desk-group h-desk h-1"></button>
            <button class="btn-office desk-group h-desk h-2"></button>
            <button class="btn-office desk-group h-desk h-3"></button>
            <button class="btn-office desk-group h-desk h-4"></button>

            <button class="btn btn-office box box-1 text-dark">Box 1</button>
            <button class="btn btn-office box box-2 text-dark">Box 2</button>
        </div>
        
        <div class="mt-4 d-flex justify-content-center gap-3">
            <small><i class="fa-solid fa-square text-primary"></i> Salles</small>
            <small><i class="fa-solid fa-square" style="color: #00a2e8;"></i> Bureaux</small>
            <small><i class="fa-solid fa-square text-dark"></i> Box</small>
        </div>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-secondary px-4">Retour à l'accueil</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>