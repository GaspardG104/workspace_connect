<nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background: rgba(0,0,0,0.2); backdrop-filter: blur(10px);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fa-solid fa-building-user me-2"></i> Workspace Connect
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item me-3">
                        <span class="text-white small">
                            <i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['user_nom']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-light text-primary px-3 me-2" href="parking/reserver.php">Parking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-outline-light px-3 me-2" href="desks.php">Bureaux</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-bold" href="logout.php"><i class="fa-solid fa-power-off"></i></a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary px-4" href="login.php">Connexion</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>