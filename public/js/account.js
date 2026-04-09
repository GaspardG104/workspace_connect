// Variable globale
window.bookingToDelete = null;

/**
 * 1. GESTION DE L'IMMATRICULATION
 */
window.toggleImmatEdit = function() {
    const textSpan = document.getElementById('immat-text');
    const input = document.getElementById('immat-input');
    const btnEdit = document.getElementById('btn-edit-immat');
    const actions = document.getElementById('immat-actions');

    if (input.style.display === 'none') {
        input.style.display = 'block';
        actions.style.display = 'block';
        textSpan.style.display = 'none';
        btnEdit.style.display = 'none';
    } else {
        input.style.display = 'none';
        actions.style.display = 'none';
        textSpan.style.display = 'block';
        btnEdit.style.display = 'block';
    }
};

window.saveImmat = function() {
    const input = document.getElementById('immat-input');
    const val = input.value;
    const formData = new FormData();
    formData.append('immatriculation', val);

    fetch('/workspace_connect/user/update_immat', { 
        method: 'POST', 
        body: formData 
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('immat-text').innerText = val || 'Aucune (pas de véhicule)';
            document.getElementById('immat-text').classList.toggle('text-muted', !val);
            window.toggleImmatEdit();
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert("Erreur lors de la communication avec le serveur"));
};

/**
 * 2. GESTION DE LA SUPPRESSION (Correction Suppression Multiple)
 */
window.prepareDelete = function(id, name, type, id_series) {
    window.bookingToDelete = id;
    
    // Correction : on s'assure que id_series est bien traité comme un nombre ou null
    const hasSeries = (id_series !== null && id_series !== undefined && id_series !== 'null');
    
    const optionSeries = document.getElementById('optionSeries');
    if (optionSeries) optionSeries.style.display = hasSeries ? 'block' : 'none';

    const checkAllSeries = document.getElementById('deleteAllSeries');
    if (checkAllSeries) checkAllSeries.checked = false;

    const msg = document.getElementById('deleteMessage');
    if (msg) msg.textContent = `Voulez-vous vraiment annuler la réservation "${name}" ?`;

    const modalEl = document.getElementById('deleteModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
};

/**
 * INITIALISATION AU CHARGEMENT
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Boutons Immat
    const btnEditImmat = document.getElementById('btn-edit-immat');
    if (btnEditImmat) btnEditImmat.onclick = window.toggleImmatEdit;

    const btnCancelImmat = document.getElementById('btn-cancel-immat');
    if (btnCancelImmat) btnCancelImmat.onclick = window.toggleImmatEdit;

    const btnSaveImmat = document.getElementById('btn-save-immat');
    if (btnSaveImmat) btnSaveImmat.onclick = window.saveImmat;

    // Confirmation Suppression (FIX Suppression Multiple Visuelle)
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.onclick = function() {
            if (!window.bookingToDelete) return;
            
            const btn = this;
            const originalText = btn.innerHTML;
            const isDeletingSeries = document.getElementById('deleteAllSeries').checked;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const formData = new FormData();
            formData.append('notifyInvites', document.getElementById('notifyInvites').checked);
            formData.append('deleteAllSeries', isDeletingSeries);

            fetch(`/workspace_connect/reservations/delete/${window.bookingToDelete}`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (isDeletingSeries) {
                        // SI SUPPRESSION MULTIPLE : On recharge pour nettoyer tout le tableau proprement
                        window.location.reload();
                    } else {
                        // SI SUPPRESSION UNIQUE : On enlève juste la ligne
                        const row = document.getElementById(`booking-row-${window.bookingToDelete}`);
                        if (row) row.remove();
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    }
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        };
    }

    // 3. FIX MOT DE PASSE (Vérification de l'existence du formulaire)
    const pwdForm = document.getElementById('updatePwdForm');
    if (pwdForm) {
        pwdForm.onsubmit = function(e) {
            e.preventDefault();
            const feedback = document.getElementById('pwd-feedback');
            const formData = new FormData(this);

            fetch('/workspace_connect/user/update_password', { 
                method: 'POST', 
                body: formData 
            })
            .then(r => r.json())
            .then(data => {
                feedback.style.display = 'block';
                feedback.className = 'mt-3 alert ' + (data.success ? 'alert-success' : 'alert-danger');
                feedback.textContent = data.message;
                if (data.success) pwdForm.reset();
            })
            .catch(err => {
                feedback.style.display = 'block';
                feedback.className = 'mt-3 alert alert-danger';
                feedback.textContent = "Erreur technique lors de la mise à jour.";
            });
        };
    }
});