```markdown
# Lifstories Broadcast

PHP/MySQL webcast module ready for cPanel, designed to manage the lifecycle of a YouTube Live broadcast, display the replay afterward, and provide a download link if needed. The project remains standalone for easy deployment, while offering integration paths for WordPress and Joomla.

## Structure

```

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

```

## Features Delivered

- Full CRUD for broadcasts  
- Publish and unpublish functionality  
- Automatic status handling with manual override  
- Responsive public pages  
- YouTube Live / replay embedding  
- Download button via URL or secure local file  
- Standalone admin authentication with PHP sessions, password hashing, and rate limiting  
- WordPress/Joomla authentication adapters when CMS bootstrap is configured  

## Quick Start

1. Upload the folder to cPanel  
2. Import `database/install.sql` in phpMyAdmin  
3. Update `config/config.php`  
4. Open `login.php`  
5. Log in with `admin@example.com / ChangeMe!123`, then update these credentials in the config  

## Documentation

- Overview: `docs/project-overview.md`  
- cPanel installation: `docs/cpanel-installation.md`  
- WordPress: `docs/wordpress-integration.md`  
- Joomla: `docs/joomla-integration.md`  
- Security: `docs/security-notes.md`  
- Troubleshooting: `docs/troubleshooting.md`  
- Checklist: `docs/deployment-checklist.md`  

## Design Decisions

- No full WordPress plugin or Joomla extension: the core remains a standalone module  
- No custom video backend: ingestion and streaming are handled by YouTube Live  
- No complex custom auth system: standalone mode is intentionally minimal, otherwise the CMS handles authentication  
```
