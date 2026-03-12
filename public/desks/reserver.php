<?php
session_start(); 
// 1. Vérifications de sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: login.php?redirect=inscription.php');
    exit;
}
$pdo = require_once __DIR__ . '/../../config/db.php';
$nom_user = $_SESSION['user_nom'];

// On récupère les bureaux, box et salles (type 'bureau' ou 'salle' dans ta BDD)
$stmt = $pdo->query("SELECT id, nom, type FROM resources WHERE type IN ('bureau', 'salle') ORDER BY nom");
$resources = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de l'Open Space - Workspace Connect</title>
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../styles/style_plan.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php"><i class="fa-solid fa-building-user me-2"></i> Workspace Connect</a>
        <div class="ms-auto">
            <span class="text-white me-3 small">Utilisateur : <strong><?= htmlspecialchars($nom_user) ?></strong></span>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="text-center mb-4">
        <h1 class="fw-bold">Réservation Open Space</h1>
        <div id="ajax-message" class="mt-2"></div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body p-4">
            <h5 class="card-title mb-4 text-secondary text-center">Cliquez sur un espace pour voir les disponibilités</h5>
            
            <div class="floor-plan-container">
                <div class="floor-plan">
                    <div class="zone salle s1" onclick="selectZone(1, 'Salle de réunion 1')">Salle de réunion 1</div>
                    <div class="zone salle s2" onclick="selectZone(2, 'Salle de réunion 2')">Salle de réunion 2</div>

                    <div class="zone bureau bv1" onclick="selectZone(3, 'Bureau V1')"></div>
                    <div class="zone bureau bv2" onclick="selectZone(4, 'Bureau V2')"></div>

                    <div class="zone bureau bh1" onclick="selectZone(5, 'Bureau H1')"></div>
                    <div class="zone bureau bh2" onclick="selectZone(6, 'Bureau H2')"></div>
                    <div class="zone bureau bh3" onclick="selectZone(7, 'Bureau H3')"></div>
                    <div class="zone bureau bh4" onclick="selectZone(8, 'Bureau H4')"></div>

                    <div class="zone box bx1" onclick="selectZone(9, 'Box perso 1')">Box 1</div>
                    <div class="zone box bx2" onclick="selectZone(10, 'Box perso 2')">Box 2</div>
                </div>
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
                <h4 id="selected-title" class="fw-bold text-primary mb-4">Sélectionnez une zone</h4>
                <form id="bookingForm">
                    <input type="hidden" name="resource" id="resourceSelect" required>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Début</label>
                        <input type="datetime-local" name="debut" id="debutInput" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fin</label>
                        <input type="datetime-local" name="fin" id="finInput" class="form-control" required>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-primary w-100 fw-bold py-2 mt-2">Réserver cet espace</button>
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
        initialView: 'timeGridWeek', // Plus précis pour les bureaux/salles
        locale: 'fr',
        selectable: true,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
        select: function(info) {
            const format = (d) => d.toISOString().slice(0, 16);
            document.getElementById('debutInput').value = format(info.start);
            document.getElementById('finInput').value = format(info.end);
        }
    });
    calendar.render();
});

function selectZone(id, nom) {
    // Animation de sélection sur le plan
    document.querySelectorAll('.zone').forEach(z => z.classList.remove('selected'));
    const target = document.querySelector(`.zone[onclick*="${id}"]`);
    if(target) target.classList.add('selected');

    document.getElementById('resourceSelect').value = id;
    document.getElementById('selected-title').innerText = nom;
    document.getElementById('calendar').style.display = 'block';
    document.getElementById('res-form-box').style.display = 'block';

    calendar.updateSize();
    calendar.setOption('events', 'get_events.php?id_resource=' + id);
    calendar.refetchEvents();
    
    // Scroll doux vers le calendrier
    document.getElementById('calendar').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const msgDiv = document.getElementById('ajax-message');
    fetch('process_reservation.php', { method: 'POST', body: new FormData(this) })
    .then(r => r.json())
    .then(data => {
        msgDiv.innerHTML = data.message;
        msgDiv.className = "alert " + (data.success ? "alert-success" : "alert-danger");
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (data.success) calendar.refetchEvents();
        setTimeout(() => msgDiv.style.display = 'none', 4000);
    });
});
</script>
</body>
</html>