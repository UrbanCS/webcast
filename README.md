# Lifstories Broadcast

Module webcast PHP/MySQL prêt pour cPanel, conçu pour piloter le cycle de vie d’une diffusion YouTube Live, afficher la rediffusion ensuite, et proposer un lien de téléchargement si nécessaire. Le projet reste autonome pour être simple à déployer, tout en offrant des chemins d’intégration WordPress et Joomla.

## Arborescence
text
lifstories-broadcast/
├── .htaccess
├── admin.php
├── download.php
├── event.php
├── index.php
├── login.php
├── logout.php
├── admin/
│   ├── index.php
│   └── events/
│       ├── create/index.php
│       ├── delete.php
│       ├── edit/index.php
│       ├── index.php
│       └── toggle-publish.php
├── app/
│   ├── Controllers/
│   ├── Helpers/
│   ├── Models/
│   ├── Services/
│   └── Views/
├── config/
│   ├── config.example.php
│   ├── config.php
│   └── lang/fr.php
├── database/
│   ├── install.sql
│   ├── sample-data.sql
│   └── schema.sql
├── docs/
│   ├── cpanel-installation.md
│   ├── deployment-checklist.md
│   ├── joomla-integration.md
│   ├── local-testing.md
│   ├── project-overview.md
│   ├── security-notes.md
│   ├── troubleshooting.md
│   ├── wordpress-integration.md
│   └── snippets/
├── public/
│   └── assets/
│       ├── css/
│       ├── js/
│       └── uploads/
└── storage/
    ├── cache/
    ├── logs/
    └── uploads/

## Fonctionnalités livrées
- CRUD complet des diffusions.
- Publication et dépublication.
- Statuts automatiques et override manuel.
- Pages publiques responsive.
- Embed YouTube Live / replay.
- Bouton de téléchargement via URL ou fichier local sécurisé.
- Connexion admin autonome avec session PHP, hash de mot de passe et rate limiting.
- Adaptateurs d’authentification WordPress/Joomla quand le bootstrap CMS est configuré.

## Mise en route rapide
1. Uploadez le dossier sur cPanel.
2. Importez database/install.sql dans phpMyAdmin.
3. Mettez à jour config/config.php.
4. Ouvrez login.php.
5. Connectez-vous avec admin@example.com / ChangeMe!123, puis changez ces valeurs dans la config.

## Documentation
- Vue d’ensemble : docs/project-overview.md
- Installation cPanel : docs/cpanel-installation.md
- WordPress : docs/wordpress-integration.md
- Joomla : docs/joomla-integration.md
- Sécurité : docs/security-notes.md
- Dépannage : docs/troubleshooting.md
- Checklist : docs/deployment-checklist.md

## Décisions de conception
- Pas de plugin WordPress complet ni d’extension Joomla complète : le cœur reste un module autonome.
- Pas de backend vidéo custom : l’ingestion et la diffusion sont gérées par YouTube Live.
- Pas de système d’auth maison complexe : le mode autonome est volontairement minimal, sinon le CMS peut porter l’authentification.
