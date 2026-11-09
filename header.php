<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SATOMAS</title>
    <link rel="stylesheet" href="includes/style.css">
</head>
<body>
<?php if (isset($_SESSION['utilisateur'])) { ?>
    <?php
    // ! Nom du rôle lisible pour l'affichage
    $nomsRoles = array(
        'salarie' => 'Salarié',
        'gestionnaire' => 'Gestionnaire',
        'administrateur' => 'Administrateur'
    );
    $roleUtilisateur = $_SESSION['utilisateur']['role'];
    ?>
    <header class="entete">
        <a href="index.php?page=accueil" class="logo">
            <img src="image/logo.png" alt="Logo SATOMAS">
        </a>

        <nav class="menu">
            <a href="index.php?page=accueil" class="<?php if ($page == 'accueil') { echo 'actif'; } ?>">Accueil</a>
            <a href="index.php?page=catalogue" class="<?php if ($page == 'catalogue' || $page == 'fiche') { echo 'actif'; } ?>">Catalogue</a>
            <?php if ($roleUtilisateur == 'salarie') { ?>
                <a href="index.php?page=planning" class="<?php if ($page == 'planning') { echo 'actif'; } ?>">Mon planning</a>
                <a href="index.php?page=historique" class="<?php if ($page == 'historique') { echo 'actif'; } ?>">Mon historique</a>
            <?php } ?>
            <?php if ($roleUtilisateur == 'gestionnaire' || $roleUtilisateur == 'administrateur') { ?>
                <a href="index.php?page=suivi" class="<?php if ($page == 'suivi') { echo 'actif'; } ?>">Suivi activité</a>
                <a href="index.php?page=gestion_catalogue" class="<?php if ($page == 'gestion_catalogue' || $page == 'intervention_form' || $page == 'categories') { echo 'actif'; } ?>">Gestion catalogue</a>
                <?php if ($roleUtilisateur == 'administrateur') { ?>
                <a href="index.php?page=comptes" class="<?php if ($page == 'comptes') { echo 'actif'; } ?>">Gestion comptes</a>
            <?php } ?>
            <?php } ?>
        </nav>

        <div class="utilisateur">
            <span class="role"><?php echo $nomsRoles[$roleUtilisateur]; ?></span>
            <a href="index.php?page=deconnexion">Déconnexion</a>
        </div>
    </header>
<?php } ?>