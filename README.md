Lifstories Broadcast
A PHP/MySQL webcast module ready for cPanel, designed to manage the lifecycle of a YouTube Live broadcast, display the replay afterward, and provide a download link when needed. The project remains self-contained to keep deployment simple, while also offering WordPress and Joomla integration paths.

Directory Structure
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
Delivered Features
Full broadcast CRUD.
Publish and unpublish actions.
Automatic statuses with manual override.
Responsive public pages.
YouTube Live / replay embed.
Download button via URL or secure local file.
Standalone admin login with PHP session, password hashing, and rate limiting.
WordPress/Joomla authentication adapters when CMS bootstrap is configured.
Quick Start
Upload the folder to cPanel.
Import database/install.sql into phpMyAdmin.
Update config/config.php.
Open login.php.
Sign in with admin@example.com / ChangeMe!123, then change these values in the config.
Documentation
Overview: docs/project-overview.md
cPanel installation: docs/cpanel-installation.md
WordPress: docs/wordpress-integration.md
Joomla: docs/joomla-integration.md
Security: docs/security-notes.md
Troubleshooting: docs/troubleshooting.md
Checklist: docs/deployment-checklist.md
Design Decisions
No full WordPress plugin or full Joomla extension: the core remains a standalone module.
No custom video backend: ingestion and streaming are handled by YouTube Live.
No complex custom auth system: standalone mode is intentionally minimal, otherwise the CMS can handle authentication.
