<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace Connect - Accueil</title>
    <link rel="stylesheet" href="/workspace_connect/public/styles/main_theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/workspace_connect/public/styles/calendar.css">

<!-- Pour les calendriers (je les avaient oublier et j'ai passser 1h a me demander pourquoi ça marcher pas comme un con) -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        #ajax-message {
            max-width: 90%;
            word-wrap: break-word;
            white-space: normal;
            position: static !important;
            top: auto !important;
            right: auto !important;
            min-width: auto !important;
        }
    </style>


</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <main>