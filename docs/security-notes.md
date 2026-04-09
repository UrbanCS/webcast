# Notes de sécurité

## Mesures incluses
- PDO avec requêtes préparées.
- Échappement HTML systématique dans les vues.
- CSRF sur les formulaires admin.
- Sessions PHP durcies avec cookies `HttpOnly` et `SameSite`.
- Rate limiting simple sur la connexion autonome.
- Téléversement avec whitelist d’extensions et vérification MIME.
- Fichiers locaux stockés dans `storage/uploads` et servis par `download.php`.
- Protection `.htaccess` sur `storage/`, `app/Views/` et les uploads publics.

## Recommandations avant production
- Remplacez les identifiants par défaut dans `config/config.php`.
- Activez HTTPS et mettez `security.secure_cookies` à `true`.
- Servez le module sous le même domaine que WordPress/Joomla si vous utilisez un iframe.
- Restreignez l’accès admin via IP au niveau cPanel si votre contexte le permet.
