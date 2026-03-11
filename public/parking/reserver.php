<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: /../login.php');
    exit;
}

$pdo = require_once __DIR__ . '/../../config/db.php';
$message = "";
$id_user = $_SESSION['user_id']; 
$nom_user = $_SESSION['user_nom'];

// Traitement de la réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resource'])) {
    $id_resource = $_POST['resource'];
    $date_debut = $_POST['debut'];
    $date_fin = $_POST['fin'];

    $maintenant = new DateTime();
    $debut_choisi = new DateTime($date_debut);

    if ($debut_choisi < $maintenant) {
        $message = "❌ Erreur : Le voyage dans le temps n'est pas encore possible... Vous ne pouvez pas réserver dans le passé !";
    } else {
        try {
            $sql = "INSERT INTO bookings (id_user, id_resource, date_debut, date_fin) 
                    VALUES (:user, :res, :debut, :fin)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user'  => $id_user,
                'res'   => $id_resource,
                'debut' => $date_debut,
                'fin'   => $date_fin
            ]);
            $message = "✅ Réservation réussie pour " . htmlspecialchars($nom_user) . " !";
        } catch (Exception $e) {
            $message = "❌ Erreur : Cette place est déjà réservée sur ce créneau.";
        }
    }
}

$resources = $pdo->query("SELECT id, nom FROM resources WHERE type = 'parking' ORDER BY nom")->fetchAll();
$now = date('Y-m-d\TH:i');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Réserver un parking</title>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <link rel="stylesheet" href="/../styles/style_reserver.css">
</head>
<body>

    <h1>Réserver ma place</h1>
    
    <?php if(!empty($message)) echo "<p><strong>$message</strong></p>"; ?>

    <h3>1. Cliquez sur une place pour voir ses disponibilités</h3>
    <div class="parking-map">
        <?php foreach($resources as $r): ?>
            <div class="place-btn" id="btn-<?= $r['id'] ?>" onclick="selectPlace(<?= $r['id'] ?>, '<?= htmlspecialchars($r['nom']) ?>')">
                <?= htmlspecialchars($r['nom']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div id='calendar'></div>

    <div class="form-container" id="res-form-box" style="display:none;">
        <h3 id="selected-title">Nouvelle réservation</h3>
        <form method="POST">
            <input type="hidden" name="resource" id="resourceSelect" required>

            <label>Date de début :</label><br>
            <input type="datetime-local" name="debut" id="debutInput" required>
            <br><br>

            <label>Date de fin :</label><br>
            <input type="datetime-local" name="fin" id="finInput" required>
            <br><br>

            <button type="submit">Confirmer la réservation pour cette place</button>
        </form>
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
                // On ne charge rien au début tant qu'on n'a pas cliqué
                events: [], 
                
                select: function(info) {
                    document.getElementById('debutInput').value = info.startStr.substring(0, 16);
                    document.getElementById('finInput').value = info.endStr.substring(0, 16);
                }
            });
        calendar.render();
    });

    function selectPlace(id, nom) {
        // 1. Mise à jour visuelle des boutons
        document.querySelectorAll('.place-btn').forEach(btn => btn.classList.remove('selected'));
        document.getElementById('btn-' + id).classList.add('selected');

        // 2. Remplissage du formulaire caché
        document.getElementById('resourceSelect').value = id;
        document.getElementById('selected-title').innerText = "Réservation pour : " + nom;
        
        // 3. AFFICHAGE
        const calEl = document.getElementById('calendar');
        const formEl = document.getElementById('res-form-box');
        
        calEl.style.display = 'block';
        formEl.style.display = 'block'; // Utilise block plutôt que inline-block pour éviter les sauts de ligne bizarres

        // 4. LA CORRECTION DU BUG D'AFFICHAGE
        // Sans ça, la sélection de dates ne fonctionnera pas bien
        setTimeout(() => {
            calendar.updateSize();
        }, 50);
            
        // 5. Mise à jour des événements
        calendar.setOption('events', 'get_events.php?id_resource=' + id);
        calendar.refetchEvents();
        
        // Scroll fluide
        calEl.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>