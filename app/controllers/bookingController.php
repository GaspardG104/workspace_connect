<?php
namespace App\Controllers;

use PDO;
use App\Models\Booking; // Importation du modèle
use App\Models\Resource;

class BookingController {
    
    public function parking() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /workspace_connect/login');
            exit;
        }
        $db = require __DIR__ . '/../../config/db.php';
        $resources = Resource::getByType($db, 'parking');

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'reservations/parking.php'; 
        include $viewPath . 'layouts/footer.php';
    }

    public function desk() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /workspace_connect/login');
            exit;
        }
        $db = require __DIR__ . '/../../config/db.php';
        $resources = Resource::getAllExceptParking($db);

        // Préparation des données pour la vue
        $res_map = [];
        $capacities = [];
        foreach ($resources as $r) {
            $res_map[$r['nom']] = $r['id'];
            $capacities[$r['id']] = $r['capacite'];
        }

        $stmtUsers = $db->prepare("SELECT id, prenom, nom FROM users WHERE id != :me ORDER BY nom ASC");
        $stmtUsers->execute(['me' => $_SESSION['user_id']]);
        $all_users = $stmtUsers->fetchAll(); 

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'reservations/desk.php'; 
        include $viewPath . 'layouts/footer.php';
    }

    public function store() {
        $db = require __DIR__ . '/../../config/db.php';
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => "Session expirée."]);
            exit;
        }

        $id_user = $_SESSION['user_id'];
        $id_resource = $_POST['resource'] ?? $_POST['id_resource'] ?? null;
        $date_debut = $_POST['debut'] ?? null;
        $date_fin = $_POST['fin'] ?? null;
        $invites = $_POST['invites'] ?? [];

        if (!$id_resource || !$date_debut || !$date_fin) {
            echo json_encode(['success' => false, 'message' => "❌ Données manquantes."]);
            exit;
        }

        try {
            $db->beginTransaction();

            $dates_a_reserver = [];
            $id_series = null;

            $startObj = new \DateTime($date_debut);
            $endObj = new \DateTime($date_fin);
            
            // --- NOUVEAU : DETECTION AUTO SÉLECTION SOURIS (MULTI-JOURS) ---
            // On vérifie si la date de début et la date de fin sont des jours différents
            $is_multi_day_selection = $startObj->format('Y-m-d') !== $endObj->format('Y-m-d');
            $is_recurring = isset($_POST['is_recurring']) && $_POST['is_recurring'] === 'true';

            if ($is_multi_day_selection && !$is_recurring) {
                // Création automatique d'une série pour la sélection à la souris
                $stmtSeries = $db->prepare("INSERT INTO booking_series (rrule_string) VALUES (?)");
                $stmtSeries->execute(["MOUSE_SELECTION"]);
                $id_series = $db->lastInsertId();

                $interval = new \DateInterval('P1D');
                // On clone pour ne pas modifier l'original, et on ajoute 1 sec pour inclure le dernier jour dans la boucle
                $tempEnd = clone $endObj;
                $tempEnd->modify('+1 second');
                $period = new \DatePeriod(new \DateTime($startObj->format('Y-m-d')), $interval, $tempEnd);

                foreach ($period as $dt) {
                    // N est le numéro du jour : 6 (Samedi), 7 (Dimanche)
                    if ((int)$dt->format('N') >= 6) continue; // On ignore le week-end
                    // Pour chaque jour, on garde l'heure de début et de fin choisie
                    $d = $dt->format('Y-m-d') . ' ' . $startObj->format('H:i:s');
                    $f = $dt->format('Y-m-d') . ' ' . $endObj->format('H:i:s');
                    $dates_a_reserver[] = [$d, $f];
                }
            } 
            // --- FIN DETECTION AUTO ---
            
            // 1. Gestion de la récurrence classique (Si cochée via le formulaire)
            elseif ($is_recurring) {
                $dates_a_reserver[] = [$date_debut, $date_fin]; // On ajoute le premier créneau
                $type_recurence = $_POST['recurrence_type'] ?? 'WEEKLY';
                $nb_repetitions = intval($_POST['recurrence_count'] ?? 1);
                
                for ($i = 1; $i < $nb_repetitions; $i++) {
                    // N est le numéro du jour : 6 (Samedi), 7 (Dimanche)
                    if ((int)$dt->format('N') >= 6) continue; // On ignore le week-end
                    $interval = ($type_recurence === 'DAILY') ? "P{$i}D" : (($type_recurence === 'MONTHLY') ? "P{$i}M" : "P{$i}W");
                    $d = new \DateTime($date_debut);
                    $f = new \DateTime($date_fin);
                    $d->add(new \DateInterval($interval));
                    $f->add(new \DateInterval($interval));
                    $dates_a_reserver[] = [$d->format('Y-m-d H:i:s'), $f->format('Y-m-d H:i:s')];
                }

                $stmtSeries = $db->prepare("INSERT INTO booking_series (rrule_string) VALUES (?)");
                $stmtSeries->execute(["FREQ=$type_recurence;COUNT=$nb_repetitions"]);
                $id_series = $db->lastInsertId();
            } else {
                // Cas simple : On vérifie si le jour unique est un week-end
                if ((int)$startObj->format('N') >= 6) {
                    throw new \Exception("❌ Les réservations sont interdites le week-end.");
                }
                $dates_a_reserver[] = [$date_debut, $date_fin];
            }

            // 2. Insertion des bookings
            foreach ($dates_a_reserver as $creneau) {
                if (!Booking::isAvailable($db, $id_resource, $creneau[0], $creneau[1])) {
                    // On formate la date pour le message d'erreur
                    $dateError = new \DateTime($creneau[0]);
                    throw new \Exception("Le créneau du " . $dateError->format('d/m/Y') . " est déjà pris.");
                }
                
                Booking::create($db, $id_user, $id_resource, $creneau[0], $creneau[1], $id_series);
                $newBookingId = $db->lastInsertId();

                if (!empty($invites)) {
                    $this->addAttendees($db, $newBookingId, $invites);
                }
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => "✅ Réservation réussie !"]);

        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getEvents() {
        $db = require __DIR__ . '/../../config/db.php';
        $id_resource = isset($_GET['id_resource']) ? intval($_GET['id_resource']) : 0;
        
        // Utilisation du modèle
        $events = Booking::getEventsByResource($db, $id_resource);
        
        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }

    private function addAttendees($db, $bookingId, $invites) {
        $stmtUser = $db->prepare("SELECT email, nom, prenom FROM users WHERE id = ?");
        $stmtAt = $db->prepare("INSERT INTO attendees (id_booking, email, nom_invite) VALUES (?, ?, ?)");
        foreach ($invites as $id_invite) {
            $stmtUser->execute([$id_invite]);
            $u = $stmtUser->fetch(PDO::FETCH_ASSOC);
            if ($u) {
                $stmtAt->execute([$bookingId, $u['email'], $u['prenom'] . ' ' . $u['nom']]);
            }
        }
    }
    private function isResourceAvailable($db, $resourceId, $start, $end) {
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE id_resource = ? 
                AND (date_debut < ? AND date_fin > ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$resourceId, $end, $start]);
        return $stmt->fetchColumn() == 0;
    }

}
    
    
    
    
