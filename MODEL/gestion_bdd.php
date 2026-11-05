<?php
include 'connexion.php'; // ! Inclut le fichier de connexion à la base de données.

// ! Vérifie l'adresse e-mail et le mot de passe, puis connecte l'utilisateur.
function verifierIdentifiants($loginSaisi, $mdpSaisi)
{
    global $bdd;

    if (!$bdd instanceof PDO) {
        throw new RuntimeException('La connexion à la base de données n’est pas disponible.');
    }

    $requete = $bdd->prepare(
        'SELECT id_utilisateur, nom, prenom, email, telephone, service, mot_de_passe, role
         FROM utilisateur WHERE email = ?'
    );
    $requete->execute([$loginSaisi]);
    $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

    // ! Refuse la connexion si le compte n'existe pas ou si le mot de passe est faux.
    if (!$utilisateur || !password_verify($mdpSaisi, $utilisateur['mot_de_passe'])) {
        return false;
    }

    // ! Démarre la session et renouvelle son identifiant après la connexion.
    if (session_status() !== PHP_SESSION_ACTIVE && !session_start()) {
        throw new RuntimeException('Impossible de démarrer la session.');
    }
    session_regenerate_id(true);

    // ! Garde les informations du compte en session, mais pas son mot de passe.
    unset($utilisateur['mot_de_passe']);
    $_SESSION['utilisateur'] = $utilisateur;

    return true;
}

// ! Transforme le statut de la base en texte lisible
function libelleStatut($statut)
{
    if ($statut == 'a_pourvoir') {
        return 'À pourvoir';
    } elseif ($statut == 'attribuee') {
        return 'Attribuée';
    } elseif ($statut == 'terminee') {
        return 'Terminée';
    } else {
        return 'Annulée';
    }
}

// ! Liste des interventions, avec filtres facultatifs
function obtenirInterventions($motCle = '', $idCategorie = '', $date = '')
{
    global $bdd;

    $sql = 'SELECT id_intervention, intitule, date_intervention,
                   heure_debut, heure_fin, lieu, statut
            FROM intervention
            WHERE 1 = 1';
    $parametres = array();

    // ! Filtre par mot-clé (intitulé ou description)
    if ($motCle != '') {
        $sql = $sql . ' AND (intitule LIKE :motcle1 OR description LIKE :motcle2)';
        $parametres['motcle1'] = '%' . $motCle . '%';
        $parametres['motcle2'] = '%' . $motCle . '%';
    }

    // ! Filtre par catégorie
    if ($idCategorie != '') {
        $sql = $sql . ' AND id_categorie = :categorie';
        $parametres['categorie'] = $idCategorie;
    }

    // ! Filtre par date
    if ($date != '') {
        $sql = $sql . ' AND date_intervention = :date';
        $parametres['date'] = $date;
    }

    $sql = $sql . ' ORDER BY date_intervention ASC, heure_debut ASC';

    $requete = $bdd->prepare($sql);
    $requete->execute($parametres);

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

// ! Liste des catégories (pour la liste déroulante)
function obtenirCategories()
{
    global $bdd;

    $requete = $bdd->query('SELECT id_categorie, nom FROM categorie ORDER BY nom ASC');

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

// ! Une intervention avec sa catégorie et son avion (false si elle n'existe pas)
function obtenirInterventionParId($idIntervention)
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT i.id_intervention, i.intitule, i.description, i.date_intervention,
                i.heure_debut, i.heure_fin, i.lieu, i.statut,
                c.nom AS categorie, a.modele, a.immatriculation
         FROM intervention i
         INNER JOIN categorie c ON i.id_categorie = c.id_categorie
         INNER JOIN avion a ON i.id_avion = a.id_avion
         WHERE i.id_intervention = ?'
    );
    $requete->execute([$idIntervention]);

    return $requete->fetch(PDO::FETCH_ASSOC);
}

// ! Le salarié a-t-il déjà une intervention attribuée qui chevauche ce créneau ?
function salarieOccupe($idUtilisateur, $date, $heureDebut, $heureFin)
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT COUNT(*)
         FROM affectation af
         INNER JOIN intervention i ON af.id_intervention = i.id_intervention
         WHERE af.id_utilisateur = :utilisateur
           AND i.date_intervention = :date
           AND i.statut = \'attribuee\'
           AND i.heure_debut < :fin
           AND i.heure_fin > :debut'
    );
    $requete->execute([
        'utilisateur' => $idUtilisateur,
        'date' => $date,
        'fin' => $heureFin,
        'debut' => $heureDebut
    ]);

    return $requete->fetchColumn() > 0;
}

// ! Le salarié accepte une intervention : renvoie true, ou un message d'erreur
function accepterIntervention($idUtilisateur, $idIntervention)
{
    global $bdd;

    $intervention = obtenirInterventionParId($idIntervention);

    if (!$intervention) {
        return 'Intervention introuvable.';
    }
    if ($intervention['statut'] != 'a_pourvoir') {
        return 'Cette intervention n’est plus disponible.';
    }
    if (salarieOccupe($idUtilisateur, $intervention['date_intervention'], $intervention['heure_debut'], $intervention['heure_fin'])) {
        return 'Vous avez déjà une activité sur ce créneau.';
    }

    // ! On enregistre l'affectation, puis on passe l'intervention en « attribuée »
    $requete = $bdd->prepare('INSERT INTO affectation (id_utilisateur, id_intervention) VALUES (?, ?)');
    $requete->execute([$idUtilisateur, $idIntervention]);

    $requete = $bdd->prepare('UPDATE intervention SET statut = \'attribuee\' WHERE id_intervention = ?');
    $requete->execute([$idIntervention]);

    return true;
}