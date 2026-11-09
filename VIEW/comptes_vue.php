<?php include 'header.php'; ?>

<main class="contenu">
    <h1>Gestion des comptes</h1>
    <p class="sous-titre">Créer, modifier le rôle ou supprimer un utilisateur</p>

    <?php
    // ! Message de réussite après une action
    $texteOk = '';
    if (isset($_GET['ok'])) {
        if ($_GET['ok'] == 'creation') {
            $texteOk = 'Compte créé.';
        } elseif ($_GET['ok'] == 'role') {
            $texteOk = 'Rôle modifié.';
        } elseif ($_GET['ok'] == 'suppr') {
            $texteOk = 'Compte supprimé.';
        }
    }
    ?>

    <?php if ($texteOk != '') { ?>
        <p class="message-ok"><?php echo $texteOk; ?></p>
    <?php } ?>

    <?php if ($erreur != '') { ?>
        <p class="message-erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php } ?>

    <?php if ($utilisateurSuppr) { ?>
        <section class="panneau confirmation">
            <h2>Supprimer le compte de <?php echo htmlspecialchars($utilisateurSuppr['prenom'] . ' ' . $utilisateurSuppr['nom']); ?> ?</h2>
            <p>Cette action est irréversible.</p>
            <form class="actions" method="post" action="index.php?page=comptes">
                <input type="hidden" name="id_utilisateur" value="<?php echo $utilisateurSuppr['id_utilisateur']; ?>">
                <button class="btn btn-rouge" type="submit" name="supprimer">Oui, supprimer</button>
                <a class="btn btn-contour" href="index.php?page=comptes">Annuler</a>
            </form>
        </section>
    <?php } ?>

    <?php if ($utilisateurRole) { ?>
        <section class="panneau formulaire espace-bas">
            <h2>Modifier le rôle de <?php echo htmlspecialchars($utilisateurRole['prenom'] . ' ' . $utilisateurRole['nom']); ?></h2>
            <form method="post" action="index.php?page=comptes">
                <input type="hidden" name="id_utilisateur" value="<?php echo $utilisateurRole['id_utilisateur']; ?>">

                <label for="nouveau_role">Nouveau rôle</label>
                <select id="nouveau_role" name="nouveau_role">
                    <?php foreach ($rolesPossibles as $r) { ?>
                        <option value="<?php echo $r; ?>" <?php if ($utilisateurRole['role'] == $r) { echo 'selected'; } ?>>
                            <?php echo libelleRole($r); ?>
                        </option>
                    <?php } ?>
                </select>

                <div class="actions">
                    <button class="btn" type="submit" name="changer_role">Enregistrer</button>
                    <a class="btn btn-contour" href="index.php?page=comptes">Annuler</a>
                </div>
            </form>
        </section>
    <?php } ?>

    <div class="table-defilante">
        <table>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Service</th>
                <th>Rôle</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($utilisateurs as $utilisateur) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['service']); ?></td>
                    <td><span class="badge role-<?php echo $utilisateur['role']; ?>"><?php echo libelleRole($utilisateur['role']); ?></span></td>
                    <td><?php echo date('d/m/Y', strtotime($utilisateur['date_creation'])); ?></td>
                    <td>
                        <a href="index.php?page=comptes&modifier_role=<?php echo $utilisateur['id_utilisateur']; ?>">Modifier le rôle</a>
                        <?php if ($utilisateur['id_utilisateur'] == $idConnecte) { ?>
                            | <em>Compte actuel</em>
                        <?php } else { ?>
                            | <a href="index.php?page=comptes&supprimer=<?php echo $utilisateur['id_utilisateur']; ?>">Supprimer</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <section class="panneau formulaire espace">
        <h2>Créer un compte</h2>
        <form method="post" action="index.php?page=comptes">

            <div class="formulaire-ligne">
                <div>
                    <label for="nom">Nom</label>
                    <input id="nom" type="text" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                </div>
                <div>
                    <label for="prenom">Prénom</label>
                    <input id="prenom" type="text" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>
                </div>
            </div>

            <div class="formulaire-ligne">
                <div>
                    <label for="email">Email professionnel</label>
                    <input id="email" type="text" name="email" placeholder="prenom.nom@mas.fr" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div>
                    <label for="service">Service</label>
                    <input id="service" type="text" name="service" placeholder="Production, Études…" value="<?php echo htmlspecialchars($service); ?>" required>
                </div>
            </div>

            <div class="formulaire-ligne">
                <div>
                    <label for="role_compte">Rôle</label>
                    <select id="role_compte" name="role_compte">
                        <?php foreach ($rolesPossibles as $r) { ?>
                            <option value="<?php echo $r; ?>" <?php if ($roleChoisi == $r) { echo 'selected'; } ?>>
                                <?php echo libelleRole($r); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="mot_de_passe">Mot de passe provisoire</label>
                    <input id="mot_de_passe" type="text" name="mot_de_passe" required>
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-orange" type="submit" name="creer">Créer le compte</button>
            </div>
        </form>
    </section>
</main>

<?php include 'footer.php'; ?>