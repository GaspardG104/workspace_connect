<link rel="stylesheet" href="/workspace_connect/public/styles/style_parking.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> <!--pour l'horloge-->

<body class="bg-light">

    <div class="container">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Réserver ma place <i class="fa-solid fa-square-parking text-primary"></i></h1>
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

        <button type="submit" id="submitBtn" class="btn btn-success w-100 fw-bold py-2">
            Confirmer la réservation
        </button>
    </form>
</div>
            </div>
        </div>
    </div>
    
<script src="/workspace_connect/public/js/parking.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> <!--pour l'horloge-->