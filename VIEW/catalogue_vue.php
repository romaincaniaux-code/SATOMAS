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

    <?php if ($total == 0) { ?>
        <p>Aucune intervention ne correspond à votre recherche.</p>
    <?php } else { ?>
        <p class="compteur">
            <?php echo $total; ?> intervention(s) trouvée(s) - page <?php echo $pageCourante; ?> sur <?php echo $nbPages; ?>
        </p>
    <?php } ?>

    <div class="grille">
        <?php foreach ($interventions as $intervention) { ?>
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

    <?php if ($nbPages > 1) { ?>
        <nav class="pagination" aria-label="Pagination du catalogue">

            <?php if ($pageCourante > 1) { ?>
                <a href="index.php?page=catalogue<?php echo htmlspecialchars($filtresUrl); ?>&amp;p=<?php echo $pageCourante - 1; ?>">‹ Précédent</a>
            <?php } else { ?>
                <span class="inactif">‹ Précédent</span>
            <?php } ?>

            <?php for ($i = 1; $i <= $nbPages; $i++) { ?>
                <?php if ($i == $pageCourante) { ?>
                    <span class="courante"><?php echo $i; ?></span>
                <?php } else { ?>
                    <a href="index.php?page=catalogue<?php echo htmlspecialchars($filtresUrl); ?>&amp;p=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php } ?>
            <?php } ?>

            <?php if ($pageCourante < $nbPages) { ?>
                <a href="index.php?page=catalogue<?php echo htmlspecialchars($filtresUrl); ?>&amp;p=<?php echo $pageCourante + 1; ?>">Suivant ›</a>
            <?php } else { ?>
                <span class="inactif">Suivant ›</span>
            <?php } ?>

        </nav>
    <?php } ?>
</main>

<?php include 'footer.php'; ?>