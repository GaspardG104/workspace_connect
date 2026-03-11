<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/db.php';

$id_resource = isset($_GET['id_resource']) ? intval($_GET['id_resource']) : 0;

if ($id_resource > 0) {
    // On ne récupère que les réservations de la place spécifique choisie
    $stmt = $pdo->prepare("SELECT b.id, b.date_debut as start, b.date_fin as end, 'OCCUPÉ' as title 
                           FROM bookings b 
                           WHERE b.id_resource = :id");
    $stmt->execute(['id' => $id_resource]);
} else {
    // Par sécurité, si pas d'ID, on ne renvoie rien
    echo json_encode([]);
    exit;
}

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($events);