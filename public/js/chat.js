function sendMessage() {
    const input = document.getElementById('user-input');
    const chatBody = document.getElementById('chat-body');
    const message = input.value;

    // Afficher le message utilisateur
    chatBody.innerHTML += `<div class="text-end mb-2"><span class="bg-light p-2 rounded shadow-sm">${message}</span></div>`;
    input.value = '';

    fetch('/workspace_connect/chat/process', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ message: message })
    })
    .then(res => res.json())
    .then(data => {
        // Afficher la réponse de l'IA
        chatBody.innerHTML += `<div class="text-start mb-2"><span class="bg-primary text-white p-2 rounded shadow-sm">${data.reponse}</span></div>`;
        
        // Si l'IA dit que c'est complet, on peut proposer un bouton "Confirmer la réservation"
        if (data.complet) {
            chatBody.innerHTML += `<div class="text-center"><button class="btn btn-sm btn-success">Confirmer la réservation</button></div>`;
        }
        chatBody.scrollTop = chatBody.scrollHeight;
    });
}