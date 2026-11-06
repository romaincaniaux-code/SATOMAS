<?php include 'header.php'; ?>

<main class="contenu">
    <section class="bandeau">
        <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['utilisateur']['prenom']); ?></h1>
        <p>Suivez vos interventions de maintenance sur avions court et moyen-courrier : choisissez, planifiez et consultez votre historique.</p>
        <a class="btn btn-orange" href="index.php?page=catalogue">Voir les interventions</a>
    </section>

    <h2>Présentation de MAS</h2>
    <div class="grille">
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
</main>

<?php include 'footer.php'; ?>