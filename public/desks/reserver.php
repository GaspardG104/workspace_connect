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
        /* Style pour l'élément sélectionné */
        .selected-resource { border: 3px solid #ffc107 !important; box-shadow: 0 0 15px rgba(255,193,7,0.6) !important; }
        /* Message flottant pour ne pas casser le design */
        #ajax-message { position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 250px; display: none; }
        #calendar { background: #fff; padding: 10px; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div id="ajax-message" class="alert shadow-lg"></div>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-7">
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

        <div class="col-lg-5">
            <div id="booking-ui" style="display:none;">
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
    calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
        selectable: true,
        select: function(info) {
            document.getElementById('startInput').value = info.startStr + "T09:00";
            document.getElementById('endInput').value = info.startStr + "T18:00";
        }
    });
    calendar.render();
});

function selectResource(id, nom, el) {
    if(!id) { showMsg("Ressource non trouvée en BDD", false); return; }

    document.querySelectorAll('.desk-unit, .btn-meeting, .btn-box').forEach(b => b.classList.remove('selected-resource'));
    el.classList.add('selected-resource');

    document.getElementById('booking-ui').style.display = 'block';
    document.getElementById('display-name').innerText = "Poste : " + nom;
    document.getElementById('res_id').value = id;

    calendar.setOption('events', 'get_events.php?id_resource=' + id);
    calendar.refetchEvents();
    calendar.updateSize();
}

function showMsg(txt, isSuccess) {
    const m = document.getElementById('ajax-message');
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
        if(data.success) { calendar.refetchEvents(); this.reset(); }
    })
    .finally(() => { btn.disabled = false; });
});
</script>
</body>
</html>