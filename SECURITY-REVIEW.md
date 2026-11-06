# Revue de sécurité — SATOMAS

## Vue d’ensemble

La revue statique du projet n’a pas permis de confirmer une vulnérabilité exploitable majeure de type injection SQL ou XSS dans le code PHP actuel. Toutefois, plusieurs points de sécurité sont encore faibles ou incomplets pour un déploiement réel en environnement partagé ou Internet.

## Points identifiés

### 1) Identifiants de base de données codés en dur
- Fichier : [MODEL/connexion.php](MODEL/connexion.php)
- Risque : fuite d’information, difficulté de rotation des secrets, exposition en environnement non sécurisé.
- Observation : les identifiants MySQL sont stockés directement dans le code source (`root`, `root`, `127.0.0.1`, port 8889).
- Impact : si le projet est publié ou copié dans un dépôt public, la base de données devient exposée.
- Recommandation : externaliser les variables de configuration via variables d’environnement ou un fichier de config hors du dépôt.

### 2) Absence de protection CSRF sur les actions sensibles
- Fichiers : [CONTROLLER/fiche_controller.php](CONTROLLER/fiche_controller.php), [CONTROLLER/planning_controller.php](CONTROLLER/planning_controller.php), [VIEW/fiche_vue.php](VIEW/fiche_vue.php), [VIEW/planning_vue.php](VIEW/planning_vue.php)
- Risque : action non intentionnelle de modification d’état, exécution de requêtes via un site tiers.
- Observation : les actions « accepter une intervention » et « annuler une intervention » sont déclenchées par des formulaires sans jeton CSRF ni vérification de provenance.
- Impact : un utilisateur authentifié peut être victime d’une requête cross-site entraînant une modification de son planning sans action explicite.
- Recommandation : générer et vérifier un token CSRF pour chaque formulaire modifiant l’état.

### 3) Sécurité de session insuffisante
- Fichiers : [index.php](index.php), [MODEL/deconnexion.php](MODEL/deconnexion.php), [MODEL/gestion_bdd.php](MODEL/gestion_bdd.php)
- Risque : fixation de session, cookies plus facilement exploités, mauvaise isolation entre sessions.
- Observation : la session est démarrée sans paramètres renforcés ; il n’y a pas de configuration explicite pour `session.use_strict_mode`, `session.cookie_httponly`, `session.cookie_secure` et une rotation sécurisée des identifiants n’est pas centralisée.
- Impact : l’application est plus vulnérable à des attaques de fixation ou d’utilisation de session au travers de cookies non protégés.
- Recommandation : définir des paramètres de session sécurisés et régénérer l’identifiant de session de façon systématique lors de l’authentification.

### 4) Validation des entrées et contrôles d’accès à améliorer
- Fichiers : [CONTROLLER/fiche_controller.php](CONTROLLER/fiche_controller.php), [CONTROLLER/planning_controller.php](CONTROLLER/planning_controller.php), [MODEL/gestion_bdd.php](MODEL/gestion_bdd.php)
- Risque : logique métier plus sensible à des valeurs non attendues ou à des manipulations directes.
- Observation : les identifiants de l’intervention et les données utilisateur sont lus depuis des valeurs issues de la requête et converties sans validation métier complète avant traitement.
- Impact : les contrôles principaux reposent sur le rôle en session, mais la robustesse de validation côté serveur reste incomplète pour un usage au-delà d’un contexte pédagogique.
- Recommandation : centraliser la validation des entrées et renforcer les contrôles d’autorisation sur chaque action.

## Conclusion

Le projet est fonctionnel et cohérent pour un usage pédagogique ou interne, mais il n’est pas encore prêt pour un environnement de production sans sécurisation complémentaire. Les risques principaux sont liés à la configuration de sécurité, à l’absence de CSRF et à la gestion des secrets.

## Priorité recommandée

1. Supprimer les identifiants en dur et passer par des variables d’environnement.
2. Ajouter un mécanisme de protection CSRF sur les formulaires modifiant le statut.
3. Renforcer la configuration de session et la validation des entrées.

---

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>
