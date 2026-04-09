```markdown
# Lifstories Broadcast

A lightweight PHP/MySQL webcast module designed for cPanel environments. It enables websites to manage the full lifecycle of a YouTube Live broadcast — from scheduling and live display to replay and optional file download.

Built to be simple, reliable, and easy to deploy, while remaining compatible with WordPress and Joomla integrations.

---

## 🚀 Features

- Full CRUD for broadcast events  
- Publish / unpublish control  
- Automatic event status detection (scheduled, live, replay)  
- Manual status override  
- YouTube Live embed (live + replay)  
- Download button via URL or local file  
- Responsive public pages  
- Lightweight admin dashboard  
- Standalone admin authentication (PHP sessions + password hashing + rate limiting)  
- Compatible with WordPress and Joomla authentication systems  

---

## 📁 Project Structure

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

---

## ⚙️ How It Works

This module does **not** handle video streaming itself.

Instead, it:
1. Uses **YouTube Live** for broadcasting  
2. Embeds the live stream or replay into your site  
3. Manages event scheduling and display logic  
4. Optionally provides a downloadable file (MP4 or external link)  

---

## 🛠️ Quick Start (cPanel)

1. Upload the project folder to your server via cPanel  
2. Create a MySQL database  
3. Import:
```

database/install.sql

```
4. Update:
```

config/config.php

```
5. Open:
```

/login.php

```
6. Login with:
```

Email: [admin@example.com](mailto:admin@example.com)
Password: ChangeMe!123

```
7. Change credentials immediately in config  

---

## 🔐 Authentication

- Standalone mode includes a **minimal admin login system**
- Secured with:
- password hashing  
- PHP sessions  
- basic rate limiting  

### Important:
- No public user accounts  
- No signup system  
- No password reset flow  

When integrated into WordPress or Joomla, authentication can be handled by the CMS.

---

## 🎬 Event Lifecycle

Each broadcast follows this flow:

| Stage       | Behavior |
|------------|---------|
| Scheduled  | Displays date, time, and countdown |
| Live       | Embeds YouTube Live player |
| Replay     | Shows recorded video |
| Download   | Optional download button appears |

Status is automatically calculated based on:
- event start time  
- duration  

Admin can override status manually.

---

## 📦 File Downloads

You can attach a downloadable file by:

- Providing a direct URL  
or  
- Uploading a file to:
```

public/assets/uploads/

```

Supported formats:
- `.mp4`
- `.mov`
- `.zip` (optional)

---

## 🔌 Integration

### WordPress
- Embed via:
- shortcode
- custom page template
- iframe  

See:
```

docs/wordpress-integration.md

```

### Joomla
- Embed via:
  - custom HTML module
  - iframe  

See:
```

docs/joomla-integration.md

```

---

## 📚 Documentation

- Project overview → `docs/project-overview.md`  
- cPanel setup → `docs/cpanel-installation.md`  
- WordPress integration → `docs/wordpress-integration.md`  
- Joomla integration → `docs/joomla-integration.md`  
- Security notes → `docs/security-notes.md`  
- Troubleshooting → `docs/troubleshooting.md`  
- Deployment checklist → `docs/deployment-checklist.md`  

---

## 🔒 Security Notes

- PDO with prepared statements  
- CSRF protection  
- Output escaping  
- Session hardening  
- File upload validation  
- Restricted file execution via `.htaccess`  

---

## 🧠 Design Decisions

- No custom streaming backend → handled by YouTube Live  
- No full WordPress plugin or Joomla extension → kept lightweight  
- No complex authentication system → minimal admin-only auth  
- Optimized for **speed of development and reliability**  

---

## ⚠️ Limitations

- Not a full video hosting platform  
- Requires YouTube Live for broadcasting  
- No advanced user roles or permissions  
- File uploads are basic (not a media manager replacement)  

---

## ✅ Deployment Checklist

- [ ] Upload files to cPanel  
- [ ] Import database  
- [ ] Configure environment  
- [ ] Secure admin credentials  
- [ ] Test event creation  
- [ ] Test live embed  
- [ ] Test replay  
- [ ] Test download link  

---

## 📌 Summary

This project is a **webcast management layer**, not a streaming platform.

It provides a clean, simple way to:
- schedule broadcasts  
- display live video  
- preserve replays  
- offer downloads  

All while staying lightweight, fast, and compatible with common CMS environments.

---
```
