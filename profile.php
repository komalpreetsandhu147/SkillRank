<?php
require_once __DIR__ . '/auth.php';
require_login('student');
$user = current_user();
$student = one('SELECT * FROM students WHERE user_id=?', 'i', [$user['id']]);
$skills = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score DESC', 'i', [$user['id']]);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $career_goal = trim($_POST['career_goal'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if ($name && $university && $course && $semester) {
        query('UPDATE users SET name=? WHERE id=?', 'si', [$name, $user['id']]);
        query('INSERT INTO students (user_id, university, course, semester, phone, career_goal, bio) VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE university=VALUES(university), course=VALUES(course), semester=VALUES(semester), phone=VALUES(phone), career_goal=VALUES(career_goal), bio=VALUES(bio)',
            'issssss',
            [$user['id'], $university, $course, $semester, $phone, $career_goal, $bio]
        );
        $message = 'Profile updated successfully.';
        $user = current_user();
        $student = one('SELECT * FROM students WHERE user_id=?', 'i', [$user['id']]);
    }
}

$pageTitle = 'Student Profile';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Student Identity</div>
<h1>Profile & Career Readiness</h1>
<p>Keep your academic credentials and verified skill proofs up-to-date for campus placements.</p>
</div>
<a class="button button-quiet" href="resume.php">View Generated Resume <span>↗</span></a>
</div>

<?php if($message): ?>
<div class="alert" style="background:#eef6cf;color:var(--teal);font-weight:700">✓ <?= e($message) ?></div>
<?php endif; ?>

<div class="dashboard-grid">
<section class="panel">
<div class="panel-head">
<h2>Academic & Personal Information</h2>
<small>Visible on your verified resume</small>
</div>
<form method="post" class="form-stack">
<div class="form-grid">
<label>Full Name
<input name="name" value="<?= e($user['name']) ?>" required>
</label>
<label>Email Address
<input value="<?= e($user['email']) ?>" disabled style="background:#f5f7fa;cursor:not-allowed">
</label>
</div>

<div class="form-grid">
<label>University / College
<input name="university" value="<?= e($student['university'] ?? $user['university']) ?>" required>
</label>
<label>Course / Major
<input name="course" value="<?= e($student['course'] ?? $user['course']) ?>" required>
</label>
</div>

<div class="form-grid">
<label>Current Semester
<input name="semester" value="<?= e($student['semester'] ?? $user['semester']) ?>" required>
</label>
<label>Contact Phone
<input name="phone" value="<?= e($student['phone'] ?? '') ?>" placeholder="+91 98765 43210">
</label>
</div>

<label>Target Career Role
<input name="career_goal" value="<?= e($student['career_goal'] ?? 'Full-Stack Software Developer') ?>" placeholder="e.g. Full-Stack Web Developer, Cloud Engineer">
</label>

<label>Brief Bio / Professional Summary
<textarea name="bio" rows="3" placeholder="Passionate computer science student focused on full-stack web engineering and database management."><?= e($student['bio'] ?? '') ?></textarea>
</label>

<button class="button button-primary" type="submit" style="align-self:start">Save Profile Changes ↗</button>
</form>
</section>

<aside>
<section class="panel" style="margin-bottom:18px">
<div class="panel-head">
<h2>Verified Skill Levels</h2>
<small>Updated via tests</small>
</div>
<?php if ($skills): ?>
<?php foreach ($skills as $sk): 
    $lvlColor = match($sk['level']) {
        'Expert' => '#287a68',
        'Advanced' => '#176b78',
        'Intermediate' => '#d88b62',
        default => '#647586'
    };
?>
<div class="skill-row" style="margin:14px 0">
<div class="skill-label">
<strong><?= e($sk['name']) ?></strong>
<span style="font-weight:700;color:<?= $lvlColor ?>"><?= e($sk['level']) ?> (<?= round($sk['score']) ?>%)</span>
</div>
<div class="progress"><i data-progress="<?= round($sk['score']) ?>"></i></div>
</div>
<?php endforeach; ?>
<?php else: ?>
<p class="muted">No assessments completed yet. Take a test to earn skill verification.</p>
<a href="practice.php" class="button button-primary full">Take First Test ↗</a>
<?php endif; ?>
</section>

<div class="panel" style="background:#f8fafc">
<h3 style="margin-bottom:6px">Skill Progression Levels</h3>
<small style="display:block;color:var(--muted);line-height:1.5">
• <strong>Expert (90%+)</strong>: Production-ready technical mastery.<br>
• <strong>Advanced (75 - 89%)</strong>: Independent problem solving & design.<br>
• <strong>Intermediate (55 - 74%)</strong>: Solid working knowledge.<br>
• <strong>Beginner (&lt; 55%)</strong>: Learning syntax and core concepts.
</small>
</div>
</aside>
</div>

<?php include 'includes/footer.php'; ?>