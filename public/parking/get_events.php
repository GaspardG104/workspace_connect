<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/db.php';

// On récupère toutes les réservations pour les afficher
$stmt = $pdo->query("SELECT b.id, b.date_debut as start, b.date_fin as end, r.nom as title 
                     FROM bookings b 
                     JOIN resources r ON b.id_resource = r.id
                     WHERE r.type = 'parking'");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On renvoie le tout au format JSON (compréhensible par le calendrier)
header('Content-Type: application/json');
echo json_encode($events);