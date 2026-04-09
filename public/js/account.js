let bookingToDelete = null;

// Fonctions Immatriculation
function toggleImmatEdit() {
    const text = document.getElementById('immat-container');
    const edit = document.getElementById('immat-edit');
    if (edit.style.display === 'none') {
        edit.style.display = 'block';
        text.style.display = 'none';
    } else {
        edit.style.display = 'none';
        text.style.display = 'flex';
    }
}

function saveImmat() {
    const val = document.getElementById('immat-input').value;
    const formData = new FormData();
    formData.append('immatriculation', val);

    fetch('/workspace_connect/user/update_immat', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('immat-text').innerText = val || 'Non renseignée';
            toggleImmatEdit();
        } else {
            alert(data.message);
        }
    });
}

// Fonctions Suppression
function prepareDelete(id, isSeries) {
    bookingToDelete = id;
    document.getElementById('seriesOption').style.display = isSeries ? 'block' : 'none';
    document.getElementById('deleteAllSeries').checked = false;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').onclick = function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    // UI Loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    const formData = new FormData();
    formData.append('notifyInvites', document.getElementById('notifyInvites').checked);
    formData.append('deleteAllSeries', document.getElementById('deleteAllSeries').checked);

    fetch(`/workspace_connect/reservations/delete/${bookingToDelete}`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // SUPPRESSION VISUELLE DIRECTE
            const row = document.getElementById(`booking-row-${bookingToDelete}`);
            if (row) {
                row.remove();
                
                // Si plus de lignes, afficher le message "vide"
                const tbody = document.querySelector('#bookingTable tbody');
                if (tbody.querySelectorAll('tr:not(.no-data)').length === 0) {
                    tbody.innerHTML = '<tr class="no-data"><td colspan="3" class="text-center py-5 text-muted">Aucune réservation trouvée.</td></tr>';
                }
            }
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        } else {
            alert("Erreur : " + data.message);
        }
    })
    .catch(() => alert("Erreur technique"))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
};