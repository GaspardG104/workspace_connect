let calendar;

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        selectable: true,
        select: function (info) {
            document.getElementById('startInput').value = info.startStr + "T09:00";
            let endDate = new Date(info.end);

            // Si c'est une sélection sur la vue Mois (allDay), FullCalendar donne le lendemain à 00:00.
            // On recule d'un jour pour revenir au jour réel de fin de réservation.
            if (info.allDay) {
                endDate.setDate(endDate.getDate() - 1);
            }

            // On formate la date au format YYYY-MM-DD
            let day = endDate.getDate().toString().padStart(2, '0');
            let month = (endDate.getMonth() + 1).toString().padStart(2, '0');
            let year = endDate.getFullYear();

            let formattedEndDate = `${year}-${month}-${day}T18:00`;
            document.getElementById('endInput').value = formattedEndDate;
        }
    });
    calendar.render();
});


// La fonction est maintenant bien définie au niveau global
function selectResource(id, nom, el) {
    if (!id) {
        showMsg("Ressource non trouvée en BDD", false);
        return;
    }

    // 1. Gérer la sélection visuelle (couleur du bureau)
    document.querySelectorAll('.selected-resource').forEach(b => b.classList.remove('selected-resource'));
    el.classList.add('selected-resource');

    // 2. Afficher le bloc de réservation et déclencher l'animation de décalage
    const calendarCol = document.getElementById('calendar-column');
    const bookingUi = document.getElementById('booking-ui');
    const layoutWrapper = document.getElementById('layout-wrapper');
    const resFormBox = document.getElementById('form-box-rec');

    // Gestion de la capacité et des invités
    const inviteSection = document.getElementById('invite-section');
    const capDisplay = document.getElementById('cap-val');
    const capaciteMax = resCapacities[id] || 1;

    if (resFormBox) {
        resFormBox.style.display = 'block'; // Le formulaire revient dès qu'on clique
    }

    if (capaciteMax > 1) {
        inviteSection.style.display = 'block';
        capDisplay.innerText = capaciteMax;
    } else {
        inviteSection.style.display = 'none';
    }

    bookingUi.classList.remove('refresh-anim'); //on enlève l'animation pour la calendrier si elle y était déjà

    // affichage des conteneurs
    calendarCol.style.display = 'block'; // On affiche la colonne parent
    bookingUi.style.display = 'block';     // On affiche l'UI interne


    void bookingUi.offsetWidth;     // On force un petit "recalcul" pour que le navigateur voie le changement
    bookingUi.classList.add('refresh-anim');

    // 3. Mettre à jour les informations du formulaire
    document.getElementById('display-name').innerText = "Poste : " + nom;
    document.getElementById('res_id').value = id;

    // 4. Charger les événements et forcer le calendrier à recalculer sa taille
    calendar.setOption('events', '/workspace_connect/reservation/getEvents?id_resource=' + id);
    calendar.refetchEvents();

    // TRÈS IMPORTANT : On attend un court instant que l'animation commence 
    // pour que FullCalendar ajuste sa largeur, sinon il reste invisible ou buggé.
    setTimeout(() => {
        layoutWrapper.classList.add('active');
        // On attend un peu plus (500ms au lieu de 200ms) pour que 
        // l'espace soit suffisant avant de dessiner le calendrier
        setTimeout(() => {
            calendar.updateSize();
        }, 500);
    }, 10);
}

function showMsg(txt, isSuccess) {
    const m = document.getElementById('ajax-message');
    if (!m) return;
    m.innerHTML = txt;
    m.className = "alert shadow-lg " + (isSuccess ? "alert-success" : "alert-danger");
    m.style.display = "block";
    setTimeout(() => { m.style.display = "none"; }, 4000);
}

document.getElementById('bookingForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('subBtn');
    const resFormBox = document.getElementById('recurrence-options');
    btn.disabled = true;

    fetch('/workspace_connect/reservation/store', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            showMsg(data.message, data.success);
            if (data.success) {
                calendar.refetchEvents();
                this.reset();
            }
            if (resFormBox) {
                resFormBox.style.display = 'none';
            }
        })
        .finally(() => { btn.disabled = false; });
});

const userSearch = document.getElementById('userSearch');
const userSuggestions = document.getElementById('userSuggestions');
const tagsContainer = document.getElementById('tags-container');
const hiddenInputs = document.getElementById('hidden-inputs');
let selectedUsers = [];

// Filtrer la liste pendant la frappe
userSearch.addEventListener('input', function () {
    const term = this.value.toLowerCase();
    const items = document.querySelectorAll('.user-option');
    let hasResults = false;

    items.forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        if (name.includes(term) && term.length > 0) {
            item.parentElement.style.display = 'block';
            hasResults = true;
        } else {
            item.parentElement.style.display = 'none';
        }
    });

    userSuggestions.classList.toggle('show', hasResults);
});

// Ajouter un invité lors du clic sur un nom
document.querySelectorAll('.user-option').forEach(option => {
    option.addEventListener('click', function (e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const capaciteMax = parseInt(document.getElementById('cap-val').innerText) || 1;

        // Vérification : pas déjà ajouté et respect de la capacité (soi-même + invités)
        if (!selectedUsers.includes(id)) {
            if (selectedUsers.length + 1 < capaciteMax) {
                addTag(id, name);
            } else {
                alert("Capacité maximale de la salle atteinte !");
            }
        }

        userSearch.value = '';
        userSuggestions.classList.remove('show');
    });
});

function addTag(id, name) {
    selectedUsers.push(id);

    // Créer le tag visuel
    const tag = document.createElement('div');
    tag.className = 'invite-tag';
    tag.innerHTML = `${name} <span class="remove-invite" onclick="removeTag('${id}', this)">&times;</span>`;
    tagsContainer.appendChild(tag);

    // Créer l'input caché pour le formulaire
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'invites[]';
    input.value = id;
    input.id = 'input-invite-' + id;
    hiddenInputs.appendChild(input);
}

function removeTag(id, element) {
    selectedUsers = selectedUsers.filter(uid => uid !== id);
    element.parentElement.remove();
    document.getElementById('input-invite-' + id).remove();
}

// Fermer la liste si on clique ailleurs
document.addEventListener('click', (e) => {
    if (!userSearch.contains(e.target)) userSuggestions.classList.remove('show');
});

document.getElementById('is_recurring').addEventListener('change', function() {
    const options = document.getElementById('recurrence-options');
    if (this.checked) {
        options.style.display = 'block';
    } else {
        options.style.display = 'none';
    }
});

document.getElementById('recurrence_type').addEventListener('change', function() {
    const label = document.getElementById('label-count');
    const value = this.value;

    if (value === 'DAILY') {
        label.innerText = "Combien de jours ?";
    } else if (value === 'WEEKLY') {
        label.innerText = "Combien de semaines ?";
    } else if (value === 'MONTHLY') {
        label.innerText = "Combien de mois ?";
    }
});
