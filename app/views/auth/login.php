<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 shadow-lg border-0" style="width: 100%; max-width: 400px; border-radius: 15px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Connexion <i class="fa-regular fa-id-card text-primary ms-2"></i></h2>
            <p class="text-muted small">Accédez à votre espace Workspace Connect</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3 border-0 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect) ?>">

            <div class="mb-3">
                <label class="form-label small fw-bold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="adresse@email.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0 ps-0" placeholder="********" required>
                    <button class="btn btn-outline-light border-start-0 text-muted" type="button" id="togglePassword" style="border: 1px solid #dee2e6;">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">
                    Se connecter <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                </button>
                <a href="index.php" class="btn btn-link btn-sm text-decoration-none text-muted">Retour à l'accueil</a>
            </div>
        </form>
    </div>
</div>

