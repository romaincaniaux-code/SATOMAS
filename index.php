<?php
// ! Point d'entrée unique : toutes les pages passent par ici
session_start();
include'MODEL/gestion_bdd.php';

// ! Page demandée dans l'adresse (connexion par défaut)
$page = isset($_GET['page']) ? $_GET['page'] : 'connexion';

// ! Si personne n'est connecté, seule la page de connexion est accessible
if (!isset($_SESSION['utilisateur']) && $page != 'connexion') {
    $page = 'connexion';
}

// ! On appelle le contrôleur qui correspond à la page
switch ($page) {
    case 'connexion':
        require 'CONTROLLER/connexion_controller.php';
        break;

    case 'catalogue':
        require 'CONTROLLER/catalogue_controller.php';
        break;

    case 'deconnexion':
        include 'MODEL/deconnexion.php';
        break;

    case 'fiche':
        require 'CONTROLLER/fiche_controller.php';
        break;
        
    default:
        include 'CONTROLLER/accueil_controller.php';
        break;
}
?>