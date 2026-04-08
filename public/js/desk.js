let calendar;
let isMeetingRoom = false;

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
        unselectAuto: false,
        select: function (info) {
            // Vérifier s'il y a un avertissement sur les weekends
            let hasWeekend = false;
            const startDate = new Date(info.start);
            const endDateCheck = new Date(info.end);
            
            const interval = new Date(startDate);
            while (interval < endDateCheck) {
                const dayOfWeek = interval.getDay();
                if (dayOfWeek === 0 || dayOfWeek === 6) {
                    hasWeekend = true;
                    break;
                }
                interval.setDate(interval.getDate() + 1);
            }

            if (hasWeekend) {
                // Afficher un avertissement sans bloquer
                const warningMsg = document.getElementById('display-date');
                if (warningMsg) {
                    warningMsg.innerHTML = '<span style="color: #ffc107;"><i class="fa-solid fa-triangle-exclamation me-2"></i>Les jours du week-end seront ignorés.</span>';
                }
            } else {
                // Effacer l'avertissement s'il n'y a pas de weekend
                const warningMsg = document.getElementById('display-date');
                if (warningMsg) {
                    warningMsg.innerHTML = '';
                }
            }

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

            let endTime = isMeetingRoom ? "10:00" : "18:00";
            let formattedEndDate = `${year}-${month}-${day}T${endTime}`;
            document.getElementById('endInput').value = formattedEndDate;
        },
        eventClick: function(info) {
            showBookingDetailsModal(info.event);
        }
    });
    calendar.render();

    // === FIX POUR LE TACTILE - FullCalendar ne réagit pas aux clics simples ===
    // Capturer directement les clics et setAr les values des inputs
    
    // Reporter le setup après un délai pour s'assurer que le calendrier est prêt
    setTimeout(function() {
        calendarEl.addEventListener('click', function(e) {
            const dayCell = e.target.closest('.fc-daygrid-day');
            if (!dayCell) return;

            try {
                // Essayer de récupérer la date via data-date
                let dateStr = dayCell.getAttribute('data-date');
                
                // Fallback: parser le contenu HTML
                if (!dateStr) {
                    const dayNum = dayCell.querySelector('.fc-daygrid-day-number');
                    if (!dayNum) return;
                    
                    const text = dayNum.textContent.trim();
                    if (!text) return;
                    
                    // Obtenir mois/année du view
                    if (calendar && calendar.view) {
                        const start = calendar.view.activeStart;
                        const year = start.getFullYear();
                        const month = String(start.getMonth() + 1).padStart(2, '0');
                        const day = String(text).padStart(2, '0');
                        dateStr = `${year}-${month}-${day}`;
                    }
                }
                
                if (!dateStr) return;

                // Remplir directement les champs
                console.log('Selected date:', dateStr);
                const startInput = document.getElementById('startInput');
                const endInput = document.getElementById('endInput');
                
                if (startInput) {
                    startInput.value = dateStr + 'T09:00';
                }
                if (endInput) {
                    endInput.value = dateStr + (isMeetingRoom ? 'T10:00' : 'T18:00');
                }
                
                // Aussi déclencher la fonction select du calendrier
                const selectFunction = calendar.getOption('select');
                if (selectFunction) {
                    const startDate = new Date(dateStr + 'T00:00:00');
                    const endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + 1);
                    
                    selectFunction({
                        start: startDate,
                        end: endDate,
                        startStr: dateStr,
                        allDay: true
                    });
                }
            } catch(err) {
                console.error('Error selecting date:', err);
            }
        });
    }, 500);
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

    // Déterminer le type de ressource pour ajuster les heures par défaut
    isMeetingRoom = nom.startsWith('Salle') || nom.startsWith('Box');

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
    
    // TRÈS IMPORTANT : On attend un court instant que l'animation commence 
    // pour que FullCalendar ajuste sa largeur, sinon il reste invisible ou buggé.
    setTimeout(() => {
        layoutWrapper.classList.add('active');
        // On attend un peu plus (500ms au lieu de 200ms) pour que 
        // l'espace soit suffisant avant de dessiner le calendrier
        setTimeout(() => {
            calendar.render(); // Assurer que le calendrier est rendu après affichage
            calendar.refetchEvents();
            calendar.updateSize();
        }, 200);
    }, 10);
}

function showMsg(txt, isSuccess) {
    const m = document.getElementById('ajax-message');
    if (!m) return;
    m.innerHTML = txt;
    m.className = "alert text-center mx-auto " + (isSuccess ? "alert-success" : "alert-danger");
    m.style.display = "block";
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
            // Cas 1 : Conflits détectés
            if (data.hasConflicts) {
                showConflictModalDesk(data);
                btn.disabled = false;
                return;
            }

            // Cas 2 : Succès ou erreur standard
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

// Fonction pour afficher le modal des conflits (desk)
function showConflictModalDesk(data) {
    const conflictsList = document.getElementById('conflictsList');
    const availableCountText = document.getElementById('availableCountText');
    
    // Construire la liste des conflits
    let conflictHTML = '';
    data.conflicts.forEach(conflict => {
        conflictHTML += `
            <div class="alert alert-warning mb-2 py-2">
                <strong>${conflict.date}</strong><br>
                <small>
                    De ${conflict.heure_debut} à ${conflict.heure_fin}<br>
                    Réservé par: ${conflict.user}
                </small>
            </div>
        `;
    });
    
    conflictsList.innerHTML = conflictHTML;
    
    // Afficher le nombre de dates disponibles
    const totalDates = data.conflicts.length + data.availableDatesCount;
    availableCountText.innerText = `📅 Vous avez ${data.availableDatesCount} date(s) disponible(s) sur ${totalDates} jours sélectionnés.`;
    
    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('conflictModal'));
    modal.show();
}

// Gestionnaire pour le bouton "Réserver seulement les dates disponibles" (desk)
document.getElementById('reserveAvailableBtn').addEventListener('click', function() {
    const form = document.getElementById('bookingForm');
    const formData = new FormData(form);
    formData.append('skipConflicts', 'true');
    
    const btn = document.getElementById('subBtn');
    const resFormBox = document.getElementById('recurrence-options');
    
    btn.disabled = true;
    
    // Fermer le modal
    const modalEl = document.getElementById('conflictModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
    
    fetch('/workspace_connect/reservation/store', {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            showMsg(data.message, data.success);
            if (data.success) {
                calendar.refetchEvents();
                form.reset();
                if (resFormBox) resFormBox.style.display = 'none';
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
            if (selectedUsers.length + 1 <= capaciteMax) {
                addTag(id, name);
                // Griser l'utilisateur sélectionné dans la liste
                this.style.opacity = '0.5';
                this.style.pointerEvents = 'none';
                this.style.cursor = 'not-allowed';
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
    
    // Rendre visible à nouveau l'utilisateur dans la liste des suggestions
    const userOption = document.querySelector(`.user-option[data-id="${id}"]`);
    if (userOption) {
        userOption.style.opacity = '1';
        userOption.style.pointerEvents = 'auto';
        userOption.style.cursor = 'pointer';
    }
}

// Fermer la liste si on clique ailleurs
document.addEventListener('click', (e) => {
    if (!userSearch.contains(e.target)) userSuggestions.classList.remove('show');
});

// Fonction pour mettre à jour le label de fréquence
function updateRecurrenceLabel() {
    const label = document.getElementById('label-count');
    const recurrenceType = document.getElementById('recurrence_type');
    const value = recurrenceType.value;

    if (value === 'DAILY') {
        label.innerText = "Combien de jours ?";
    } else if (value === 'WEEKLY') {
        label.innerText = "Combien de semaines ?";
    } else if (value === 'MONTHLY') {
        label.innerText = "Combien de mois ?";
    }
}

document.getElementById('is_recurring').addEventListener('change', function() {
    const options = document.getElementById('recurrence-options');
    if (this.checked) {
        options.style.display = 'block';
        updateRecurrenceLabel(); // Mettre à jour le label dès l'affichage
    } else {
        options.style.display = 'none';
    }
});

document.getElementById('recurrence_type').addEventListener('change', function() {
    updateRecurrenceLabel(); // Utiliser la fonction commune
});

// --- GESTION DE LA MODALE DE DÉTAILS DE RÉSERVATION ---
let currentBookingId = null;
let currentBookingSeriesId = null;

function showBookingDetailsModal(event) {
    // Récupérer les données de l'événement
    currentBookingId = event.id;
    currentBookingSeriesId = event.extendedProps.id_series || null;
    const organizerName = event.extendedProps.prenom + ' ' + event.extendedProps.nom;
    const resourceName = event.extendedProps.resource_name || 'Bureau';
    const startDate = new Date(event.start).toLocaleString('fr-FR', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});
    const endDate = new Date(event.end).toLocaleString('fr-FR', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});

    // Remplir la modale
    document.getElementById('modalResourceName').innerText = resourceName;
    document.getElementById('modalOrganizerName').innerText = organizerName;
    document.getElementById('modalPeriod').innerText = `Du ${startDate} au ${endDate}`;

    // Vérifier si c'est la réservation de l'utilisateur courant et gérer l'option de suppression
    const isOwnBooking = event.extendedProps.id_user === currentUserId;
    const deleteBtn = document.getElementById('confirmDeleteBookingBtn');
    
    if (isOwnBooking) {
        deleteBtn.style.display = 'block';
        document.getElementById('optionInvites').style.display = 'block';
    } else {
        deleteBtn.style.display = 'none';
        document.getElementById('optionInvites').style.display = 'none';
    }

    // Gérer l'affichage de l'option série
    const optionSeries = document.getElementById('optionSeries');
    const separator = document.getElementById('seriesSeparator');
    document.getElementById('deleteAllSeries').checked = false;
    
    if (isOwnBooking && currentBookingSeriesId && currentBookingSeriesId !== null && currentBookingSeriesId !== 'null' && currentBookingSeriesId !== 0) {
        optionSeries.style.display = 'block';
        separator.style.display = 'block';
    } else {
        optionSeries.style.display = 'none';
        separator.style.display = 'none';
    }

    // Afficher la modale
    const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
    modal.show();
}

// Fonction de suppression
function handleDeleteBooking() {
    if (!currentBookingId) {
        console.error('No booking ID set');
        return;
    }

    const btn = this;
    const originalText = btn.innerHTML;
    
    const notifyInvites = document.getElementById('notifyInvites')?.checked || false;
    const deleteAllSeries = document.getElementById('deleteAllSeries')?.checked || false;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Suppression...';

    const formData = new FormData();
    formData.append('notifyInvites', notifyInvites);
    formData.append('deleteAllSeries', deleteAllSeries);

    fetch(`/workspace_connect/reservation/delete/${currentBookingId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const msg = document.getElementById('ajax-message');
        if (msg) {
            msg.innerHTML = data.message;
            msg.className = data.success ? "alert alert-success" : "alert alert-danger";
            msg.style.display = "block";
        }

        if (data.success) {
            // Fermer la modale et rafraîchir le calendrier
            bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal')).hide();
            calendar.refetchEvents();
        }

        btn.disabled = false;
        btn.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Erreur:', error);
        const msg = document.getElementById('ajax-message');
        if (msg) {
            msg.innerHTML = '❌ Erreur de connexion au serveur.';
            msg.className = 'alert alert-danger';
            msg.style.display = 'block';
        }
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Attacher le gestionnaire d'événement au bouton de suppression
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirmDeleteBookingBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', handleDeleteBooking);
    }
});
