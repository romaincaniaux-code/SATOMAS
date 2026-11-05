<?php require 'header.php'; ?>

<h1>Accueil</h1>
<p>Bienvenue <?php echo htmlspecialchars($_SESSION['utilisateur']['prenom'] . ' ' . $_SESSION['utilisateur']['nom']); ?></p>

<?php require 'footer.php'; ?>