# Dynamic Resume System

A dynamic personal resume website built with PHP, MySQL, HTML, and CSS.

## Requirements

- PHP 8.0 or later
- MySQL or MariaDB
- PDO MySQL extension
- GD extension for profile image processing
- Web browser

## Installation

1. Create a MySQL database named `resume_db`, or use the included SQL file which creates it.
2. Import `resume_db.sql` using phpMyAdmin or the MySQL command line.
3. Copy `config/database.example.php` to `config/database.php`.
4. Edit `config/database.php` with your local database username and password.
5. Make sure the `uploads` directory exists and is writable by PHP.

Do not upload your real `config/database.php` or database credentials to GitHub.

## Run Locally

From the project directory:

```bash
php -S localhost:8000
```

Open the public resume:

http://localhost:8000/public/

Open the administrator login:

http://localhost:8000/public/login.php

## Administrator Login

The sample database contains the account:

- Email: `admin@example.com`
- Password: Use the password already configured for this local account.

Email: admin@example.com

Password: Resume@2026!

Change this demo password before using the system in production.

## Features

- Dynamic public resume
- Administrator login and logout
- Session-based access protection
- Add, edit, and delete resume entries
- Profile image upload and processing
- MySQL database integration
- Input validation and output escaping

## Technologies

- PHP
- MySQL / MariaDB
- HTML
- CSS
- Git and GitHub

## Developer

Anish Kafle  
Intelligence Computing Student  
Dong-Eui University, South Korea
