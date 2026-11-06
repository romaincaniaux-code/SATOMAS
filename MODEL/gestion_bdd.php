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
        'SELECT intervention.id_intervention, intervention.intitule, intervention.description,
                intervention.date_intervention, intervention.heure_debut, intervention.heure_fin,
                intervention.lieu, intervention.statut,
                categorie.nom AS categorie, avion.modele, avion.immatriculation
         FROM intervention
         INNER JOIN categorie ON intervention.id_categorie = categorie.id_categorie
         INNER JOIN avion ON intervention.id_avion = avion.id_avion
         WHERE intervention.id_intervention = ?'
    );
    $requete->execute([$idIntervention]);

    return $requete->fetch(PDO::FETCH_ASSOC);
}

// ! Le salarié a-t-il déjà une intervention attribuée qui chevauche ce créneau ?
//* AntiSlach Permet d'éviter que php considère les interventions annulées ou terminées comme des chevauchements
function salarieOccupe($idUtilisateur, $date, $heureDebut, $heureFin)
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT COUNT(*)
         FROM affectation
         INNER JOIN intervention ON affectation.id_intervention = intervention.id_intervention
         WHERE affectation.id_utilisateur = :utilisateur
           AND intervention.date_intervention = :date
           AND intervention.statut = \'attribuee\' 
           AND intervention.heure_debut < :fin
           AND intervention.heure_fin > :debut'
    );
    $requete->execute([
        'utilisateur' => $idUtilisateur,
        'date' => $date,
        'fin' => $heureFin,
        'debut' => $heureDebut
    ]);

    return $requete->fetchColumn() > 0;
}

// ! Interventions attribuées à un salarié pour un jour donné
function obtenirPlanning($idUtilisateur, $date)
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT intervention.id_intervention, intervention.intitule, intervention.heure_debut,
                intervention.heure_fin, intervention.lieu, intervention.statut,
                avion.modele, avion.immatriculation
         FROM affectation
         INNER JOIN intervention ON affectation.id_intervention = intervention.id_intervention
         INNER JOIN avion ON intervention.id_avion = avion.id_avion
         WHERE affectation.id_utilisateur = :utilisateur
           AND intervention.date_intervention = :date
         ORDER BY intervention.heure_debut ASC'
    );
    $requete->execute(['utilisateur' => $idUtilisateur, 'date' => $date]);

    return $requete->fetchAll(PDO::FETCH_ASSOC);
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

// ! Le salarié annule son intervention : renvoie true, ou un message d'erreur
function annulerIntervention($idUtilisateur, $idIntervention)
{
    global $bdd;

    $intervention = obtenirInterventionParId($idIntervention);

    if (!$intervention) {
        return 'Intervention introuvable.';
    }

    // ! L'intervention doit bien être attribuée à ce salarié
    $requete = $bdd->prepare('SELECT COUNT(*) FROM affectation WHERE id_utilisateur = ? AND id_intervention = ?');
    $requete->execute([$idUtilisateur, $idIntervention]);
    if ($requete->fetchColumn() == 0) {
        return 'Cette intervention ne vous est pas attribuée.';
    }

    if ($intervention['statut'] != 'attribuee') {
        return 'Cette intervention ne peut plus être annulée.';
    }

    // ! Impossible d'annuler une intervention déjà commencée
    $debut = $intervention['date_intervention'] . ' ' . $intervention['heure_debut'];
    if ($debut <= date('Y-m-d H:i:s')) {
        return 'Cette intervention a déjà commencé.';
    }

    // ! On supprime l'affectation, l'intervention redevient « à pourvoir »
    $requete = $bdd->prepare('DELETE FROM affectation WHERE id_utilisateur = ? AND id_intervention = ?');
    $requete->execute([$idUtilisateur, $idIntervention]);

    $requete = $bdd->prepare('UPDATE intervention SET statut = \'a_pourvoir\' WHERE id_intervention = ?');
    $requete->execute([$idIntervention]);

    return true;
}

//? HISTORIQUE
// ! Calcule la durée d'une intervention à partir des heures (ex. 8 h ou 3,5 h)
function dureeIntervention($heureDebut, $heureFin)
{
    $secondes = strtotime($heureFin) - strtotime($heureDebut);
    $heures = $secondes / 3600;

    return str_replace('.', ',', $heures) . ' h';
}

// ! Interventions terminées d'un salarié (les plus récentes en premier)
function obtenirHistorique($idUtilisateur)
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT intervention.id_intervention, intervention.intitule, intervention.date_intervention,
                intervention.heure_debut, intervention.heure_fin,
                avion.modele, avion.immatriculation
         FROM affectation
         INNER JOIN intervention ON affectation.id_intervention = intervention.id_intervention
         INNER JOIN avion ON intervention.id_avion = avion.id_avion
         WHERE affectation.id_utilisateur = :utilisateur AND intervention.statut = :statut
         ORDER BY intervention.date_intervention DESC, intervention.heure_debut DESC'
    );
    $requete->execute(['utilisateur' => $idUtilisateur, 'statut' => 'terminee']);

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}