document.addEventListener('DOMContentLoaded', function () {
    // 1. On vérifie si l'élément "calendar" existe sur la page
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return; // Si pas de calendrier, on arrête le script proprement

    // 2. Initialisation de FullCalendar
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr', // Calendrier en français
        selectable: true, // Permet la sélection à la souris
        selectMirror: true,
        unselectAuto: false,
        firstDay: 1, // La semaine commence le lundi
        height: 'auto', // Permet au calendrier de s'adapter au contenu plutôt que de se tasser
        handleWindowResize: true,
        expandRows: true,

        // --- CONFIGURATION DES JOURS OUVRES ---
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5], // Lundi à Vendredi
        },

        // --- SÉCURITÉ : EMPÊCHER DE SÉLECTIONNER UN WEEK-END ---
        selectAllow: function (selectInfo) {
            let checkDate = new Date(selectInfo.start);
            let endDate = new Date(selectInfo.end);

            // On parcourt chaque jour de la sélection
            while (checkDate < endDate) {
                let day = checkDate.getDay(); // 0 = Dimanche, 6 = Samedi
                if (day === 0 || day === 6) {
                    return false; // Si un seul jour est un weekend, on interdit TOUTE la sélection
                }
                checkDate.setDate(checkDate.getDate() + 1);
            }
            return true;
        },

        // --- ACTION LORS DE LA SÉLECTION ---
        select: function (info) {
            // On récupère les dates formatées pour PHP
            // FullCalendar donne les dates en ISO8601 (YYYY-MM-DDTHH:MM:SS)
            const dateDebut = info.startStr;
            const dateFin = info.endStr;

            // Ici, tu peux ouvrir ta modal de confirmation ou envoyer l'AJAX
            // Exemple de confirmation simple avant envoi :
            if (confirm("Voulez-vous réserver du " + info.start.toLocaleDateString() + " au " + actualEnd.toLocaleDateString() + " ?")) {
                envoyerReservation(dateDebut, dateFin);
            }

            calendar.unselect(); // On déselectionne visuellement après l'action
        },

        // --- CHARGEMENT DES ÉVÉNEMENTS EXISTANTS ---
        events: '/workspace_connect/admin/get_bookings', // Ton URL pour lire les réservations
    });

    calendar.render();

    // 3. FONCTION POUR ENVOYER LA RÉSERVATION AU CONTROLLER (AJAX)
    function envoyerReservation(debut, fin) {
        let formData = new FormData();
        formData.append('debut', debut);
        formData.append('fin', fin);
        // Ajoute ici l'ID de la ressource si nécessaire
        // formData.append('id_resource', document.getElementById('resource_id').value);

        fetch('/workspace_connect/admin/store', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    calendar.refetchEvents(); // Recharge le calendrier sans rafraîchir la page
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
                alert("Une erreur critique est survenue.");
            });
    }
});