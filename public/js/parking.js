/**
 * LOGIQUE PARKING - Horaires et Places
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialisation des horloges (Flatpickr) seulement si les éléments existent
    if (document.getElementById('heureDebutInput')) {
        flatpickr("#heureDebutInput", { 
            enableTime: true, 
            noCalendar: true, 
            dateFormat: "H:i", 
            time_24hr: true, 
            defaultDate: "08:00" 
        });
    }

    if (document.getElementById('heureFinInput')) {
        flatpickr("#heureFinInput", { 
            enableTime: true, 
            noCalendar: true, 
            dateFormat: "H:i", 
            time_24hr: true, 
            defaultDate: "18:00" 
        });
    }

    // Gestion du formulaire en AJAX
    const form = document.getElementById('reservationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const msgDiv = document.getElementById('ajax-message');
            
            btn.disabled = true;
            const formData = new FormData(this);

            fetch('/workspace_connect/reservation/store', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                msgDiv.innerText = data.message;
                msgDiv.className = data.success ? "alert alert-success" : "alert alert-danger";
                msgDiv.style.display = "block";
                if (data.success && typeof calendar !== 'undefined') {
                    calendar.refetchEvents();
                    form.reset();
                }
            })
            .catch(() => {
                msgDiv.innerText = "Erreur de connexion.";
                msgDiv.className = "alert alert-danger";
            })
            .finally(() => btn.disabled = false);
        });
    }
});

// Liaison avec le calendrier (appelée par calendar.js)
window.onCalendarSelect = function(info, actualEnd) {
    if (document.getElementById('finalDebut')) {
        document.getElementById('finalDebut').value = info.startStr;
        document.getElementById('finalFin').value = info.endStr;
    }

    const resBox = document.getElementById('res-form-box');
    if (resBox) resBox.style.display = 'block';
    
    const label = document.getElementById('display-date');
    if (label) label.innerText = "Du " + info.start.toLocaleDateString() + " au " + actualEnd.toLocaleDateString();
};

// Sélection visuelle de la place
window.selectPlace = function(id, nom) {
    console.log("Place sélectionnée :", id); // Pour vérifier que ça tourne !

    // Mise à jour visuelle des boutons
    document.querySelectorAll('.place-btn').forEach(btn => btn.classList.remove('selected'));
    const btn = document.getElementById('btn-' + id);
    if (btn) btn.classList.add('selected');

    // Mise à jour du formulaire (vérifie que ces IDs existent dans parking.php)
    const resInput = document.getElementById('resourceSelect'); 
    if (resInput) resInput.value = id;

    const title = document.getElementById('selected-title');
    if (title) title.innerText = "Place : " + nom;

    // Rafraîchir le calendrier si la fonction existe
    if (typeof window.refreshCalendarResource === 'function') {
        window.refreshCalendarResource(id);
    }
};