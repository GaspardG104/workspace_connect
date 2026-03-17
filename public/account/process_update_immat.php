<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // On autorise la valeur vide ici
    $nouvelle_immat = isset($_POST['immatriculation']) ? trim($_POST['immatriculation']) : '';

    try {
        $stmt = $pdo->prepare("UPDATE users SET immatriculation = :immat WHERE id = :id");
        $success = $stmt->execute([
            'immat' => $nouvelle_immat,
            'id'    => $userId
        ]);

        if ($success) {
            // On personnalise le message si l'utilisateur a supprimé sa plaque
            $msg = empty($nouvelle_immat) ? "Préférence mise à jour (pas de véhicule)." : "✅ Plaque mise à jour !";
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            echo json_encode(['success' => false, 'message' => "❌ Erreur lors de la mise à jour."]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "❌ Erreur technique."]);
    }
}
?>