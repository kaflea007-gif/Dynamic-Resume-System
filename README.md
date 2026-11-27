# Dynamic Resume Management System

**Web Programming II – Assignment 2**  
**Dong-Eui University**

## 1. Project Introduction

This project is a dynamic resume management system developed using PHP and MySQL.

It allows an administrator to manage resume information through a web interface. The public page displays the resume, including personal information, education, skills, projects, and work experience.

The main purpose of this project is to practice PHP, database operations, form handling, file uploads, and session management.

## 2. Technologies Used

- PHP
- MySQL
- HTML
- CSS
- PDO
- XAMPP

## 3. Main Features

- Admin login and logout
- Display resume information on a public page
- Add, edit, and delete resume entries
- Manage skills and skill levels
- Update profile information
- Upload and update a profile image
- Store resume information in a MySQL database
- Validate form input and uploaded images

## 4. Project Structure

```text
dynamic_resume/
├── assets/
├── config/
├── includes/
├── public/
├── uploads/
├── index.php
└── resume_db.sql
```

## 5. Installation and Setup

1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Copy the project folder into the XAMPP `htdocs` directory.
4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Create a database named `resume_db`.
6. Import the provided `resume_db.sql` file.
7. Check the database settings in `config/database.php`.
8. Open the project in your browser:

   `http://localhost/dynamic_resume/public/index.php`

## 6. Admin Login

Use the following account for the provided sample database:

- Email: `admin@example.com`
- Password: `Admin@12345`

These are sample project credentials. Change them before using the application outside the assignment environment.

## 7. Project Files

The `resume_db.sql` file contains the database structure and sample data needed to run the project.

The `uploads` directory is used to store uploaded profile images.

## 8. Learning Outcomes

While developing this project, I practiced connecting PHP to MySQL using PDO, performing database CRUD operations, handling user sessions, validating form data, and uploading images.

I also learned how different PHP files can work together to display and manage information dynamically.