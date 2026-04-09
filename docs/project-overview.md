# Lifstories Broadcast

## Vue d’ensemble
Lifstories Broadcast est un mini-module PHP/MySQL pour gérer des diffusions YouTube Live, leur bascule vers la rediffusion et, si besoin, un bouton de téléchargement de la vidéo finale. Le projet est conçu pour un hébergement cPanel classique, sans Node.js, sans Docker et sans processus serveur séparé.

## Fonctionnalités principales
- Tableau de bord administrateur avec création, modification, suppression, publication et recherche.
- Page publique par slug avec état planifié, direct, rediffusion ou archive.
- Logique de statut automatique basée sur la date de début, la durée prévue et la présence d’une rediffusion.
- Override manuel du statut si l’équipe doit forcer un affichage.
- Parsing YouTube flexible : URL `watch`, `youtu.be`, `live`, `embed` ou identifiant brut.
- Téléchargement via URL publique ou via fichier local sécurisé dans `storage/uploads`.
- Authentification autonome par session PHP ou intégration WordPress/Joomla via bootstrap du CMS.
- Protection CSRF, PDO préparé, sessions durcies, rate limiting de connexion et échappement de sortie.

## Stack
- PHP 8.1+
- MySQL ou MariaDB
- Apache sur cPanel
- CSS/JS natifs, sans build step

## Logique des statuts
- `scheduled` si l’heure actuelle est avant `start_at`
- `live` si l’heure actuelle est entre `start_at` et `start_at + duration_minutes`
- `replay` si l’événement est terminé et qu’une rediffusion existe
- `archived` si l’événement est forcé en archive ou terminé sans rediffusion

`start_at` est stocké en UTC dans la base. Le champ `timezone` sert à convertir l’affichage et à interpréter correctement la saisie admin.
