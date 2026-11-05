<?php include 'header.php'; ?>

<h2>Catalogue des interventions</h2>

<form method="get" action="index.php">
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

    <button type="submit">Filtrer</button>
    <a href="index.php?page=catalogue">Réinitialiser</a>
</form>

<?php if (count($interventions) == 0) { ?>
    <p>Aucune intervention ne correspond à votre recherche.</p>
<?php } ?>

<ul>
<?php foreach ($interventions as $intervention) { ?>
<li>
    <a href="index.php?page=fiche&id=<?php echo $intervention['id_intervention']; ?>">
        <?php echo htmlspecialchars($intervention['intitule']); ?>
    </a> -
    <?php echo htmlspecialchars($intervention['date_intervention']); ?>
    de <?php echo substr($intervention['heure_debut'], 0, 5); ?>
    à <?php echo substr($intervention['heure_fin'], 0, 5); ?> -
    <?php echo htmlspecialchars($intervention['lieu']); ?> -
    <?php echo libelleStatut($intervention['statut']); ?>
</li>
<?php } ?>
</ul>

<?php include 'footer.php'; ?>