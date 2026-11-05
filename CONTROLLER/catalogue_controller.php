<?php
// ! Lecture des filtres envoyés par le formulaire (vides si absents)
$motCle = isset($_GET['motcle']) ? trim($_GET['motcle']) : '';
$idCategorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';

// ! On demande les données au modèle
$interventions = obtenirInterventions($motCle, $idCategorie, $date);
$categories = obtenirCategories();

// ! On affiche la vue (une seule fois)
require 'VIEW/catalogue_vue.php';
?>