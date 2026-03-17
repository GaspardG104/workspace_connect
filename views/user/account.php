<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = require_once __DIR__ . '/../../config/db.php';
$userId = $_SESSION['user_id'];

// 1. Récupération des infos utilisateur
$queryUser = "SELECT u.nom, u.prenom, u.email, u.immatriculation, r.nom as role_nom 
              FROM users u LEFT JOIN roles r ON u.id_role = r.id WHERE u.id = ?";
$stmt = $pdo->prepare($queryUser);
$stmt->execute([$userId]);
$user = $stmt->fetch();

/// 2. Récupération des réservations (Organisateur OU Invité)
$queryRes = "
    (SELECT 
        res.nom as ressource_nom, 
        res.type as ressource_type, 
        bk.date_debut as debut, 
        bk.date_fin as fin,
        'Organisateur' as role_dans_resa
    FROM bookings bk 
    JOIN resources res ON bk.id_resource = res.id
    WHERE bk.id_user = ?)
    UNION
    (SELECT 
        res.nom as ressource_nom, 
        res.type as ressource_type, 
        bk.date_debut as debut, 
        bk.date_fin as fin,
        'Invité' as role_dans_resa
    FROM booking_invites bi
    JOIN bookings bk ON bi.id_booking = bk.id
    JOIN resources res ON bk.id_resource = res.id
    WHERE bi.id_user = ?)
    ORDER BY debut DESC";

$stmtRes = $pdo->prepare($queryRes);
// On passe deux fois l'ID : une fois pour le premier SELECT, une fois pour le second
$stmtRes->execute([$userId, $userId]);
$reservations = $stmtRes->fetchAll();

// 3. Logique flexible pour le menu déroulant (Bureaux, Salles, Boxs...)
$types_disponibles = [];
foreach ($reservations as $r) {
    if ($r['ressource_type'] !== 'parking') {
        $types_disponibles[] = $r['ressource_type'];
    }
}
$types_uniques = array_unique($types_disponibles);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/../styles/main_theme.css">
    <style>
        .card { border-radius: 15px; overflow: hidden; }
        /* Style pour souder le groupe de filtres comme sur ta capture */
        .btn-group .form-select {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            margin-left: -1px;
            border-color: #0d6efd;
        }
        .btn-outline-primary:hover { color: #fff; }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row g-4">
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center py-4">
                        <div class="mb-3 text-primary">
                            <i class="fas fa-user-circle fa-5x"></i>
                        </div>
                        <h4 class="fw-bold mb-0"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                        <span class="badge bg-primary mt-2 text-uppercase"><?= htmlspecialchars($user['role_nom'] ?? 'admin') ?></span>
                    </div>
                    <div class="card-footer bg-white p-0">
                        <div class="p-3 border-bottom">
                            <small class="text-muted d-block">Adresse Email</small>
                            <span class="fw-medium"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="p-3">
                            <small class="text-muted d-block">Plaque d'immatriculation</small>
                            <div id="immat-container" class="d-flex align-items-center justify-content-between">
                                <span id="immat-text" class="fw-medium <?= empty($user['immatriculation']) ? 'text-muted fst-italic' : '' ?>">
                                    <?= htmlspecialchars($user['immatriculation'] ?: 'Aucune (pas de véhicule)') ?>
                                </span>
                                
                                <input type="text" id="immat-input" class="form-control form-control-sm me-2" style="display:none;" 
                                    value="<?= htmlspecialchars($user['immatriculation']) ?>" placeholder="Ex: AA-123-BB ou laisser vide">
                                
                                <button id="btn-edit-immat" class="btn btn-sm btn-outline-secondary border-0">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                
                                <div id="immat-actions" style="display:none;">
                                    <button id="btn-save-immat" class="btn btn-sm btn-success me-1"><i class="fas fa-check"></i></button>
                                    <button id="btn-cancel-immat" class="btn btn-sm btn-light"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <div id="immat-feedback" class="mt-2" style="display:none;"></div>
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
                            <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm py-2">Mettre à jour</button>
                        </form>
                        <div id="pwd-feedback" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>Mes réservations</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-4">
                            <div class="btn-group w-100 shadow-sm" role="group">
                                <button class="btn btn-primary filter-btn" data-filter="all">Toutes</button>
                                
                                <button class="btn btn-outline-primary filter-btn" data-filter="parking">
                                    <i class="fas fa-car me-1"></i> Parking
                                </button>

                                <select class="form-select border-primary" id="workFilterSelect">
                                    <option value="" selected disabled>Bureaux, Salles, Boxs...</option>
                                    <?php foreach($types_uniques as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>">
                                            <?php 
                                                $label = ucfirst(htmlspecialchars($type));
                                                // Si le mot finit par "eau", on ajoute un "x", sinon on ajoute un "s"
                                                echo (substr($type, -3) === 'eau') ? $label . 'x' : $label . 's';
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button id="sortByDate" class="btn btn-outline-primary">
                                    <i class="fas fa-sort me-1"></i> Trier par date
                                </button>
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
                                        <?php foreach($reservations as $r): ?>
                                            <tr data-type="<?= htmlspecialchars($r['ressource_type']) ?>">
                                                <td>
                                                    <span class="fw-bold d-block text-dark"><?= htmlspecialchars($r['ressource_nom']) ?></span>
                                                    <div class="d-flex gap-1">
                                                        <small class="badge bg-light text-dark border text-uppercase" style="font-size: 0.65rem;"><?= htmlspecialchars($r['ressource_type']) ?></small>
                                                        <?php if($r['ressource_type'] === 'salle'): ?>
                                                            <small class="badge <?= $r['role_dans_resa'] === 'Organisateur' ? 'bg-primary' : 'bg-info' ?> text-uppercase" style="font-size: 0.65rem;">
                                                            <?= $r['role_dans_resa'] ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <span class="text-muted">Du :</span> <?= date('d/m/Y H:i', strtotime($r['debut'])) ?><br>
                                                        <span class="text-muted">Au :</span> <?= date('d/m/Y H:i', strtotime($r['fin'])) ?>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash-alt"></i></button>
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
// Logique de filtrage
function applyFilter(value) {
    const rows = document.querySelectorAll('#bookingTable tbody tr:not(.no-data)');
    rows.forEach(row => {
        const rowType = row.getAttribute('data-type');
        row.style.display = (value === 'all' || rowType === value) ? '' : 'none';
    });
}

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('workFilterSelect').selectedIndex = 0;
        // Style visuel des boutons
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.replace('btn-primary', 'btn-outline-primary'));
        this.classList.replace('btn-outline-primary', 'btn-primary');
        applyFilter(this.getAttribute('data-filter'));
    });
});

document.getElementById('workFilterSelect').addEventListener('change', function() {
    // Si on utilise le select, on remet le bouton parking en outline
    document.querySelectorAll('.filter-btn[data-filter="parking"]').forEach(b => b.classList.replace('btn-primary', 'btn-outline-primary'));
    applyFilter(this.value);
});

// Tri par date
let dateAscending = false;
document.getElementById('sortByDate').addEventListener('click', function() {
    const tbody = document.querySelector('#bookingTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.no-data)'));
    dateAscending = !dateAscending;
    rows.sort((a, b) => {
        const dateA = parseFrenchDate(a.cells[1].innerText);
        const dateB = parseFrenchDate(b.cells[1].innerText);
        return dateAscending ? dateA - dateB : dateB - dateA;
    });
    rows.forEach(row => tbody.appendChild(row));
});

function parseFrenchDate(text) {
    const match = text.match(/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})/);
    return match ? new Date(match[3], match[2]-1, match[1], match[4], match[5]) : new Date(0);
}

// Changement de mot de passe (AJAX)
document.getElementById('updatePwdForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fb = document.getElementById('pwd-feedback');
    fetch('process_update_pwd.php', { method: 'POST', body: new FormData(this) })
    .then(r => r.json()).then(data => {
        fb.style.display = 'block';
        fb.className = "alert py-2 small fw-medium " + (data.success ? "alert-success text-success" : "alert-danger text-danger");
        fb.innerText = data.message;
        if(data.success) this.reset();
    });
});


const textEl = document.getElementById('immat-text');
const inputEl = document.getElementById('immat-input');
const btnEdit = document.getElementById('btn-edit-immat');
const actionsEl = document.getElementById('immat-actions');
const btnSave = document.getElementById('btn-save-immat');
const btnCancel = document.getElementById('btn-cancel-immat');
const feedbackEl = document.getElementById('immat-feedback');

let initialVal = inputEl.value;

// Fonction de feedback intégrée (remplace l'alert)
function showImmatFeedback(msg, isSuccess) {
    feedbackEl.style.display = 'block';
    feedbackEl.className = "alert py-1 small fw-medium mt-2 " + (isSuccess ? "alert-success text-success" : "alert-danger text-danger");
    feedbackEl.innerText = (isSuccess ? "✅ " : "❌ ") + msg;
    setTimeout(() => { feedbackEl.style.display = 'none'; }, 3000);
}

btnEdit.addEventListener('click', () => {
    textEl.style.display = 'none'; btnEdit.style.display = 'none';
    inputEl.style.display = 'block'; actionsEl.style.display = 'block';
    inputEl.focus();
});

btnCancel.addEventListener('click', () => {
    inputEl.value = initialVal;
    textEl.style.display = 'block'; btnEdit.style.display = 'block';
    inputEl.style.display = 'none'; actionsEl.style.display = 'none';
});

btnSave.addEventListener('click', () => {
    const newVal = inputEl.value.trim();

    if (newVal === initialVal) { 
        btnCancel.click(); 
        return; }

    const formData = new FormData();
    formData.append('immatriculation', newVal);

    fetch('process_update_immat.php', { 
        method: 'POST', 
        body: formData })

    .then(r => r.json())

    .then(data => {
        showImmatFeedback(data.message, data.success); // On utilise le message du PHP ici
        if(data.success) {
            textEl.innerText = newVal;
            initialVal = newVal;
            if(!newVal) textEl.classList.add('text-muted', 'fst-italic');
            else textEl.classList.remove('text-muted', 'fst-italic');
            setTimeout(() => { btnCancel.click(); }, 1000);
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>