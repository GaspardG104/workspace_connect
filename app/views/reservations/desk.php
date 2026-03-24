<link rel="stylesheet" href="/workspace_connect/public/styles/style_desks.css">

<div id="ajax-message" class="alert shadow-lg"></div>

<div class="container-fluid py-4">
    <div class="text-center mb-4 text-white" >
        <h1><i class="fa-solid fa-list-check me-2"></i> Réserver un bureau, salle de réunion, box</h2>
    </div>
    <div class="row g-4 transition-layout justify-content-center" id="layout-wrapper">
        <div class="col-lg-7 transition-all" id="plan-column">
            <div class="card shadow p-3 border-0">
                <div class="plan-container">
                    <button class="btn-meeting meeting-1" onclick="selectResource(<?= $res_map['S1'] ?? 0 ?>, 'Salle 1', this)">Salle de réunion 1</button>
                    <button class="btn-meeting meeting-2" onclick="selectResource(<?= $res_map['S2'] ?? 0 ?>, 'Salle 2', this)">Salle de réunion 2</button>

                    <div class="desk-group-v v-desk-1">
                        <?php for($i=1; $i<=6; $i++): $name="I1B$i"; ?>
                            <button class="desk-unit" onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B<?= $i ?></button>
                        <?php endfor; ?>
                    </div>
                    <div class="desk-group-v v-desk-2">
                        <?php for($i=1; $i<=6; $i++): $name="I2B$i"; ?>
                            <button class="desk-unit" onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B<?= $i ?></button>
                        <?php endfor; ?>
                    </div>

                    <?php for($h=1; $h<=4; $h++): ?>
                        <div class="desk-group-h h-<?= $h ?>">
                            <?php for($i=1; $i<=6; $i++): $numIlot = $h+2; $name="I{$numIlot}B$i"; ?>
                                <button class="desk-unit" onclick="selectResource(<?= $res_map[$name] ?? 0 ?>, '<?= $name ?>', this)">B<?= $i ?></button>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>

                    <button class="btn-box box-1" onclick="selectResource(<?= $res_map['B1'] ?? 0 ?>, 'Box 1', this)">Box 1</button>
                    <button class="btn-box box-2" onclick="selectResource(<?= $res_map['B2'] ?? 0 ?>, 'Box 2', this)">Box 2</button>
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
                        <div id="invite-section" class="mt-3" style="display:none;">
                            <label class="small fw-bold text-muted mb-2">
                                Inviter des collègues (Capacité max : <span id="cap-val"></span>)
                            </label>                          
                            <div id="tags-container" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <div class="dropdown">
                                <input type="text" id="userSearch" class="form-control" placeholder="Rechercher un collègue..." autocomplete="off">
                                <ul id="userSuggestions" class="dropdown-menu w-100 shadow-sm" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach($all_users as $u): ?>
                                        <li>
                                            <a class="dropdown-item user-option" href="#" 
                                            data-id="<?= $u['id'] ?>" 
                                            data-name="<?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>">
                                                <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>                            
                            <div id="hidden-inputs"></div>
                        </div>
                        <button type="submit" id="subBtn" class="btn btn-primary w-100 mt-3 fw-bold">Réserver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: { 
            left: 'prev,next today', 
            center: 'title', 
            right: 'dayGridMonth,timeGridWeek' 
        },
        selectable: true,
        select: function(info) {
            document.getElementById('startInput').value = info.startStr + "T09:00";
            let endDate = new Date(info.end);
            
            // Si c'est une sélection sur la vue Mois (allDay), FullCalendar donne le lendemain à 00:00.
            // On recule d'un jour pour revenir au jour réel de fin de réservation.
            if (info.allDay) {
                endDate.setDate(endDate.getDate() - 1);
            }

            // On formate la date au format YYYY-MM-DD
            let day = endDate.getDate().toString().padStart(2, '0');
            let month = (endDate.getMonth() + 1).toString().padStart(2, '0');
            let year = endDate.getFullYear();
            
            let formattedEndDate = `${year}-${month}-${day}T18:00`;
            document.getElementById('endInput').value = formattedEndDate;
        }
    });
    calendar.render();
});

const resCapacities = <?= json_encode($capacities) ?>;

// La fonction est maintenant bien définie au niveau global
function selectResource(id, nom, el) {
    if(!id) { 
        showMsg("Ressource non trouvée en BDD", false); 
        return; 
    }

    // 1. Gérer la sélection visuelle (couleur du bureau)
    document.querySelectorAll('.selected-resource').forEach(b => b.classList.remove('selected-resource'));
    el.classList.add('selected-resource');

    // 2. Afficher le bloc de réservation et déclencher l'animation de décalage
    const calendarCol = document.getElementById('calendar-column');
    const bookingUi = document.getElementById('booking-ui');
    const layoutWrapper = document.getElementById('layout-wrapper');

    // Gestion de la capacité et des invités
    const inviteSection = document.getElementById('invite-section');
    const capDisplay = document.getElementById('cap-val');
    const capaciteMax = resCapacities[id] || 1;

    if (capaciteMax > 1) {
        inviteSection.style.display = 'block';
        capDisplay.innerText = capaciteMax;
    } else {
        inviteSection.style.display = 'none';
    }
    
    bookingUi.classList.remove('refresh-anim'); //on enlève l'animation pour la calendrier si elle y était déjà

    // affichage des conteneurs
    calendarCol.style.display = 'block'; // On affiche la colonne parent
    bookingUi.style.display = 'block';     // On affiche l'UI interne

    
    void bookingUi.offsetWidth;     // On force un petit "recalcul" pour que le navigateur voie le changement
    bookingUi.classList.add('refresh-anim');

    // 3. Mettre à jour les informations du formulaire
    document.getElementById('display-name').innerText = "Poste : " + nom;
    document.getElementById('res_id').value = id;

    // 4. Charger les événements et forcer le calendrier à recalculer sa taille
    calendar.setOption('events', '/workspace_connect/reservation/getEvents?id_resource=' + id);
    calendar.refetchEvents();
    
    // TRÈS IMPORTANT : On attend un court instant que l'animation commence 
    // pour que FullCalendar ajuste sa largeur, sinon il reste invisible ou buggé.
    setTimeout(() => {
        layoutWrapper.classList.add('active');
        // On attend un peu plus (500ms au lieu de 200ms) pour que 
        // l'espace soit suffisant avant de dessiner le calendrier
        setTimeout(() => {
            calendar.updateSize();
        }, 500); 
    }, 10);
}

function showMsg(txt, isSuccess) {
    const m = document.getElementById('ajax-message');
    if(!m) return;
    m.innerHTML = txt;
    m.className = "alert shadow-lg " + (isSuccess ? "alert-success" : "alert-danger");
    m.style.display = "block";
    setTimeout(() => { m.style.display = "none"; }, 4000);
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('subBtn');
    btn.disabled = true;

    fetch('/workspace_connect/reservation/store', { method: 'POST', body: new FormData(this) })
    .then(r => r.json())
    .then(data => {
        showMsg(data.message, data.success);
        if(data.success) { 
            calendar.refetchEvents(); 
            this.reset(); 
        }
    })
    .finally(() => { btn.disabled = false; });
});

const userSearch = document.getElementById('userSearch');
const userSuggestions = document.getElementById('userSuggestions');
const tagsContainer = document.getElementById('tags-container');
const hiddenInputs = document.getElementById('hidden-inputs');
let selectedUsers = [];

// Filtrer la liste pendant la frappe
userSearch.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    const items = document.querySelectorAll('.user-option');
    let hasResults = false;

    items.forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        if (name.includes(term) && term.length > 0) {
            item.parentElement.style.display = 'block';
            hasResults = true;
        } else {
            item.parentElement.style.display = 'none';
        }
    });

    userSuggestions.classList.toggle('show', hasResults);
});

// Ajouter un invité lors du clic sur un nom
document.querySelectorAll('.user-option').forEach(option => {
    option.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const capaciteMax = parseInt(document.getElementById('cap-val').innerText) || 1;

        // Vérification : pas déjà ajouté et respect de la capacité (soi-même + invités)
        if (!selectedUsers.includes(id)) {
            if (selectedUsers.length + 1 < capaciteMax) {
                addTag(id, name);
            } else {
                alert("Capacité maximale de la salle atteinte !");
            }
        }

        userSearch.value = '';
        userSuggestions.classList.remove('show');
    });
});

function addTag(id, name) {
    selectedUsers.push(id);
    
    // Créer le tag visuel
    const tag = document.createElement('div');
    tag.className = 'invite-tag';
    tag.innerHTML = `${name} <span class="remove-invite" onclick="removeTag('${id}', this)">&times;</span>`;
    tagsContainer.appendChild(tag);

    // Créer l'input caché pour le formulaire
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'invites[]';
    input.value = id;
    input.id = 'input-invite-' + id;
    hiddenInputs.appendChild(input);
}

function removeTag(id, element) {
    selectedUsers = selectedUsers.filter(uid => uid !== id);
    element.parentElement.remove();
    document.getElementById('input-invite-' + id).remove();
}

// Fermer la liste si on clique ailleurs
document.addEventListener('click', (e) => {
    if (!userSearch.contains(e.target)) userSuggestions.classList.remove('show');
});


</script>
