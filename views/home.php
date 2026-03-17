<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/main_theme.css"> </head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <main class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-8 text-center text-white">
                <h1 class="display-4 fw-bold mb-4">Simplifiez vos réservations...</h1>
                
                <?php if (!isset($isLoggedIn)): ?>
                    <a href="/login" class="btn btn-lg btn-light fw-bold px-5 py-3 shadow">Commencer maintenant</a>
                <?php else: ?>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/reservation/parking" class="btn btn-lg btn-primary fw-bold px-4 shadow">Réserver un Parking</a>
                        <a href="/reservation/desk" class="btn btn-lg btn-outline-light fw-bold px-4">Réserver un Bureau</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>