<?php include 'header.php'; ?>

<main class="contenu">
    <h1>Mon planning du jour</h1>
    <p class="sous-titre"><?php echo date('d/m/Y', strtotime($date)); ?></p>

    <div class="navigation-jours">
        <a class="btn btn-contour" href="index.php?page=planning&date=<?php echo $jourPrecedent; ?>">‹ Jour précédent</a>
        <a class="btn btn-contour" href="index.php?page=planning">Aujourd'hui</a>
        <a class="btn btn-contour" href="index.php?page=planning&date=<?php echo $jourSuivant; ?>">Jour suivant ›</a>
    </div>

    <?php if (isset($_GET['ok'])) { ?>
        <p class="message-ok">Intervention annulée : elle est de nouveau disponible.</p>
    <?php } ?>

    <?php if ($message != '') { ?>
        <p class="message-erreur"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <?php if (count($planning) == 0) { ?>
        <p>Aucune intervention ce jour-là.</p>
    <?php } ?>

    <?php foreach ($planning as $p) { ?>
        <article class="carte planning-item">
            <div>
                <p class="planning-heure">
                    <?php echo substr($p['heure_debut'], 0, 5); ?> - <?php echo substr($p['heure_fin'], 0, 5); ?>
                </p>
                <h3>
                    <a href="index.php?page=fiche&id=<?php echo $p['id_intervention']; ?>">
                        <?php echo htmlspecialchars($p['intitule']); ?>
                    </a>
                </h3>
                <p class="info">
                    <?php echo htmlspecialchars($p['modele']); ?> (<?php echo htmlspecialchars($p['immatriculation']); ?>)
                    - <?php echo htmlspecialchars($p['lieu']); ?>
                </p>
                <span class="badge statut-<?php echo $p['statut']; ?>"><?php echo libelleStatut($p['statut']); ?></span>
            </div>

            <?php
            // ! On peut annuler seulement si attribuée et pas encore commencée
            $debut = $date . ' ' . $p['heure_debut'];
            if ($p['statut'] == 'attribuee' && $debut > $maintenant) { ?>
                <form method="post" action="index.php?page=planning&date=<?php echo $date; ?>">
                    <input type="hidden" name="id_intervention" value="<?php echo $p['id_intervention']; ?>">
                    <button class="btn btn-rouge" type="submit" name="annuler">Annuler</button>
                </form>
            <?php } else { ?>
                <p class="raison">Annulation impossible (déjà commencée ou terminée).</p>
            <?php } ?>
        </article>
    <?php } ?>
</main>

<?php include 'footer.php'; ?>