 <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/workspace_connect/home">
                <i class="fa-solid fa-car-side me-2"></i> Workspace Connect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class ="nav-link btn text-white px-3 me-2 btn-sm" href="/workspace_connect/user/account"><i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['user_nom']) ?></a>           
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn text-primary px-3 me-2 btn-sm" href="/workspace_connect/reservation/parking"> <i class="fa-solid fa-square-parking"></i> Parking</a> 
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn text-primary-.text-info-emphasis px-3 me-2 btn-sm" href="/workspace_connect/reservation/desk">Bureaux <i class="fa-solid fa-computer"></i></a>
                        </li>
                        <?php if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 2): ?>
                            <li class="nav-item">
                                <a class="nav-link text-success fw-bold" href="/workspace_connect/admin/register"><i class="fa-solid fa-user-check"></i> Gestion Utilisateurs </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 2): ?>
                            <li class="nav-item">
                                <a class="nav-link text-warning fw-bold" href="/workspace_connect/reservations/all"><i class="fa-solid fa-calendar"></i> Gestion Réservations </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold" href="/workspace_connect/logout"> Déconnexion <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light px-4" href="/workspace_connect/login">Se connecter</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>