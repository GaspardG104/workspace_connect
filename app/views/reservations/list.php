<link rel="stylesheet" href="/workspace_connect/public/styles/style_list_reservations.css">

<div class="container mt-4">
    <h2><i class="fa-solid fa-list-check me-2 text-primary"></i>Gestion des réservations</h2>

    <div class="row g-3 mb-4 mt-2 p-3 bg-light rounded shadow-sm border">
        <div class="col-md-4">
            <label class="small fw-bold">Recherche instantanée</label>
            <input type="text" id="ajax-search" class="form-control shadow-sm" placeholder="Taper pour chercher (nom, invité, salle)...">
        </div>
        <div class="col-md-3">
            <label class="small fw-bold">Filtrer par date</label>
            <input type="date" id="ajax-date" class="form-control shadow-sm">
        </div>
        <div class="col-md-3">
            <label class="small fw-bold">Trier par</label>
            <select id="ajax-sort" class="form-select shadow-sm">
                <option value="date_debut">Date</option>
                <option value="user_nom">Compte</option>
                <option value="resource_nom">Ressource</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-secondary w-100 shadow-sm" onclick="resetFilters()">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Ressource</th>
                        <th>Date & Heure</th>
                        <th>Statut</th>
                        <?php if ($_SESSION['user_role'] == 1): ?>
                        <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="reservation-table-body">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<!--Pour la partie de message de suppression pour prévenirs les participants-->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fa-solid fa-exclamation-triangle me-2"></i>Confirmation de suppression</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="deleteMessage"></p>
        <div id="notificationOptions">
            <hr>
            <div class="form-check mb-2" id="optionInvites">
                <input class="form-check-input" type="checkbox" id="notifyInvites" checked>
                <label class="form-check-label" for="notifyInvites">
                    Prévenir les participants par email
                </label>
            </div>
            <div class="form-check" id="optionOrganizer">
                <input class="form-check-input" type="checkbox" id="notifyOrganizer" checked>
                <label class="form-check-label" for="notifyOrganizer">
                    Prévenir l'organisateur de l'annulation
                </label>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Confirmer la suppression</button>
      </div>
    </div>
  </div>
</div>

<script>
const searchInput = document.getElementById('ajax-search');
const dateInput = document.getElementById('ajax-date');
const sortSelect = document.getElementById('ajax-sort');
const tableBody = document.getElementById('reservation-table-body');

// On récupère le rôle PHP pour le JS
const isAdmin = <?= $_SESSION['user_role'] == 1 ? 'true' : 'false' ?>;

function fetchReservations() {
    const search = searchInput.value;
    const date = dateInput.value;
    const sort = sortSelect.value;

    fetch(`/workspace_connect/reservations/search?search=${encodeURIComponent(search)}&date=${date}&sort=${sort}`)
        .then(response => response.json())
        .then(data => {
            renderTable(data);
        })
        .catch(error => console.error('Erreur:', error));
}

function renderTable(data) {
    tableBody.innerHTML = '';

    if (data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="${isAdmin ? 5 : 4}" class="text-center py-4 text-muted">Aucune réservation trouvée</td></tr>`;
        return;
    }

    data.forEach(res => {
        const statusColor = res.statut === 'confirme' ? 'bg-success' : 'bg-warning';
        
        let row = `
            <tr>
                <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size: 0.8rem;">
                        ${res.user_prenom[0]}${res.user_nom[0]}
                    </div>
                <div>
                    <div class="d-flex align-items-center">
                    <span class="fw-bold text-dark me-2">${res.user_prenom} ${res.user_nom}</span>
                            
                        ${res.nb_invites > 0 ? `
                            <span class="badge rounded-pill bg-info text-dark cursor-pointer" 
                                style="cursor: pointer; font-size: 0.7rem;" 
                                onclick="toggleAttendees(${res.id})" 
                                title="Cliquer pour voir les invités">
                                +${res.nb_invites}
                            </span>
                        ` : ''}
                    </div>
                    <small class="text-muted d-block">${res.role_label}</small>
                    <div id="attendees-${res.id}" class="d-none mt-1 animate__animated animate__fadeIn">
                        <small class="text-info fw-bold" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-users me-1"></i> ${res.liste_invites}
                                        </small>
                    </div>
                </div>
            </div>
            </td>
        <td>
            <span class="fw-bold d-block">${res.resource_nom}</span>
            <small class="badge bg-light text-dark border text-uppercase" style="font-size: 0.6rem;">${res.resource_type}</small>
        </td>
        <td>
            <div class="small">
                <span class="text-muted">Le :</span> ${new Date(res.date_debut).toLocaleString('fr-FR', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit'})}
            </div>
        </td>
        <td><span class="badge ${statusColor}">${res.statut}</span></td>
        `;

        // Si Admin, on ajoute la colonne Action avec le bouton poubelle
        const fullName = `${res.user_prenom} ${res.user_nom}`;

        if (isAdmin) {
            row += `
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-danger border-0" title="Supprimer" 
                        onclick="prepareDelete(${res.id}, '${fullName.replace(/'/g, "\\'")}', ${res.nb_invites || 0}, ${res.id_user})">
                        <i class="fa-solid fa-trash-alt"></i>
                    </button>
                </td>
            `;
        }

        row += `</tr>`;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
}
// --- LOGIQUE DE SUPPRESSION ---
let bookingToDelete = null;

// CETTE FONCTION DOIT S'APPELER prepareDelete (comme dans le onclick du bouton)
function prepareDelete(id, organizerName, nbInvites, organizerId) {
    bookingToDelete = id;
    
    // On récupère l'ID de session via PHP
    const currentUserId = <?= $_SESSION['user_id'] ?>;
    
    // Affichage du nom dans la modale
    document.getElementById('deleteMessage').innerHTML = `Voulez-vous vraiment supprimer la réservation de <strong>${organizerName}</strong> ?`;
    
    // Gestion de l'affichage des options de mail
    const optionInvites = document.getElementById('optionInvites');
    const optionOrganizer = document.getElementById('optionOrganizer');
    
    if(optionInvites) optionInvites.style.display = (nbInvites > 0) ? 'block' : 'none';
    if(optionOrganizer) optionOrganizer.style.display = (organizerId != currentUserId) ? 'block' : 'none';

    // Ouverture de la modale
    const modalEl = document.getElementById('deleteModal');
    const myModal = new bootstrap.Modal(modalEl);
    myModal.show();
}

// Action du bouton de confirmation
document.getElementById('confirmDeleteBtn').onclick = function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    const notifyInvites = document.getElementById('notifyInvites')?.checked || false;
    const notifyOrganizer = document.getElementById('notifyOrganizer')?.checked || false;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Suppression...';

    const formData = new FormData();
    formData.append('notifyInvites', notifyInvites);
    formData.append('notifyOrganizer', notifyOrganizer);

    fetch(`/workspace_connect/reservations/delete/${bookingToDelete}`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Fermer la modale
            const modalEl = document.getElementById('deleteModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if(modalInstance) modalInstance.hide();
            
            fetchReservations(); // Recharger la liste
        } else {
            alert("Erreur : " + data.message);
        }
    })
    .catch(err => console.error(err))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
};

// --- AUTRES FONCTIONS ---
function toggleAttendees(bookingId) {
    const element = document.getElementById(`attendees-${bookingId}`);
    if (element) {
        element.classList.toggle('d-none');
    }
}

function resetFilters() {
    searchInput.value = '';
    dateInput.value = '';
    sortSelect.value = 'date_debut';
    fetchReservations();
}

searchInput.addEventListener('input', fetchReservations);
dateInput.addEventListener('change', fetchReservations);
sortSelect.addEventListener('change', fetchReservations);

document.addEventListener('DOMContentLoaded', fetchReservations);
</script>