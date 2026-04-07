<link rel="stylesheet" href="/workspace_connect/public/styles/style_desks.css">

<div id="ajax-message" class="alert shadow-lg"></div>

<div class="container-fluid py-4">
    <div class="text-center mb-4 text-white">
        <h1><i class="fa-solid fa-list-check me-2"></i> Réserver un bureau, salle de réunion, box</h2>
    </div>
    <div class="row g-4 transition-layout justify-content-center" id="layout-wrapper">
        <div class="col-lg-7 transition-all" id="plan-column">
            <div class="card shadow p-3 border-0">
                <div class="plan-container">
                    <button class="btn-meeting meeting-1"
                        onclick="selectResource(<?= $res_map['S1'] ?? 0 ?>, 'Salle 1', this)">Salle de réunion
                        1</button>
                    <button class="btn-meeting meeting-2"
                        onclick="selectResource(<?= $res_map['S2'] ?? 0 ?>, 'Salle 2', this)">Salle de réunion
                        2</button>

                    <div class="desk-group-v v-desk-1">
                        <?php for($i=1; $i<=6; $i++): $name="I1B$i"; ?>
                        <button class="desk-unit"
                            onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B
                            <?= $i ?>
                        </button>
                        <?php endfor; ?>
                    </div>
                    <div class="desk-group-v v-desk-2">
                        <?php for($i=1; $i<=6; $i++): $name="I2B$i"; ?>
                        <button class="desk-unit"
                            onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B
                            <?= $i ?>
                        </button>
                        <?php endfor; ?>
                    </div>

                    <?php for($h=1; $h<=4; $h++): ?>
                    <div class="desk-group-h h-<?= $h ?>">
                        <?php for($i=1; $i<=6; $i++): $numIlot = $h+2; $name="I{$numIlot}B$i"; ?>
                        <button class="desk-unit"
                            onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B
                            <?= $i ?>
                        </button>
                        <?php endfor; ?>
                    </div>
                    <?php endfor; ?>

                    <button class="btn-box box-1"
                        onclick="selectResource(<?= $res_map['B1'] ?? 0 ?>, 'Box 1', this)">Box 1</button>
                    <button class="btn-box box-2"
                        onclick="selectResource(<?= $res_map['B2'] ?? 0 ?>, 'Box 2', this)">Box 2</button>
                </div>
            </div>
        </div>

        <div class="col-lg-5" id="calendar-column" style="display: none;">
            <div id="booking-ui">
                <div class="card shadow-sm border-0 p-3 mb-3">
                    <h4 id="display-name" class="fw-bold text-primary"></h4>
                    <div id="calendar"></div>
                </div>

                <div class="card shadow-sm border-0 p-3">
                    <form id="bookingForm">
                        <input type="hidden" name="resource" id="res_id">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small fw-bold">Début</label>
                                <input type="datetime-local" name="debut" id="startInput" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold">Fin</label>
                                <input type="datetime-local" name="fin" id="endInput" class="form-control" required>
                            </div>
                        </div>
                        <div id="display-date" class="mt-2"></div>
                        <div id="invite-section" class="mt-3" style="display:none;">
                            <label class="small fw-bold text-muted mb-2">
                                Inviter des collègues (Capacité max : <span id="cap-val"></span>)
                            </label>
                            <div id="tags-container" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <div class="dropdown">
                                <input type="text" id="userSearch" class="form-control"
                                    placeholder="Rechercher un collègue..." autocomplete="off">
                                <ul id="userSuggestions" class="dropdown-menu w-100 shadow-sm"
                                    style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach($all_users as $u): ?>
                                    <li>
                                        <a class="dropdown-item user-option" href="#" data-id="<?= $u['id'] ?>"
                                            data-name="<?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>">
                                            <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div id="hidden-inputs"></div>
                        </div>
                        <div class="mb-3 p-3 border rounded bg-light">

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring"
                                    value="true">
                                <label class="form-check-label fw-bold" for="is_recurring">
                                    <i class="fa-solid fa-repeat me-1"></i> Répéter cette réservation
                                </label>
                            </div>


                            <div id="recurrence-options" class="mt-3" style="display:none">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="small fw-bold">Fréquence</label>
                                        <select name="recurrence_type" id="recurrence_type"
                                            class="form-select form-select-sm">
                                            <option value="WEEKLY">Toutes les semaines</option>
                                            <option value="DAILY">Tous les jours</option>
                                            <option value="MONTHLY">Tous les mois</option>
                                        </select>
                                    </div>


                                    <div class="col-md-6">
                                        <label id="label-count" class="small fw-bold">Pendant combien de fois ?</label>
                                        <input type="number" name="recurrence_count" id="recurrence_count"
                                            class="form-select form-select-sm" value="1" min="1">
                                    </div>
                                </div>
                                <div class="form-text mt-2 text-muted">
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    Les invitations seront dupliquées sur chaque créneau.
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="subBtn" class="btn btn-primary w-100 mt-3 fw-bold">Réserver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa-solid fa-calendar-check me-2"></i>Détails de la réservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Ressource</label>
                    <p id="modalResourceName" class="fw-bold mb-0"></p>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Organisateur</label>
                    <p id="modalOrganizerName" class="fw-bold mb-0"></p>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Période</label>
                    <p id="modalPeriod" class="mb-0"></p>
                </div>
                <hr>
                <div id="notificationOptions">
                    <div class="form-check mb-2" id="optionInvites">
                        <input class="form-check-input" type="checkbox" id="notifyInvites" checked>
                        <label class="form-check-label" for="notifyInvites">
                            Prévenir les participants par email
                        </label>
                    </div>
                    <div class="form-check text-danger fw-bold" id="optionSeries" style="display:none;">
                        <input class="form-check-input" type="checkbox" id="deleteAllSeries">
                        <label class="form-check-label" for="deleteAllSeries">
                            <i class="fa-solid fa-layer-group me-1"></i> Supprimer TOUTE la série récurrente
                        </label>
                    </div>
                    <hr id="seriesSeparator" style="display:none;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBookingBtn">Supprimer cette
                    réservation</button>
            </div>
        </div>
    </div>
</div>

<script>
    const resCapacities = <?= json_encode($capacities) ?>;
    const currentUserId = <?= $_SESSION['user_id'] ?>;
</script>
<script src="/workspace_connect/public/js/desk.js"></script>