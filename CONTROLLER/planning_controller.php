<?php
// ! Heure de Paris pour comparer correctement les horaires
date_default_timezone_set('Europe/Paris');

// ! Le planning est réservé aux salariés
if ($_SESSION['utilisateur']['role'] != 'salarie') {
    header('Location: index.php?page=accueil');
    exit;
}

$idUtilisateur = $_SESSION['utilisateur']['id_utilisateur'];
$message = '';

// ! Jour demandé dans l'adresse (aujourd'hui par défaut)
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (strtotime($date) === false) {
    $date = date('Y-m-d');
}
$date = date('Y-m-d', strtotime($date));

// ! Si le salarié a cliqué sur « Annuler »
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['annuler'])) {
    $resultat = annulerIntervention($idUtilisateur, (int) $_POST['id_intervention']);

    if ($resultat === true) {
        header('Location: index.php?page=planning&date=' . $date . '&ok=1');
        exit;
    } else {
        $message = $resultat;
    }
}

// ! Données pour la vue
$planning = obtenirPlanning($idUtilisateur, $date);
$jourPrecedent = date('Y-m-d', strtotime($date . ' -1 day'));
$jourSuivant = date('Y-m-d', strtotime($date . ' +1 day'));
$maintenant = date('Y-m-d H:i:s');

require 'VIEW/planning_vue.php';
?>