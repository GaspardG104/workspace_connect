</main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Pour les calendriers (attention ce n'est pas le m^me lien que celui au dessus même si ils se ressemblent) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php 
        // On définit le chemin vers le fichier chat.php
        $chatPath = __DIR__ . '/../reservations/chat.php'; 
        
        if (file_exists($chatPath)) {
            include $chatPath;
        }
    ?>

</body>
</html>