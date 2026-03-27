<?php
namespace App\Models;
use PDO;

class Resource {
    public static function getByType($db, $type) {
        $stmt = $db->prepare("SELECT id, nom, capacite FROM resources WHERE type = ? ORDER BY nom ASC");
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllExceptParking($db) {
        return $db->query("SELECT id, nom, capacite FROM resources WHERE type != 'parking' ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}