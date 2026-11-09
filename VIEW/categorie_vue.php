<?php include 'header.php'; ?>

<main class="contenu">
    <h1>Gestion des catégories</h1>
    <p class="sous-titre">Ajouter, modifier ou supprimer une catégorie d'intervention</p>

    <?php
    // ! Message de réussite après une action
    $texteOk = '';
    if (isset($_GET['ok'])) {
        if ($_GET['ok'] == 'ajout') {
            $texteOk = 'Catégorie ajoutée.';
        } elseif ($_GET['ok'] == 'modif') {
            $texteOk = 'Catégorie modifiée.';
        } elseif ($_GET['ok'] == 'suppr') {
            $texteOk = 'Catégorie supprimée.';
        }
    }
    ?>

    <?php if ($texteOk != '') { ?>
        <p class="message-ok"><?php echo $texteOk; ?></p>
    <?php } ?>

    <?php if ($erreur != '') { ?>
        <p class="message-erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php } ?>

    <?php if ($categorieSuppr) { ?>
        <section class="panneau confirmation">
            <h2>Supprimer la catégorie « <?php echo htmlspecialchars($categorieSuppr['nom']); ?> » ?</h2>
            <p>Cette action est irréversible.</p>
            <form class="actions" method="post" action="index.php?page=categories">
                <input type="hidden" name="id_categorie" value="<?php echo $categorieSuppr['id_categorie']; ?>">
                <button class="btn btn-rouge" type="submit" name="supprimer">Oui, supprimer</button>
                <a class="btn btn-contour" href="index.php?page=categories">Annuler</a>
            </form>
        </section>
    <?php } ?>

    <div class="table-defilante">
        <table>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($categories as $categorie) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($categorie['nom']); ?></td>
                    <td><?php echo htmlspecialchars($categorie['description']); ?></td>
                    <td>
                        <a href="index.php?page=categories&modifier=<?php echo $categorie['id_categorie']; ?>">Modifier</a> |
                        <a href="index.php?page=categories&supprimer=<?php echo $categorie['id_categorie']; ?>">Supprimer</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <section class="panneau formulaire espace">
        <?php if ($categorieModif) { ?>
            <h2>Modifier la catégorie</h2>
        <?php } else { ?>
            <h2>Ajouter une catégorie</h2>
        <?php } ?>

        <form method="post" action="index.php?page=categories">
            <?php if ($categorieModif) { ?>
                <input type="hidden" name="id_categorie" value="<?php echo $categorieModif['id_categorie']; ?>">
            <?php } ?>

            <label for="nom">Nom</label>
            <input id="nom" type="text" name="nom" required
                   value="<?php if ($categorieModif) { echo htmlspecialchars($categorieModif['nom']); } ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?php if ($categorieModif) { echo htmlspecialchars($categorieModif['description']); } ?></textarea>

            <div class="actions">
                <?php if ($categorieModif) { ?>
                    <button class="btn" type="submit" name="modifier">Enregistrer</button>
                    <a class="btn btn-contour" href="index.php?page=categories">Annuler</a>
                <?php } else { ?>
                    <button class="btn btn-orange" type="submit" name="ajouter">Ajouter</button>
                <?php } ?>
            </div>
        </form>
    </section>
</main>

<?php include 'footer.php'; ?>