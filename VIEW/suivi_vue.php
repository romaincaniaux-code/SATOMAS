<?php include 'header.php'; ?>

<main class="contenu">
    <h1>Suivi de l'activité</h1>
    <p class="sous-titre">Qui fait quoi ?</p>

    <?php if ($nbAPourvoir > 0) { ?>
        <p class="alerte"><?php echo $nbAPourvoir; ?> intervention(s) encore à pourvoir : à affecter rapidement.</p>
    <?php } ?>

    <form class="filtres" method="get" action="index.php">
        <input type="hidden" name="page" value="suivi">

        <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">

        <select name="salarie">
            <option value="">Tous les salariés</option>
            <?php foreach ($salaries as $salarie) { ?>
                <option value="<?php echo $salarie['id_utilisateur']; ?>"
                    <?php if ($idSalarie == $salarie['id_utilisateur']) { echo 'selected'; } ?>>
                    <?php echo htmlspecialchars($salarie['prenom'] . ' ' . $salarie['nom']); ?>
                </option>
            <?php } ?>
        </select>

        <button class="btn" type="submit">Filtrer</button>
        <a class="btn btn-contour" href="index.php?page=suivi">Réinitialiser</a>
    </form>

    <?php if (count($suivi) == 0) { ?>
        <p>Aucune intervention ne correspond à ces filtres.</p>
    <?php } else { ?>
        <div class="table-defilante">
            <table>
                <tr>
                    <th>Intervention</th>
                    <th>Date</th>
                    <th>Horaires</th>
                    <th>Avion</th>
                    <th>Salarié affecté</th>
                    <th>Statut</th>
                </tr>
                <?php foreach ($suivi as $ligne) { ?>
                    <tr class="<?php if ($ligne['statut'] == 'a_pourvoir') { echo 'ligne-a-pourvoir'; } ?>">
                        <td>
                            <a href="index.php?page=fiche&id=<?php echo $ligne['id_intervention']; ?>">
                                <?php echo htmlspecialchars($ligne['intitule']); ?>
                            </a>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($ligne['date_intervention'])); ?></td>
                        <td><?php echo substr($ligne['heure_debut'], 0, 5); ?> - <?php echo substr($ligne['heure_fin'], 0, 5); ?></td>
                        <td>
                            <?php echo htmlspecialchars($ligne['modele']); ?>
                            (<?php echo htmlspecialchars($ligne['immatriculation']); ?>)
                        </td>
                        <td>
                            <?php
                            // ! Pas de salarié affecté : on affiche un tiret
                            if ($ligne['nom'] == null) {
                                echo '—';
                            } 
                            else
                            {
                                echo htmlspecialchars($ligne['prenom'] . ' ' . $ligne['nom']);
                            }
                            ?>
                        </td>
                        <td><span class="badge statut-<?php echo $ligne['statut']; ?>"><?php echo libelleStatut($ligne['statut']); ?></span></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
    <?php if ($nbPages > 1) { ?>
    <nav class="pagination" aria-label="Pagination du suivi">

        <?php if ($pageCourante > 1) { ?>
            <a href="index.php?page=suivi<?php echo htmlspecialchars($filtresUrl); ?>&amp;p=<?php echo $pageCourante - 1; ?>">‹ Précédent</a>
        <?php } else { ?>
            <span class="inactif">‹ Précédent</span>
        <?php } ?>

        <?php for ($i = 1; $i <= $nbPages; $i++) { ?>
            <?php if ($i == $pageCourante) { ?>
                <span class="courante"><?php echo $i; ?></span>
            <?php } else { ?>
                <a href="index.php?page=suivi<?php echo htmlspecialchars($filtresUrl); ?>&amp;p=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php } ?>
        <?php } ?>

        <?php if ($pageCourante < $nbPages) { ?>
            <a href="index.php?page=suivi<?php echo htmlspecialchars($filtresUrl); ?>&amp;p=<?php echo $pageCourante + 1; ?>">Suivant ›</a>
        <?php } else { ?>
            <span class="inactif">Suivant ›</span>
        <?php } ?>

    </nav>
<?php } ?>
</main>

<?php include 'footer.php'; ?>