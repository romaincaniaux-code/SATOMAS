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
                   heure_debut, heure_fin, lieu, statut, image
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

// ! Liste des catégories (pour la liste déroulante et la gestion)
function obtenirCategories()
{
    global $bdd;

    $requete = $bdd->query('SELECT id_categorie, nom, description FROM categorie ORDER BY nom ASC');

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
                categorie.nom AS categorie, avion.modele, avion.immatriculation, intervention.image
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
//GESTIONNAIRE
// ! Toutes les interventions avec leur avion, filtre facultatif par date
function obtenirSuivi($date = '')
{
    global $bdd;

    $sql = 'SELECT intervention.id_intervention, intervention.intitule, intervention.date_intervention,
                   intervention.heure_debut, intervention.heure_fin, intervention.lieu, intervention.statut,
                   avion.modele, avion.immatriculation
            FROM intervention
            INNER JOIN avion ON intervention.id_avion = avion.id_avion
            WHERE 1 = 1';
    $parametres = array();

    // ! Filtre par date
    if ($date != '') {
        $sql = $sql . ' AND intervention.date_intervention = :date';
        $parametres['date'] = $date;
    }

    $sql = $sql . ' ORDER BY intervention.date_intervention ASC, intervention.heure_debut ASC';

    $requete = $bdd->prepare($sql);
    $requete->execute($parametres);

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

// ! Salarié affecté à une intervention (false si personne n'est affecté)
function obtenirSalarieAffecte($idIntervention)
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT utilisateur.id_utilisateur, utilisateur.nom, utilisateur.prenom
         FROM affectation
         INNER JOIN utilisateur ON affectation.id_utilisateur = utilisateur.id_utilisateur
         WHERE affectation.id_intervention = ?'
    );
    $requete->execute([$idIntervention]);

    return $requete->fetch(PDO::FETCH_ASSOC);
}

// ! Liste des salariés (pour la liste déroulante)
function obtenirSalaries()
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT id_utilisateur, nom, prenom
         FROM utilisateur
         WHERE role = :role
         ORDER BY nom ASC'
    );
    $requete->execute(['role' => 'salarie']);

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

//? CATEGORIES
// ! Une catégorie par son numéro (false si elle n'existe pas)
function obtenirCategorieParId($idCategorie)
{
    global $bdd;

    $requete = $bdd->prepare('SELECT id_categorie, nom, description FROM categorie WHERE id_categorie = ?');
    $requete->execute([$idCategorie]);

    return $requete->fetch(PDO::FETCH_ASSOC);
}

// ! Ajoute une catégorie
function ajouterCategorie($nom, $description)
{
    global $bdd;

    $requete = $bdd->prepare('INSERT INTO categorie (nom, description) VALUES (?, ?)');
    $requete->execute([$nom, $description]);
}

// ! Modifie une catégorie
function modifierCategorie($idCategorie, $nom, $description)
{
    global $bdd;

    $requete = $bdd->prepare('UPDATE categorie SET nom = ?, description = ? WHERE id_categorie = ?');
    $requete->execute([$nom, $description, $idCategorie]);
}

// ! Supprime une catégorie : renvoie true, ou un message d'erreur
function supprimerCategorie($idCategorie)
{
    global $bdd;

    // ! Impossible de supprimer une catégorie utilisée par des interventions
    $requete = $bdd->prepare('SELECT COUNT(*) FROM intervention WHERE id_categorie = ?');
    $requete->execute([$idCategorie]);
    if ($requete->fetchColumn() > 0) {
        return 'Suppression impossible : des interventions utilisent cette catégorie.';
    }

    $requete = $bdd->prepare('DELETE FROM categorie WHERE id_categorie = ?');
    $requete->execute([$idCategorie]);

    return true;
}

//? GESTION DU CATALOGUE
// ! Liste des avions (pour la liste déroulante)
function obtenirAvions()
{
    global $bdd;

    $requete = $bdd->query('SELECT id_avion, modele, immatriculation FROM avion ORDER BY modele ASC');

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

// ! Toutes les interventions avec catégorie et image (pour la page de gestion)
function obtenirInterventionsGestion()
{
    global $bdd;

    $requete = $bdd->query(
        'SELECT intervention.id_intervention, intervention.intitule, intervention.date_intervention,
                intervention.statut, intervention.image, intervention.mis_en_avant,
                categorie.nom AS categorie
         FROM intervention
         INNER JOIN categorie ON intervention.id_categorie = categorie.id_categorie
         ORDER BY intervention.date_intervention ASC, intervention.heure_debut ASC'
    );

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

// ! Ajoute une intervention (toujours « à pourvoir » au départ)
function ajouterIntervention($idCategorie, $idAvion, $intitule, $description, $date, $heureDebut, $heureFin, $lieu, $image, $misEnAvant)
{
    global $bdd;

    $requete = $bdd->prepare(
        'INSERT INTO intervention (id_categorie, id_avion, intitule, description, date_intervention, heure_debut, heure_fin, lieu, statut, image, mis_en_avant)
         VALUES (:categorie, :avion, :intitule, :description, :date,
                 :debut, :fin, :lieu, :statut, :image, :avant)'
    );
    $requete->execute([
        'categorie' => $idCategorie,
        'avion' => $idAvion,
        'intitule' => $intitule,
        'description' => $description,
        'date' => $date,
        'debut' => $heureDebut,
        'fin' => $heureFin,
        'lieu' => $lieu,
        'statut' => 'a_pourvoir',
        'image' => $image,
        'avant' => $misEnAvant
    ]);
}
// ! Les 3 prochaines interventions « mises en avant » (page d'accueil)
function obtenirInterventionsMisesEnAvant()
{
    global $bdd;

    $requete = $bdd->query(
        'SELECT id_intervention, intitule, date_intervention, heure_debut, heure_fin, lieu, statut, image
         FROM intervention
         WHERE mis_en_avant = 1
         ORDER BY date_intervention ASC, heure_debut ASC
         LIMIT 3'
    );

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

// ! Une intervention telle qu'elle est en base (pour pré-remplir le formulaire de modification)
function obtenirInterventionBrute($idIntervention)
{
    global $bdd;

    $requete = $bdd->prepare(
        'SELECT id_intervention, id_categorie, id_avion, intitule, description,
                date_intervention, heure_debut, heure_fin, lieu, image, mis_en_avant
         FROM intervention
         WHERE id_intervention = ?'
    );
    $requete->execute([$idIntervention]);

    return $requete->fetch(PDO::FETCH_ASSOC);
}

// ! Modifie une intervention (le statut ne change pas ici)
function modifierIntervention($idIntervention, $idCategorie, $idAvion, $intitule, $description, $date, $heureDebut, $heureFin, $lieu, $image, $misEnAvant)
{
    global $bdd;

    $requete = $bdd->prepare(
        'UPDATE intervention
         SET id_categorie = :categorie, id_avion = :avion, intitule = :intitule,
             description = :description, date_intervention = :date,
             heure_debut = :debut, heure_fin = :fin, lieu = :lieu,
             image = :image, mis_en_avant = :avant
         WHERE id_intervention = :id'
    );
    $requete->execute([
        'categorie' => $idCategorie,
        'avion' => $idAvion,
        'intitule' => $intitule,
        'description' => $description,
        'date' => $date,
        'debut' => $heureDebut,
        'fin' => $heureFin,
        'lieu' => $lieu,
        'image' => $image,
        'avant' => $misEnAvant,
        'id' => $idIntervention
    ]);
}

// ! Supprime une intervention : renvoie true, ou un message d'erreur
function supprimerIntervention($idIntervention)
{
    global $bdd;

    // ! Impossible de supprimer une intervention à laquelle un salarié est affecté
    $requete = $bdd->prepare('SELECT COUNT(*) FROM affectation WHERE id_intervention = ?');
    $requete->execute([$idIntervention]);
    if ($requete->fetchColumn() > 0) {
        return 'Suppression impossible : un salarié est affecté à cette intervention.';
    }

    // ! On garde le nom de l'image pour supprimer aussi le fichier
    $intervention = obtenirInterventionBrute($idIntervention);

    $requete = $bdd->prepare('DELETE FROM intervention WHERE id_intervention = ?');
    $requete->execute([$idIntervention]);

    if ($intervention && $intervention['image'] != '' && file_exists('image/interventions/' . $intervention['image'])) {
        unlink('image/interventions/' . $intervention['image']);
    }

    return true;
}
//? COMPTES
// ! Nom lisible d'un rôle
function libelleRole($role)
{
    if ($role == 'salarie') {
        return 'Salarié';
    } elseif ($role == 'gestionnaire') {
        return 'Gestionnaire';
    } else {
        return 'Administrateur';
    }
}

// ! Liste de tous les comptes
function obtenirUtilisateurs()
{
    global $bdd;

    $requete = $bdd->query(
        'SELECT id_utilisateur, nom, prenom, email, service, role, date_creation
         FROM utilisateur
         ORDER BY nom ASC'
    );

    return $requete->fetchAll(PDO::FETCH_ASSOC);
}

// ! Un compte par son numéro (false s'il n'existe pas)
function obtenirUtilisateurParId($idUtilisateur)
{
    global $bdd;

    $requete = $bdd->prepare('SELECT id_utilisateur, nom, prenom, email, service, role FROM utilisateur WHERE id_utilisateur = ?');
    $requete->execute([$idUtilisateur]);

    return $requete->fetch(PDO::FETCH_ASSOC);
}

// ! Cet email est-il déjà utilisé par un compte ?
function emailExiste($email)
{
    global $bdd;

    $requete = $bdd->prepare('SELECT COUNT(*) FROM utilisateur WHERE email = ?');
    $requete->execute([$email]);

    return $requete->fetchColumn() > 0;
}

// ! Crée un compte (le mot de passe est enregistré chiffré)
function ajouterUtilisateur($nom, $prenom, $email, $service, $role, $motDePasse)
{
    global $bdd;

    $requete = $bdd->prepare(
        'INSERT INTO utilisateur (nom, prenom, email, service, mot_de_passe, role)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $requete->execute([$nom, $prenom, $email, $service, password_hash($motDePasse, PASSWORD_DEFAULT), $role]);
}

// ! Change le rôle d'un compte
function modifierRoleUtilisateur($idUtilisateur, $role)
{
    global $bdd;

    $requete = $bdd->prepare('UPDATE utilisateur SET role = ? WHERE id_utilisateur = ?');
    $requete->execute([$role, $idUtilisateur]);
}

// ! Supprime un compte : renvoie true, ou un message d'erreur
function supprimerUtilisateur($idUtilisateur)
{
    global $bdd;

    // ! Impossible de supprimer un compte qui a des interventions affectées
    $requete = $bdd->prepare('SELECT COUNT(*) FROM affectation WHERE id_utilisateur = ?');
    $requete->execute([$idUtilisateur]);
    if ($requete->fetchColumn() > 0) {
        return 'Suppression impossible : des interventions sont affectées à ce compte.';
    }

    $requete = $bdd->prepare('DELETE FROM utilisateur WHERE id_utilisateur = ?');
    $requete->execute([$idUtilisateur]);

    return true;
}
