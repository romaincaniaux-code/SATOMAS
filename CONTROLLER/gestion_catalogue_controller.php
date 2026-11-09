<?php
// ! Page réservée au gestionnaire et à l'administrateur
$role = $_SESSION['utilisateur']['role'];
if ($role != 'gestionnaire' && $role != 'administrateur') {
    header('Location: index.php?page=accueil');
    exit;
}

$erreur = '';
$interventionSuppr = false;

// ! Suppression confirmée
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supprimer'])) {
    $resultat = supprimerIntervention((int) $_POST['id_intervention']);

    if ($resultat === true) {
        header('Location: index.php?page=gestion_catalogue&ok=suppr');
        exit;
    } else {
        $erreur = $resultat;
    }
}

// ! Clic sur « Supprimer » : on demande confirmation
if (isset($_GET['supprimer'])) {
    $interventionSuppr = obtenirInterventionParId((int) $_GET['supprimer']);
}

$parPage = 12;
$pageCourante = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;

$toutes = obtenirInterventionsGestion();
$total = count($toutes);
$nbPages = max(1, (int) ceil($total / $parPage));
$pageCourante = min($pageCourante, $nbPages);

$interventions = array_slice($toutes, ($pageCourante - 1) * $parPage, $parPage);

require 'VIEW/gestion_catalogue_vue.php';
?>