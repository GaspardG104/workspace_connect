<?php
namespace App\Controllers;

ini_set('display_errors', 1);
error_reporting(E_ALL);

class AdminController {
    private $userModel; // pour faire l'importation du fichier excel (csv en vrais pck j'ai sois disant 
                        // pas la bonne version de php pour utiliser une bibliotheque php)
    public function __construct() {
        

        // Double sécurité : on vérifie que l'utilisateur est admin (ID de rôle 1)
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 2])) {
            header('Location: /workspace_connect/login');
            exit;
        }
        $this->userModel = new \App\Models\User();
    }


    public function register() {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 2])) {
            header('Location: /workspace_connect/login');
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';

        // 1. On récupère les rôles pour le menu déroulant
        $roles = $db->query("SELECT * FROM roles")->fetchAll(\PDO::FETCH_ASSOC);

        $users = \App\Models\User::getAllWithRoles($db);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        // On passe $roles et $users à la vue
        include $viewPath . 'admin/users_list.php'; 
        include $viewPath . 'layouts/footer.php';
    }

    // Traite la création du compte
    public function storeUser() {
        if ($_SESSION['user_role'] != 1) {
        $_SESSION['msg'] = "🚫 Droits insuffisants : Seul un administrateur peut créer un compte.";
        header('Location: /workspace_connect/admin/users_list');
        exit;
        }
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /workspace_connect/admin/users_list');
            exit;
        }

        $db = require __DIR__ . '/../../config/db.php';

        $id_role = $_POST['id_role'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $immatriculation = $_POST['immatriculation'];
        $password = $_POST['password'];

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (id_role, nom, prenom, email, immatriculation, password_hash) 
                    VALUES (:id_role, :nom, :prenom, :email, :imma, :pass)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_role' => $id_role,
                ':nom'     => $nom,
                ':prenom'  => $prenom,
                ':email'   => $email,
                ':imma'    => $immatriculation,
                ':pass'    => $password_hash
            ]);

            $_SESSION['msg'] = "✅ L'utilisateur $prenom $nom a été créé avec succès !";
        } catch (\Exception $e) {
            $_SESSION['msg'] = "❌ Erreur : " . $e->getMessage();
        }

        header('Location: /workspace_connect/admin/register');
        exit;
    }

        // --- Affiche la liste des utilisateurs ---
    public function users_list() {
        
        $db = require __DIR__ . '/../../config/db.php';
        
        // On récupère les users avec le nom de leur rôle
        $sql = "SELECT u.*, r.nom as role_nom 
                FROM users u 
                JOIN roles r ON u.id_role = r.id 
                ORDER BY u.nom ASC";
        $users = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'admin/users_list.php';
        include $viewPath . 'layouts/footer.php';
    }

    // --- Modifie un utilisateur ---
    public function editUser($id) {
        if ($_SESSION['user_role'] != 1) {
        $_SESSION['msg'] = "🚫 Droits insuffisants : Seul un administrateur peut éditer un compte.";
        header('Location: /workspace_connect/admin/users_list');
        exit;
        }

        $db = require __DIR__ . '/../../config/db.php';

        // 1. Traitement de la mise à jour (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id_role = $_POST['id_role'];
                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $email = $_POST['email'];
                $immatriculation = $_POST['immatriculation'];
                
                if (!empty($_POST['password'])) {
                    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET id_role=?, nom=?, prenom=?, email=?, immatriculation=?, password_hash=? WHERE id=?";
                    $params = [$id_role, $nom, $prenom, $email, $immatriculation, $pass, $id];
                } else {
                    $sql = "UPDATE users SET id_role=?, nom=?, prenom=?, email=?, immatriculation=? WHERE id=?";
                    $params = [$id_role, $nom, $prenom, $email, $immatriculation, $id];
                }

            $db->prepare($sql)->execute($params);
            $_SESSION['msg'] = "✅ Utilisateur $prenom $nom mis à jour avec succès !";
            } catch (\Exception $e) {
                $_SESSION['msg'] = "❌ Erreur lors de la modification : " . $e->getMessage();
            }
            header('Location: /workspace_connect/admin/register'); // Retour à la liste
            exit;
        }

        // 2. Affichage du formulaire (GET)
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            header('Location: /workspace_connect/admin/register');
            exit;
        }

        $roles = $db->query("SELECT * FROM roles")->fetchAll(\PDO::FETCH_ASSOC);

        $viewPath = __DIR__ . '/../views/';
        include $viewPath . 'layouts/header.php';
        include $viewPath . 'admin/edit_user.php'; // On appelle la nouvelle vue
        include $viewPath . 'layouts/footer.php';
    }

    public function deleteUser($id) {
        if ($_SESSION['user_role'] != 1) {
        $_SESSION['msg'] = "🚫 Droits insuffisants : Seul un administrateur peut supprimer un compte.";
        header('Location: /workspace_connect/admin/users_list');
        exit;
        }

        $db = require __DIR__ . '/../../config/db.php';

        // Sécurité supplémentaire : on ne s'auto-supprime pas
        if ($id == $_SESSION['user_id']) {
            $_SESSION['msg'] = "❌ Vous ne pouvez pas supprimer votre propre compte admin !";
            header('Location: /workspace_connect/admin/users_list');
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['msg'] = "✅ Utilisateur supprimé avec succès.";
        } catch (\Exception $e) {
            $_SESSION['msg'] = "❌ Impossible de supprimer : l'utilisateur est peut-être lié à des réservations.";
        }

    header('Location: /workspace_connect/admin/users_list');
    exit;
    }
public function import() {
    $db = require __DIR__ . '/../../config/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['user_file'])) {
        $file = $_FILES['user_file']['tmp_name'];
        $handle = fopen($file, "r");

        $roleMapping = [
            'admin' => 1, 'administrateur' => 1, 'manager' => 2,
            'collaborateur' => 3, 'collaboratrice' => 3, 'employé' => 4, 'employe' => 4, 'employée' => 4
        ];

        $successCount = 0;
        $errors = [];
        $lineNum = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $lineNum++;
            
            // On nettoie juste les espaces blancs, rien d'autre.
            $data = array_map('trim', $data);

            // 1. Détection de l'entête : on saute la ligne 1 si elle contient du texte connu
            $firstCell = strtolower($data[0] ?? '');
            if ($lineNum === 1 && (in_array($firstCell, ['rôle', 'role', 'fonction']))) {
                continue;
            }

            // On saute les lignes vides
            if (empty($data[1]) && empty($data[3])) continue;

            // 2. Récupération des colonnes (Ordre : Rôle, Nom, Prénom, Email, Immat)
            $roleTxt = $firstCell;
            $nom     = $data[1] ?? '';
            $prenom  = $data[2] ?? '';
            $email   = $data[3] ?? '';
            $immatriculation   = $data[4] ?? '';

            // 3. VERIFICATION STRICTE DE L'EMAIL
            // Si la colonne 3 ne contient pas d'email valide, on ne l'insère pas.
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Ligne $lineNum : Email invalide ($email)";
                continue;
            }

            // 4. TRADUCTION DU RÔLE
            $id_role = 4; // Par défaut
            foreach ($roleMapping as $key => $id) {
                if (str_contains($roleTxt, $key)) {
                    $id_role = $id;
                    break;
                }
            }

            $password_hash = password_hash($nom . "123", PASSWORD_DEFAULT);

            try {
                // Utilisation de l'INSERT direct (comme dans ton storeUser)
                $sql = "INSERT INTO users (id_role, nom, prenom, email, immatriculation, password_hash) 
                        VALUES (:id_role, :nom, :prenom, :email, :imma, :pass)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id_role' => $id_role,
                    ':nom'     => $nom,
                    ':prenom'  => $prenom,
                    ':email'   => $email,
                    ':imma'    => $immatriculation,
                    ':pass'    => $password_hash
                ]);
                $successCount++;

            } catch (\Exception $e) {
                // Si l'erreur est un doublon
                if ($e->getCode() == 23000) {
                    $errors[] = "Ligne $lineNum : L'email $email existe déjà.";
                } else {
                    $errors[] = "Ligne $lineNum : Erreur base de données.";
                }
            }
        }
        fclose($handle);

        // Message de fin
        $msg = "✅ $successCount utilisateurs importés avec succès.";
        if (!empty($errors)) {
            $msg .= " | ⚠️ Notes : " . implode(" / ", array_slice($errors, 0, 3)); 
            if (count($errors) > 3) $msg .= "...";
        }
        
        $_SESSION['msg'] = $msg;
        header('Location: /workspace_connect/admin/register');
        exit;
    }
}

}