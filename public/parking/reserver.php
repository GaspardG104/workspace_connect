<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = require_once __DIR__ . '/../../config/db.php';
$message = "";
$id_user = $_SESSION['user_id']; 
$nom_user = $_SESSION['user_nom'];

// ON VERIFIE SI LE BOUTON A ETE CLIQUE
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

$resources = $pdo->query("SELECT id, nom FROM resources WHERE type = 'parking'")->fetchAll();
$now = date('Y-m-d\TH:i');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Réserver un parking</title>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        body { font-family: sans-serif; padding: 20px; text-align: center; }
        #calendar { max-width: 900px; margin: 20px auto; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ccc; display: inline-block; border-radius: 8px; text-align: left; }
    </style>
</head>
<body>

    <h1>Réserver ma place</h1>
    
    <?php if(!empty($message)) echo "<p><strong>$message</strong></p>"; ?>

    <div id='calendar'></div>

    <div class="form-container">
        <form method="POST">
            <label>Place de parking :</label><br>
            <select name="resource" id="resourceSelect" required>
                <option value="">-- Choisissez --</option>
                <?php foreach($resources as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <label>Date de début :</label><br>
            <input type="datetime-local" name="debut" id="debutInput" required>
            <br><br>

            <label>Date de fin :</label><br>
            <input type="datetime-local" name="fin" id="finInput" required>
            <br><br>

            <button type="submit">Confirmer la réservation</button>
        </form>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'timeGridWeek',
          locale: 'fr',
          selectable: true,
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
          },
          events: 'get_events.php', // AFFICHE LES RESERVATIONS EXISTANTES
          
          select: function(info) {
            // Conversion du format de date pour HTML5 (YYYY-MM-DDTHH:MM)
            let start = info.startStr.substring(0, 16);
            let end = info.endStr.substring(0, 16);

            document.getElementById('debutInput').value = start;
            document.getElementById('finInput').value = end;

            // Optionnel : Appel à check_availability.php si tu l'as créé
            fetch(`check_availability.php?start=${start}&end=${end}&type=parking`)
                .then(res => res.json())
                .then(data => {
                    if(data.available_id) {
                        let select = document.getElementById('resourceSelect');
                        select.value = data.available_id;
                        // On force un petit message visuel rapide (optionnel)
                        console.log("Place trouvée : " + data.available_id);
                    } else {
                        alert("Aucune place n'est libre pour ce créneau !");
                    }
                });
          }
        });
        calendar.render();
      });
    </script>
</body>
</html>