-- resume_db schema and sample data
-- Admin login: admin@example.com / Admin@12345
-- Re-importing recreates the tables (resets any edits).

CREATE DATABASE IF NOT EXISTS `resume_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resume_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `resume_entries`;
DROP TABLE IF EXISTS `users`;

-- Table 1: users (administrator profile and authentication)
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `headline` VARCHAR(150) NOT NULL,
  `summary` TEXT NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar_path` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: resume_entries (experience, education, projects)
CREATE TABLE `resume_entries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `entry_type` ENUM('experience','education','project') NOT NULL DEFAULT 'experience',
  `title` VARCHAR(120) NOT NULL,
  `organization` VARCHAR(120) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL DEFAULT NULL,
  `description` TEXT NOT NULL,
  `technologies` VARCHAR(255) NULL DEFAULT NULL,
  `github_url` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_resume_user` (`user_id`),
  CONSTRAINT `fk_resume_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: skills (technical skills shown on the public CV)
CREATE TABLE `skills` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `skill_name` VARCHAR(80) NOT NULL,
  `skill_level` ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  PRIMARY KEY (`id`),
  KEY `fk_skills_user` (`user_id`),
  CONSTRAINT `fk_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Sample data
INSERT INTO `users` (`id`, `full_name`, `headline`, `summary`, `email`, `password`, `avatar_path`) VALUES
(1, 'Anish Kafle', 'Intelligence Computing Student',
 'I am an Intelligence Computing student at Dong-Eui University, South Korea, interested in software development, web technologies, and problem-solving. I have practical experience developing academic and personal projects using C++, PHP, MySQL, HTML, and CSS. Through these projects, I have gained experience with object-oriented programming, database management, CRUD operations, authentication, image uploads, and Git/GitHub. I am continuously improving my technical skills and seeking opportunities to gain practical experience in software development.',
 'admin@example.com',
 '$2y$10$N9jxwtvmr3LknjQgGJtdDuD8d9/H2/4RK/kc2QERGQHNLuBnpRqdG',
 'uploads/avatar_1_63d39dae25252c4a.jpg');

-- No experience rows (empty state shows on the public page).
-- Project start dates are placeholders.
INSERT INTO `resume_entries`
  (`id`, `user_id`, `entry_type`, `title`, `organization`, `start_date`, `end_date`, `description`, `technologies`, `github_url`) VALUES
(1, 1, 'education',
 'Bachelor''s Degree in Intelligence Computing (Undergraduate)',
 'Dong-Eui University, South Korea',
 '2025-03-01', NULL,
 'Currently pursuing an undergraduate degree in Intelligence Computing, developing knowledge and practical skills in programming, computer science, software development, and database systems. Started in March 2025; currently in the 4th semester.',
 NULL, NULL),
(2, 1, 'project',
 'Campus Lost & Found System',
 'University Team Project',
 '2026-09-01', NULL,
 'A university web application that allows students to report, browse, and manage lost and found items. This was a team project. My responsibilities: item CRUD operations, image uploads, search and filtering functionality, and item detail pages.',
 'PHP, MySQL, HTML, CSS, JavaScript, Git, GitHub',
 'https://github.com/AnoujPandey/Campus-Lost-Found-System'),
(3, 1, 'project',
 'Library Borrowing System',
 'University OOP Project',
 '2026-09-01', NULL,
 'A command-line library system that manages library items, borrowing operations, item availability, and exceptions. My contributions: the LibraryItem abstract parent class, the Book class, and ItemUnavailableException.',
 'C++, Object-Oriented Programming',
 'https://github.com/AnoujPandey/Library-Borrowing-System'),
(4, 1, 'project',
 'Dynamic Resume Management System',
 'Individual Project',
 '2026-09-01', NULL,
 'A dynamic resume website with an admin dashboard to manage personal information, education, work experience, projects, and technical skills. Includes authentication, CRUD operations, and profile image upload.',
 'PHP, MySQL, PDO, HTML, CSS, Git, GitHub',
 'https://github.com/kaflea007-gif/Dynamic-Resume-System');

INSERT INTO `skills` (`id`, `user_id`, `skill_name`, `skill_level`) VALUES
(1, 1, 'C++', 'Intermediate'),
(2, 1, 'PHP', 'Beginner'),
(3, 1, 'MySQL', 'Beginner'),
(4, 1, 'HTML', 'Intermediate'),
(5, 1, 'CSS', 'Intermediate'),
(6, 1, 'JavaScript', 'Beginner'),
(7, 1, 'Git', 'Beginner'),
(8, 1, 'GitHub', 'Beginner'),
(9, 1, 'Object-Oriented Programming', 'Intermediate'),
(10, 1, 'Database Management', 'Beginner');
