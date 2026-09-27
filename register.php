<?php 
require_once 'auth.php'; 
if (current_user()) redirect('dashboard.php'); 
$error = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $name = trim($_POST['name'] ?? ''); 
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL); 
    $password = $_POST['password'] ?? ''; 
    $university = trim($_POST['university'] ?? 'Northbridge University');
    $course = trim($_POST['course'] ?? 'BCA');
    $semester = trim($_POST['semester'] ?? 'Semester 4');
    $phone = trim($_POST['phone'] ?? '');

    if (!$name || !$email || strlen($password) < 6) {
        $error = 'Please provide your full name, a valid email address, and a password with at least 6 characters.';
    } elseif (one('SELECT id FROM users WHERE email=?', 's', [$email])) {
        $error = 'An account with that email address already exists. Please sign in instead.';
    } else { 
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        query('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, \'student\')', 'sss', [$name, $email, $hashed]); 
        $id = db()->insert_id; 

        query('INSERT INTO students (user_id, university, course, semester, phone, career_goal) VALUES (?, ?, ?, ?, ?, ?)', 
            'isssss', 
            [$id, $university, $course, $semester, $phone, 'Full-Stack Software Developer']
        ); 

        // Initialize default skills with Beginner level
        $skills = many('SELECT id FROM skills');
        foreach ($skills as $sk) {
            query('INSERT INTO student_skills (user_id, skill_id, score, level) VALUES (?, ?, 0, \'Beginner\')', 'ii', [$id, $sk['id']]);
        }

        // Initialize empty resume
        $summary = "Motivated $course student at $university eager to demonstrate practical software engineering competencies through verified SkillRank assessments.";
        query('INSERT INTO resumes (user_id, headline, summary, ai_summary) VALUES (?, ?, ?, ?)', 'isss', [$id, 'Aspiring Software Developer', $summary, $summary]);

        $_SESSION['user_id'] = $id; 
        redirect('dashboard.php'); 
    } 
} 

$pageTitle = 'Create Student Profile'; 
include 'includes/header.php'; 
?>
<div class="auth-page">
<div class="auth-panel">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span></span>
</a>
<div class="auth-heading">
<div class="eyebrow">Your growth starts here</div>
<h1>Make progress visible.</h1>
<p>Build a student profile that gets smarter with every assessment.</p>
</div>

<?php if($error): ?>
<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="form-stack">
<label>Full name
<input name="name" required placeholder="Aarav Kapoor" value="<?= e($_POST['name'] ?? '') ?>">
</label>
<label>Email address
<input type="email" name="email" required placeholder="you@university.edu" value="<?= e($_POST['email'] ?? '') ?>">
</label>
<div class="form-grid">
<label>University / College
<input name="university" value="<?= e($_POST['university'] ?? 'Northbridge University') ?>">
</label>
<label>Course / Degree
<input name="course" value="<?= e($_POST['course'] ?? 'BCA / B.Tech CS') ?>">
</label>
</div>
<div class="form-grid">
<label>Semester
<input name="semester" value="<?= e($_POST['semester'] ?? 'Semester 4') ?>">
</label>
<label>Phone Number (optional)
<input name="phone" placeholder="+91 98765 43210" value="<?= e($_POST['phone'] ?? '') ?>">
</label>
</div>
<label>Password
<input type="password" name="password" required minlength="6" placeholder="At least 6 characters">
</label>
<button class="button button-primary full" type="submit">Create my student profile <span>↗</span></button>
</form>

<p class="form-note">
Already registered? <a href="login.php">Sign in</a> · <a href="admin_login.php">Admin portal →</a>
</p>
</div>
<div class="auth-art auth-art-alt">
<div class="auth-stat">
<strong>+12.4%</strong>
<span>average skill growth<br>after 30 days of active practice</span>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>