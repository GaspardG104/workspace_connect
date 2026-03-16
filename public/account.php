<?php
session_start();
// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = require_once __DIR__ . '/../config/db.php';
$userId = $_SESSION['user_id'];

// 1. Récupération des infos de l'utilisateur avec son rôle
$queryUser = "
    SELECT u.nom, u.prenom, u.email, u.immatriculation, r.nom as role_nom 
    FROM users u
    LEFT JOIN roles r ON u.id_role = r.id
    WHERE u.id = ?
";
$stmt = $pdo->prepare($queryUser);
$stmt->execute([$userId]);
$user = $stmt->fetch();

// 2. Récupération des réservations (Table bookings + Type de ressource pour le filtre)
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="styles/main_theme.css">
    <style>
        .card { border-radius: 15px; overflow: hidden; }
        .btn-check:checked + .btn-outline-primary { background-color: #0d6efd; color: white; }
        .filter-section { background: #fff; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
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
                        <h5 class="fw-bold mb-3"><i class="fas fa-lock me-2"></i>Changer le mot de passe</h5>
                        <form id="updatePwdForm">
                            <div class="mb-3">
                                <label class="small fw-bold">Ancien mot de passe</label>
                                <input type="password" name="old_pwd" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold">Nouveau mot de passe</label>
                                <input type="password" name="new_pwd" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm">Mettre à jour</button>
                        </form>
                        <div id="pwd-feedback" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>Mes réservations</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-4">
                            <div class="btn-group w-100" role="group">
                                <button id="sortByDate" class="btn btn-sm btn-outline-primary mb-2">
                                    <i class="fas fa-sort"></i> Trier par date (Plus récent/ancien)
                                </button>
                                <input type="radio" class="btn-check" name="resFilter" id="all" checked>
                                <label class="btn btn-outline-primary" for="all">Toutes</label>

                                <input type="radio" class="btn-check" name="resFilter" id="parking">
                                <label class="btn btn-outline-primary" for="parking"><i class="fas fa-car me-1"></i> Parkings</label>

                                <input type="radio" class="btn-check" name="resFilter" id="work">
                                <label class="btn btn-outline-primary" for="work"><i class="fas fa-laptop me-1"></i> Bureaux/Salles</label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="bookingTable">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>Ressource</th>
                                        <th>Dates</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($reservations)): ?>
                                        <tr class="no-data"><td colspan="3" class="text-center py-5 text-muted">Aucune réservation trouvée.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($reservations as $r): 
                                            // Catégorisation pour le JS
                                            $cat = ($r['ressource_type'] === 'parking') ? 'parking' : 'work';
                                        ?>
                                            <tr data-type="<?= $cat ?>">
                                                <td>
                                                    <span class="fw-bold d-block text-dark"><?= htmlspecialchars($r['ressource_nom']) ?></span>
                                                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;"><?= htmlspecialchars($r['ressource_type']) ?></small>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <span class="text-muted">Du :</span> <?= date('d/m/Y H:i', strtotime($r['debut'])) ?><br>
                                                        <span class="text-muted">Au :</span> <?= date('d/m/Y H:i', strtotime($r['fin'])) ?>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <button title="Annuler" class="btn btn-sm btn-outline-danger border-0">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
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
    // 1.1 GESTION DU FILTRE DES RÉSERVATIONS PAR TYPES
    document.querySelectorAll('input[name="resFilter"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const filterValue = this.id;
            const rows = document.querySelectorAll('#bookingTable tbody tr:not(.no-data)');
            
            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                if (filterValue === 'all' || rowType === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // 1.2 GESTION DU FILTRE DES RÉSERVATIONS PAR DATES
    let dateAscending = false; // Par défaut, on est du plus récent au plus ancien (SQL)

document.getElementById('sortByDate').addEventListener('click', function() {
    const tbody = document.querySelector('#bookingTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.no-data)'));

    dateAscending = !dateAscending; // On inverse l'ordre à chaque clic

    rows.sort((a, b) => {
        // On récupère le texte de la date dans la deuxième colonne (index 1)
        // On cherche le format DD/MM/YYYY HH:mm via le texte du premier "Du :"
        const dateA = parseFrenchDate(a.cells[1].innerText);
        const dateB = parseFrenchDate(b.cells[1].innerText);

        return dateAscending ? dateA - dateB : dateB - dateA;
    });

    // On ré-insère les lignes dans le bon ordre
    rows.forEach(row => tbody.appendChild(row));
});

// Fonction utilitaire pour transformer "16/03/2026 09:00" en objet Date JS
function parseFrenchDate(text) {
    // On extrait la partie "Du : 16/03/2026 09:00"
    const match = text.match(/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})/);
    if (!match) return new Date(0);
    const [_, day, month, year, hour, min] = match;
    return new Date(year, month - 1, day, hour, min);
}

    // 2. GESTION DU CHANGEMENT DE MOT DE PASSE (AJAX)
    document.getElementById('updatePwdForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const feedback = document.getElementById('pwd-feedback');
        
        fetch('process_update_pwd.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            feedback.style.display = 'block';
            feedback.className = "alert py-2 small fw-medium " + (data.success ? "alert-success text-success" : "alert-danger text-danger");
            feedback.innerText = data.message;
            if(data.success) this.reset();
        })
        .catch(err => {
            console.error(err);
            feedback.style.display = 'block';
            feedback.className = "alert alert-danger py-2 small";
            feedback.innerText = "Une erreur est survenue lors de la communication avec le serveur.";
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>