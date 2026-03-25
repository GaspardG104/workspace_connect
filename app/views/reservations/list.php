<link rel="stylesheet" href="/workspace_connect/public/styles/style_list_reservations.css">

<div id="app-container" class="container-fluid py-4">
    <div class="text-center mb-4 text-white" >
        <h1><i class="fa-solid fa-list-check me-2"></i> Gestion des réservations</h2>
    </div>
    <div class="row g-3 mb-4 mt-2 p-3 bg-light rounded shadow-sm border">
        <div class="col-md-4">
            <label class="small fw-bold">Recherche instantanée</label>
            <input type="text" id="ajax-search" class="form-control shadow-sm" placeholder="Taper pour chercher (nom, invité, salle)...">
        </div>
        <div class="col-md-3">
            <label class="small fw-bold">Filtrer par date</label>
            <input type="date" id="ajax-date" class="form-control shadow-sm">
        </div>
        <div class="col-md-3">
            <label class="small fw-bold">Trier par</label>
            <select id="ajax-sort" class="form-select shadow-sm">
                <option value="date_debut">Date</option>
                <option value="user_nom">Compte</option>
                <option value="resource_nom">Ressource</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-secondary w-100 shadow-sm" onclick="resetFilters()">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Ressource</th>
                        <th>Date & Heure</th>
                        <th>Statut</th>
                        <?php if ($_SESSION['user_role'] == 1): ?>
                        <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="reservation-table-body">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<!--Pour la partie de message de suppression pour prévenirs les participants-->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fa-solid fa-exclamation-triangle me-2"></i>Confirmation de suppression</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="deleteMessage"></p>
        <div id="notificationOptions">
            <hr>
            <div class="form-check mb-2" id="optionInvites">
                <input class="form-check-input" type="checkbox" id="notifyInvites" checked>
                <label class="form-check-label" for="notifyInvites">
                    Prévenir les participants par email
                </label>
            </div>
            <div class="form-check" id="optionOrganizer">
                <input class="form-check-input" type="checkbox" id="notifyOrganizer" checked>
                <label class="form-check-label" for="notifyOrganizer">
                    Prévenir l'organisateur de l'annulation
                </label>
            </div>
            <div class="form-check mb-2 text-danger fw-bold" id="optionSeries" style="display:none;">
    <input class="form-check-input" type="checkbox" id="deleteAllSeries">
    <label class="form-check-label" for="deleteAllSeries">
        <i class="fa-solid fa-layer-group me-1"></i> Supprimer TOUTE la série récurrente
    </label>
</div>
<hr id="seriesSeparator" style="display:none;">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Confirmer la suppression</button>
      </div>
    </div>
  </div>
</div>

<script>
    const isAdmin = <?= $_SESSION['user_role'] == 1 ? 'true' : 'false' ?>;
</script>

<script src="/workspace_connect/public/js/list.js"></script>