<link rel="stylesheet" href="/workspace_connect/public/styles/style_account.css">

<div class="container py-5">
    <div class="row g-4">

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center py-4">
                    <div class="mb-3 text-primary">
                        <i class="fas fa-user-circle fa-5x"></i>
                    </div>
                    <h4 class="fw-bold mb-0">
                        <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                    </h4>
                    <span class="badge bg-primary mt-2 text-uppercase">
                        <?= htmlspecialchars($user['role_nom'] ?? 'admin') ?>
                    </span>
                </div>
                <div class="card-footer bg-white p-0">
                    <div class="p-3 border-bottom">
                        <small class="text-muted d-block">Adresse Email</small>
                        <span class="fw-medium">
                            <?= htmlspecialchars($user['email']) ?>
                        </span>
                    </div>
                    <div class="p-3">
                        <small class="text-muted d-block">Plaque d'immatriculation</small>
                        <div id="immat-container" class="d-flex align-items-center justify-content-between">
                            <span id="immat-text"
                                class="fw-medium <?= empty($user['immatriculation']) ? 'text-muted fst-italic' : '' ?>">
                                <?= htmlspecialchars($user['immatriculation'] ?: 'Aucune (pas de véhicule)') ?>
                            </span>

                            <input type="text" id="immat-input" class="form-control form-control-sm me-2"
                                style="display:none;" value="<?= htmlspecialchars($user['immatriculation']) ?>"
                                placeholder="Ex: AA-123-BB ou laisser vide">

                            <button id="btn-edit-immat" class="btn btn-sm btn-outline-secondary border-0">
                                <i class="fas fa-pencil-alt"></i>
                            </button>

                            <div id="immat-actions" style="display:none;">
                                <button id="btn-save-immat" class="btn btn-sm btn-success me-1"><i
                                        class="fas fa-check"></i></button>
                                <button id="btn-cancel-immat" class="btn btn-sm btn-light"><i
                                        class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div id="immat-feedback" class="mt-2" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-lock me-2"></i>Changer le mot de passe</h5>
                    <form id="updatePwdForm">
                        <div class="mb-3">
                            <label class="small fw-bold">Ancien mot de passe</label>
                            <input type="password" name="old_pwd" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Nouveau mot de passe</label>
                            <input type="password" name="new_pwd" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm py-2">Mettre à jour</button>
                    </form>
                    <div id="pwd-feedback" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>Mes réservations
                    </h5>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-center mb-4">
                        <div class="btn-group w-100 shadow-sm" role="group">

                            <button type="button" data-filter="all"
                                class="btn <?= ($filterType === 'all') ? 'btn-primary' : 'btn-outline-primary' ?> filter-btn d-flex align-items-center justify-content-center">
                                Toutes
                            </button>

                            <button type="button" data-filter="parking"
                                class="btn <?= ($filterType === 'parking') ? 'btn-primary' : 'btn-outline-primary' ?> filter-btn">
                                <i class="fas fa-car me-1"></i> Parking
                            </button>

                            <select class="form-select border-primary" id="workFilterSelect">
                                <option value="" selected disabled>Bureaux, Salles, Boxs...</option>
                                <?php foreach($types_uniques as $type): ?>
                                <?php if($type === 'parking') continue; ?>

                                <option data-filter="<?= htmlspecialchars($type) ?>" <?=($filterType===$type)
                                    ? 'selected' : '' ?>>
                                    <?php 
                                                $label = ucfirst(htmlspecialchars($type));
                                                echo (substr($type, -3) === 'eau') ? $label . 'x' : $label . 's';
                                            ?>
                                </option>
                                <?php endforeach; ?>
                            </select>

                            <button id="sortByDate" class="btn btn-outline-primary">
                                <i class="fas fa-sort me-1"></i> Trier par date
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="bookingTable">
                            <thead class="table-light text-secondary">
                                <tr id="booking-row-<?= $r['id'] ?>">
                                    <th>Ressource</th>
                                    <th>Dates</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($bookings)): ?>
                                <tr class="no-data">
                                    <td colspan="3" class="text-center py-5 text-muted">Aucune réservation trouvée.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($bookings as $r): ?>
                                <tr id="booking-row-<?= $r['id'] ?>">
                                    <td>
                                        <span class="fw-bold d-block text-dark">
                                            <?= htmlspecialchars($r['resource_name']) ?>
                                        </span>
                                        <div class="d-flex gap-1">
                                            <small class="badge bg-light text-dark border text-uppercase"
                                                style="font-size: 0.65rem;">
                                                <?= htmlspecialchars($r['resource_type']) ?>
                                            </small>
                                            <?php if($r['resource_type'] === 'salle'): ?>
                                            <small
                                                class="badge <?= ($r['role_dans_resa'] === 'Organisateur') ? 'bg-primary' : 'bg-info' ?> text-uppercase"
                                                style="font-size: 0.65rem;">
                                                <?= htmlspecialchars($r['role_dans_resa']) ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="text-muted">Du :</span>
                                            <?= date('d/m/Y H:i', strtotime($r['debut'])) ?><br>
                                            <span class="text-muted">Au :</span>
                                            <?= date('d/m/Y H:i', strtotime($r['fin'])) ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if ($r['role_dans_resa'] === 'Organisateur'): ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Annuler ma réservation"
                                            onclick="prepareDelete(
                                                                    <?= $r['id'] ?>, 
                                                                    '<?= addslashes($r['resource_name']) ?>', 
                                                                    '<?= $r['resource_type'] ?>', 
                                                                    <?= $r['id_series'] ?? 'null' ?>
                                                                )">
                                            <i class="fa-solid fa-trash-can me-1"></i> Annuler
                                        </button>
                                        <?php else: ?>
                                        <span class="badge bg-light text-muted border">Invité</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- pour la suppression de réservation et prévénirs les participants-->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-exclamation-triangle me-2"></i>Annuler ma réservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Voulez-vous vraiment annuler cette réservation ?</p>

                <div id="notificationOptions" class="mt-3 p-3 bg-light rounded border">
                    <p class="small fw-bold mb-2 text-muted">Options de notification :</p>
                    <div class="form-check" id="optionInvites">
                        <input class="form-check-input" type="checkbox" id="notifyInvites" checked>
                        <label class="form-check-label small" for="notifyInvites">
                            Prévenir les participants par email de l'annulation
                        </label>
                    </div>
                    <div class="mb-3 form-check" id="optionSeries" style="display: none;">
                        <input type="checkbox" class="form-check-input" id="deleteAllSeries">
                        <label class="form-check-label text-danger fw-bold" for="deleteAllSeries">
                            Annuler toute la série de réservations
                        </label>
                    </div>
                    <input type="hidden" id="notifyOrganizer" value="false">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Conserver</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Confirmer l'annulation</button>
            </div>
        </div>
    </div>
</div>


<script src="/workspace_connect/public/js/account.js"></script>