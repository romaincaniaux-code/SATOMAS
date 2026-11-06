<?php include 'header.php'; ?>

<main class="contenu">
    <h1>Catalogue des interventions</h1>
    <p class="sous-titre">Choisissez une intervention à réaliser</p>

    <form class="filtres" method="get" action="index.php">
        <input type="hidden" name="page" value="catalogue">

        <input type="text" name="motcle" placeholder="Rechercher par mot-clé"
               value="<?php echo htmlspecialchars($motCle); ?>">

        <select name="categorie">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $categorie) { ?>
                <option value="<?php echo $categorie['id_categorie']; ?>"
                    <?php if ($idCategorie == $categorie['id_categorie']) { echo 'selected'; } ?>>
                    <?php echo htmlspecialchars($categorie['nom']); ?>
                </option>
            <?php } ?>
        </select>

        <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">

        <button class="btn" type="submit">Filtrer</button>
        <a class="btn btn-contour" href="index.php?page=catalogue">Réinitialiser</a>
    </form>

    <?php if (count($interventions) == 0) { ?>
        <p>Aucune intervention ne correspond à votre recherche.</p>
    <?php } ?>

    <div class="grille">
        <?php foreach ($interventions as $intervention) { ?>
            <article class="carte">
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