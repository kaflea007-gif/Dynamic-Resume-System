-- Dynamic Resume: resume_db schema and sample data
-- Admin login:  admin@example.com  /  Admin@12345
-- Import with phpMyAdmin (Import tab) or:  mysql -u root -p < resume_db.sql

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
  PRIMARY KEY (`id`),
  KEY `fk_resume_user` (`user_id`),
  CONSTRAINT `fk_resume_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: skills (technical skills shown on the public CV)
CREATE TABLE `skills` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `skill_name` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_skills_user` (`user_id`),
  CONSTRAINT `fk_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Sample data (edit these through the admin dashboard)
INSERT INTO `users` (`id`, `full_name`, `headline`, `summary`, `email`, `password`, `avatar_path`) VALUES
(1, 'Anish', 'Intelligence Computing Student',
 'Undergraduate student in Intelligence Computing at Dong-Eui University, currently in my 4th semester. I enjoy building dynamic, database-driven web applications with PHP and MySQL.',
 'admin@example.com',
 '$2y$10$N9jxwtvmr3LknjQgGJtdDuD8d9/H2/4RK/kc2QERGQHNLuBnpRqdG',
 'uploads/avatar_1_63d39dae25252c4a.jpg');

INSERT INTO `resume_entries` (`id`, `user_id`, `entry_type`, `title`, `organization`, `start_date`, `end_date`, `description`) VALUES
(1, 1, 'education', 'Intelligence Computing', 'Dong-Eui University', '2025-03-23', NULL, 'I am currently studying in 4th semester.'),
(2, 1, 'project', 'Static Curriculum Vitae', 'Web Programming I', '2026-03-01', '2026-06-30', 'Designed and built a personal CV with semantic HTML5 and responsive CSS3.'),
(3, 1, 'project', 'Dynamic Resume CMS', 'Web Programming II', '2026-09-01', NULL, 'Re-engineered the static CV into a PHP 8 and MySQL (PDO) portfolio with session-based admin CRUD and GD image thumbnails.');

INSERT INTO `skills` (`user_id`, `skill_name`) VALUES
(1, 'PHP'), (1, 'MySQL'), (1, 'PDO'), (1, 'HTML5'), (1, 'CSS3');
