<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $id_user = $_SESSION['user_id'];
    $id_resource = $_POST['resource'];
    $date_debut = $_POST['debut'];
    $date_fin = $_POST['fin'];

    $maintenant = new DateTime();
    $debut_choisi = new DateTime($date_debut);

    if ($debut_choisi < $maintenant) {
        echo json_encode(['success' => false, 'message' => "❌ Erreur : Le voyage dans le temps n'est pas encore possible..."]);
        exit;
    }

    try {
        $sql = "INSERT INTO bookings (id_user, id_resource, date_debut, date_fin) VALUES (:user, :res, :debut, :fin)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user'  => $id_user,
            'res'   => $id_resource,
            'debut' => $date_debut,
            'fin'   => $date_fin
        ]);
        echo json_encode(['success' => true, 'message' => "✅ Réservation réussie !"]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "❌ Erreur : Cette ressources est déjà prise."]);
    }

    $bookingId = $pdo->lastInsertId();

    if (!empty($_POST['invites']) && is_array($_POST['invites'])) {
        $sqlInvite = "INSERT INTO booking_invites (id_booking, id_user) VALUES (?, ?)";
        $stmtInvite = $pdo->prepare($sqlInvite);
        
        foreach ($_POST['invites'] as $inviteeId) {
            $stmtInvite->execute([$bookingId, $inviteeId]);
        }
    }
}