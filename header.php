<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SATOMAS</title>
</head>
<body>
<?php if (isset($_SESSION['utilisateur'])) { ?>
    <nav>
        <a href="index.php?page=accueil">Accueil</a> |
        <a href="index.php?page=deconnexion">Déconnexion</a>
    </nav>
<?php } ?>