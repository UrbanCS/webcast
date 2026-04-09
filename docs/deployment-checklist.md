# Checklist de déploiement

- Les fichiers du dossier `lifstories-broadcast` sont uploadés sur le bon domaine.
- La base MySQL/MariaDB existe.
- `database/install.sql` a été importé via phpMyAdmin.
- `config/config.php` contient les bons identifiants.
- `auth.admin_email` et `auth.admin_password_hash` ont été changés.
- `app.base_url` est vérifié si le module vit dans un sous-dossier.
- `storage/` est writable par PHP.
- L’accès public `index.php` fonctionne.
- L’accès admin `login.php` fonctionne.
- Un événement test a été créé.
- La page publique du slug test s’affiche.
- Le lien YouTube Live ou Replay s’embed correctement.
- Le téléchargement via URL ou fichier local a été vérifié.
- L’intégration WordPress/Joomla choisie a été testée sur le site cible.
