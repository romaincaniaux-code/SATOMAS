<?php
// ! Nombre d'interventions affichées par page
$parPage = 12;

// ! Lecture des filtres envoyés par le formulaire (vides si absents)
$motCle = isset($_GET['motcle']) ? trim($_GET['motcle']) : '';
$idCategorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';

// ! Numéro de la page demandée (page 1 par défaut)
// ! Attention : on n'utilise pas $page, déjà utilisée par index.php et par le menu
$pageCourante = isset($_GET['p']) ? (int) $_GET['p'] : 1;

// ! Toutes les interventions correspondant aux filtres, puis leur nombre et le nombre de pages
$toutes = obtenirInterventions($motCle, $idCategorie, $date);
$total = count($toutes);
$nbPages = (int) ceil($total / $parPage);
if ($nbPages < 1) {
    $nbPages = 1;
}

// ! On garde le numéro de page entre 1 et le nombre de pages
if ($pageCourante < 1) {
    $pageCourante = 1;
}
if ($pageCourante > $nbPages) {
    $pageCourante = $nbPages;
}

// ! Numéro de la première ligne de cette page (la page 1 commence à 0)
$depart = ($pageCourante - 1) * $parPage;

// ! On garde seulement les interventions de cette page
$interventions = array_slice($toutes, $depart, $parPage);
$categories = obtenirCategories();

// ! Filtres à remettre dans les liens de pagination (pour ne pas perdre la recherche)
$filtres = array();
if ($motCle != '') {
    $filtres['motcle'] = $motCle;
}
if ($idCategorie != '') {
    $filtres['categorie'] = $idCategorie;
}
if ($date != '') {
    $filtres['date'] = $date;
}
$filtresUrl = http_build_query($filtres);
if ($filtresUrl != '') {
    $filtresUrl = '&' . $filtresUrl;
}

// ! On affiche la vue
require 'VIEW/catalogue_vue.php';
?>