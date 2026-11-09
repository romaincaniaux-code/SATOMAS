<?php
// ! Page réservée à l'administrateur
if ($_SESSION['utilisateur']['role'] != 'administrateur') {
    header('Location: index.php?page=accueil');
    exit;
}

$idConnecte = $_SESSION['utilisateur']['id_utilisateur'];
$rolesPossibles = array('salarie', 'gestionnaire', 'administrateur');

$erreur = '';
$utilisateurRole = false;
$utilisateurSuppr = false;

// ! Valeurs du formulaire de création (conservées en cas d'erreur)
$nom = '';
$prenom = '';
$email = '';
$service = '';
$roleChoisi = 'salarie';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ! Création d'un compte
    if (isset($_POST['creer'])) {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $service = trim($_POST['service']);
        $roleChoisi = $_POST['role_compte'];
        $motDePasse = $_POST['mot_de_passe'];

        if ($nom == '' || $prenom == '' || $email == '' || $service == '' || $motDePasse == '') {
            $erreur = 'Tous les champs sont obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Le format de l’email est incorrect.';
        } elseif (emailExiste($email)) {
            $erreur = 'Cet email est déjà utilisé par un autre compte.';
        } elseif (!in_array($roleChoisi, $rolesPossibles)) {
            $erreur = 'Rôle invalide.';
        } else {
            ajouterUtilisateur($nom, $prenom, $email, $service, $roleChoisi, $motDePasse);
            header('Location: index.php?page=comptes&ok=creation');
            exit;
        }
    }

    // ! Changement de rôle
    if (isset($_POST['changer_role'])) {
        $id = (int) $_POST['id_utilisateur'];
        $nouveauRole = $_POST['nouveau_role'];

        if (in_array($nouveauRole, $rolesPossibles)) {
            modifierRoleUtilisateur($id, $nouveauRole);
            header('Location: index.php?page=comptes&ok=role');
            exit;
        } else {
            $erreur = 'Rôle invalide.';
        }
    }

    // ! Suppression confirmée
    if (isset($_POST['supprimer'])) {
        $id = (int) $_POST['id_utilisateur'];

        // ! L'administrateur ne peut pas supprimer son propre compte
        if ($id == $idConnecte) {
            $erreur = 'Vous ne pouvez pas supprimer votre propre compte.';
        } else {
            $resultat = supprimerUtilisateur($id);
            if ($resultat === true) {
                header('Location: index.php?page=comptes&ok=suppr');
                exit;
            } else {
                $erreur = $resultat;
            }
        }
    }
}

// ! Clic sur « Modifier le rôle »
if (isset($_GET['modifier_role'])) {
    $utilisateurRole = obtenirUtilisateurParId((int) $_GET['modifier_role']);
}

// ! Clic sur « Supprimer » : on demande confirmation
if (isset($_GET['supprimer'])) {
    $utilisateurSuppr = obtenirUtilisateurParId((int) $_GET['supprimer']);
}

$utilisateurs = obtenirUtilisateurs();

require 'VIEW/comptes_vue.php';
?>