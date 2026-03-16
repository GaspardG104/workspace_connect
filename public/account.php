<?php
session_start();
// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = require_once __DIR__ . '/../config/db.php';
$userId = $_SESSION['user_id'];

// 1. Récupération des infos avec jointure pour le rôle
$queryUser = "
    SELECT u.nom, u.prenom, u.email, u.immatriculation, r.nom as role_nom 
    FROM users u
    LEFT JOIN roles r ON u.id_role = r.id
    WHERE u.id = ?
";
$stmt = $pdo->prepare($queryUser);
$stmt->execute([$userId]);
$user = $stmt->fetch();

// 2. Récupération des réservations
$queryRes = "
    SELECT res.nom as ressource_nom, res.type as ressource_type, bk.date_debut as debut, bk.date_fin as fin 
    FROM bookings bk
    JOIN resources res ON bk.id_resource = res.id
    WHERE bk.id_user = ?
    ORDER BY bk.date_debut DESC
";
$stmtRes = $pdo->prepare($queryRes);
$stmtRes->execute([$userId]);
$reservations = $stmtRes->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Compte - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="styles/main_theme.css">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="fw-bold mb-4 text-center">Gestion de mon compte</h2>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center py-4">
                        <div class="mb-3 text-primary">
                            <i class="fas fa-user-circle fa-5x"></i>
                        </div>
                        <h4 class="fw-bold mb-0"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                        <span class="badge bg-primary mt-2"><?= htmlspecialchars($user['role_nom'] ?? 'Utilisateur') ?></span>
                    </div>
                    <div class="card-footer bg-white p-0">
                        <div class="p-3 border-bottom">
                            <small class="text-muted d-block">Adresse Email</small>
                            <span class="fw-medium"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="p-3">
                            <small class="text-muted d-block">Plaque d'immatriculation</small>
                            <span class="fw-medium"><?= htmlspecialchars($user['immatriculation'] ?: 'Non renseignée') ?></span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="fas fa-lock me-2"></i>Sécurité</h5>
                        <form id="updatePwdForm">
                            <div class="mb-3">
                                <label class="small fw-bold">Ancien mot de passe</label>
                                <input type="password" name="old_pwd" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold">Nouveau mot de passe</label>
                                <input type="password" name="new_pwd" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 fw-bold">Mettre à jour</button>
                        </form>
                        <div id="pwd-feedback" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2"></i>Mes réservations récentes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Ressource</th>
                                        <th>Date & Heure</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($reservations)): ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">Aucune réservation active.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($reservations as $r): ?>
                                            <tr>
                                                <td class="ps-3 fw-bold text-primary"><?= htmlspecialchars($r['ressource_nom']) ?></td>
                                                <td>
                                                    <small class="d-block"><strong>Du :</strong> <?= date('d/m/Y H:i', strtotime($r['debut'])) ?></small>
                                                    <small class="d-block"><strong>Au :</strong> <?= date('d/m/Y H:i', strtotime($r['fin'])) ?></small>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('updatePwdForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const feedback = document.getElementById('pwd-feedback');
        
        fetch('process_update_pwd.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            feedback.style.display = 'block';
            feedback.className = "alert py-2 small " + (data.success ? "alert-success" : "alert-danger");
            feedback.innerText = data.message;
            if(data.success) this.reset();
        });
    });
    </script>
</body>
</html>