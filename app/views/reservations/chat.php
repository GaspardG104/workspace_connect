<div id="chat-wrapper" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    
    <div id="chat-launcher" class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
         style="width: 60px; height: 60px; cursor: pointer; float: right;" onclick="toggleChat()">
        <i class="fa-solid fa-comments fs-3"></i>
    </div>

    <div class="card shadow-lg" id="chat-card" style="display: none; border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-robot me-2"></i>Assistant IA</span>
            <button class="btn btn-sm text-white" onclick="toggleChat()">
                <i class="fa-solid fa-chevron-down"></i>
            </button>
        </div>
        <div id="chat-body" class="card-body overflow-auto" style="height: 350px; background-color: #f8f9fa;">
            <p class="small text-muted">
                Bonjour <?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur') ?>, comment puis-je vous aider ?
            </p>
        </div>
        <div class="card-footer bg-white border-0">
            <div class="input-group">
                <input type="text" id="user-input" class="form-control" placeholder="Posez votre question...">
                <button class="btn btn-primary" onclick="sendMessage()">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="/workspace_connect/public/styles/style_chat.css">
<script src="/workspace_connect/public/js/chat.js"></script>