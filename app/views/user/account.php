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
                                
                                <button type="button" data-filter="all" 
                                class="btn <?= ($filterType === 'all') ? 'btn-primary' : 'btn-outline-primary' ?> filter-btn d-flex align-items-center justify-content-center">
                                Toutes
                                </button>
                                
                                <button type="button" data-filter="parking" 
                                class="btn <?= ($filterType === 'parking') ? 'btn-primary' : 'btn-outline-primary' ?> filter-btn">
                                    <i class="fas fa-car me-1"></i> Parking
                                </button>

                                <select class="form-select border-primary" id="workFilterSelect">
                                    <option value="" selected disabled>Bureaux, Salles, Boxs...</option>
                                    <?php foreach($types_uniques as $type): ?>
                                        <?php if($type === 'parking') continue; ?>
                                        
                                        <option data-filter="<?= htmlspecialchars($type) ?>" 
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
                                        <tr class="no-data">
                                            <td colspan="3" class="text-center py-5 text-muted">Aucune réservation trouvée.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($bookings as $r): ?>
                                            <tr data-type="<?= htmlspecialchars($r['resource_type']) ?>">
                                                <td>
                                                    <span class="fw-bold d-block text-dark"><?= htmlspecialchars($r['resource_name']) ?></span>
                                                    <div class="d-flex gap-1">
                                                        <small class="badge bg-light text-dark border text-uppercase" style="font-size: 0.65rem;">
                                                            <?= htmlspecialchars($r['resource_type']) ?>
                                                        </small>
                                                        <?php if($r['resource_type'] === 'salle'): ?>
                                                            <small class="badge <?= ($r['role_dans_resa'] === 'Organisateur') ? 'bg-primary' : 'bg-info' ?> text-uppercase" style="font-size: 0.65rem;">
                                                                <?= htmlspecialchars($r['role_dans_resa']) ?>
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
                                                    <?php if ($r['role_dans_resa'] === 'Organisateur'): ?>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                title="Annuler ma réservation"
                                                                onclick="prepareDelete(
                                                                    <?= $r['id'] ?>, 
                                                                    '<?= addslashes($r['resource_name']) ?>', 
                                                                    '<?= $r['resource_type'] ?>', 
                                                                    <?= $r['id_series'] ?? 'null' ?>
                                                                )">
                                                            <i class="fa-solid fa-trash-can me-1"></i> Annuler
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border">Invité</span>
                                                    <?php endif; ?>
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

        <!-- pour la suppression de réservation et prévénirs les participants-->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-exclamation-triangle me-2"></i>Annuler ma réservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">Voulez-vous vraiment annuler cette réservation ?</p>
                    
                    <div id="notificationOptions" class="mt-3 p-3 bg-light rounded border">
                        <p class="small fw-bold mb-2 text-muted">Options de notification :</p>
                        <div class="form-check" id="optionInvites">
                            <input class="form-check-input" type="checkbox" id="notifyInvites" checked>
                            <label class="form-check-label small" for="notifyInvites">
                                Prévenir les participants par email de l'annulation
                            </label>
                        </div>
                        <div class="mb-3 form-check" id="optionSeries" style="display: none;">
                            <input type="checkbox" class="form-check-input" id="deleteAllSeries">
                            <label class="form-check-label text-danger fw-bold" for="deleteAllSeries">
                                Annuler toute la série de réservations
                            </label>
                        </div>
                        <input type="hidden" id="notifyOrganizer" value="false">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Conserver</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Confirmer l'annulation</button>
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
    btn.addEventListener('click', function(e) {
        e.preventDefault(); // Empêcher la navigation
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
    applyFilter(this.options[this.selectedIndex].getAttribute('data-filter') || this.value);
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

// On déclare ces deux variables en haut de ton script pour qu'elles soient accessibles partout
let bookingToDelete = null;
let currentBookingSeriesId = null; 

function prepareDelete(id, resourceName, resourceType, idSeries) { 
    bookingToDelete = id;
    currentBookingSeriesId = idSeries; // On stocke l'ID de série

    // 1. Message personnalisé
    document.getElementById('deleteMessage').innerHTML = `Voulez-vous vraiment annuler votre réservation pour : <strong>${resourceName}</strong> ?`;
    
    const optionInvites = document.getElementById('optionInvites');
    const optionSeries = document.getElementById('optionSeries');
    const checkboxSeries = document.getElementById('deleteAllSeries');

    // On réinitialise la case à cocher de la série à chaque ouverture
    if(checkboxSeries) checkboxSeries.checked = false;

    // 2. Logique d'affichage selon le type de ressource
    if (resourceType && resourceType.toLowerCase() === 'salle') {
        optionInvites.style.display = 'block'; 
    } else {
        optionInvites.style.display = 'none';
        const checkbox = document.getElementById('notifyInvites');
        if (checkbox) checkbox.checked = false;
    }

    // 3. NOUVELLE LOGIQUE : Affichage de l'option Série
    // On l'affiche uniquement si idSeries existe et n'est pas nul
    if(optionSeries) {
        if (idSeries && idSeries !== null && idSeries !== 'null' && idSeries !== 0) {
            optionSeries.style.display = 'block';
        } else {
            optionSeries.style.display = 'none';
        }
    }

    const myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    myModal.show();
}

document.getElementById('confirmDeleteBtn').onclick = function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    // 1. RÉCUPÉRATION DES CHOIX (Tes anciens + le nouveau)
    // On utilise l'optionnel chaining ?. au cas où l'élément n'existe pas sur certaines pages
    const notifyInvites = document.getElementById('notifyInvites')?.checked || false;
    const deleteAllSeries = document.getElementById('deleteAllSeries')?.checked || false; // <-- NOUVEAU

    // Effet visuel de chargement (CONSERVÉ)
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Annulation...';

    const formData = new FormData();
    formData.append('notifyInvites', notifyInvites);
    formData.append('deleteAllSeries', deleteAllSeries); // <-- ON ENVOIE L'INFO AU CONTROLLER
    formData.append('notifyOrganizer', 'false'); // L'utilisateur est l'organisateur

    // Appel à l'API (CONSERVÉ)
    fetch(`/workspace_connect/reservations/delete/${bookingToDelete}`, {
        method: 'POST',
        body: formData
    })
    .then(r => {
        if (!r.ok) throw new Error('Erreur réseau');
        return r.json();
    })
    .then(data => {
        if (data.success) {
            // Supprimer la ligne du tableau au lieu de recharger la page
            const row = document.querySelector(`button[onclick*="prepareDelete(${bookingToDelete}"]`)?.closest('tr');
            if (row) {
                row.remove();
                // Vérifier s'il reste des réservations
                const tbody = document.querySelector('#bookingTable tbody');
                const remainingRows = tbody.querySelectorAll('tr:not(.no-data)');
                if (remainingRows.length === 0) {
                    const noDataRow = tbody.querySelector('tr.no-data');
                    if (!noDataRow) {
                        tbody.innerHTML = '<tr class="no-data"><td colspan="3" class="text-center py-5 text-muted">Aucune réservation trouvée.</td></tr>';
                    }
                }
            }
            // Fermer la modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            if (modal) modal.hide();
        } else {
            alert("Erreur : " + data.message);
            // Réinitialisation du bouton en cas d'erreur métier (CONSERVÉ)
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        alert("Une erreur technique est survenue.");
        // Réinitialisation du bouton en cas d'erreur technique (CONSERVÉ)
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
};
</script>

