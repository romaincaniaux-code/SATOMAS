<?php include 'header.php'; ?>
<link rel="stylesheet" href="includes/connexion.css">

<main class="login-main">
    <div class="plane-animation" aria-hidden="true">
        <img src="https://images.vexels.com/media/users/3/145795/isolated/preview/05cd33059a006bf49006097af4ccba98-plane-in-flight-by-vexels.png" alt="">
    </div>

    <section class="login-card">
        <div class="brand">
            <img src="image/logo.png" alt="Logo SATOMAS">
        </div>

        <h1>Connexion</h1>
        <p class="description">Espace interne réservé aux salariés MAS</p>

        <?php if ($erreur != '') { ?>
            <p class="error"><?php echo htmlspecialchars($erreur); ?></p>
        <?php } ?>

        <form method="post" action="index.php?page=connexion">
            <label for="email">Email professionnel</label>
            <input id="email" type="email" name="email" placeholder="prenom.nom@mas.fr" required>

            <label for="mot_de_passe">Mot de passe</label>
            <input id="mot_de_passe" type="password" name="mot_de_passe" placeholder="••••••••" required>

            <button type="submit">Se connecter</button>
        </form>

        <p class="login-note">Pas de création de compte : contactez l'administrateur.</p>
    </section>
</main>

<?php include 'footer.php'; ?>