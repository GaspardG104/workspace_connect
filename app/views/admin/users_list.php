<div class="container mt-5">
    <h2 class="mb-4">Gestion des utilisateurs</h2>
    <table class="table table-hover shadow-sm bg-white">
        <thead class="table-primary">
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['prenom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge bg-info"><?= htmlspecialchars($u['role_nom']) ?></span></td>
                <td>
                    <a href="/workspace_connect/admin/editUser/<?= $u['id'] ?>" class="btn btn-sm btn-warning">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $u['id'] ?>)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>