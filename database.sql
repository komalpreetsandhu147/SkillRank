-- SKILLRANK Database Schema & Seed Data
-- Tech Fest Project: SkillRank - AI-Powered Skill Assessment & Career Readiness Platform
-- Fully compatible with MySQL 5.7+ / MariaDB 10.4+ / XAMPP

CREATE DATABASE IF NOT EXISTS `skillrank` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skillrank`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(160) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('student','admin') DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Students Profile Table
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE NOT NULL,
    `university` VARCHAR(160) DEFAULT 'Northbridge University',
    `course` VARCHAR(100) DEFAULT 'BCA / B.Tech CS',
    `semester` VARCHAR(40) DEFAULT 'Semester 4',
    `phone` VARCHAR(30) DEFAULT '',
    `avatar` VARCHAR(255) DEFAULT '',
    `bio` TEXT,
    `career_goal` VARCHAR(160) DEFAULT 'Full-Stack Software Developer',
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Subjects Table
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(80) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `description` VARCHAR(255) DEFAULT '',
    `icon` VARCHAR(30) DEFAULT '✦'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Topics Table
CREATE TABLE IF NOT EXISTS `topics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Questions Table
CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_id` INT NOT NULL,
    `topic_id` INT NOT NULL,
    `difficulty` ENUM('Easy','Medium','Hard') NOT NULL DEFAULT 'Easy',
    `question` TEXT NOT NULL,
    `option_a` VARCHAR(255) NOT NULL,
    `option_b` VARCHAR(255) NOT NULL,
    `option_c` VARCHAR(255) NOT NULL,
    `option_d` VARCHAR(255) NOT NULL,
    `correct_answer` CHAR(1) NOT NULL,
    `marks` INT DEFAULT 10,
    `explanation` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`topic_id`) REFERENCES `topics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tests Catalog
CREATE TABLE IF NOT EXISTS `tests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_id` INT NOT NULL,
    `title` VARCHAR(120) NOT NULL,
    `description` VARCHAR(255) DEFAULT '',
    `difficulty` ENUM('All Levels','Easy','Medium','Hard') DEFAULT 'All Levels',
    `duration_minutes` INT DEFAULT 10,
    `total_questions` INT DEFAULT 5,
    `passing_score` INT DEFAULT 60,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Quiz Attempts
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `test_id` INT NULL,
    `score` INT DEFAULT 0,
    `total_marks` INT DEFAULT 0,
    `questions_count` INT DEFAULT 0,
    `accuracy` DECIMAL(5,2) DEFAULT 0,
    `time_taken_seconds` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Answers Table
CREATE TABLE IF NOT EXISTS `answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `attempt_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `selected_answer` CHAR(1),
    `is_correct` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Skills Table
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Student Skills / Skill Progress Table
CREATE TABLE IF NOT EXISTS `student_skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `skill_id` INT NOT NULL,
    `score` DECIMAL(5,2) DEFAULT 0,
    `level` VARCHAR(30) DEFAULT 'Beginner',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `student_skill` (`user_id`, `skill_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Achievements Table
CREATE TABLE IF NOT EXISTS `achievements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(120) NOT NULL,
    `description` VARCHAR(255),
    `icon` VARCHAR(10) DEFAULT '✦',
    `earned_at` DATE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Academic Records Table
CREATE TABLE IF NOT EXISTS `academic_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `marks` DECIMAL(5,2),
    `attendance` DECIMAL(5,2),
    `syllabus_progress` DECIMAL(5,2),
    `exam_date` DATE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Resumes Table
CREATE TABLE IF NOT EXISTS `resumes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE NOT NULL,
    `headline` VARCHAR(160) DEFAULT '',
    `summary` TEXT,
    `ai_summary` TEXT,
    `projects` TEXT,
    `education` TEXT,
    `certifications` TEXT,
    `github_url` VARCHAR(255) DEFAULT '',
    `linkedin_url` VARCHAR(255) DEFAULT '',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compatibility Views
CREATE OR REPLACE VIEW `admins` AS 
    SELECT u.id, u.name, u.email, u.created_at FROM users u WHERE u.role='admin';

CREATE OR REPLACE VIEW `test_results` AS 
    SELECT qa.*, u.name as student_name, s.name as subject_name 
    FROM quiz_attempts qa 
    JOIN users u ON u.id=qa.user_id 
    JOIN subjects s ON s.id=qa.subject_id;

CREATE OR REPLACE VIEW `skill_progress` AS 
    SELECT ss.*, sk.name as skill_name, u.name as student_name 
    FROM student_skills ss 
    JOIN skills sk ON sk.id=ss.skill_id 
    JOIN users u ON u.id=ss.user_id;

CREATE OR REPLACE VIEW `leaderboard` AS 
    SELECT 
        u.id as user_id,
        u.name,
        u.email,
        st.university,
        st.course,
        COALESCE(SUM(qa.score), 0) as total_score,
        COALESCE(SUM(qa.questions_count), 0) as solved_count,
        COALESCE(ROUND(AVG(qa.accuracy)), 0) as average_accuracy,
        COUNT(qa.id) as attempts_count
    FROM users u
    LEFT JOIN students st ON st.user_id=u.id
    LEFT JOIN quiz_attempts qa ON qa.user_id=u.id
    WHERE u.role='student'
    GROUP BY u.id
    ORDER BY total_score DESC, average_accuracy DESC;

-- Demo Users (Password: demo123)
-- Hash generated via password_hash('demo123', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`) VALUES 
(1, 'Aarav Kapoor', 'aarav@skillrank.demo', '$2y$10$xRjCRpJI/1mn4tUmTFkMWOGcoTbza0VX9DZxhVQK94X7cMqR.4vV.', 'student'),
(2, 'Meera Shah', 'meera@skillrank.demo', '$2y$10$xRjCRpJI/1mn4tUmTFkMWOGcoTbza0VX9DZxhVQK94X7cMqR.4vV.', 'student'),
(3, 'SkillRank Admin', 'admin@skillrank.demo', '$2y$10$xRjCRpJI/1mn4tUmTFkMWOGcoTbza0VX9DZxhVQK94X7cMqR.4vV.', 'admin')
ON DUPLICATE KEY UPDATE name=VALUES(name), password_hash=VALUES(password_hash), role=VALUES(role);

INSERT INTO `students` (`user_id`, `university`, `course`, `semester`, `phone`, `career_goal`) VALUES 
(1, 'Northbridge University', 'BCA / B.Tech CS', 'Semester 6', '+91 98765 43210', 'Full-Stack Web Developer'),
(2, 'Northbridge University', 'B.Tech Information Technology', 'Semester 4', '+91 98111 22008', 'Data Engineer & AI Specialist')
ON DUPLICATE KEY UPDATE university=VALUES(university), course=VALUES(course), semester=VALUES(semester), phone=VALUES(phone);

-- Subjects
INSERT INTO `subjects` (`id`, `name`, `code`, `description`, `icon`) VALUES 
(1, 'PHP', 'PHP', 'Server-side scripting, OOP, database queries and API development', '🐘'),
(2, 'JavaScript', 'JS', 'Modern ES6+, DOM manipulation, asynchronous programming, and web APIs', '⚡'),
(3, 'DBMS', 'DB', 'Relational database design, SQL querying, normalization, and ACID properties', '🗄️'),
(4, 'HTML & CSS', 'WEB', 'Semantic web structure, responsive layout with Flexbox/Grid, and modern UI styling', '🎨')
ON DUPLICATE KEY UPDATE name=VALUES(name), code=VALUES(code), description=VALUES(description), icon=VALUES(icon);

-- Topics
INSERT INTO `topics` (`id`, `subject_id`, `name`) VALUES 
(1, 1, 'Syntax & Forms'),
(2, 1, 'OOP & Architecture'),
(3, 1, 'Sessions & Security'),
(4, 2, 'DOM & Events'),
(5, 2, 'Async & Promises'),
(6, 2, 'ES6+ Features'),
(7, 3, 'Normalization & Schema'),
(8, 3, 'SQL Queries & Joins'),
(9, 3, 'Transactions & Indexing'),
(10, 4, 'Semantic HTML'),
(11, 4, 'Flexbox & CSS Grid'),
(12, 4, 'Responsive Design & Tokens')
ON DUPLICATE KEY UPDATE subject_id=VALUES(subject_id), name=VALUES(name);

-- Skills
INSERT IGNORE INTO `skills` (`name`) VALUES ('PHP'), ('JavaScript'), ('DBMS'), ('HTML & CSS');

-- Tests
INSERT INTO `tests` (`id`, `subject_id`, `title`, `description`, `difficulty`, `duration_minutes`, `total_questions`, `passing_score`) VALUES 
(1, 1, 'PHP Essentials Assessment', 'Verify PHP fundamentals, form handling, and object-oriented syntax', 'Medium', 10, 5, 60),
(2, 2, 'JavaScript Core & Async Sprint', 'Test DOM manipulation, events, Promises, and modern JS features', 'Medium', 10, 5, 60),
(3, 3, 'DBMS & SQL Mastery Test', 'Assess database normalization, complex SQL joins, and transaction concepts', 'Hard', 12, 5, 60),
(4, 4, 'Frontend HTML5 & CSS3 Challenge', 'Validate semantic HTML5 architecture, Flexbox layouts, and CSS grid responsiveness', 'Easy', 8, 5, 60)
ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), difficulty=VALUES(difficulty);

-- Verified Student Skills
INSERT INTO `student_skills` (`user_id`, `skill_id`, `score`, `level`) VALUES 
(1, 1, 88.00, 'Advanced'),
(1, 2, 78.00, 'Advanced'),
(1, 3, 64.00, 'Intermediate'),
(1, 4, 92.00, 'Expert'),
(2, 1, 72.00, 'Intermediate'),
(2, 2, 84.00, 'Advanced'),
(2, 3, 79.00, 'Advanced'),
(2, 4, 80.00, 'Advanced')
ON DUPLICATE KEY UPDATE score=VALUES(score), level=VALUES(level);

-- Achievements
INSERT INTO `achievements` (`user_id`, `title`, `description`, `icon`, `earned_at`) VALUES 
(1, 'Consistency Champion', 'Practiced 7 days in a row', '◒', '2026-08-20'),
(1, 'PHP Problem Solver', 'Solved 25+ PHP challenges with 85%+ accuracy', '✦', '2026-08-26'),
(1, 'Top 10 Momentum', 'Reached top rank on the weekly college leaderboard', '♛', '2026-09-01')
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Academic Records
INSERT INTO `academic_records` (`user_id`, `subject_id`, `marks`, `attendance`, `syllabus_progress`, `exam_date`) VALUES 
(1, 1, 88.00, 94.00, 85.00, '2026-09-18'),
(1, 2, 78.00, 90.00, 78.00, '2026-09-22'),
(1, 3, 68.00, 84.00, 65.00, '2026-09-27'),
(1, 4, 92.00, 98.00, 92.00, '2026-09-15')
ON DUPLICATE KEY UPDATE marks=VALUES(marks);

-- Initial Quiz Attempts
INSERT INTO `quiz_attempts` (`user_id`, `subject_id`, `score`, `total_marks`, `questions_count`, `accuracy`, `time_taken_seconds`, `created_at`) VALUES 
(1, 1, 88, 100, 10, 88.00, 420, '2026-08-28 14:30:00'),
(1, 2, 78, 100, 10, 78.00, 390, '2026-08-30 16:15:00'),
(1, 3, 64, 100, 10, 64.00, 450, '2026-09-02 11:20:00'),
(2, 1, 72, 100, 10, 72.00, 410, '2026-09-01 10:00:00'),
(2, 2, 84, 100, 10, 84.00, 375, '2026-09-03 15:45:00');

-- Resumes
INSERT INTO `resumes` (`user_id`, `headline`, `summary`, `ai_summary`, `projects`, `education`, `github_url`, `linkedin_url`) VALUES 
(1, 'Aspiring Full-Stack Software Developer', 
'Motivated Computer Science & Applications student with proven expertise in Web Development (HTML5/CSS3, PHP) and Relational Database Systems. Recognized by SkillRank with Verified Expert status in HTML & CSS (92%) and Advanced level in PHP (88%). Passionate about building performant, user-centric software solutions.', 
'Motivated Computer Science & Applications student with proven expertise in Web Development (HTML5/CSS3, PHP) and Relational Database Systems. Recognized by SkillRank with Verified Expert status in HTML & CSS (92%) and Advanced level in PHP (88%). Passionate about building performant, user-centric software solutions.', 
'[{\"title\":\"SkillRank Assessment Platform\",\"tech\":\"PHP, MySQL, CSS Grid, Vanilla JS\",\"description\":\"Architected a responsive skill verification web application with adaptive assessments, real-time analytics, and automated resume generation.\"},{\"title\":\"E-Commerce Inventory Manager\",\"tech\":\"PHP, PDO, MariaDB, REST API\",\"description\":\"Engineered a secure inventory tracking system implementing prepared statements, ACID transactions, and role-based access control.\"}]', 
'[{\"degree\":\"Bachelor of Computer Applications (BCA)\",\"school\":\"Northbridge University\",\"year\":\"2023 - 2026\",\"score\":\"CGPA: 8.8 / 10\"}]', 
'https://github.com/aarav-dev', 
'https://linkedin.com/in/aaravkapoor')
ON DUPLICATE KEY UPDATE headline=VALUES(headline), summary=VALUES(summary);
