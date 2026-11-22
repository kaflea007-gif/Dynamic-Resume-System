
# Dynamic Resume System

A PHP and MySQL-based dynamic resume management system.

## Requirements
- PHP 8.0 or later
- MySQL or MariaDB
- Web browser

## Installation

1. Create a database named `resume_db` in phpMyAdmin.
2. Import the `resume_db.sql` file.
3. Copy `config/database.example.php` and rename the copy to `database.php`.
4. Update the database username and password in `config/database.php`.
5. Place the project inside your web server directory.

## Run Locally

From the project directory, run:

```bash
php -S localhost:8000
```

Open:

http://localhost:8000/public/

## Admin Login

Use the administrator account stored in the imported database.

## Features
- Public dynamic resume page
- Admin login and logout
- Add, edit, and delete resume entries
- Profile photo upload
- MySQL database integration
- Session-based authentication

## Technologies
- PHP
- MySQL
- HTML
- CSS
