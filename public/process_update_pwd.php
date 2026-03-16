<?php
session_start();
header('Content-Type: application/json');

// 1. Vérification de la session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expirée, veuillez vous reconnecter.']);
    exit;
}

// 2. Connexion à la base de données
try {
    $pdo = require_once __DIR__ . '/db.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}

$userId = $_SESSION['user_id'];
$old_pwd = $_POST['old_pwd'] ?? '';
$new_pwd = $_POST['new_pwd'] ?? '';

// 3. Validation des champs
if (empty($old_pwd) || empty($new_pwd)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
    exit;
}

if (strlen($new_pwd) < 6) {
    echo json_encode(['success' => false, 'message' => 'Le nouveau mot de passe doit faire au moins 6 caractères.']);
    exit;
}

// 4. Vérification de l'ancien mot de passe
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($old_pwd, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => "L'ancien mot de passe est incorrect."]);
    exit;
}

// 5. Mise à jour avec le nouveau mot de passe (haché)
$hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");

if ($update->execute([$hashed_pwd, $userId])) {
    echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès !']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour en base de données.']);
}