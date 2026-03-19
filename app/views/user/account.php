<link rel="stylesheet" href="/workspace_connect/public/styles/style_account.css">

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
                                
                                <a href="/workspace_connect/user/account?type=all" 
                                class="btn <?= ($filterType === 'all') ? 'btn-primary' : 'btn-outline-primary' ?> filter-btn d-flex align-items-center justify-content-center">
                                Toutes
                                </a>
                                
                                <a href="/workspace_connect/user/account?type=parking" 
                                class="btn <?= ($filterType === 'parking') ? 'btn-primary' : 'btn-outline-primary' ?> filter-btn">
                                    <i class="fas fa-car me-1"></i> Parking
                                </a>

                                <select class="form-select border-primary" id="workFilterSelect" onchange="location = this.value;">
                                    <option value="" selected disabled>Bureaux, Salles, Boxs...</option>
                                    <?php foreach($types_uniques as $type): ?>
                                        <?php if($type === 'parking') continue; ?>
                                        
                                        <option value="/workspace_connect/user/account?type=<?= htmlspecialchars($type) ?>" 
                                                <?= ($filterType === $type) ? 'selected' : '' ?>>
                                            <?php 
                                                $label = ucfirst(htmlspecialchars($type));
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
                                    <?php if(empty($bookings)): ?>
                                        <tr class="no-data"><td colspan="3" class="text-center py-5 text-muted">Aucune réservation trouvée.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($bookings as $r): ?>
                                            <tr data-type="<?= htmlspecialchars($r['resource_type']) ?>">
                                                <td>
                                                    <span class="fw-bold d-block text-dark"><?= htmlspecialchars($r['resource_name']) ?></span>
                                                    <div class="d-flex gap-1">
                                                        <small class="badge bg-light text-dark border text-uppercase" style="font-size: 0.65rem;"><?= htmlspecialchars($r['resource_type']) ?></small>
                                                        <?php if($r['resource_type'] === 'salle'): ?>
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
    fetch('/workspace_connect/user/updatePassword', { method: 'POST', body: new FormData(this) })
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

    fetch('/workspace_connect/user/updateImmat', { 
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

