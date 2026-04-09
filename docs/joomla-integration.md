# Intégration Joomla

## Option A : module HTML personnalisé avec iframe
La solution la plus simple consiste à afficher l’événement public dans un iframe responsif.

Voir `docs/snippets/joomla-custom-html.html`.

## Option B : auth admin via session Joomla
1. Dans `config/config.php`, mettez `auth.mode` à `joomla`.
2. Définissez `auth.joomla.root_path` vers la racine du site Joomla.
3. Définissez `auth.joomla.group_ids` selon les groupes autorisés.
4. Facultatif : définissez `auth.joomla.login_url` si vous voulez une redirection explicite vers la page de connexion Joomla.

## Notes
- L’iframe reste l’intégration la plus stable sur hébergement mutualisé.
- Le bootstrap Joomla dépend davantage de la version du site et de sa structure. Testez la connexion admin sur un environnement de préproduction avant la mise en ligne.
