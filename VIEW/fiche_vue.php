<?php include 'header.php'; ?>

<p><a href="index.php?page=catalogue">← Retour au catalogue</a></p>

<h2><?php echo htmlspecialchars($intervention['intitule']); ?></h2>

<?php if (isset($_GET['ok'])) { ?>
    <p style="color:green;">Intervention acceptée.</p>
<?php } ?>

<?php if ($message != '') { ?>
    <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<p>Catégorie : <?php echo htmlspecialchars($intervention['categorie']); ?></p>
<p>Avion : <?php echo htmlspecialchars($intervention['modele']); ?> (<?php echo htmlspecialchars($intervention['immatriculation']); ?>)</p>
<p>Lieu : <?php echo htmlspecialchars($intervention['lieu']); ?></p>
<p>Date : <?php echo htmlspecialchars($intervention['date_intervention']); ?></p>
<p>Horaires : <?php echo substr($intervention['heure_debut'], 0, 5); ?> à <?php echo substr($intervention['heure_fin'], 0, 5); ?></p>
<p>Statut : <?php echo libelleStatut($intervention['statut']); ?></p>
<p>Description : <?php echo htmlspecialchars($intervention['description']); ?></p>

<?php if ($peutAccepter) { ?>
    <form method="post" action="index.php?page=fiche&id=<?php echo $intervention['id_intervention']; ?>">
        <button type="submit" name="accepter">Accepter l'intervention</button>
    </form>
<?php } else { ?>
    <button type="button" disabled>Accepter l'intervention</button>
    <p><?php echo htmlspecialchars($raison); ?></p>
<?php } ?>

<?php include 'footer.php'; ?>