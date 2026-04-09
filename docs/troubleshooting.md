# Dépannage

## La page affiche une erreur 500
- Vérifiez `storage/logs/app.log`.
- Confirmez les identifiants MySQL dans `config/config.php`.
- Vérifiez que PHP 8.1+ est bien actif dans cPanel.

## Les styles ne chargent pas
- Renseignez `app.base_url` si le dossier est servi dans un sous-répertoire non standard.
- Vérifiez que `public/assets/...` est bien uploadé.

## Impossible de se connecter
- En mode autonome, confirmez `auth.admin_email` et `auth.admin_password_hash`.
- Après plusieurs essais, attendez la fin de la fenêtre de rate limiting ou supprimez le cache correspondant dans `storage/cache`.
- En mode CMS, vérifiez le chemin de bootstrap WordPress/Joomla.

## L’événement reste en “Diffusion prévue”
- Vérifiez le fuseau horaire sélectionné dans la fiche.
- Souvenez-vous que la base stocke l’heure en UTC.
- Vérifiez `duration_minutes`.

## Le lecteur YouTube n’apparaît pas
- Vérifiez que le lien ou l’identifiant contient bien une vidéo YouTube valide.
- En direct, si la fenêtre horaire est active mais que le champ live est vide, le module affiche volontairement un placeholder.

## Le bouton de téléchargement ne s’affiche pas
- Si vous utilisez `download_url`, assurez-vous qu’il s’agit d’une URL absolue ou d’un chemin commençant par `/`.
- Si vous utilisez `local_file_path`, vérifiez que le fichier existe bien dans `storage/uploads`.
