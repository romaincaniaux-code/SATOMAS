<?php
// ! Message d'erreur vide par défaut
$erreur = '';

// ! Si le formulaire a été envoyé
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mdp = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

    // ! On demande au modèle de vérifier les identifiants
    if (verifierIdentifiants($email, $mdp)) {
        header('Location: index.php?page=accueil');
        exit;
    } else {
        $erreur = 'Identifiants incorrects';
    }
}

// ! On affiche la vue
include 'VIEW/connexion_vue.php';
?>