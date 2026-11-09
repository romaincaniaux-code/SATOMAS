<?php
// ! Page réservée au gestionnaire et à l'administrateur
$role = $_SESSION['utilisateur']['role'];
if ($role != 'gestionnaire' && $role != 'administrateur') {
    header('Location: index.php?page=accueil');
    exit;
}

$erreur = '';
$categories = obtenirCategories();
$avions = obtenirAvions();

// ! Valeurs du formulaire (vides par défaut)
$intitule = '';
$description = '';
$idCategorie = 0;
$idAvion = 0;
$date = '';
$heureDebut = '';
$heureFin = '';
$lieu = '';
$misEnAvant = 0;
$imageActuelle = '';

// ! Mode modification si un numéro est donné dans l'adresse
$idModif = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idModif > 0) {
    $existante = obtenirInterventionBrute($idModif);

    // ! Si l'intervention n'existe pas, retour à la gestion du catalogue
    if (!$existante) {
        header('Location: index.php?page=gestion_catalogue');
        exit;
    }

    // ! On remplit le formulaire avec les valeurs de la base
    $intitule = $existante['intitule'];
    $description = $existante['description'];
    $idCategorie = $existante['id_categorie'];
    $idAvion = $existante['id_avion'];
    $date = $existante['date_intervention'];
    $heureDebut = substr($existante['heure_debut'], 0, 5);
    $heureFin = substr($existante['heure_fin'], 0, 5);
    $lieu = $existante['lieu'];
    $misEnAvant = $existante['mis_en_avant'];
    $imageActuelle = $existante['image'];
}

// ! Envoi du formulaire (ajout ou modification)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enregistrer'])) {
    $intitule = trim($_POST['intitule']);
    $description = trim($_POST['description']);
    $idCategorie = (int) $_POST['id_categorie'];
    $idAvion = (int) $_POST['id_avion'];
    $date = $_POST['date'];
    $heureDebut = $_POST['heure_debut'];
    $heureFin = $_POST['heure_fin'];
    $lieu = trim($_POST['lieu']);
    $misEnAvant = isset($_POST['mis_en_avant']) ? 1 : 0;

    // ! Par défaut, on garde l'image actuelle (vide si ajout)
    $nomImage = $imageActuelle;
    $nouvelleImage = '';

    // ! Contrôles des champs obligatoires
    if ($intitule == '' || $lieu == '' || $date == '' || $heureDebut == '' || $heureFin == '' || $idCategorie == 0 || $idAvion == 0) {
        $erreur = 'Tous les champs sont obligatoires, sauf la description et l’image.';
    } elseif ($heureFin <= $heureDebut) {
        $erreur = 'L’heure de fin doit être après l’heure de début.';
    }

    // ! Contrôles et enregistrement de la nouvelle image (facultative)
    if ($erreur == '' && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $extensionsAutorisees = array('jpg', 'jpeg', 'png', 'webp');

        if (!in_array($extension, $extensionsAutorisees)) {
            $erreur = 'L’image doit être au format jpg, png ou webp.';
        } elseif ($_FILES['image']['size'] > 2000000) {
            $erreur = 'L’image est trop lourde (2 Mo maximum).';
        } else {
            // ! Nom unique pour éviter d'écraser une autre image
            $nouvelleImage = 'intervention_' . time() . '_' . rand(100, 999) . '.' . $extension;
            move_uploaded_file($_FILES['image']['tmp_name'], 'image/interventions/' . $nouvelleImage);
            $nomImage = $nouvelleImage;
        }
    } elseif ($erreur == '' && isset($_FILES['image']) && $_FILES['image']['error'] != 0 && $_FILES['image']['error'] != 4) {
        $erreur = 'Erreur lors de l’envoi de l’image.';
    }

    // ! Si tout est correct, on enregistre
    if ($erreur == '') {
        if ($nomImage == '') {
            $nomImage = null;
        }

        if ($idModif > 0) {
            modifierIntervention($idModif, $idCategorie, $idAvion, $intitule, $description, $date, $heureDebut, $heureFin, $lieu, $nomImage, $misEnAvant);

            // ! Si une nouvelle image remplace l'ancienne, on supprime l'ancien fichier
            if ($nouvelleImage != '' && $imageActuelle != '' && file_exists('image/interventions/' . $imageActuelle)) {
                unlink('image/interventions/' . $imageActuelle);
            }

            header('Location: index.php?page=gestion_catalogue&ok=modif');
        } else {
            ajouterIntervention($idCategorie, $idAvion, $intitule, $description, $date, $heureDebut, $heureFin, $lieu, $nomImage, $misEnAvant);
            header('Location: index.php?page=gestion_catalogue&ok=ajout');
        }
        exit;
    }
}

require 'VIEW/intervention_form_vue.php';
?>