<div id="chat-wrapper" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; width: 350px;">
    <div class="card shadow" id="chat-card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-robot me-2"></i>Assistant IA</span>
            <button class="btn-close btn-close-white" onclick="toggleChat()"></button>
        </div>
        <div id="chat-body" class="card-body overflow-auto" style="height: 300px; background-color: #f8f9fa;">
            <p class="small text-muted">
                Bonjour <?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur') ?>, que puis-je réserver pour vous ?
            </p>
        </div>
        <div class="card-footer">
            <div class="input-group">
                <input type="text" id="user-input" class="form-control" placeholder="Réserve la salle 1 demain...">
                <button class="btn btn-primary" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<script src="/workspace_connect/public/js/chat.js"></script>