<?php
session_start();
// On récupère la connexion à la base de données
$pdo = require_once __DIR__ . '/../config/db.php';

// On vérifie que l'utilisateur est connecté et que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // On nettoie la valeur reçue (trim) pour éviter les espaces inutiles
    $nouvelle_immat = isset($_POST['immatriculation']) ? trim($_POST['immatriculation']) : '';

    // Validation minimale : pas vide
    if (empty($nouvelle_immat)) {
        echo json_encode(['success' => false, 'message' => "❌ La plaque ne peut pas être vide."]);
        exit;
    }

    try {
        // Préparation de la requête de mise à jour
        $stmt = $pdo->prepare("UPDATE users SET immatriculation = :immat WHERE id = :id");
        $success = $stmt->execute([
            'immat' => $nouvelle_immat,
            'id'    => $userId
        ]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => "✅ Plaque d'immatriculation mise à jour !"]);
        } else {
            echo json_encode(['success' => false, 'message' => "❌ Erreur lors de la mise à jour."]);
        }
    } catch (Exception $e) {
        // En cas d'erreur SQL (ex: problème de connexion)
        echo json_encode(['success' => false, 'message' => "❌ Erreur technique : " . $e->getMessage()]);
    }
} else {
    // Si on essaie d'accéder au fichier directement sans être connecté
    echo json_encode(['success' => false, 'message' => "🚫 Accès non autorisé."]);
}