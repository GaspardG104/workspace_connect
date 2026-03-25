const searchInput = document.getElementById('ajax-search');
const dateInput = document.getElementById('ajax-date');
const sortSelect = document.getElementById('ajax-sort');
const tableBody = document.getElementById('reservation-table-body');

// On récupère le rôle PHP pour le JS
//const isAdmin = <?= $_SESSION['user_role'] == 1 ? 'true' : 'false' ?>; // SE TROUVE DANS LE FICHIER list.php

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
                        onclick="prepareDelete(${res.id}, '${fullName.replace(/'/g, "\\'")}', ${res.nb_invites || 0}, ${res.id_user}, ${res.id_series})">
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
let currentBookingSeriesId = null; //pour les réservations récurentes

// CETTE FONCTION DOIT S'APPELER prepareDelete (comme dans le onclick du bouton)
function prepareDelete(id, organizerName, nbInvites, organizerId, idSeries) {
    bookingToDelete = id;
    currentBookingSeriesId = idSeries;

// On récupère l'ID de session via PHP
const currentUserId = document.getElementById('app-container').dataset.userId;  // SE TROUVE DANS LE FICHIER list.php
    
    // Affichage du nom dans la modale
    document.getElementById('deleteMessage').innerHTML = `Voulez-vous vraiment supprimer la réservation de <strong>${organizerName}</strong> ?`;
    
    // Gestion de l'affichage des options de mail
    const optionInvites = document.getElementById('optionInvites');
    const optionOrganizer = document.getElementById('optionOrganizer');

    // Affichage de l'option Série
    const optionSeries = document.getElementById('optionSeries');
    const separator = document.getElementById('seriesSeparator');
    const checkboxSeries = document.getElementById('deleteAllSeries');
    checkboxSeries.checked = false;
    
    if(optionInvites) optionInvites.style.display = (nbInvites > 0) ? 'block' : 'none';
    if(optionOrganizer) optionOrganizer.style.display = (organizerId != currentUserId) ? 'block' : 'none';

    // On vérifie si idSeries existe, n'est pas nul et n'est pas 0
    if (idSeries && idSeries !== null && idSeries !== 'null' && idSeries !== 0) {
        optionSeries.style.display = 'block';
        separator.style.display = 'block';
    } else {
        optionSeries.style.display = 'none';
        separator.style.display = 'none';
    }

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
    const deleteAllSeries = document.getElementById('deleteAllSeries')?.checked || false;
    

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Suppression...';

    const formData = new FormData();
    formData.append('notifyInvites', notifyInvites);
    formData.append('notifyOrganizer', notifyOrganizer);
    formData.append('deleteAllSeries', deleteAllSeries);

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