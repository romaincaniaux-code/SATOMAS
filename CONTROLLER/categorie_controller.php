<?php
// ! Page réservée au gestionnaire et à l'administrateur
$role = $_SESSION['utilisateur']['role'];
if ($role != 'gestionnaire' && $role != 'administrateur') {
    header('Location: index.php?page=accueil');
    exit;
}

$erreur = '';
$categorieModif = false;
$categorieSuppr = false;

// ! Valeurs du formulaire (vides si absentes)
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ! Ajout d'une catégorie
    if (isset($_POST['ajouter'])) {
        if ($nom == '') {
            $erreur = 'Le nom est obligatoire.';
        } else {
            ajouterCategorie($nom, $description);
            header('Location: index.php?page=categories&ok=ajout');
            exit;
        }
    }

    // ! Modification d'une catégorie
    if (isset($_POST['modifier'])) {
        $id = (int) $_POST['id_categorie'];
        if ($nom == '') {
            $erreur = 'Le nom est obligatoire.';
            $categorieModif = obtenirCategorieParId($id);
        } else {
            modifierCategorie($id, $nom, $description);
            header('Location: index.php?page=categories&ok=modif');
            exit;
        }
    }

    // ! Suppression confirmée
    if (isset($_POST['supprimer'])) {
        $resultat = supprimerCategorie((int) $_POST['id_categorie']);
        if ($resultat === true) {
            header('Location: index.php?page=categories&ok=suppr');
            exit;
        } else {
            $erreur = $resultat;
        }
    }
}

// ! Clic sur « Modifier » : on remplit le formulaire
if (isset($_GET['modifier']) && !$categorieModif) {
    $categorieModif = obtenirCategorieParId((int) $_GET['modifier']);
}

// ! Clic sur « Supprimer » : on demande confirmation
if (isset($_GET['supprimer'])) {
    $categorieSuppr = obtenirCategorieParId((int) $_GET['supprimer']);
}

$categories = obtenirCategories();

require 'VIEW/categorie_vue.php';
?>