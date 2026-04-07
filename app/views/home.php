<link rel="stylesheet" href="/workspace_connect/public/styles/style_index.css">
<main class="container">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-8 text-center text-white">
            <h1 class="display-4 fw-bold mb-4">Simplifiez vos réservations de parking et de bureaux.</h1>
            <p class="lead mb-5">Notre plateforme permet aux collaborateurs de réserver leurs ressources en quelques clics, en toute sécurité.</p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/workspace_connect/login" class="btn btn-lg btn-light fw-bold px-5 py-3 shadow">Commencer maintenant</a>
            <?php else: ?>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/workspace_connect/reservation/parking" class="btn btn-lg btn-primary fw-bold px-4 shadow">Réserver un Parking</a>
                    <a href="/workspace_connect/reservation/desk" class="btn btn-lg btn-light fw-bold text-primary px-4">Réserver un Bureau</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>