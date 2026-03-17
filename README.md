# workspace_connect
Project Workspace Connect for COGNIZANT 

Kanban link : https://padlet.com/gaspardguidetti/workspace-connect-uz8av0q98dj5marw

Dossier de travail :
/workspace-connect
  /public           <-- Point d'entrée (index.php, css, images)
  /src              <-- La logique PHP (Classes, Fonctions)
  /config           <-- Connexion DB et clés API
  /vendor           <-- Bibliothèques installées par Composer


  workspace_connect/
├── app/                # Le cœur de l'application (PHP pur)
│   ├── Controllers/    # Reçoivent les requêtes, appellent les Modèles
│   │   ├── AuthController.php
│   │   ├── BookingController.php
│   │   └── UserController.php
│   ├── Models/         # Seuls fichiers qui touchent à la base de données
│   │   ├── Booking.php
│   │   ├── Resource.php
│   │   └── User.php
│   └── Core/           # Mini-moteur (Router, Database connection)
│       ├── Database.php
│       └── Router.php
├── config/             # Configuration (db_config.php, etc.)
├── public/             # LE SEUL DOSSIER ACCESSIBLE PAR LE NAVIGATEUR
│   ├── css/            # Les fichiers .css (main_theme.css, style_desks.css)
│   ├── js/             # Le JavaScript (extrait de tes fichiers PHP)
│   ├── img/            # Les images
│   └── index.php       # Le "Front Controller" (point d'entrée unique)
├── views/              # Les fichiers HTML (avec un peu de PHP pour les variables)
│   ├── auth/           # login.php, inscription.php
│   ├── booking/        # reserver.php (générique pour desks et parking)
│   ├── user/           # account.php
│   └── partials/       # navbar.php, header.php, footer.php
└── vendor/             # Pour l'autoloader (si tu utilises Composer)