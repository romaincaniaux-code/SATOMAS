<?php include 'header.php'; ?>

<main class="contenu">
    <p><a href="index.php?page=gestion_catalogue">← Retour à la gestion du catalogue</a></p>

    <?php if ($idModif > 0) { ?>
        <h1>Modifier l'intervention</h1>
    <?php } else { ?>
        <h1>Ajouter une intervention</h1>
    <?php } ?>

    <?php if ($erreur != '') { ?>
        <p class="message-erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php } ?>

    <section class="panneau formulaire">
        <form method="post"
              action="index.php?page=intervention_form<?php if ($idModif > 0) { echo '&id=' . $idModif; } ?>"
              enctype="multipart/form-data">

            <label for="intitule">Intitulé</label>
            <input id="intitule" type="text" name="intitule" value="<?php echo htmlspecialchars($intitule); ?>" required>

            <div class="formulaire-ligne">
                <div>
                    <label for="id_categorie">Catégorie</label>
                    <select id="id_categorie" name="id_categorie" required>
                        <option value="">Choisir une catégorie</option>
                        <?php foreach ($categories as $categorie) { ?>
                            <option value="<?php echo $categorie['id_categorie']; ?>"
                                <?php if ($idCategorie == $categorie['id_categorie']) { echo 'selected'; } ?>>
                                <?php echo htmlspecialchars($categorie['nom']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="id_avion">Avion</label>
                    <select id="id_avion" name="id_avion" required>
                        <option value="">Choisir un avion</option>
                        <?php foreach ($avions as $avion) { ?>
                            <option value="<?php echo $avion['id_avion']; ?>"
                                <?php if ($idAvion == $avion['id_avion']) { echo 'selected'; } ?>>
                                <?php echo htmlspecialchars($avion['modele'] . ' (' . $avion['immatriculation'] . ')'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="formulaire-ligne">
                <div>
                    <label for="lieu">Lieu</label>
                    <input id="lieu" type="text" name="lieu" value="<?php echo htmlspecialchars($lieu); ?>" required>
                </div>
                <div>
                    <label for="date">Date</label>
                    <input id="date" type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
                </div>
            </div>

            <div class="formulaire-ligne">
                <div>
                    <label for="heure_debut">Heure de début</label>
                    <input id="heure_debut" type="time" name="heure_debut" value="<?php echo htmlspecialchars($heureDebut); ?>" required>
                </div>
                <div>
                    <label for="heure_fin">Heure de fin</label>
                    <input id="heure_fin" type="time" name="heure_fin" value="<?php echo htmlspecialchars($heureFin); ?>" required>
                </div>
            </div>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>

            <?php if ($imageActuelle != '') { ?>
                <label>Image actuelle</label>
                <img class="image-actuelle" src="image/interventions/<?php echo htmlspecialchars($imageActuelle); ?>" alt="">
                <label for="image">Nouvelle image (laisser vide pour garder l'actuelle)</label>
            <?php } else { ?>
                <label for="image">Image (jpg, png ou webp, 2 Mo maximum)</label>
            <?php } ?>
            <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

            <label class="case">
                <input type="checkbox" name="mis_en_avant" <?php if ($misEnAvant == 1) { echo 'checked'; } ?>>
                Mettre en avant sur la page d'accueil
            </label>

            <div class="actions">
                <button class="btn btn-orange" type="submit" name="enregistrer">Enregistrer</button>
                <a class="btn btn-contour" href="index.php?page=gestion_catalogue">Annuler</a>
            </div>
        </form>
    </section>
</main>

<?php include 'footer.php'; ?>