<?php
// ! Numéro de l'intervention demandée dans l'adresse
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = '';

// ! Si l'intervention n'existe pas, retour au catalogue
if (!obtenirInterventionParId($id)) {
    header('Location: index.php?page=catalogue');
    exit;
}

$role = $_SESSION['utilisateur']['role'];
$idUtilisateur = $_SESSION['utilisateur']['id_utilisateur'];

// ! Si le salarié a cliqué sur « Accepter »
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accepter']) && $role == 'salarie') {
    $resultat = accepterIntervention($idUtilisateur, $id);

    if ($resultat === true) {
        header('Location: index.php?page=fiche&id=' . $id . '&ok=1');
        exit;
    } else {
        $message = $resultat;
    }
}

// ! On relit l'intervention (son statut a pu changer)
$intervention = obtenirInterventionParId($id);

// ! Le bouton est-il actif ? Sinon, pour quelle raison ?
$peutAccepter = false;
$raison = '';

if ($role != 'salarie') {
    $raison = 'Seuls les salariés peuvent accepter une intervention.';
} elseif ($intervention['statut'] != 'a_pourvoir') {
    $raison = 'Cette intervention n’est pas disponible (' . libelleStatut($intervention['statut']) . ').';
} elseif (salarieOccupe($idUtilisateur, $intervention['date_intervention'], $intervention['heure_debut'], $intervention['heure_fin'])) {
    $raison = 'Vous avez déjà une activité sur ce créneau.';
} else {
    $peutAccepter = true;
}

require 'VIEW/fiche_vue.php';
?>