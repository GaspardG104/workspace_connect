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
                <thead class="table-dark">
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
                            <span class="fw-bold d-block text-dark">${res.user_prenom} ${res.user_nom}</span>
                            <small class="text-muted">${res.role_label}</small>
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
        if (isAdmin) {
            row += `
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-danger border-0" title="Supprimer" onclick="deleteBooking(${res.id}, this)">
                        <i class="fa-solid fa-trash-alt"></i>
                    </button>
                </td>
            `;
        }

        row += `</tr>`;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
}

// Nouvelle fonction de suppression
function deleteBooking(id, btn) {
    if (!confirm("Voulez-vous vraiment supprimer cette réservation ?")) return;

    // Animation du bouton
    const icon = btn.querySelector('i');
    icon.className = "fa-solid fa-spinner fa-spin";
    btn.disabled = true;

    fetch(`/workspace_connect/reservations/delete/${id}`, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // On rafraîchit la liste
            fetchReservations();
        } else {
            alert("Erreur : " + data.message);
            icon.className = "fa-solid fa-trash-alt";
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error("Erreur suppression:", err);
        alert("Une erreur technique est survenue.");
        icon.className = "fa-solid fa-trash-alt";
        btn.disabled = false;
    });
}

searchInput.addEventListener('input', fetchReservations);
dateInput.addEventListener('change', fetchReservations);
sortSelect.addEventListener('change', fetchReservations);

function resetFilters() {
    searchInput.value = '';
    dateInput.value = '';
    sortSelect.value = 'date_debut';
    fetchReservations();
}

// Chargement initial
document.addEventListener('DOMContentLoaded', fetchReservations);
</script>