<?php include 'header.php'; ?>

<main class="contenu">
    <h1>Gestion du catalogue</h1>
    <p class="sous-titre">Ajouter, modifier ou supprimer des interventions</p>

    <?php
    // ! Message de réussite après une action
    $texteOk = '';
    if (isset($_GET['ok'])) {
        if ($_GET['ok'] == 'ajout' || $_GET['ok'] == '1') {
            $texteOk = 'Intervention ajoutée.';
        } elseif ($_GET['ok'] == 'modif') {
            $texteOk = 'Intervention modifiée.';
        } elseif ($_GET['ok'] == 'suppr') {
            $texteOk = 'Intervention supprimée.';
        }
    }
    ?>

    <?php if ($texteOk != '') { ?>
        <p class="message-ok"><?php echo $texteOk; ?></p>
    <?php } ?>

    <?php if ($erreur != '') { ?>
        <p class="message-erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php } ?>

    <?php if ($interventionSuppr) { ?>
        <section class="panneau confirmation">
            <h2>Supprimer « <?php echo htmlspecialchars($interventionSuppr['intitule']); ?> » ?</h2>
            <p>Cette action est irréversible.</p>
            <form class="actions" method="post" action="index.php?page=gestion_catalogue">
                <input type="hidden" name="id_intervention" value="<?php echo $interventionSuppr['id_intervention']; ?>">
                <button class="btn btn-rouge" type="submit" name="supprimer">Oui, supprimer</button>
                <a class="btn btn-contour" href="index.php?page=gestion_catalogue">Annuler</a>
            </form>
        </section>
    <?php } ?>

    <div class="actions espace-bas">
        <a class="btn btn-orange" href="index.php?page=intervention_form">+ Ajouter une intervention</a>
        <a class="btn btn-contour" href="index.php?page=categories">Gérer les catégories</a>
    </div>

    <div class="table-defilante">
        <table>
            <tr>
                <th>Image</th>
                <th>Intitulé</th>
                <th>Catégorie</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Mise en avant</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($interventions as $intervention) { ?>
                <tr>
                    <td>
                        <?php if ($intervention['image'] != '') { ?>
                            <img class="miniature" src="image/interventions/<?php echo htmlspecialchars($intervention['image']); ?>" alt="">
                        <?php } else { ?>
                            
                        <?php } ?>
                    </td>
                    <td><?php echo htmlspecialchars($intervention['intitule']); ?></td>
                    <td><?php echo htmlspecialchars($intervention['categorie']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($intervention['date_intervention'])); ?></td>
                    <td><span class="badge statut-<?php echo $intervention['statut']; ?>"><?php echo libelleStatut($intervention['statut']); ?></span></td>
                    <td><?php if ($intervention['mis_en_avant'] == 1) { echo 'Oui'; } else { echo 'Non'; } ?></td>
                    <td>
                        <a href="index.php?page=intervention_form&id=<?php echo $intervention['id_intervention']; ?>">Modifier</a> |
                        <a href="index.php?page=gestion_catalogue&supprimer=<?php echo $intervention['id_intervention']; ?>">Supprimer</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php if ($nbPages > 1) { ?>
    <nav class="pagination" aria-label="Pagination de la gestion du catalogue">
        <?php if ($pageCourante > 1) { ?>
            <a href="index.php?page=gestion_catalogue&amp;p=<?php echo $pageCourante - 1; ?>">‹ Précédent</a>
        <?php } else { ?>
            <span class="inactif">‹ Précédent</span>
        <?php } ?>

        <?php for ($i = 1; $i <= $nbPages; $i++) { ?>
            <?php if ($i == $pageCourante) { ?>
                <span class="courante"><?php echo $i; ?></span>
            <?php } else { ?>
                <a href="index.php?page=gestion_catalogue&amp;p=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php } ?>
        <?php } ?>

        <?php if ($pageCourante < $nbPages) { ?>
            <a href="index.php?page=gestion_catalogue&amp;p=<?php echo $pageCourante + 1; ?>">Suivant ›</a>
        <?php } else { ?>
            <span class="inactif">Suivant ›</span>
        <?php } ?>
    </nav>
<?php } ?>
</main>

<?php include 'footer.php'; ?>