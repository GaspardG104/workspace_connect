 <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/../index.php">
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
                                <a class="nav-link text-white fw-bold" href="/../inscription.php">Inscription</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold" href="/../logout.php">Déconnexion</a>
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