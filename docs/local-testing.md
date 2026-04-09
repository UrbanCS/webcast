# Test local

## En local sans CMS
1. Créez une base MySQL/MariaDB.
2. Importez `database/install.sql`.
3. Ajustez `config/config.php`.
4. Servez le dossier avec un environnement PHP local.
5. Connectez-vous à `login.php`.

## En local avec WordPress
1. Placez le module à côté du projet WordPress.
2. Passez `auth.mode` à `wordpress`.
3. Renseignez le chemin absolu vers `wp-load.php`.
4. Connectez-vous à WordPress avec un compte administrateur, puis ouvrez `admin.php`.

## En local avec Joomla
1. Placez le module à côté du projet Joomla.
2. Passez `auth.mode` à `joomla`.
3. Renseignez `auth.joomla.root_path`.
4. Testez ensuite `admin.php`.
