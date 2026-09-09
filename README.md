# SkillRank - Academic & Skill Development Platform

A server-rendered Tech Fest project using only HTML, CSS, JavaScript, PHP, and MySQL. It connects academic progress, adaptive question practice, verified skill progression, ranking, and an auto-generated resume.

## Folder structure

- `index.php`, `about.php`: public landing and how-it-works pages
- `login.php`, `register.php`, `logout.php`: student authentication
- `dashboard.php`, `profile.php`, `practice.php`, `quiz.php`, `result.php`: student workflow
- `analytics.php`, `leaderboard.php`, `resume.php`: student insights and resume
- `admin_login.php`, `admin_dashboard.php`, `students.php`, `student_profile.php`: admin operations
- `questions.php`, `question_form.php`, `admin_analytics.php`: question and analytics management
- `includes/`: shared sidebar/header/footer
- `assets/`: responsive CSS and chart/progress JavaScript
- `database.sql`: schema, demo data, and hashed demo passwords

## XAMPP setup

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin`.
3. Import `database.sql`. It creates the `skillrank` database and all tables.
4. Copy this folder to `C:\xampp\htdocs\techfest` (or configure Apache to serve it).
5. Open `http://localhost/techfest/`.
6. If your MySQL root account has a password, update `DB_PASS` in `config.php`.

## Demo accounts

- Student: `aarav@skillrank.demo` / `demo123`
- Student: `meera@skillrank.demo` / `demo123`
- Admin: `admin@skillrank.demo` / `demo123`

## How it works

HTML is used for semantic page structure and forms. CSS defines the responsive product UI, dashboard layout, progress bars, print-friendly resume, and responsive mobile behavior. JavaScript animates progress bars and draws the lightweight analytics line chart with Canvas. PHP handles sessions, role guards, validated form processing, prepared MySQL queries, quiz scoring, result persistence, skill level recalculation, question CRUD, rankings, and server-rendered pages.

New quiz performance updates the matching skill with a weighted score, then assigns: Beginner below 55, Intermediate 55-74, Advanced 75-89, or Expert 90+. The resume reads from the same live student skill records, so skill changes appear automatically. Use the browser's Print dialog on the resume page and choose "Save as PDF".
