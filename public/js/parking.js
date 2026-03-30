let calendar;

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        selectable: true,
        longPressDelay:150,           
        selectLongPressDelay: 150,     
        selectMinDistance: 5,              
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
            document.getElementById('display-date').innerText = texteDate;

            // 3. Heures par défaut
            let hDebut = "08:30";
            let hFin = "17:00";

            // 4. Liaison avec ton formulaire (LES NOMS DE VARIABLES IMPORTANTS)
            // On définit les fonctions globalement pour qu'elles soient accessibles partout
            window.updateFinalDebut = function(val) {
                hDebut = val;
                document.getElementById('finalDebut').value = currentStartDate + " " + hDebut + ":00";
            };

            window.updateFinalFin = function(val) {
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

            // 7. Dégel du calendrier
            calendar.unselect(); 

            if (window.innerWidth < 768 && formBox) {
                window.scrollTo({
                    top: formBox.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        },
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
        calendar.render();  
        calendar.setOption('events', '/workspace_connect/reservation/getEvents?id_resource=' + id);
        calendar.refetchEvents();
        calendar.updateSize();
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