# Campus Pulse — UIU (Full-stack: HTML/CSS/JS + PHP + MySQL)

This is your Web Programming course project, rebuilt from the HTML prototype
into a real client-server app:

- **Frontend:** plain HTML + CSS + vanilla JS (`public/index.php`, `public/assets/`)
- **Backend:** PHP 8 with PDO (`public/api/*.php`)
- **Database:** MySQL / MariaDB (`database/schema.sql`)

The look, layout, and every feature from your prototype (login with 3 roles,
home feed, events with approval workflow, resources hub with real file
uploads, research grants, campus alerts/status, user directory, search,
command palette, dark mode, profile with photo upload) all work the same way
— except now everything is actually stored in a database and survives a
page refresh / different browser / different user.

---

## 1. Requirements

Any local PHP+MySQL stack works. The easiest for a UIU student is **XAMPP**:
https://www.apachefriends.org/ (Apache + MySQL/MariaDB + PHP + phpMyAdmin, all in one).

## 2. Install

1. Install XAMPP and start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Copy the whole `campus-pulse` folder into XAMPP's web root:
   - Windows: `C:\xampp\htdocs\campus-pulse`
   - macOS: `/Applications/XAMPP/htdocs/campus-pulse`
   - Linux: `/opt/lampp/htdocs/campus-pulse`
3. Create the database:
   - Open **phpMyAdmin** (http://localhost/phpmyadmin)
   - Click **Import** → choose `database/schema.sql` → **Go**
   - This creates the `campus_pulse` database, all tables, and seed data
     (demo news/events/resources + 6 demo user accounts).
4. Check `config/db.php` — the defaults (`host=localhost`, `user=root`,
   `password=""`) match a stock XAMPP install, so you usually don't need to
   change anything. If your MySQL root user has a password, put it there.
5. Open the app: **http://localhost/campus-pulse/public/**

That's it — no `composer install`, no build step, no npm. It's plain PHP.

## 3. Demo accounts

All demo accounts use the password: **`password123`**

| Email | Role |
|---|---|
| shad@uiu.ac.bd | Student |
| farhana@uiu.ac.bd | Faculty |
| admin@uiu.ac.bd | Admin |

(A few more student/faculty accounts exist too — see `database/schema.sql`.)

Pick the matching role tab on the login screen before signing in — the
account's real role is checked against your selection server-side.

## 4. Project structure

```
campus-pulse/
├── config/
│   └── db.php              # DB connection settings — edit if needed
├── includes/
│   └── helpers.php         # session/auth/JSON helpers shared by all API files
├── database/
│   └── schema.sql          # run this once in phpMyAdmin / mysql CLI
└── public/                 # <-- this is your web root (point Apache here)
    ├── index.php           # the whole app shell (login + dashboard)
    ├── assets/
    │   ├── css/style.css
    │   └── js/app.js       # all frontend logic, talks to /api/*.php
    ├── uploads/
    │   ├── resources/      # uploaded notes / question papers land here
    │   └── avatars/        # uploaded profile photos land here
    └── api/                # PHP endpoints (session-auth, PDO, JSON in/out)
        ├── login.php / logout.php / me.php
        ├── events.php      # list, create, approve/reject, "interested" ping
        ├── resources.php   # list, upload (multipart), approve/reject, download
        ├── grants.php      # research grant submissions + approval
        ├── alerts.php      # ticker alerts + campus status (admin only)
        ├── directory.php   # user directory (admin only)
        ├── feed.php        # news + achievements
        ├── search.php      # cross-content search
        └── profile.php     # update name/dept/bio + avatar upload
```

## 5. How the roles/permissions work

- **Student:** browse everything, ping "interested" on events, upload
  resources (goes to pending until admin approves), submit nothing else.
- **Faculty:** everything a student can do, plus create events (pending
  approval) and submit research grants (pending approval).
- **Admin:** everything is auto-approved when *they* create it, plus they can
  approve/reject faculty-submitted events, resources, and grants; manage the
  campus-wide status pill and the alert ticker; and browse the full user
  directory.

All of this is enforced **server-side** in the PHP files (not just hidden in
the UI), using `require_role([...])` in `includes/helpers.php`.

## 6. For your report / viva

A few things worth knowing if you need to explain the implementation:

- **Auth:** PHP sessions (`$_SESSION['user_id']`), passwords hashed with
  `password_hash()` / verified with `password_verify()` (bcrypt).
- **DB access:** PDO with prepared statements everywhere (protects against
  SQL injection).
- **API style:** each `api/*.php` file is a small REST-ish endpoint — GET to
  read, POST with an `action` field to write (create/approve/reject/etc).
  The frontend JS (`app.js`) calls these with `fetch()` and re-renders.
- **File uploads:** handled with PHP's `$_FILES` + `move_uploaded_file()`,
  stored under `public/uploads/`, path saved in the DB.
- **No frameworks** — deliberately plain PHP/JS so every line is something
  you can explain, no "magic" from a framework to hand-wave over.

## 7. Troubleshooting

- **"Database connection failed"** → check MySQL is running in XAMPP and
  the credentials in `config/db.php` match.
- **Blank page / 500 error** → open XAMPP's Apache error log, or run
  `php -l public/api/whatever.php` to check for a typo you introduced.
- **Uploads not saving** → make sure `public/uploads/resources/` and
  `public/uploads/avatars/` are writable (on Linux/macOS: `chmod -R 775 public/uploads`).
