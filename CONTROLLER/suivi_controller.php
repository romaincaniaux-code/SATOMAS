<?php
// ! Page réservée au gestionnaire et à l'administrateur
$role = $_SESSION['utilisateur']['role'];
if ($role != 'gestionnaire' && $role != 'administrateur') {
    header('Location: index.php?page=accueil');
    exit;
}

// ! Lecture des filtres (vides si absents)
$date = isset($_GET['date']) ? $_GET['date'] : '';
$idSalarie = isset($_GET['salarie']) ? $_GET['salarie'] : '';

// ! Toutes les interventions (filtrées par date)
$interventions = obtenirSuivi($date);
$salaries = obtenirSalaries();

$suivi = array();
$nbAPourvoir = 0;

foreach ($interventions as $ligne) {
    // ! On cherche le salarié affecté à cette intervention
    $salarie = obtenirSalarieAffecte($ligne['id_intervention']);

    if ($salarie) {
        $ligne['id_utilisateur'] = $salarie['id_utilisateur'];
        $ligne['nom'] = $salarie['nom'];
        $ligne['prenom'] = $salarie['prenom'];
    } else {
        $ligne['id_utilisateur'] = null;
        $ligne['nom'] = null;
        $ligne['prenom'] = null;
    }

    // ! Filtre par salarié : on garde la ligne seulement si c'est le bon salarié
    if ($idSalarie == '' || $ligne['id_utilisateur'] == $idSalarie) {
        $suivi[] = $ligne;

        // ! On compte les interventions encore à pourvoir
        if ($ligne['statut'] == 'a_pourvoir') {
            $nbAPourvoir = $nbAPourvoir + 1;
        }
    }
}


$parPage = 12;
$pageCourante = isset($_GET['p']) ? (int) $_GET['p'] : 1;

$total = count($suivi);
$nbPages = max(1, (int) ceil($total / $parPage));

if ($pageCourante < 1) {
    $pageCourante = 1;
}
if ($pageCourante > $nbPages) {
    $pageCourante = $nbPages;
}

$depart = ($pageCourante - 1) * $parPage;
$suivi = array_slice($suivi, $depart, $parPage);

$filtres = array();
if ($date != '') {
    $filtres['date'] = $date;
}
if ($idSalarie != '') {
    $filtres['salarie'] = $idSalarie;
}

$filtresUrl = http_build_query($filtres);
if ($filtresUrl != '') {
    $filtresUrl = '&' . $filtresUrl;
}

require 'VIEW/suivi_vue.php';
?>