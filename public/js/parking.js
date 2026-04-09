let calendar;

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded - initializing calendar');
    var calendarEl = document.getElementById('calendar');
    console.log('Calendar element found:', calendarEl);
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        selectable: true,
        unselectAuto: false,
        dragRevertDuration: 0,
        selectMirror: true,
        selectMinDistance: 0, // Permettre la sélection immédiate sur mobile
        dayMaxEvents: 3, // Limiter les événements pour éviter l'encombrement


        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        // Fonction de formatage pour l'input datetime-local
select: function (info) {
    // 1. Vérifier s'il y a un weekend
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

    // 2. Préparation de l'affichage et des variables
    const displayDateElement = document.getElementById('display-date');
    let hDebut = "08:30";
    let hFin = "17:00";

    // 3. Calcul de la date de fin (Logique Desk.js qui marche)
    let endDate = new Date(info.end);
    if (info.allDay) {
        endDate.setDate(endDate.getDate() - 1);
    }

    // Formatage manuel (plus sûr que toISOString)
    let day = endDate.getDate().toString().padStart(2, '0');
    let month = (endDate.getMonth() + 1).toString().padStart(2, '0');
    let year = endDate.getFullYear();
    let formattedEndDate = `${year}-${month}-${day}`;
    let formattedStartDate = info.startStr.split('T')[0];

    // 4. Construction du texte pour l'affichage (L'humain voit le vrai dernier jour)
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    let texteDate = "Le " + startDate.toLocaleDateString('fr-FR', options);

    if (formattedStartDate !== formattedEndDate) {
        texteDate = "Du " + startDate.toLocaleDateString('fr-FR', options) + " au " + endDate.toLocaleDateString('fr-FR', options);
    }

    if (displayDateElement) {
        if (hasWeekend) {
            displayDateElement.innerHTML = texteDate + '<br><span style="color:#b15f00;"><i class="fa-solid fa-triangle-exclamation me-1"></i>Les jours du week-end seront ignorés.</span>';
        } else {
            displayDateElement.innerText = texteDate;
        }
    }

    // 5. Mise à jour des inputs pour le formulaire (SQL)
    // IMPORTANT : Pour le SQL, on garde la logique de fin inclusive ou exclusive selon ton besoin.
    // Si tu veux que le SQL reçoive le jour affiché, utilise formattedEndDate.
    
    window.updateFinalDebut = function (val) {
        document.getElementById('finalDebut').value = formattedStartDate + " " + val + ":00";
    };

    window.updateFinalFin = function (val) {
        // Ici on utilise formattedEndDate pour que SQL reçoive exactement le jour affiché (ex: 24)
        document.getElementById('finalFin').value = formattedEndDate + " " + val + ":00";
    };

    // Initialisation
    document.getElementById('heureDebutInput').value = hDebut;
    document.getElementById('heureFinInput').value = hFin;
    window.updateFinalDebut(hDebut);
    window.updateFinalFin(hFin);

    // 6. Affichage du formulaire
    const formBox = document.getElementById('res-form-box');
    if (formBox) {
        formBox.style.display = 'block';
    }
},
        eventClick: function(info) {
            showBookingDetailsModal(info.event);
        }
    });
    console.log('Calendar initialized (not rendered yet)');
    // calendar.render(); // Commenté car on le fait dans selectPlace quand la div est visible

    // === FIX POUR LE TACTILE - Capturer les clics et remplir les dates ===
    
    /* setTimeout(function() {
        calendarEl.addEventListener('click', function(e) {
            const dayCell = e.target.closest('.fc-daygrid-day');
            if (!dayCell) return;

            try {
                let dateStr = dayCell.getAttribute('data-date');
                
                if (!dateStr) {
                    const dayNum = dayCell.querySelector('.fc-daygrid-day-number');
                    if (!dayNum) return;
                    
                    const text = dayNum.textContent.trim();
                    if (!text) return;
                    
                    if (calendar && calendar.view) {
                        const start = calendar.view.activeStart;
                        const year = start.getFullYear();
                        const month = String(start.getMonth() + 1).padStart(2, '0');
                        const day = String(text).padStart(2, '0');
                        dateStr = `${year}-${month}-${day}`;
                    }
                }
                
                if (!dateStr) return;

                console.log('Parking date selected:', dateStr);

                // Déclencher la fonction select du calendrier
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
                console.error('Error selecting parking date:', err);
            }
        });
    }, 500); */
});

function selectPlace(id, nom) {
    console.log('selectPlace called with id:', id, 'nom:', nom);
    document.querySelectorAll('.place-btn').forEach(btn => btn.classList.remove('selected'));
    document.getElementById('btn-' + id).classList.add('selected');

    document.getElementById('resourceSelect').value = id;
    document.getElementById('selected-title').innerText = "Place : " + nom;

    document.getElementById('calendar').style.display = 'block';
    document.getElementById('res-form-box').style.display = 'block';

    console.log('About to render calendar');
    setTimeout(() => {
        calendar.render();
        calendar.setOption('events', '/workspace_connect/reservation/getEvents?id_resource=' + id);
        calendar.refetchEvents();
        calendar.updateSize();
        console.log('Calendar rendered and events loaded');
    }, 50);
}

// Gestion de l'envoi AJAX
document.getElementById('bookingForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const msgDiv = document.getElementById('ajax-message');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerText = "Traitement...";

    fetch('/workspace_connect/reservation/store', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            // Cas 1 : Conflits détectés
            if (data.hasConflicts) {
                // Afficher le modal avec les conflits
                showConflictModal(data);
                submitBtn.disabled = false;
                submitBtn.innerText = "Confirmer la réservation";
                return;
            }

            // Cas 2 : Succès ou erreur standard
            msgDiv.innerHTML = data.message;
            msgDiv.className = "alert text-center mx-auto " + (data.success ? "alert-success" : "alert-danger");
            msgDiv.style.display = "block";

            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (data.success) {
                calendar.refetchEvents();

                const inputD = document.getElementById('heureDebutInput');
                const inputF = document.getElementById('heureFinInput');

                if (inputD) inputD.value = "";
                if (inputF) inputF.value = "";

                if (document.getElementById('finalDebut')) document.getElementById('finalDebut').value = "";
                if (document.getElementById('finalFin')) document.getElementById('finalFin').value = "";
            }

            // Faire disparaître le message après 3 secondes
            setTimeout(() => {
                msgDiv.style.transition = "opacity 0.5s ease";
                msgDiv.style.opacity = "0";

                setTimeout(() => {
                    msgDiv.style.display = "none";
                    msgDiv.style.opacity = "1";
                }, 500);
            }, 3000);
        })
        .catch(error => {
            msgDiv.innerHTML = "❌ Erreur de connexion au serveur.";
            msgDiv.className = "alert alert-danger";
            window.scrollTo({ top: 0, behavior: 'smooth' });
            console.error("Erreur brute du serveur :", error);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerText = "Confirmer la réservation";
        });

});

// Fonction pour afficher le modal des conflits
function showConflictModal(data) {
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

// Gestionnaire pour le bouton "Réserver seulement les dates disponibles"
document.getElementById('reserveAvailableBtn').addEventListener('click', function() {
    const form = document.getElementById('bookingForm');
    const formData = new FormData(form);
    formData.append('skipConflicts', 'true');
    
    const submitBtn = document.getElementById('submitBtn');
    const msgDiv = document.getElementById('ajax-message');
    
    submitBtn.disabled = true;
    submitBtn.innerText = "Traitement...";
    
    // Fermer le modal
    const modalEl = document.getElementById('conflictModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
    
    fetch('/workspace_connect/reservation/store', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            msgDiv.innerHTML = data.message;
            msgDiv.className = "alert text-center mx-auto " + (data.success ? "alert-success" : "alert-danger");
            msgDiv.style.display = "block";
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            if (data.success) {
                calendar.refetchEvents();
                
                const inputD = document.getElementById('heureDebutInput');
                const inputF = document.getElementById('heureFinInput');
                
                if (inputD) inputD.value = "";
                if (inputF) inputF.value = "";
                
                if (document.getElementById('finalDebut')) document.getElementById('finalDebut').value = "";
                if (document.getElementById('finalFin')) document.getElementById('finalFin').value = "";
            }
            
            setTimeout(() => {
                msgDiv.style.transition = "opacity 0.5s ease";
                msgDiv.style.opacity = "0";
                
                setTimeout(() => {
                    msgDiv.style.display = "none";
                    msgDiv.style.opacity = "1";
                }, 500);
            }, 3000);
        })
        .catch(error => {
            msgDiv.innerHTML = "❌ Erreur lors de la réservation.";
            msgDiv.className = "alert alert-danger";
            window.scrollTo({ top: 0, behavior: 'smooth' });
            console.error(error);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerText = "Confirmer la réservation";
        });
});

// --- GESTION DE LA MODALE DE DÉTAILS DE RÉSERVATION ---
let currentBookingId = null;
let currentBookingSeriesId = null;

function showBookingDetailsModal(event) {
    // Récupérer les données de l'événement
    currentBookingId = event.id;
    currentBookingSeriesId = event.extendedProps.id_series || null;
    const organizerName = event.extendedProps.prenom + ' ' + event.extendedProps.nom;
    const resourceName = event.extendedProps.resource_name || 'Place de parking';
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
            msg.className = "alert text-center mx-auto " + (data.success ? "alert-success" : "alert-danger");
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
            msg.className = 'alert text-center mx-auto alert-danger';
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

// Gestion du formulaire de récurrence
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