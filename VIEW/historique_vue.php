<?php include 'header.php'; ?>

<main class="contenu">
    <h1>Mon historique</h1>
    <p class="sous-titre">Interventions réalisées</p>

    <section class="panneau" style="margin-bottom: 24px;">
        <h2>Mes informations</h2>
        <div class="detail-ligne"><span>Nom</span><span><?php echo htmlspecialchars($utilisateur['nom']); ?></span></div>
        <div class="detail-ligne"><span>Prénom</span><span><?php echo htmlspecialchars($utilisateur['prenom']); ?></span></div>
        <div class="detail-ligne"><span>Email</span><span><?php echo htmlspecialchars($utilisateur['email']); ?></span></div>
        <div class="detail-ligne"><span>Téléphone</span><span><?php echo htmlspecialchars($utilisateur['telephone']); ?></span></div>
        <div class="detail-ligne"><span>Service</span><span><?php echo htmlspecialchars($utilisateur['service']); ?></span></div>
    </section>

    <?php if (count($historique) == 0) { ?>
        <p>Aucune intervention réalisée pour le moment.</p>
    <?php } else { ?>
        <div class="table-defilante">
            <table>
                <tr>
                    <th>Date</th>
                    <th>Intitulé</th>
                    <th>Avion</th>
                    <th>Durée</th>
                    <th>Détail</th>
                </tr>
                <?php foreach ($historique as $intervention) { ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($intervention['date_intervention'])); ?></td>
                        <td><?php echo htmlspecialchars($intervention['intitule']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($intervention['modele']); ?>
                            (<?php echo htmlspecialchars($intervention['immatriculation']); ?>)
                        </td>
                        <td><?php echo dureeIntervention($intervention['heure_debut'], $intervention['heure_fin']); ?></td>
                        <td><a href="index.php?page=fiche&id=<?php echo $intervention['id_intervention']; ?>">Voir</a></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
</main>

<?php include 'footer.php'; ?>