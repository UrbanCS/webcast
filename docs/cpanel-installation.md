# Installation cPanel

## 1. Envoyer les fichiers
1. Uploadez le dossier `lifstories-broadcast` dans votre hébergement via le Gestionnaire de fichiers cPanel ou par FTP.
2. Placez-le par exemple dans `public_html/lifstories-broadcast`.

## 2. Créer la base de données
1. Dans cPanel, ouvrez `MySQL Databases`.
2. Créez une base, un utilisateur et associez-les avec tous les privilèges.
3. Notez le nom complet de la base et de l’utilisateur, souvent préfixés par le compte cPanel.

## 3. Importer le SQL
1. Ouvrez `phpMyAdmin`.
2. Sélectionnez la base créée.
3. Importez `database/install.sql`.

## 4. Configurer l’application
1. Ouvrez `config/config.php`.
2. Remplissez `database.host`, `database.name`, `database.username`, `database.password`.
3. Ajustez `app.base_url` si vous voulez une URL fixe, par exemple `https://example.com/lifstories-broadcast`.
4. Laissez `auth.mode` sur `standalone` pour un test rapide, ou passez à `wordpress` / `joomla` si vous activez l’intégration CMS.

## 5. Connexion admin autonome
- Courriel par défaut : `admin@example.com`
- Mot de passe par défaut : `ChangeMe!123`

Changez immédiatement ces valeurs dans `config/config.php` avant la mise en production.

## 6. Permissions recommandées
- Dossiers : `755`
- Fichiers : `644`
- `storage/` doit rester accessible en écriture PHP pour les logs, le cache de rate limiting et les uploads.

## 7. URL utiles
- Accueil public : `/lifstories-broadcast/`
- Page d’événement : `/lifstories-broadcast/event.php?slug=mon-slug`
- Dashboard : `/lifstories-broadcast/admin.php`
- Connexion : `/lifstories-broadcast/login.php`

## 8. Test local rapide
1. Placez le dossier sur un environnement PHP local.
2. Créez une base MySQL/MariaDB.
3. Importez `database/install.sql`.
4. Mettez à jour `config/config.php`.
5. Ouvrez `index.php` dans votre navigateur local.
