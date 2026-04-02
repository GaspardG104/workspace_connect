<link rel="stylesheet" href="/workspace_connect/public/styles/style_users.css">

<div class="container-fluid py-4">
    <div class="text-center mb-4 text-white" >
        <h1><i class="fa-solid fa-address-book"></i> Gestion des utilisateurs</h2>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-info py-2 small"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <?php if ($_SESSION['user_role'] == 1): ?>
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3" id="formTitle">
                        <?= isset($userData) ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' ?>
                    </h4>

                    <form action="<?= isset($userData) ? '/workspace_connect/admin/editUser/'.$userData['id'] : '/workspace_connect/admin/storeUser' ?>" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Rôle</label>
                            <select name="id_role" class="form-select" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= (isset($userData) && $userData['id_role'] == $role['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= $userData['nom'] ?? '' ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?= $userData['prenom'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= $userData['email'] ?? '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Immatriculation</label>
                            <input type="text" name="immatriculation" class="form-control" value="<?= $userData['immatriculation'] ?? '' ?>" placeholder="AA-123-BB">
                        </div>

                        <?php if ($_SESSION['user_role'] == 1): ?>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">
                                    <?= isset($userData) ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe' ?>
                                </label>
                                <input type="password" name="password" class="form-control" <?= isset($userData) ? '' : 'required' ?>>
                            </div>
                            <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fa-solid fa-save me-2"></i> Enregistrer
                            </button>
                            <?php if(isset($userData)): ?>
                                <a href="/workspace_connect/admin/users_list" class="btn btn-light btn-sm text-muted">Annuler la modification</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h3 class="card-title mb-4">Import groupé</h3>
            <form action="/workspace_connect/admin/import" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Fichier CSV uniquement</label>
                    <input type="file" name="user_file" class="form-control" accept=".csv" required>
                    <div class="form-text">Format attendu : Fonction, Nom, Prénom, Email, Immatriculation, MDP Temporaire</div>
                </div>
                <button type="submit">Importer</button>
            </form>
        </div>
    </div>
</div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($u['immatriculation'] ?: 'Pas de véhicule') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                    <span class="badge <?= match($u['id_role']) {
                                            1 => 'bg-danger',  // Administrateur
                                            2 => 'bg-secondary', // manager
                                            3 => 'bg-primary', // Collaborateur
                                            4 => 'bg-success',    
                                            default => 'bg-info'// Autre rôle
                                            } ?>">
                                    <i class="<?= match($u['id_role']) {
                                            1 => 'fa-solid fa-shield-halved',
                                            2 => 'fa-solid fa-user-tie',       // Exemple pour Manager
                                            3 => 'fa-solid fa-handshake',           // Exemple pour Collaborateur
                                            4 => 'fa-solid fa-screwdriver-wrench',           // Exemple pour un autre rôle
                                            default => 'fa-regular fa-user'                      // Vide si aucun rôle ne correspond
                                        } ?> me-1"></i>
                                            <?= htmlspecialchars($u['role_nom']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($_SESSION['user_role'] == 1): ?>
                                        <a href="/workspace_connect/admin/editUser/<?= $u['id'] ?>" class="btn btn-outline-warning btn-sm border-0">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $u['id'] ?>, '<?= addslashes($u['prenom'].' '.$u['nom']) ?>')" class="btn btn-outline-danger btn-sm border-0">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Lecture seule</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    if (confirm("🚨 Voulez-vous vraiment supprimer " + name + " ? Cette action est irréversible.")) {
        window.location.href = "/workspace_connect/admin/deleteUser/" + id;
    }
}
</script>