<?php
session_start(); 

// 1. Vérifications de sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: login.php?redirect=inscription.php');
    exit;
}

$pdo = require_once __DIR__ . '/../config/db.php';

// 2. Gestion des messages
$message = "";
$message_type = ""; // Pour gérer la couleur de l'alerte
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    $message_type = (strpos($message, '✅') !== false) ? 'success' : 'danger';
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

        $_SESSION['msg'] = "✅ " . htmlspecialchars($_SESSION['user_nom']) . " a créé le compte de " . htmlspecialchars($nom) . " " . htmlspecialchars($prenom) . " avec succès !";
        header("Location: inscription.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['msg'] = "❌ Erreur : Cet email est peut-être déjà utilisé ou données invalides.";
        header("Location: inscription.php");
        exit;
    }
}

// 4. Récupération des rôles
$roles_query = $pdo->query("SELECT id, nom FROM roles ORDER BY nom");
$liste_roles = $roles_query->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Workspace Connect</title>

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="styles/style_inscription.css">
    <link rel="stylesheet" href="styles/main_theme.css">
    
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card p-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-dark">Créer un compte</h2>
                    <p class="text-muted">Ajouter un nouvel utilisateur au système</p>
                </div>

                <form method="POST">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Nom</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-address-card"></i></span>
                                <input type="text" name="nom" class="form-control" placeholder="Nom" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Prénom</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-regular fa-address-card"></i></span>
                                <input type="text" name="prenom" class="form-control" placeholder="Prénom" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Adresse Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="exemple@mail.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Rôle assigné</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user-tie"></i></span>
                            <select name="id_role" id="id_role" class="form-select" required>
                                <option value="" selected disabled> -- Sélectionner un rôle -- </option>
                                <?php foreach ($liste_roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Immatriculation (Facultatif)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-car-rear"></i></span>
                            <input type="text" name="immatriculation" class="form-control" placeholder="AA-123-BB">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Mot de passe temporaire</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="********" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold py-2">
                            <i class="fa-solid fa-check-circle me-2"></i> Valider l'inscription
                        </button>
                        
                        <a href="index.php" class="btn btn-secondary fw-bold py-2">
                            <i class="fa-solid fa-arrow-right-from-bracket fa-flip-horizontal me-2"></i>Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>