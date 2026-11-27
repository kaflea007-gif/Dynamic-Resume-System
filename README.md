# Dynamic Resume System

Web Programming II - Assignment 2. A PHP 8+ / MySQL (PDO) portfolio CMS: a read-only public resume plus a session-protected admin dashboard with full CRUD and GD-processed profile thumbnails.

## Admin login (seed account)

| Email | Password |
|-------|----------|
| `admin@example.com` | `Admin@12345` |

Passwords are stored only as bcrypt hashes (`password_hash()` / `password_verify()`).

## Requirements
- PHP 8.0+ with `pdo_mysql`, `gd` and `fileinfo` (all enabled in XAMPP)
- MySQL or MariaDB

## Setup (macOS + XAMPP)
1. Copy this folder to `/Applications/XAMPP/xamppfiles/htdocs/dynamic_resume`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin > **Import** > choose `resume_db.sql` > **Go**.
   (Re-importing is safe: it recreates the tables, so data is never duplicated - but it resets any changes you made.)
4. Open http://localhost/dynamic_resume/public/index.php (admin: http://localhost/dynamic_resume/public/login.php).

### Database credentials
`config/database.php` uses the XAMPP defaults (`root`, empty password) - no secrets are stored in it. If your MySQL uses another user/password, create `config/database.local.php` that sets `$username` and `$password` (same variable names as in `database.php`). It overrides the defaults and must never be committed or submitted.

### Uploads
`uploads/` must be writable by the web server. If uploads fail on macOS:
`chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/dynamic_resume/uploads`

## Structure
- `config/database.php` - PDO connection (exceptions, assoc fetch, real prepares, try/catch)
- `includes/` - `functions.php` (escaping, CSRF, validation, GD thumbnail + upload handling), `header.php`, `footer.php`
- `public/` - `index.php` (public CV), `login.php`, `logout.php`, `admin.php` (profile, portrait upload, entries table, skills), `entry_form.php` (create/edit), `delete_entry.php`, `assets/css/style.css`
- `uploads/` - GD-generated 150x150 thumbnails (`.htaccess` blocks script execution)
- `resume_db.sql` - schema (`users`, `resume_entries`, `skills`) and sample records

## Security notes
- All queries use PDO prepared statements; all output goes through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- `session_regenerate_id(true)` on login; every admin script checks the session first and redirects to `login.php`.
- Uploads: `UPLOAD_ERR_OK` check, `is_uploaded_file()`, 2 MB cap, `finfo` MIME check (JPEG/PNG only), random filenames, GD resize to 150x150, old file removed with `unlink()`.
- POST forms use a CSRF token; project links must be http(s) URLs.

## Sample-data notes
- Project start dates in the seed data are placeholders; the public page does not show project dates.
- No work experience is seeded (the public page shows its empty-state notice). Add real jobs in the dashboard.
