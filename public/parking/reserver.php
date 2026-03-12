<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Vérifie bien le chemin vers ton login
    exit;
}

$pdo = require_once __DIR__ . '/../../config/db.php';
$id_user = $_SESSION['user_id']; 
$nom_user = $_SESSION['user_nom'];

$resources = $pdo->query("SELECT id, nom FROM resources WHERE type = 'parking' ORDER BY nom")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un parking - Workspace Connect</title>
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../styles/style_parking.css">
    <link rel="stylesheet" href="../styles/main_theme.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/..//includes/navbar.php'; ?>

    <div class="container">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Réserver ma place <i class="fa-solid fa-square-parking text-primary"></i></h1>
            <div id="ajax-message" class="fw-bold mt-2"></div>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fa-solid fa-map-location-dot me-2"></i> Sélectionnez une place</h5>
                <div class="parking-map">
                    <?php foreach($resources as $r): ?>
                        <div class="place-btn" id="btn-<?= $r['id'] ?>" onclick="selectPlace(<?= $r['id'] ?>, '<?= htmlspecialchars($r['nom']) ?>')">
                            <span class="place-name"><?= htmlspecialchars($r['nom']) ?></span>
                            <i class="fa-solid fa-car fa-xl mt-2"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 p-3">
                    <div id='calendar' style="display:none;"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 p-4" id="res-form-box" style="display:none; position: sticky; top: 20px;">
                    <h4 id="selected-title" class="fw-bold text-primary mb-4">Détails</h4>
                    <form id="bookingForm">
                        <input type="hidden" name="resource" id="resourceSelect" required>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Date de début</label>
                            <input type="datetime-local" name="debut" id="debutInput" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Date de fin</label>
                            <input type="datetime-local" name="fin" id="finInput" class="form-control" required>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-success w-100 fw-bold py-2 mt-2">
                            Confirmer la réservation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    let calendar;

    document.addEventListener('DOMContentLoaded', function() {
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
            select: function(info) {
                function formatDateForInput(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                }
                document.getElementById('debutInput').value = formatDateForInput(info.start);
                document.getElementById('finInput').value = formatDateForInput(info.end);
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
            calendar.setOption('events', 'get_events.php?id_resource=' + id);
            calendar.refetchEvents();
        }, 50);
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
            // 1. Affichage du message avec les classes Bootstrap
            msgDiv.innerHTML = data.message;
            msgDiv.className = data.success ? "alert alert-success" : "alert alert-danger";
            msgDiv.style.display = "block"; // On s'assure qu'il est visible

            // 2. Remonter en haut de la page pour voir le message
            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (data.success) {
                calendar.refetchEvents();
                document.getElementById('debutInput').value = "";
                document.getElementById('finInput').value = "";
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
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerText = "Confirmer la réservation";
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>