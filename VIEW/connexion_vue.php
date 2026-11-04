<?php include 'header.php'; ?>

<h1>Connexion</h1>

<?php if ($erreur != '') { ?>
    <p style="color:red;"><?php echo htmlspecialchars($erreur); ?></p>
<?php } ?>

<form method="post" action="index.php?page=connexion">
    <label>Email professionnel</label><br>
    <input type="email" name="email" required><br><br>
    <label>Mot de passe</label><br>
    <input type="password" name="mot_de_passe" required><br><br>
    <button type="submit">Se connecter</button>
</form>

<?php include 'footer.php'; ?>