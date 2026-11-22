# Dynamic Resume System

A PHP 8+ / MySQL (PDO) portfolio CMS: a read-only public CV plus a session-protected admin dashboard with full CRUD, skills management and GD-processed profile thumbnails.

## Admin login (seed account)

| Email | Password |
|-------|----------|
| `admin@example.com` | `Admin@12345` |

## Requirements
- PHP 8.0+ with the `pdo_mysql`, `gd` and `fileinfo` extensions
- MySQL or MariaDB

## Installation
1. Import `resume_db.sql` (phpMyAdmin > Import, or `mysql -u root -p < resume_db.sql`). It creates the `resume_db` database, tables and sample data.
2. Edit the credentials in `config/database.php` if your MySQL user is not `root` with an empty password.
3. Make sure `uploads/` is writable by the web server.

## Run locally
From the project directory:

```bash
php -S localhost:8000
```

Open http://localhost:8000/public/ (admin: http://localhost:8000/public/login.php).

## Structure
- `config/database.php` - PDO connection (exceptions, assoc fetch, real prepares)
- `includes/` - `functions.php` (escaping, CSRF, validation, GD thumbnail + upload handling), `header.php`, `footer.php`
- `public/` - `index.php` (public CV), `login.php`, `logout.php`, `admin.php` (dashboard, avatar upload, skills), `entry_form.php` (create/edit), `delete_entry.php`
- `uploads/` - GD-generated 150x150 thumbnails
- `resume_db.sql` - schema (`users`, `resume_entries`, `skills`) and sample records

## Security notes
- All queries use PDO prepared statements; all output goes through `htmlspecialchars()`.
- Passwords use `password_hash()` / `password_verify()`; the session ID is regenerated on login.
- Uploads: error-code audit, `is_uploaded_file()`, 2 MB cap, `finfo` MIME check (JPEG/PNG only), random filenames, GD resize to 150x150, old file removed with `unlink()`.
- POST forms are protected with a CSRF token.
