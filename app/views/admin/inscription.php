<link rel="stylesheet" href="/workspace_connect/public/styles/style_inscription.css">
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

                <form action="/workspace_connect/admin/storeUser" method="POST">
                    
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
