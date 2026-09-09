CREATE DATABASE IF NOT EXISTS skillrank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillrank;

CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(160) UNIQUE NOT NULL, password_hash VARCHAR(255) NOT NULL, role ENUM('student','admin') DEFAULT 'student', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE students (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT UNIQUE NOT NULL, university VARCHAR(160), course VARCHAR(100), semester VARCHAR(40), phone VARCHAR(30), avatar VARCHAR(255), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE subjects (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL, code VARCHAR(20) NOT NULL);
CREATE TABLE topics (id INT AUTO_INCREMENT PRIMARY KEY, subject_id INT NOT NULL, name VARCHAR(100) NOT NULL, FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE);
CREATE TABLE questions (id INT AUTO_INCREMENT PRIMARY KEY, subject_id INT NOT NULL, topic_id INT NOT NULL, difficulty ENUM('Easy','Medium','Hard') NOT NULL, question TEXT NOT NULL, option_a VARCHAR(255) NOT NULL, option_b VARCHAR(255) NOT NULL, option_c VARCHAR(255) NOT NULL, option_d VARCHAR(255) NOT NULL, correct_answer CHAR(1) NOT NULL, marks INT DEFAULT 10, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (subject_id) REFERENCES subjects(id), FOREIGN KEY (topic_id) REFERENCES topics(id));
CREATE TABLE quiz_attempts (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, subject_id INT NOT NULL, score INT DEFAULT 0, total_marks INT DEFAULT 0, questions_count INT DEFAULT 0, accuracy DECIMAL(5,2) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE answers (id INT AUTO_INCREMENT PRIMARY KEY, attempt_id INT NOT NULL, question_id INT NOT NULL, selected_answer CHAR(1), is_correct TINYINT(1) DEFAULT 0, FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE, FOREIGN KEY (question_id) REFERENCES questions(id));
CREATE TABLE skills (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL UNIQUE);
CREATE TABLE student_skills (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, skill_id INT NOT NULL, score DECIMAL(5,2) DEFAULT 0, level VARCHAR(30) DEFAULT 'Beginner', UNIQUE KEY student_skill (user_id, skill_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (skill_id) REFERENCES skills(id));
CREATE TABLE achievements (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, title VARCHAR(120) NOT NULL, description VARCHAR(255), icon VARCHAR(10) DEFAULT '✦', earned_at DATE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE academic_records (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, subject_id INT NOT NULL, marks DECIMAL(5,2), attendance DECIMAL(5,2), syllabus_progress DECIMAL(5,2), exam_date DATE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);

INSERT INTO subjects (name,code) VALUES ('PHP','PHP'),('JavaScript','JS'),('DBMS','DB'),('HTML & CSS','WEB');
INSERT INTO topics (subject_id,name) VALUES (1,'Syntax & Forms'),(1,'OOP & APIs'),(2,'DOM & Events'),(2,'Async JavaScript'),(3,'Normalization'),(3,'SQL Queries'),(4,'Semantic HTML'),(4,'Responsive CSS');
INSERT INTO skills (name) VALUES ('PHP'),('JavaScript'),('DBMS'),('HTML & CSS');
INSERT INTO users (name,email,password_hash,role) VALUES ('Aarav Kapoor','aarav@skillrank.demo','$2y$10$vFamV.NS1z3HIgCJMAtBgOF98CLnfFIZ6jOK1P8KnDprtjgxP17Ba','student'),('Meera Shah','meera@skillrank.demo','$2y$10$vFamV.NS1z3HIgCJMAtBgOF98CLnfFIZ6jOK1P8KnDprtjgxP17Ba','student'),('SkillRank Admin','admin@skillrank.demo','$2y$10$vFamV.NS1z3HIgCJMAtBgOF98CLnfFIZ6jOK1P8KnDprtjgxP17Ba','admin');
INSERT INTO students (user_id,university,course,semester,phone) VALUES (1,'Northbridge University','B.Tech Computer Science','Semester 6','+91 98765 43210'),(2,'Northbridge University','B.Tech Information Technology','Semester 4','+91 98111 22008');
INSERT INTO questions (subject_id,topic_id,difficulty,question,option_a,option_b,option_c,option_d,correct_answer,marks) VALUES
(1,1,'Easy','Which superglobal contains form data sent with POST?','$_GET','$_POST','$_FORM','$_DATA','B',10),
(1,2,'Medium','Which keyword creates a class in PHP?','object','define','class','struct','C',15),
(2,3,'Easy','Which method selects an element by its ID?','querySelectorAll()','getElementById()','getById()','selectId()','B',10),
(2,4,'Hard','What does Promise.all return when all promises resolve?','The first value','An array of values','A boolean','A callback','B',20),
(3,5,'Medium','Normalization primarily reduces what?','Indexes','Data redundancy','Security','Latency','B',15),
(4,7,'Easy','Which element represents the main content?','<section>','<main>','<content>','<body-main>','B',10);
INSERT INTO student_skills (user_id,skill_id,score,level) VALUES (1,1,88,'Advanced'),(1,2,76,'Intermediate'),(1,3,64,'Intermediate'),(1,4,91,'Expert'),(2,1,72,'Intermediate'),(2,2,81,'Advanced');
INSERT INTO achievements (user_id,title,description,icon,earned_at) VALUES (1,'Consistency champion','Practiced 7 days in a row','◒','2026-08-20'),(1,'PHP problem solver','Solved 25 PHP questions','✦','2026-08-26'),(1,'Top 10 momentum','Reached the weekly top 10','♛','2026-09-01');
INSERT INTO academic_records (user_id,subject_id,marks,attendance,syllabus_progress,exam_date) VALUES (1,1,86,94,82,'2026-09-18'),(1,2,78,89,74,'2026-09-22'),(1,3,68,83,61,'2026-09-27'),(1,4,92,97,90,'2026-09-15');
INSERT INTO quiz_attempts (user_id,subject_id,score,total_marks,questions_count,accuracy,created_at) VALUES (1,1,88,100,10,88,'2026-08-28'),(1,2,76,100,10,76,'2026-08-30'),(1,3,64,100,10,64,'2026-09-02'),(2,1,72,100,10,72,'2026-09-01');
