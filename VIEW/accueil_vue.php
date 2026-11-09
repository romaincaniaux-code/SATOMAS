<?php include 'header.php'; ?>

<main class="contenu">
    <section class="bandeau">
        <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['utilisateur']['prenom']); ?></h1>
        <p>Suivez vos interventions de maintenance sur avions court et moyen-courrier : choisissez, planifiez et consultez votre historique.</p>
        <a class="btn btn-orange" href="index.php?page=catalogue">Voir les interventions</a>
    </section>

    <h2>Présentation de MAS</h2>
    <div class="grille espace-bas">
        <article class="carte">
            <h3>Notre activité</h3>
            <p class="info">Maintenance aéronautique : visites régulières, évolutions techniques, reconditionnement, peinture et réparations.</p>
        </article>
        <article class="carte">
            <h3>Nos valeurs</h3>
            <p class="info">Sécurité, rigueur, qualité et esprit d'équipe au service des compagnies aériennes.</p>
        </article>
        <article class="carte">
            <h3>Coordonnées</h3>
            <p class="info">Maintenance Aero Services<br>Adresse et téléphone à compléter.</p>
        </article>
    </div>

    <h2>Interventions mises en avant</h2>
    <?php if (count($misesEnAvant) == 0) { ?>
        <p>Aucune intervention mise en avant pour le moment.</p>
    <?php } ?>

    <div class="grille">
        <?php foreach ($misesEnAvant as $intervention) { ?>
            <article class="carte">
                <?php if ($intervention['image'] != '') { ?>
                    <img class="carte-image" src="image/interventions/<?php echo htmlspecialchars($intervention['image']); ?>" alt="">
                <?php } else { ?>
                    <div class="carte-image vide">Pas d'image</div>
                <?php } ?>
                <span class="badge statut-<?php echo $intervention['statut']; ?>">
                    <?php echo libelleStatut($intervention['statut']); ?>
                </span>
                <h3>
                    <a href="index.php?page=fiche&id=<?php echo $intervention['id_intervention']; ?>">
                        <?php echo htmlspecialchars($intervention['intitule']); ?>
                    </a>
                </h3>
                <p class="info">
                    <?php echo date('d/m/Y', strtotime($intervention['date_intervention'])); ?>
                    de <?php echo substr($intervention['heure_debut'], 0, 5); ?>
                    à <?php echo substr($intervention['heure_fin'], 0, 5); ?>
                </p>
                <p class="info"><?php echo htmlspecialchars($intervention['lieu']); ?></p>
                <a class="lien" href="index.php?page=fiche&id=<?php echo $intervention['id_intervention']; ?>">Voir le détail</a>
            </article>
        <?php } ?>
    </div>
</main>

<?php include 'footer.php'; ?>