<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
$pdo = require_once __DIR__ . '/../../config/db.php';

// On prépare le mapping des IDs pour le JavaScript
$stmt = $pdo->query("SELECT id, nom FROM resources WHERE type NOT IN ('parking')");
$res_map = [];
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $res_map[$r['nom']] = $r['id'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver - Workspace Connect</title>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/../styles/style_desks.css">
    <link rel="stylesheet" href="/../styles/main_theme.css">
    <style>
        /* Message flottant pour ne pas casser le design */
        #ajax-message { position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 250px; display: none; }
        #calendar { background: #fff; padding: 10px; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container-fluid py-4">
    <div class="text-center mb-4">
        <h1 class="fw-bold">Réserver mon bureau</h1>
    </div>
    <div class="row g-4 transition-layout justify-content-center" id="layout-wrapper">
        <div class="col-lg-7 transition-all" id="plan-column">
            <div class="card shadow p-3 border-0">
                <div class="plan-container">
                    <button class="btn-meeting meeting-1" onclick="selectResource(<?= $res_map['S1'] ?? 0 ?>, 'Salle 1', this)">Salle de réunion 1</button>
                    <button class="btn-meeting meeting-2" onclick="selectResource(<?= $res_map['S2'] ?? 0 ?>, 'Salle 2', this)">Salle de réunion 2</button>

                    <div class="desk-group-v v-desk-1">
                        <?php for($i=1; $i<=6; $i++): $name="I1B$i"; ?>
                            <button class="desk-unit" onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B<?= $i ?></button>
                        <?php endfor; ?>
                    </div>
                    <div class="desk-group-v v-desk-2">
                        <?php for($i=1; $i<=6; $i++): $name="I2B$i"; ?>
                            <button class="desk-unit" onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B<?= $i ?></button>
                        <?php endfor; ?>
                    </div>

                    <?php for($h=1; $h<=4; $h++): ?>
                        <div class="desk-group-h h-<?= $h ?>">
                            <?php for($i=1; $i<=6; $i++): $numIlot = $h+2; $name="I{$numIlot}B$i"; ?>
                                <button class="desk-unit" onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B<?= $i ?></button>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>

                    <button class="btn-box box-1" onclick="selectResource(<?= $res_map['B1'] ?? 0 ?>, 'Box 1', this)">Box 1</button>
                    <button class="btn-box box-2" onclick="selectResource(<?= $res_map['B2'] ?? 0 ?>, 'Box 2', this)">Box 2</button>
                </div>
            </div>
        </div>

        <div class="col-lg-5" id="calendar-column" style="display: none;">
            <div id="booking-ui">
                <div class="card shadow-sm border-0 p-3 mb-3">
                    <h4 id="display-name" class="fw-bold text-primary"></h4>
                    <div id="calendar"></div>
                </div>

                <div class="card shadow-sm border-0 p-3">
                    <form id="bookingForm">
                        <input type="hidden" name="resource" id="res_id">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small fw-bold">Début</label>
                                <input type="datetime-local" name="debut" id="startInput" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold">Fin</label>
                                <input type="datetime-local" name="fin" id="endInput" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" id="subBtn" class="btn btn-primary w-100 mt-3 fw-bold">Réserver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
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
        select: function(info) {
            document.getElementById('startInput').value = info.startStr + "T09:00";
            document.getElementById('endInput').value = info.startStr + "T18:00";
        }
    });
    calendar.render();
});

// La fonction est maintenant bien définie au niveau global
function selectResource(id, nom, el) {
    if(!id) { 
        showMsg("Ressource non trouvée en BDD", false); 
        return; 
    }

    // 1. Gérer la sélection visuelle (couleur du bureau)
    document.querySelectorAll('.selected-resource').forEach(b => b.classList.remove('selected-resource'));
    el.classList.add('selected-resource');

    // 2. Afficher le bloc de réservation et déclencher l'animation de décalage
    const calendarCol = document.getElementById('calendar-column');
    const bookingUi = document.getElementById('booking-ui');
    const layoutWrapper = document.getElementById('layout-wrapper');
    
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
    calendar.setOption('events', 'get_events.php?id_resource=' + id);
    calendar.refetchEvents();
    
    // TRÈS IMPORTANT : On attend un court instant que l'animation commence 
    // pour que FullCalendar ajuste sa largeur, sinon il reste invisible ou buggé.
    setTimeout(() => {
        layoutWrapper.classList.add('active');
        // On attend un peu plus (500ms au lieu de 200ms) pour que 
        // l'espace soit suffisant avant de dessiner le calendrier
        setTimeout(() => {
            calendar.updateSize();
        }, 500); 
    }, 10);
}

function showMsg(txt, isSuccess) {
    const m = document.getElementById('ajax-message');
    if(!m) return;
    m.innerHTML = txt;
    m.className = "alert shadow-lg " + (isSuccess ? "alert-success" : "alert-danger");
    m.style.display = "block";
    setTimeout(() => { m.style.display = "none"; }, 4000);
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('subBtn');
    btn.disabled = true;

    fetch('process_reservation.php', { method: 'POST', body: new FormData(this) })
    .then(r => r.json())
    .then(data => {
        showMsg(data.message, data.success);
        if(data.success) { 
            calendar.refetchEvents(); 
            this.reset(); 
        }
    })
    .finally(() => { btn.disabled = false; });
});


</script>
</body>
</html>