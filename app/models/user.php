<?php
namespace App\Models;
use PDO;

class User {
    // Trouve un utilisateur par son email (pour le Login)
    public static function findByEmail($db, $email) {
        $stmt = $db->prepare('SELECT * FROM "users" WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupère tous les utilisateurs avec leur rôle (pour l'Admin)
    public static function getAllWithRoles($db) {
        $sql = "SELECT u.*, r.nom as role_nom 
                FROM users u 
                JOIN roles r ON u.id_role = r.id 
                ORDER BY u.nom ASC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Trouve un utilisateur par ID avec son rôle
    public static function findWithRole($db, $id) {
        $stmt = $db->prepare("SELECT u.*, r.nom as role_nom FROM users u LEFT JOIN roles r ON u.id_role = r.id WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}