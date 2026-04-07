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

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        // Fonction de formatage pour l'input datetime-local
        select: function (info) {
            // Vérifier s'il y a un avertissement sur les weekends
            let hasWeekend = false;
            const startDate = new Date(info.start);
            const endDate = new Date(info.end);
            
            const interval = new Date(startDate);
            while (interval < endDate) {
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
            }
            
            // 1. Récupération des dates (Début et Fin réelle)
            // On utilise 'let' pour pouvoir les mettre à jour globalement
            let currentStartDate = info.startStr.split('T')[0];

            // FullCalendar donne J+1 pour la fin, on garde la date brute pour le calcul SQL
            // mais on calcule une version lisible pour l'affichage
            let endDateObj = new Date(info.end);
            endDateObj.setDate(endDateObj.getDate());
            let currentEndDate = endDateObj.toISOString().split('T')[0];

            // 2. Affichage lisible
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            let texteDate = "Le " + info.start.toLocaleDateString('fr-FR', options);

            if (currentStartDate !== currentEndDate) {
                texteDate = "Du " + info.start.toLocaleDateString('fr-FR', options) + " au " + endDateObj.toLocaleDateString('fr-FR', options);
            }

            const displayDateElement = document.getElementById('display-date');
            if (displayDateElement) {
                if (hasWeekend) {
                    displayDateElement.innerHTML = `${texteDate} <br><span style="color:#b15f00;"><i class="fa-solid fa-triangle-exclamation me-1"></i>Les jours du week-end seront ignorés.</span>`;
                } else {
                    displayDateElement.innerText = texteDate;
                }
            }

            // 3. Heures par défaut
            let hDebut = "08:30";
            let hFin = "17:00";

            // 4. Liaison avec ton formulaire (LES NOMS DE VARIABLES IMPORTANTS)
            // On définit les fonctions globalement pour qu'elles soient accessibles partout
            window.updateFinalDebut = function (val) {
                hDebut = val;
                document.getElementById('finalDebut').value = currentStartDate + " " + hDebut + ":00";
            };

            window.updateFinalFin = function (val) {
                hFin = val;
                // CRUCIAL : On utilise currentEndDate ici pour ne pas écraser la période !
                document.getElementById('finalFin').value = currentEndDate + " " + hFin + ":00";
            };

            // Initialisation des champs
            document.getElementById('heureDebutInput').value = hDebut;
            document.getElementById('heureFinInput').value = hFin;
            window.updateFinalDebut(hDebut);
            window.updateFinalFin(hFin);

            // 5. Configuration des horloges (Flatpickr)
            flatpickr("#heureDebutInput", {
                enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true,
                defaultDate: hDebut,
                onChange: function (selectedDates, timeStr) {
                    window.updateFinalDebut(timeStr);
                }
            });

            flatpickr("#heureFinInput", {
                enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true,
                defaultDate: hFin,
                onChange: function (selectedDates, timeStr) {
                    window.updateFinalFin(timeStr);
                }
            });

            // 6. Affichage du formulaire (ID : res-form-box)
            const formBox = document.getElementById('res-form-box');
            if (formBox) {
                formBox.style.display = 'block';
            }

            // 7. Dégel du calendrier (seulement si c'est une sélection invalide ou après traitement)
            // calendar.unselect(); // Commenté pour garder la sélection visible

            if (window.innerWidth < 768 && formBox) {
                window.scrollTo({
                    top: formBox.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        },
        eventClick: function(info) {
            showBookingDetailsModal(info.event);
        }
    });
    console.log('Calendar initialized (not rendered yet)');
    // calendar.render(); // Commenté car on le fait dans selectPlace quand la div est visible
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
            // 1. Affichage du message avec les classes Bootstrap
            msgDiv.innerHTML = data.message;
            msgDiv.className = data.success ? "alert alert-success" : "alert alert-danger";
            msgDiv.style.display = "block"; // On s'assure qu'il est visible

            // 2. Remonter en haut de la page pour voir le message
            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (data.success) {
                calendar.refetchEvents();

                // Remplace par tes nouveaux IDs pour vider les champs après succès
                const inputD = document.getElementById('heureDebutInput');
                const inputF = document.getElementById('heureFinInput');

                if (inputD) inputD.value = "";
                if (inputF) inputF.value = "";

                // Si tu utilises les champs cachés pour SQL
                if (document.getElementById('finalDebut')) document.getElementById('finalDebut').value = "";
                if (document.getElementById('finalFin')) document.getElementById('finalFin').value = "";
            }

            // 3. Faire disparaître le message après 3 secondes
            setTimeout(() => {
                // Effet de disparition douce (fade out)
                msgDiv.style.transition = "opacity 0.5s ease";
                msgDiv.style.opacity = "0";

                // On cache complètement après l'animation
                setTimeout(() => {
                    msgDiv.style.display = "none";
                    msgDiv.style.opacity = "1"; // On remet l'opacité à 1 pour la prochaine fois
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

// Gestion du formulaire de récurrence
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