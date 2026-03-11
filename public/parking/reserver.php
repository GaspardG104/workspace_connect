<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: /../login.php');
    exit;
}

$pdo = require_once __DIR__ . '/../../config/db.php';
$id_user = $_SESSION['user_id']; 
$nom_user = $_SESSION['user_nom'];

// On récupère juste les parkings pour l'affichage initial des boutons
$resources = $pdo->query("SELECT id, nom FROM resources WHERE type = 'parking' ORDER BY nom")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver un parking</title>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <link rel="stylesheet" href="/../styles/style_reserver.css">
</head>
<body>

    <h1>Réserver ma place</h1>
    
    <div id="ajax-message" style="margin-bottom: 20px; font-weight: bold;"></div>

    <h3>1. Cliquez sur une place pour voir ses disponibilités</h3>
    <div class="parking-map">
        <?php foreach($resources as $r): ?>
            <div class="place-btn" id="btn-<?= $r['id'] ?>" onclick="selectPlace(<?= $r['id'] ?>, '<?= htmlspecialchars($r['nom']) ?>')">
                <?= htmlspecialchars($r['nom']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div id='calendar' style="display:none;"></div>

    <div class="form-container" id="res-form-box" style="display:none;">
        <h3 id="selected-title">Nouvelle réservation</h3>
        <form id="bookingForm">
            <input type="hidden" name="resource" id="resourceSelect" required>

            <label>Date de début :</label><br>
            <input type="datetime-local" name="debut" id="debutInput" required>
            <br><br>

            <label>Date de fin :</label><br>
            <input type="datetime-local" name="fin" id="finInput" required>
            <br><br>

            <button type="submit" id="submitBtn">Confirmer la réservation</button>
        </form>
    </div>

    <script>
    let calendar;

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth', // Vue mensuelle demandée
            locale: 'fr',
            selectable: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            events: [], 
            
            select: function(info) {
                function formatDateForInput(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                }

                const startFormatted = formatDateForInput(info.start);
                const endFormatted = formatDateForInput(info.end);

                document.getElementById('debutInput').value = startFormatted;
                document.getElementById('finInput').value = endFormatted;
            }
        });
        calendar.render();
    });

    // Sélection de la place (Plan interactif)
    function selectPlace(id, nom) {
        document.querySelectorAll('.place-btn').forEach(btn => btn.classList.remove('selected'));
        document.getElementById('btn-' + id).classList.add('selected');

        document.getElementById('resourceSelect').value = id;
        document.getElementById('selected-title').innerText = "Réservation pour : " + nom;
        
        document.getElementById('calendar').style.display = 'block';
        document.getElementById('res-form-box').style.display = 'block';

        setTimeout(() => {
            calendar.updateSize();
            calendar.setOption('events', 'get_events.php?id_resource=' + id);
            calendar.refetchEvents();
        }, 50);
        
        document.getElementById('calendar').scrollIntoView({ behavior: 'smooth' });
    }

    // Gestion de l'envoi AJAX
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault(); 

        const formData = new FormData(this);
        const msgDiv = document.getElementById('ajax-message');
        const submitBtn = document.getElementById('submitBtn');

        submitBtn.disabled = true;
        submitBtn.innerText = "Traitement...";

        fetch('process_reservation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            msgDiv.innerHTML = data.message;
            msgDiv.style.color = data.success ? "green" : "red";

            if (data.success) {
                // On rafraîchit le calendrier pour voir la nouvelle zone "OCCUPÉ"
                calendar.refetchEvents();
                // On vide les champs pour permettre une autre sélection (ex: Jeudi puis Dimanche)
                document.getElementById('debutInput').value = "";
                document.getElementById('finInput').value = "";
            }
        })
        .catch(error => {
            msgDiv.innerHTML = "❌ Erreur de connexion au serveur.";
            msgDiv.style.color = "red";
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerText = "Confirmer la réservation";
        });
    });
    </script>
</body>
</html>