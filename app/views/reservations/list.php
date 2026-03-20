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

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle bg-white mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Compte</th>
                    <th>Ressource</th>
                    <th>Dates</th>
                    <th>Statut</th>
                    <?php if ($_SESSION['user_role'] == 1): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody id="table-results">
                </tbody>
        </table>
    </div>
</div>

<script>
const tableBody = document.getElementById('table-results');
const searchInput = document.getElementById('ajax-search');
const dateInput = document.getElementById('ajax-date');
const sortSelect = document.getElementById('ajax-sort');

// Fonction principale AJAX
function fetchReservations() {
    const params = new URLSearchParams({
        search: searchInput.value,
        date: dateInput.value,
        sort: sortSelect.value
    });

    fetch(`/workspace_connect/reservations/search?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            renderTable(data);
        });
}

function renderTable(data) {
    tableBody.innerHTML = '';
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Aucun résultat trouvé</td></tr>';
        return;
    }

    data.forEach(res => {
        const badgeColor = res.role_label === 'Organisateur' ? 'bg-primary' : 'bg-info';
        const statusColor = res.statut === 'confirme' ? 'bg-success' : 'bg-secondary';
        
        const row = `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <strong>${res.user_prenom} ${res.user_nom}</strong>
                        <span class="ms-2 badge ${badgeColor} text-uppercase" style="font-size: 0.65rem;">${res.role_label}</span>
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
                <?php if ($_SESSION['user_role'] == 1): ?>
                <td>
                    <button class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-trash-alt"></i></button>
                </td>
                <?php endif; ?>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
}

// Événements pour la recherche fluide
searchInput.addEventListener('input', fetchReservations); // "input" détecte chaque touche frappée
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