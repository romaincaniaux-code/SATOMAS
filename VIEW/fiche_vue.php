<?php include 'header.php'; ?>

<main class="contenu">
    <p><a href="index.php?page=catalogue">← Retour au catalogue</a></p>

    <?php if (isset($_GET['ok'])) { ?>
        <p class="message-ok">Intervention acceptée.</p>
    <?php } ?>

    <?php if ($message != '') { ?>
        <p class="message-erreur"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <div class="fiche">
        <section class="panneau">
            <span class="badge"><?php echo htmlspecialchars($intervention['categorie']); ?></span>
            <h1><?php echo htmlspecialchars($intervention['intitule']); ?></h1>
            <p class="info"><?php echo htmlspecialchars($intervention['description']); ?></p>
        </section>

        <aside class="panneau">
            <h2>Détails</h2>
            <div class="detail-ligne">
                <span>Avion</span>
                <span><?php echo htmlspecialchars($intervention['modele']); ?> (<?php echo htmlspecialchars($intervention['immatriculation']); ?>)</span>
            </div>
            <div class="detail-ligne">
                <span>Lieu</span>
                <span><?php echo htmlspecialchars($intervention['lieu']); ?></span>
            </div>
            <div class="detail-ligne">
                <span>Date</span>
                <span><?php echo date('d/m/Y', strtotime($intervention['date_intervention'])); ?></span>
            </div>
            <div class="detail-ligne">
                <span>Horaires</span>
                <span><?php echo substr($intervention['heure_debut'], 0, 5); ?> - <?php echo substr($intervention['heure_fin'], 0, 5); ?></span>
            </div>
            <div class="detail-ligne">
                <span>Statut</span>
                <span class="badge statut-<?php echo $intervention['statut']; ?>"><?php echo libelleStatut($intervention['statut']); ?></span>
            </div>

            <?php if ($peutAccepter) { ?>
                <form method="post" action="index.php?page=fiche&id=<?php echo $intervention['id_intervention']; ?>">
                    <button class="btn btn-orange" type="submit" name="accepter">Accepter l'intervention</button>
                </form>
            <?php } else { ?>
                <button class="btn" type="button" disabled>Accepter l'intervention</button>
                <p class="raison"><?php echo htmlspecialchars($raison); ?></p>
            <?php } ?>
        </aside>
    </div>
</main>

<?php include 'footer.php'; ?>