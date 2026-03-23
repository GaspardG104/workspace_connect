let calendar;

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        // Fonction de formatage pour l'input datetime-local
        select: function (info) {
    // 1. Récupération de la date pure (YYYY-MM-DD)
    const dateStr = info.startStr.split('T')[0]; 
    
    // 2. Affichage lisible pour l'utilisateur
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('display-date').innerText = "Le " + info.start.toLocaleDateString('fr-FR', options);

    // 3. Variables pour stocker les heures
    let hDebut = "09:00";
    let hFin = "17:00";

    // Fonction pour mettre à jour les inputs cachés (ceux que SQL reçoit)
    const updateHiddenInputs = () => {
        document.getElementById('finalDebut').value = `${dateStr} ${hDebut}`;
        document.getElementById('finalFin').value = `${dateStr} ${hFin}`;
    };

    // Initialisation affichage et valeurs
    document.getElementById('heureDebutInput').value = hDebut;
    document.getElementById('heureFinInput').value = hFin;
    updateHiddenInputs();

    // 4. Horloges Flatpickr
    flatpickr("#heureDebutInput", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        defaultDate: hDebut,
        onChange: function(selectedDates, timeStr) {
            hDebut = timeStr;
            updateHiddenInputs();
        }
    });

    flatpickr("#heureFinInput", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        defaultDate: hFin,
        onChange: function(selectedDates, timeStr) {
            hFin = timeStr;
            updateHiddenInputs();
        }
    });
}
    });
    calendar.render();
});

function selectPlace(id, nom) {
    document.querySelectorAll('.place-btn').forEach(btn => btn.classList.remove('selected'));
    document.getElementById('btn-' + id).classList.add('selected');

    document.getElementById('resourceSelect').value = id;
    document.getElementById('selected-title').innerText = "Place : " + nom;

    document.getElementById('calendar').style.display = 'block';
    document.getElementById('res-form-box').style.display = 'block';

    setTimeout(() => {
        calendar.updateSize();
        calendar.setOption('events', '/workspace_connect/reservation/getEvents?id_resource=' + id);
        calendar.refetchEvents();
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
    
    if(inputD) inputD.value = "";
    if(inputF) inputF.value = "";
    
    // Si tu utilises les champs cachés pour SQL
    if(document.getElementById('finalDebut')) document.getElementById('finalDebut').value = "";
    if(document.getElementById('finalFin')) document.getElementById('finalFin').value = "";
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