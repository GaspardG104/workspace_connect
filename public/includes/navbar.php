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
                            <a class="nav-link btn text-primary px-3 me-2 btn-sm" href="parking/reserver.php"> <i class="fa-solid fa-square-parking"></i> Parking</a> 
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn text-primary-.text-info-emphasis px-3 me-2 btn-sm" href="desks/reserver.php">Bureaux <i class="fa-solid fa-person-booth"></i></a>
                        </li>
                        <?php if ($_SESSION['user_role'] == 1):?>
                            <li class="nav-item">
                                <a class="nav-link text-success fw-bold" href="/../inscription.php"><i class="fa-solid fa-user-check"></i> Inscription</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold" href="/../logout.php"> Déconnexion <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
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