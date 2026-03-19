<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h2 class="fw-bold text-center mb-4">Modifier le profil</h2>
                    
                    <form action="/workspace_connect/admin/editUser/<?= $userData['id'] ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Rôle</label>
                            <select name="id_role" class="form-select">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= $userData['id_role'] == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($userData['nom']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($userData['prenom']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userData['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Immatriculation</label>
                            <input type="text" name="immatriculation" class="form-control" value="<?= htmlspecialchars($userData['immatriculation']) ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-danger">Changer le mot de passe</label>
                            <input type="password" name="password" class="form-control" placeholder="Laissez vide pour ne pas modifier">
                            <div class="form-text">Utile si l'utilisateur a perdu ses accès.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2">Enregistrer les modifications</button>
                            <a href="/workspace_connect/admin/register" class="btn btn-light">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>