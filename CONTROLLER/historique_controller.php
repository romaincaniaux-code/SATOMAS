<?php
// ! L'historique est réservé aux salariés
if ($_SESSION['utilisateur']['role'] != 'salarie') {
    header('Location: index.php?page=accueil');
    exit;
}

// ! Informations personnelles (déjà en session) et interventions terminées
$utilisateur = $_SESSION['utilisateur'];
$historique = obtenirHistorique($utilisateur['id_utilisateur']);

require 'VIEW/historique_vue.php';
?>