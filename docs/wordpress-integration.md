# Intégration WordPress

## Option A : iframe recommandé
La voie la plus simple et la plus robuste est d’héberger le module à côté de WordPress, puis d’afficher la page publique via iframe.

Exemple :
- URL source : `https://example.com/lifstories-broadcast/event.php?slug=hommage-paris&embed=1`
- Snippet prêt à l’emploi : `docs/snippets/wordpress-shortcode.php`

## Option B : page template avec include PHP
Si WordPress et le module sont sur le même hébergement, vous pouvez inclure `event.php` directement dans un template.

Voir `docs/snippets/wordpress-page-template.php`.

## Option C : auth admin via session WordPress
1. Dans `config/config.php`, mettez `auth.mode` à `wordpress`.
2. Définissez `auth.wordpress.bootstrap` vers le chemin absolu de `wp-load.php`.
3. Ajustez `auth.wordpress.capability`, par exemple `manage_options`.

Une fois cette configuration faite, l’accès à `admin.php` utilisera la session WordPress active.

## Notes
- Le module n’est pas un plugin WordPress complet : il reste autonome et cPanel-friendly.
- Pour l’intégration éditoriale, un shortcode iframe est généralement le meilleur compromis entre simplicité et maintenance.
