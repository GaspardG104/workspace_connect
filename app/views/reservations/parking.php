<link rel="stylesheet" href="/workspace_connect/public/styles/style_parking.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> <!--pour l'horloge-->

<body class="bg-light">

    <div class="container-fluid py-4">
        <div class="text-center mb-4">
            <h1 class="fw-bold text-white">Réserver ma place <i class="fa-solid fa-square-parking"></i></h1>
            <div id="ajax-message" class="fw-bold mt-2"></div>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fa-solid fa-map-location-dot me-2"></i> Sélectionnez une place</h5>
                <div class="parking-map">
                    <?php foreach($resources as $r): ?>
                        <div class="place-btn" id="btn-<?= $r['id'] ?>" onclick="selectPlace(<?= $r['id'] ?>, '<?= htmlspecialchars($r['nom']) ?>')">
                            <span class="place-name"><?= htmlspecialchars($r['nom']) ?></span>
                            <i class="fa-solid fa-car fa-xl mt-2"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 p-3">
                    <div id='calendar' style="display:none;"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 p-4" id="res-form-box" style="display:none;">
    <h4 id="selected-title" class="fw-bold text-primary mb-1">Place : P1</h4>
    <p id="display-date" class="text-muted fw-bold mb-4 small"></p>

    <form id="bookingForm">
        <input type="hidden" name="resource" id="resourceSelect" required>
        
        <input type="hidden" name="debut" id="finalDebut">
        <input type="hidden" name="fin" id="finalFin">

        <div class="mb-3">
            <label class="form-label small fw-bold">Heure de début</label>
            <div class="input-group">
                <input type="text" id="heureDebutInput" class="form-control" readonly>
                <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Heure de fin</label>
            <div class="input-group">
                <input type="text" id="heureFinInput" class="form-control" readonly>
                <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
            </div>
        </div>

        <div class="mb-3 p-3 border rounded bg-light">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" value="true">
                <label class="form-check-label fw-bold" for="is_recurring">
                    <i class="fa-solid fa-repeat me-1"></i> Répéter cette réservation
                </label>
            </div>
            
            <div id="recurrence-options" class="mt-3" style="display:none">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="small fw-bold">Fréquence</label>
                        <select name="recurrence_type" id="recurrence_type" class="form-select form-select-sm">
                            <option value="WEEKLY">Toutes les semaines</option>
                            <option value="DAILY">Tous les jours</option>
                            <option value="MONTHLY">Tous les mois</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label id="label-count" class="small fw-bold">Pendant combien de fois ?</label>
                        <input type="number" name="recurrence_count" id="recurrence_count" class="form-select form-select-sm" value="1" min="1">
                    </div>
                </div>
                <div class="form-text mt-2 text-muted">
                    <i class="fa-solid fa-circle-info me-1"></i> 
                    Les jours du samedi et dimanche seront automatiquement ignorés.
                </div>
            </div>
        </div>

        <button type="submit" id="submitBtn" class="btn btn-success w-100 fw-bold py-2">
            Confirmer la réservation
        </button>
    </form>
</div>
            </div>
        </div>
    </div>
    
<script src="/workspace_connect/public/js/parking.js?v=<?= time(); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> <!--pour l'horloge-->

<!-- Modal pour les détails de réservation et suppression -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa-solid fa-calendar-check me-2"></i>Détails de la réservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Place de parking</label>
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
                <button type="button" class="btn btn-danger" id="confirmDeleteBookingBtn">Supprimer cette réservation</button>
            </div>
        </div>
    </div>
</div>

<script>
    const currentUserId = <?= $_SESSION['user_id'] ?>;
</script>