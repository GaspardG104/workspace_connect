<?php
session_start(); 

// 1. Vérifications de sécurité (DOIVENT être avant tout affichage)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: login.php?redirect=inscription.php');
    exit;
}

$pdo = require_once __DIR__ . '/../config/db.php';

// 2. Gestion des messages en session
$message = "";
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    unset($_SESSION['msg']); 
}

// 3. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_role' => $id_role,
            'nom'     => $nom,
            'prenom'  => $prenom,
            'email'   => $email,
            'imma'    => $immatriculation,
            'pass'    => $password_hash
        ]);

        $_SESSION['msg'] = "✅ " . htmlspecialchars($_SESSION['user_nom']) . " a créé le compte de " . htmlspecialchars($nom) . " avec succès !";
        
        // REDIRECTION CRUCIALE
        header("Location: inscription.php");
        exit; // Arrête le script ici pour forcer la redirection

    } catch (Exception $e) {
        $_SESSION['msg'] = "❌ Erreur : Cet email est peut-être déjà utilisé ou données invalides.";
        header("Location: inscription.php");
        exit;
    }
}

// 4. On récupère les rôles SEULEMENT si on n'a pas redirigé (donc pour l'affichage)
$roles_query = $pdo->query("SELECT id, nom FROM roles ORDER BY nom");
$liste_roles = $roles_query->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription - Workspace Connect</title>
    <link rel="stylesheet" href="styles/style_inscription.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <h1>Créer un compte</h1>
    <p><?= $message ?></p>

    <form method="POST">
        <input type="text" name="nom" placeholder="Nom" required> <i class="fa-solid fa-address-card"></i><br><br>
        <input type="text" name="prenom" placeholder="Prénom" required> <i class="fa-regular fa-address-card"></i><br><br>
        <input type="email" name="email" placeholder="Email" required> <i class="fa-solid fa-envelope"></i> <br><br>
        <div class="input-group">
        <select name="id_role" id="id_role" require><option value="">-- Sélectionner un rôle --</option>
        <?php foreach ($liste_roles as $role): ?><option value="<?= $role['id'] ?>">
        <?= htmlspecialchars($role['nom']) ?></option>
        <?php endforeach; ?></select><i class="fa-solid fa-user-tie"><br><br>
        </div>
        <input type="text" name="immatriculation" placeholder="Immatriculation (ex: AA-123-BB)"> <i class="fa-solid fa-car-rear"></i> <br><br>
        <input type="password" name="password" placeholder="Mot de passe" required>  <i class="fa-solid fa-lock"></i><br><br>
        
        <button type="submit">Valider</button> <i class="fa-solid fa-handshake"></i>
    </form>
    <a href="logout.php" style="color: red;">Se déconnecter</a>
</body>
</html>