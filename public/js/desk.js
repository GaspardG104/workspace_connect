/**
 * LOGIQUE DESK - Invités, Capacité et Récurrence
 */
let selectedUsers = [];

// Liaison avec le calendrier
window.onCalendarSelect = function(info, actualEnd) {
    document.getElementById('startInput').value = info.startStr + "T09:00";
    
    let day = actualEnd.getDate().toString().padStart(2, '0');
    let month = (actualEnd.getMonth() + 1).toString().padStart(2, '0');
    let year = actualEnd.getFullYear();
    document.getElementById('endInput').value = `${year}-${month}-${day}T18:00`;

    const display = document.getElementById('display-date');
    if (display) display.innerText = "Sélection : " + info.start.toLocaleDateString();
};

// Sélection visuelle d'un bureau ou salle
function selectResource(id, nom, element) {
    document.querySelectorAll('.desk-unit, .btn-meeting').forEach(btn => btn.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('calendar-column').style.display = 'block';
    document.getElementById('layout-wrapper').classList.add('active');

    const calendarColumn = document.getElementById('calendar-column');
    calendarColumn.style.display = 'block';

        requestAnimationFrame(() => {
        document.getElementById('layout-wrapper').classList.add('active');
    });

    const resourceInput = document.querySelector('input[name="id_resource"]');
        if (resourceInput) {
            resourceInput.value = id; // On utilise la variable 'id' passée en paramètre
            
        } else {
            console.error("L'input hidden 'id_resource' est introuvable !");
        }

    // Gestion de la capacité pour les invités
    const inviteSection = document.getElementById('invite-section');
    const cap = resCapacities[id] || 1;
    
    if (cap > 1) {
        inviteSection.style.display = 'block';
        document.getElementById('max-invites').innerText = cap - 1;
    } else {
        inviteSection.style.display = 'none';
        clearInvites();
    }

    window.refreshCalendarResource(id);
}

// --- SYSTÈME DE TAGS D'INVITÉS ---
function addTag(id, name) {
    const max = parseInt(document.getElementById('max-invites').innerText);
    if (selectedUsers.length >= max) {
        alert("Capacité maximale atteinte !");
        return;
    }
    if (selectedUsers.includes(id)) return;

    selectedUsers.push(id);
    const container = document.getElementById('tags-container');
    const tag = document.createElement('div');
    tag.className = 'user-tag';
    tag.innerHTML = `${name} <span class="remove-tag" onclick="removeTag('${id}', this)">&times;</span>`;
    container.appendChild(tag);

    const hidden = document.getElementById('hidden-invites');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'invites[]';
    input.value = id;
    input.id = 'input-invite-' + id;
    hidden.appendChild(input);
}

function removeTag(id, el) {
    selectedUsers = selectedUsers.filter(u => u !== id);
    el.parentElement.remove();
    document.getElementById('input-invite-' + id).remove();
}

function clearInvites() {
    selectedUsers = [];
    document.getElementById('tags-container').innerHTML = '';
    document.getElementById('hidden-invites').innerHTML = '';
}

// --- GESTION RÉCURRENCE ---
document.getElementById('is_recurring')?.addEventListener('change', function() {
    document.getElementById('recurrence-options').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('recurrence_type')?.addEventListener('change', function() {
    const label = document.getElementById('label-count');
    const values = { 'DAILY': 'jours', 'WEEKLY': 'semaines', 'MONTHLY': 'mois' };
    label.innerText = `Combien de ${values[this.value] || 'fois'} ?`;
});

document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
    e.preventDefault(); // Bloque le rechargement

    const formData = new FormData(this);

    fetch('/workspace_connect/reservation/store', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('ajax-message');
        msg.style.display = 'block';
        msg.className = 'alert shadow-lg text-center mx-auto ' + (data.success ? 'alert-success' : 'alert-danger');
        msg.innerText = data.message;

        // Gestion des conflits
        if (data.hasConflicts) {
            afficherConflits(data);
            return;
        }

        if (data.success) {
            window.refreshCalendarResource(
                document.querySelector('input[name="id_resource"]').value
            );
            // Cache le message après 4s
            setTimeout(() => msg.style.display = 'none', 4000);
        }
    })
    .catch(err => console.error('Erreur réseau:', err));
});

function afficherConflits(data) {
    const liste = document.getElementById('conflictsList');
    liste.innerHTML = data.conflicts.map(c =>
        `<div class="alert alert-warning py-1 mb-1">
            📅 ${c.date} de ${c.heure_debut} à ${c.heure_fin} — <strong>${c.user}</strong>
        </div>`
    ).join('');

    document.getElementById('availableCountText').innerText =
        `${data.availableDatesCount} date(s) disponible(s) sur votre sélection.`;

    new bootstrap.Modal(document.getElementById('conflictModal')).show();
}

// Bouton "Réserver seulement les dates disponibles"
document.getElementById('reserveAvailableBtn')?.addEventListener('click', function() {
    const formData = new FormData(document.getElementById('bookingForm'));
    formData.append('skipConflicts', 'true');

    bootstrap.Modal.getInstance(document.getElementById('conflictModal')).hide();

    fetch('/workspace_connect/reservation/store', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('ajax-message');
        msg.style.display = 'block';
        msg.className = 'alert shadow-lg text-center mx-auto ' + (data.success ? 'alert-success' : 'alert-danger');
        msg.innerText = data.message;
        if (data.success) {
            window.refreshCalendarResource(
                document.querySelector('input[name="id_resource"]').value
            );
            setTimeout(() => msg.style.display = 'none', 4000);
        }
    });
});