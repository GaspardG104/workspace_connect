<?php
session_start();
$pdo = require_once __DIR__ . '/../config/db.php';

$error = "";
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role'] = $user['id_role'];

        $target = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php';
        header("Location: $target");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Workspace Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="styles/style_login.css">
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 shadow-lg border-0" style="width: 100%; max-width: 400px; border-radius: 15px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Connexion <i class="fa-regular fa-id-card text-primary ms-2"></i></h2>
            <p class="text-muted small">Accédez à votre espace Workspace Connect</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3 border-0 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect) ?>">

            <div class="mb-3">
                <label class="form-label small fw-bold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="adresse@email.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="********" required>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">
                    Se connecter <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                </button>
                <a href="index.php" class="btn btn-link btn-sm text-decoration-none text-muted">Retour à l'accueil</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>