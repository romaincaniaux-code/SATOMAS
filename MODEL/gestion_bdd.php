<?php
include 'connexion.php'; // Inclut le fichier de connexion à la base de données.
// Vérifie l'adresse e-mail et le mot de passe, puis connecte l'utilisateur.
function verifierIdentifiants($loginSaisi, $mdpSaisi)
{
    // Utilise la connexion à la base créée dans connexion.php.
    global $bdd;

    // Cherche le compte correspondant à l'adresse e-mail.
    if (!$bdd instanceof PDO) {
        throw new RuntimeException('La connexion à la base de données n’est pas disponible.');
    }

    $requete = $bdd->prepare(
        'SELECT id_utilisateur, nom, prenom, email, telephone, service, mot_de_passe, role
         FROM utilisateur WHERE email = ?'
    );
    $requete->execute([$loginSaisi]);
    $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

    // Refuse la connexion si le compte n'existe pas ou si le mot de passe est faux.
    if (!$utilisateur || !password_verify($mdpSaisi, $utilisateur['mot_de_passe'])) {
        return false;
    }

    // Démarre la session et renouvelle son identifiant après la connexion.
    if (session_status() !== PHP_SESSION_ACTIVE && !session_start()) {
        throw new RuntimeException('Impossible de démarrer la session.');
    }
    session_regenerate_id(true);

    // Garde les informations du compte en session, mais pas son mot de passe.
    unset($utilisateur['mot_de_passe']);
    $_SESSION['utilisateur'] = $utilisateur;

    // Indique à la page qui appelle cette fonction que la connexion a réussi.
    return true;
}