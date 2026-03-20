// 1. Gérer l'ouverture/fermeture du chat
function toggleChat() {
    const chatCard = document.getElementById('chat-card');
    if (chatCard.style.display === "none") {
        chatCard.style.display = "block";
    } else {
        chatCard.style.display = "none";
    }
}

// 2. Envoyer le message
function sendMessage() {
    const input = document.getElementById('user-input');
    const chatBody = document.getElementById('chat-body');
    const message = input.value.trim();

    if (!message) return;

    chatBody.innerHTML += `<div class="text-end mb-2"><span class="bg-light p-2 rounded shadow-sm d-inline-block">${message}</span></div>`;
    input.value = '';
    chatBody.scrollTop = chatBody.scrollHeight;

    fetch('/workspace_connect/chat/process', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ message: message })
    })
    .then(res => res.json())
    .then(data => {
        // Gemini peut parfois renvoyer du texte avec des ```json ... ```
        // On essaie de nettoyer si nécessaire
        let responseText = data.reponse || data; 
        chatBody.innerHTML += `<div class="text-start mb-2"><span class="bg-primary text-white p-2 rounded shadow-sm d-inline-block">${responseText}</span></div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    })
    .catch(err => {
        console.error("Erreur:", err);
        chatBody.innerHTML += `<div class="text-start mb-2"><span class="bg-danger text-white p-2 rounded shadow-sm d-inline-block">L'IA n'a pas pu répondre.</span></div>`;
    });
}
// 3. Écouter la touche "Entrée"
document.addEventListener('DOMContentLoaded', function() {
    const userInput = document.getElementById('user-input');
    if (userInput) {
        userInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
});