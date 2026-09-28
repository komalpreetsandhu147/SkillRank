<?php
// Database upgrade and seed script for SkillRank
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$mysqli = db();
if (!$mysqli) {
    echo "Notice: Skipping db_setup because database connection is not established.\n";
    return;
}

echo "Beginning SkillRank database setup...\n";

// 1. Create tables if not exist or alter missing columns
$queries = [
    // Users
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(160) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('student','admin') DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Students
    "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        university VARCHAR(160) DEFAULT 'Northbridge University',
        course VARCHAR(100) DEFAULT 'BCA / B.Tech CS',
        semester VARCHAR(40) DEFAULT 'Semester 4',
        phone VARCHAR(30) DEFAULT '',
        avatar VARCHAR(255) DEFAULT '',
        bio TEXT,
        career_goal VARCHAR(160) DEFAULT 'Full-Stack Software Developer',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Subjects
    "CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        code VARCHAR(20) NOT NULL,
        description VARCHAR(255) DEFAULT '',
        icon VARCHAR(30) DEFAULT '✦'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Topics
    "CREATE TABLE IF NOT EXISTS topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Questions (with explanation column)
    "CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        topic_id INT NOT NULL,
        difficulty ENUM('Easy','Medium','Hard') NOT NULL DEFAULT 'Easy',
        question TEXT NOT NULL,
        option_a VARCHAR(255) NOT NULL,
        option_b VARCHAR(255) NOT NULL,
        option_c VARCHAR(255) NOT NULL,
        option_d VARCHAR(255) NOT NULL,
        correct_answer CHAR(1) NOT NULL,
        marks INT DEFAULT 10,
        explanation TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tests (structured tests catalog)
    "CREATE TABLE IF NOT EXISTS tests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        title VARCHAR(120) NOT NULL,
        description VARCHAR(255) DEFAULT '',
        difficulty ENUM('All Levels','Easy','Medium','Hard') DEFAULT 'All Levels',
        duration_minutes INT DEFAULT 10,
        total_questions INT DEFAULT 5,
        passing_score INT DEFAULT 60,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Quiz Attempts
    "CREATE TABLE IF NOT EXISTS quiz_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject_id INT NOT NULL,
        test_id INT NULL,
        score INT DEFAULT 0,
        total_marks INT DEFAULT 0,
        questions_count INT DEFAULT 0,
        accuracy DECIMAL(5,2) DEFAULT 0,
        time_taken_seconds INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Answers
    "CREATE TABLE IF NOT EXISTS answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attempt_id INT NOT NULL,
        question_id INT NOT NULL,
        selected_answer CHAR(1),
        is_correct TINYINT(1) DEFAULT 0,
        FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Skills
    "CREATE TABLE IF NOT EXISTS skills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Student Skills
    "CREATE TABLE IF NOT EXISTS student_skills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        skill_id INT NOT NULL,
        score DECIMAL(5,2) DEFAULT 0,
        level VARCHAR(30) DEFAULT 'Beginner',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY student_skill (user_id, skill_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Achievements
    "CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(120) NOT NULL,
        description VARCHAR(255),
        icon VARCHAR(10) DEFAULT '✦',
        earned_at DATE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Academic records
    "CREATE TABLE IF NOT EXISTS academic_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject_id INT NOT NULL,
        marks DECIMAL(5,2),
        attendance DECIMAL(5,2),
        syllabus_progress DECIMAL(5,2),
        exam_date DATE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Resumes
    "CREATE TABLE IF NOT EXISTS resumes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        headline VARCHAR(160) DEFAULT '',
        summary TEXT,
        ai_summary TEXT,
        projects TEXT,
        education TEXT,
        certifications TEXT,
        github_url VARCHAR(255) DEFAULT '',
        linkedin_url VARCHAR(255) DEFAULT '',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($queries as $sql) {
    if (!$mysqli->query($sql)) {
        echo "Error running table query: " . $mysqli->error . "\n";
    }
}

// Check and add missing columns to existing tables
$col_checks = [
    ['questions', 'explanation', 'TEXT NULL AFTER marks'],
    ['students', 'bio', 'TEXT NULL AFTER avatar'],
    ['students', 'career_goal', 'VARCHAR(160) DEFAULT "Full-Stack Software Developer" AFTER bio'],
    ['quiz_attempts', 'time_taken_seconds', 'INT DEFAULT 0 AFTER accuracy'],
    ['quiz_attempts', 'test_id', 'INT NULL AFTER subject_id'],
    ['subjects', 'description', 'VARCHAR(255) DEFAULT "" AFTER code'],
    ['subjects', 'icon', 'VARCHAR(30) DEFAULT "✦" AFTER description']
];

foreach ($col_checks as [$table, $col, $def]) {
    $res = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    if ($res && $res->num_rows === 0) {
        $mysqli->query("ALTER TABLE `$table` ADD COLUMN `$col` $def");
        echo "Added column $col to $table\n";
    }
}

// Create Views for compatibility:
// admins view, test_results view, skill_progress view, leaderboard view
$views = [
    "CREATE OR REPLACE VIEW admins AS SELECT u.id, u.name, u.email, u.created_at FROM users u WHERE u.role='admin'",
    "CREATE OR REPLACE VIEW test_results AS SELECT qa.*, u.name as student_name, s.name as subject_name FROM quiz_attempts qa JOIN users u ON u.id=qa.user_id JOIN subjects s ON s.id=qa.subject_id",
    "CREATE OR REPLACE VIEW skill_progress AS SELECT ss.*, sk.name as skill_name, u.name as student_name FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id JOIN users u ON u.id=ss.user_id",
    "CREATE OR REPLACE VIEW leaderboard AS 
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
        ORDER BY total_score DESC, average_accuracy DESC"
];

foreach ($views as $view_sql) {
    $mysqli->query($view_sql);
}

// 2. Insert or update default demo accounts with valid hash for 'demo123'
$demo_hash = password_hash('demo123', PASSWORD_DEFAULT);

$users = [
    ['Aarav Kapoor', 'aarav@skillrank.demo', $demo_hash, 'student'],
    ['Meera Shah', 'meera@skillrank.demo', $demo_hash, 'student'],
    ['SkillRank Admin', 'admin@skillrank.demo', $demo_hash, 'admin'],
];

foreach ($users as [$name, $email, $hash, $role]) {
    $stmt = $mysqli->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), password_hash=VALUES(password_hash), role=VALUES(role)");
    $stmt->bind_param("ssss", $name, $email, $hash, $role);
    $stmt->execute();
}

// Get user IDs
$aarav = one("SELECT id FROM users WHERE email='aarav@skillrank.demo'");
$meera = one("SELECT id FROM users WHERE email='meera@skillrank.demo'");

if ($aarav) {
    $mysqli->query("INSERT INTO students (user_id, university, course, semester, phone, career_goal) 
        VALUES ({$aarav['id']}, 'Northbridge University', 'BCA / B.Tech CS', 'Semester 6', '+91 98765 43210', 'Full-Stack Web Developer')
        ON DUPLICATE KEY UPDATE university=VALUES(university), course=VALUES(course), semester=VALUES(semester), phone=VALUES(phone)");
}

if ($meera) {
    $mysqli->query("INSERT INTO students (user_id, university, course, semester, phone, career_goal) 
        VALUES ({$meera['id']}, 'Northbridge University', 'B.Tech Information Technology', 'Semester 4', '+91 98111 22008', 'Data Engineer & AI Specialist')
        ON DUPLICATE KEY UPDATE university=VALUES(university), course=VALUES(course), semester=VALUES(semester), phone=VALUES(phone)");
}

// 3. Subjects and Topics
$subjects = [
    [1, 'PHP', 'PHP', 'Server-side scripting, OOP, database queries and API development', '🐘'],
    [2, 'JavaScript', 'JS', 'Modern ES6+, DOM manipulation, asynchronous programming, and web APIs', '⚡'],
    [3, 'DBMS', 'DB', 'Relational database design, SQL querying, normalization, and ACID properties', '🗄️'],
    [4, 'HTML & CSS', 'WEB', 'Semantic web structure, responsive layout with Flexbox/Grid, and modern UI styling', '🎨']
];

foreach ($subjects as [$id, $name, $code, $desc, $icon]) {
    $mysqli->query("INSERT INTO subjects (id, name, code, description, icon) VALUES ($id, '$name', '$code', '$desc', '$icon')
        ON DUPLICATE KEY UPDATE name=VALUES(name), code=VALUES(code), description=VALUES(description), icon=VALUES(icon)");
}

// Topics
$topics = [
    [1, 1, 'Syntax & Forms'],
    [2, 1, 'OOP & Architecture'],
    [3, 1, 'Sessions & Security'],
    [4, 2, 'DOM & Events'],
    [5, 2, 'Async & Promises'],
    [6, 2, 'ES6+ Features'],
    [7, 3, 'Normalization & Schema'],
    [8, 3, 'SQL Queries & Joins'],
    [9, 3, 'Transactions & Indexing'],
    [10, 4, 'Semantic HTML'],
    [11, 4, 'Flexbox & CSS Grid'],
    [12, 4, 'Responsive Design & Tokens']
];

foreach ($topics as [$id, $sub_id, $name]) {
    $mysqli->query("INSERT INTO topics (id, subject_id, name) VALUES ($id, $sub_id, '$name')
        ON DUPLICATE KEY UPDATE subject_id=VALUES(subject_id), name=VALUES(name)");
}

// Skills
foreach (['PHP', 'JavaScript', 'DBMS', 'HTML & CSS'] as $sk) {
    $mysqli->query("INSERT IGNORE INTO skills (name) VALUES ('$sk')");
}

// 4. Structured Tests
$tests = [
    [1, 1, 'PHP Essentials Assessment', 'Verify PHP fundamentals, form handling, and object-oriented syntax', 'Medium', 10, 5, 60],
    [2, 2, 'JavaScript Core & Async Sprint', 'Test DOM manipulation, events, Promises, and modern JS features', 'Medium', 10, 5, 60],
    [3, 3, 'DBMS & SQL Mastery Test', 'Assess database normalization, complex SQL joins, and transaction concepts', 'Hard', 12, 5, 60],
    [4, 4, 'Frontend HTML5 & CSS3 Challenge', 'Validate semantic HTML5 architecture, Flexbox layouts, and CSS grid responsiveness', 'Easy', 8, 5, 60]
];

foreach ($tests as [$id, $sub_id, $title, $desc, $diff, $dur, $tq, $ps]) {
    $mysqli->query("INSERT INTO tests (id, subject_id, title, description, difficulty, duration_minutes, total_questions, passing_score)
        VALUES ($id, $sub_id, '$title', '$desc', '$diff', $dur, $tq, $ps)
        ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), difficulty=VALUES(difficulty), duration_minutes=VALUES(duration_minutes)");
}

// 5. Rich Question Bank with Explanations
$q_data = [
    // PHP Questions
    [1, 1, 'Easy', 'Which superglobal variable contains form data sent via HTTP POST method?', '$_GET', '$_POST', '$_FORM', '$_DATA', 'B', 10, '$_POST is the standard PHP superglobal used to collect values submitted via HTTP POST.'],
    [1, 2, 'Medium', 'Which keyword is used to create a class in PHP?', 'object', 'define', 'class', 'struct', 'C', 10, 'The class keyword defines a blueprint for objects in PHP.'],
    [1, 3, 'Hard', 'Which PHP function is recommended to prevent SQL injection when executing dynamic queries with MySQLi?', 'strip_tags()', 'prepare() with bind_param()', 'addslashes()', 'urlencode()', 'B', 15, 'Prepared statements using prepare() and parameter binding separate code from user data, preventing SQL injection completely.'],
    [1, 1, 'Easy', 'What does the function session_start() do in PHP?', 'Deletes all sessions', 'Initializes or resumes a PHP session', 'Destroys user cookies', 'Connects to MySQL database', 'B', 10, 'session_start() creates a session or resumes the current one based on a session identifier passed via a GET or POST request, or passed via a cookie.'],
    [1, 2, 'Medium', 'In PHP OOP, which visibility keyword allows access only within the defining class and its child subclasses?', 'public', 'private', 'protected', 'static', 'C', 10, 'protected members can be accessed within the class itself and by inheriting/parent classes, unlike private which is restricted only to the defining class.'],
    [1, 3, 'Hard', 'Which password hashing algorithm is used by default in PHP password_hash() in PHP 8?', 'MD5', 'SHA-256', 'BCrypt (PASSWORD_BCRYPT)', 'Bcrypt / Argona2 depending on default', 'C', 15, 'password_hash() with PASSWORD_DEFAULT uses strong BCrypt (or Argon2id in modern environments) designed to resist brute-force attacks.'],
    [1, 1, 'Medium', 'How do you check if a variable is set and is not NULL in PHP?', 'empty()', 'isset()', 'is_null()', 'defined()', 'B', 10, 'isset() returns true if the variable is declared and has a value other than NULL.'],
    [1, 2, 'Hard', 'What is the purpose of the declare(strict_types=1); directive in PHP?', 'Disables OOP', 'Enforces strict type-checking for function arguments and return types', 'Prevents file uploads', 'Forces HTTPS only', 'B', 15, 'strict_types=1 ensures PHP does not automatically coerce types for scalar arguments and return values in that file.'],

    // JavaScript Questions
    [2, 4, 'Easy', 'Which method selects an HTML element by its unique ID in the DOM?', 'querySelectorAll()', 'getElementById()', 'getById()', 'selectId()', 'B', 10, 'document.getElementById() returns the element that has the ID attribute with the specified value.'],
    [2, 5, 'Hard', 'What does Promise.all() return when all input promises resolve successfully?', 'The first resolved value', 'An array containing all resolved values in order', 'A boolean true', 'A callback function', 'B', 15, 'Promise.all resolves with an array of values from each promise in the exact order they were supplied.'],
    [2, 6, 'Medium', 'Which ES6 operator is used to unpack elements of an array or properties of an object?', 'Rest operator', 'Spread operator (...)', 'Arrow operator (=>)', 'Nullish coalescing (??)', 'B', 10, 'The spread operator (...) expands an iterable (like an array) into individual elements.'],
    [2, 4, 'Easy', 'Which event listener method is the modern standard for handling user clicks in JavaScript?', 'attachClick()', 'addEventListener()', 'onClick()', 'bindEvent()', 'B', 10, 'element.addEventListener("click", callback) is the standard modern DOM Level 2 event listener.'],
    [2, 5, 'Medium', 'What does the async keyword in front of a JavaScript function declaration signify?', 'The function runs in Web Workers', 'The function automatically returns a Promise', 'The function blocks execution', 'The function cannot have return statements', 'B', 10, 'An async function always returns a Promise; if a primitive value is returned, it is automatically wrapped in a resolved Promise.'],
    [2, 6, 'Hard', 'What is the main difference between let and const in JavaScript?', 'let has block scope while const has global scope', 'const cannot be reassigned after declaration while let can', 'const only stores numbers', 'let cannot be declared inside loops', 'B', 15, 'Variables declared with const maintain a constant reference and cannot be reassigned; let allows reassignment.'],
    [2, 5, 'Hard', 'How do you gracefully catch errors in an async/await block in modern JavaScript?', 'Using try...catch blocks', 'Using if (error)', 'Using window.onerror', 'Using await.catch()', 'A', 15, 'try...catch cleanly intercepts rejected promises when used with await.'],
    [2, 4, 'Medium', 'What does event.preventDefault() do inside a form submission handler?', 'Refreshes the webpage', 'Stops the browser from performing default action (like page reload)', 'Submits form via AJAX automatically', 'Clears input values', 'B', 10, 'event.preventDefault() suppresses default browser behaviors such as full-page form reloads.'],

    // DBMS Questions
    [3, 7, 'Medium', 'Normalization in relational databases primarily aims to reduce what?', 'Indexes and memory', 'Data redundancy and update anomalies', 'Security risks', 'Query execution speed', 'B', 10, 'Normalization organizes tables to eliminate redundant data and ensure data dependencies make sense.'],
    [3, 8, 'Medium', 'Which SQL JOIN returns all rows from the left table and matched rows from the right table?', 'INNER JOIN', 'LEFT JOIN (or LEFT OUTER JOIN)', 'RIGHT JOIN', 'CROSS JOIN', 'B', 10, 'A LEFT JOIN returns all records from the left table, and matching records from the right table. If no match, NULL is returned for right columns.'],
    [3, 7, 'Hard', 'What requirement must a relation satisfy to be in Third Normal Form (3NF)?', 'Must have no foreign keys', 'Must be in 2NF and have no transitive dependencies on candidate keys', 'Must contain only text columns', 'Must have at least three tables', 'B', 15, '3NF requires that the table is in 2NF and that all non-key attributes are directly dependent on the primary key (no transitive dependency).'],
    [3, 9, 'Hard', 'What does the "A" in ACID database properties stand for?', 'Accuracy', 'Atomicity', 'Availability', 'Authorization', 'B', 15, 'Atomicity guarantees that all operations within a database transaction are completed successfully; if not, the transaction is completely rolled back.'],
    [3, 8, 'Easy', 'Which SQL clause is used to filter records resulting from a GROUP BY query?', 'WHERE', 'HAVING', 'FILTER', 'ORDER BY', 'B', 10, 'The HAVING clause was added to SQL because the WHERE keyword cannot be used with aggregate functions.'],
    [3, 8, 'Medium', 'What is the difference between primary key and unique key in relational databases?', 'Primary key allows multiple NULL values', 'A table can have multiple unique keys, but only one primary key which cannot be NULL', 'Unique keys automatically create foreign keys', 'There is no difference', 'B', 10, 'A table allows only one Primary Key and disallows NULL, whereas multiple UNIQUE constraints are allowed and typically permit one NULL.'],
    [3, 9, 'Hard', 'What database structure is created to drastically speed up data retrieval operations at the cost of additional storage and write time?', 'Triggers', 'Indexes (B-Trees / Hash)', 'Stored Procedures', 'Views', 'B', 15, 'Indexes provide fast lookups on specified columns, avoiding full table scans.'],

    // HTML & CSS Questions
    [4, 10, 'Easy', 'Which HTML5 semantic element is intended to encapsulate the main content unique to a document?', '<section>', '<main>', '<content>', '<article-center>', 'B', 10, 'The <main> tag represents the dominant content of the <body> of a document.'],
    [4, 11, 'Medium', 'In CSS Flexbox, which property aligns items along the cross-axis?', 'justify-content', 'align-items', 'flex-direction', 'flex-wrap', 'B', 10, 'justify-content aligns items along the main axis, while align-items controls alignment along the cross-axis.'],
    [4, 11, 'Medium', 'Which CSS Grid property specifies the number and widths of columns in a grid layout?', 'grid-template-columns', 'grid-column-gap', 'grid-auto-flow', 'columns', 'A', 10, 'grid-template-columns defines the columns of the grid with a space-separated list of values.'],
    [4, 12, 'Easy', 'Which HTML meta tag ensures a webpage renders properly and responsively on mobile devices?', '<meta name="device" content="mobile">', '<meta name="viewport" content="width=device-width, initial-scale=1">', '<meta charset="utf-8">', '<meta http-equiv="responsive">', 'B', 10, 'The viewport meta tag configures the browser to display the website scaled correctly across mobile and desktop devices.'],
    [4, 10, 'Medium', 'What attribute should always be provided on an <img> tag for web accessibility (a11y) and screen readers?', 'title', 'alt', 'caption', 'name', 'B', 10, 'The alt attribute provides alternative text for screen readers and displays when the image fails to load.'],
    [4, 12, 'Hard', 'What does the CSS clamp() function take as its three parameters in order?', 'clamp(max, preferred, min)', 'clamp(min, preferred, max)', 'clamp(step, start, stop)', 'clamp(height, width, depth)', 'B', 15, 'clamp(MIN, VAL, MAX) clamps a value between an acceptable lower and upper bound, ideal for fluid responsive typography.'],
    [4, 11, 'Hard', 'What is the effect of setting box-sizing: border-box in CSS?', 'Includes padding and border within the element’s total width and height', 'Doubles the margin', 'Removes all element borders', 'Forces elements into table layout', 'A', 15, 'border-box makes responsive styling predictable because padding and borders do not expand the declared width/height.']
];

// Clear and insert fresh questions
$mysqli->query("DELETE FROM questions WHERE id > 0");

$stmt = $mysqli->prepare("INSERT INTO questions (subject_id, topic_id, difficulty, question, option_a, option_b, option_c, option_d, correct_answer, marks, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($q_data as $q) {
    $stmt->bind_param("iisssssssis", $q[0], $q[1], $q[2], $q[3], $q[4], $q[5], $q[6], $q[7], $q[8], $q[9], $q[10]);
    $stmt->execute();
}
echo "Inserted " . count($q_data) . " questions into question bank.\n";

// 6. Seed demo student skill progression, achievements, and resumes
if ($aarav) {
    $uid = $aarav['id'];
    $skills_to_seed = [
        ['PHP', 88, 'Advanced'],
        ['JavaScript', 78, 'Advanced'],
        ['DBMS', 64, 'Intermediate'],
        ['HTML & CSS', 92, 'Expert']
    ];
    foreach ($skills_to_seed as [$sk_name, $score, $lvl]) {
        $sk = one("SELECT id FROM skills WHERE name='$sk_name'");
        if ($sk) {
            $mysqli->query("INSERT INTO student_skills (user_id, skill_id, score, level) VALUES ($uid, {$sk['id']}, $score, '$lvl')
                ON DUPLICATE KEY UPDATE score=VALUES(score), level=VALUES(level)");
        }
    }

    // Achievements for Aarav
    $ach = [
        ['Consistency Champion', 'Practiced 7 days in a row', '◒', '2026-08-20'],
        ['PHP Problem Solver', 'Solved 25+ PHP challenges with 85%+ accuracy', '✦', '2026-08-26'],
        ['Top 10 Momentum', 'Reached top rank on the weekly college leaderboard', '♛', '2026-09-01']
    ];
    $mysqli->query("DELETE FROM achievements WHERE user_id=$uid");
    foreach ($ach as [$title, $desc, $icon, $dt]) {
        $mysqli->query("INSERT INTO achievements (user_id, title, description, icon, earned_at) VALUES ($uid, '$title', '$desc', '$icon', '$dt')");
    }

    // Academic records for Aarav
    $mysqli->query("DELETE FROM academic_records WHERE user_id=$uid");
    $records = [
        [1, 88, 94, 85, '2026-09-18'],
        [2, 78, 90, 78, '2026-09-22'],
        [3, 68, 84, 65, '2026-09-27'],
        [4, 92, 98, 92, '2026-09-15']
    ];
    foreach ($records as [$sub_id, $marks, $att, $syl, $dt]) {
        $mysqli->query("INSERT INTO academic_records (user_id, subject_id, marks, attendance, syllabus_progress, exam_date) VALUES ($uid, $sub_id, $marks, $att, $syl, '$dt')");
    }

    // Resume for Aarav
    $summary = "Motivated Computer Science & Applications student with proven expertise in Web Development (HTML5/CSS3, PHP) and Relational Database Systems. Recognized by SkillRank with Verified Expert status in HTML & CSS (92%) and Advanced level in PHP (88%). Passionate about building performant, user-centric software solutions.";
    $projects = json_encode([
        ['title' => 'SkillRank Assessment Platform', 'tech' => 'PHP, MySQL, CSS Grid, Vanilla JS', 'description' => 'Architected a responsive skill verification web application with adaptive assessments, real-time analytics, and automated resume generation.'],
        ['title' => 'E-Commerce Inventory Manager', 'tech' => 'PHP, PDO, MariaDB, REST API', 'description' => 'Engineered a secure inventory tracking system implementing prepared statements, ACID transactions, and role-based access control.']
    ]);
    $edu = json_encode([
        ['degree' => 'Bachelor of Computer Applications (BCA)', 'school' => 'Northbridge University', 'year' => '2023 - 2026', 'score' => 'CGPA: 8.8 / 10']
    ]);

    $mysqli->query("INSERT INTO resumes (user_id, headline, summary, ai_summary, projects, education, github_url, linkedin_url)
        VALUES ($uid, 'Aspiring Full-Stack Software Developer', '$summary', '$summary', '$projects', '$edu', 'https://github.com/aarav-dev', 'https://linkedin.com/in/aaravkapoor')
        ON DUPLICATE KEY UPDATE headline=VALUES(headline), summary=VALUES(summary), ai_summary=VALUES(ai_summary), projects=VALUES(projects), education=VALUES(education)");
}

// Seed Meera Shah
if ($meera) {
    $uid = $meera['id'];
    $skills_to_seed = [
        ['PHP', 72, 'Intermediate'],
        ['JavaScript', 84, 'Advanced'],
        ['DBMS', 79, 'Advanced'],
        ['HTML & CSS', 80, 'Advanced']
    ];
    foreach ($skills_to_seed as [$sk_name, $score, $lvl]) {
        $sk = one("SELECT id FROM skills WHERE name='$sk_name'");
        if ($sk) {
            $mysqli->query("INSERT INTO student_skills (user_id, skill_id, score, level) VALUES ($uid, {$sk['id']}, $score, '$lvl')
                ON DUPLICATE KEY UPDATE score=VALUES(score), level=VALUES(level)");
        }
    }
}

echo "SkillRank database setup completed successfully!\n";
