<?php 
require_once __DIR__ . '/auth.php';
require_login('student');
$user = current_user();
$uid = $user['id'];

// Core Student Performance Metrics
$stats = one('SELECT COALESCE(SUM(questions_count),0) solved, COALESCE(SUM(score),0) points, COALESCE(AVG(accuracy),0) accuracy, COUNT(id) tests_taken FROM quiz_attempts WHERE user_id=?', 'i', [$uid]);
$monthly = one('SELECT COALESCE(SUM(questions_count),0) solved, COALESCE(SUM(score),0) points, COALESCE(AVG(accuracy),0) accuracy FROM quiz_attempts WHERE user_id=? AND created_at >= DATE_FORMAT(CURRENT_DATE,\'%Y-%m-01\')', 'i', [$uid]);

// Rank calculation
$rank = one('SELECT COUNT(*)+1 rank_no FROM (SELECT u.id, COALESCE(SUM(qa.score),0) points FROM users u LEFT JOIN quiz_attempts qa ON qa.user_id=u.id WHERE u.role=\'student\' GROUP BY u.id HAVING points > (SELECT COALESCE(SUM(score),0) FROM quiz_attempts WHERE user_id=?)) ranks', 'i', [$uid]);

$skills = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score DESC', 'i', [$uid]);
$achievements = many('SELECT * FROM achievements WHERE user_id=? ORDER BY earned_at DESC LIMIT 4', 'i', [$uid]);
$recentTests = many('SELECT qa.*, s.name as subject_name, s.code as subject_code FROM quiz_attempts qa JOIN subjects s ON s.id=qa.subject_id WHERE qa.user_id=? ORDER BY qa.created_at DESC LIMIT 4', 'i', [$uid]);
$records = many('SELECT s.name, ar.marks, ar.attendance, ar.syllabus_progress FROM academic_records ar JOIN subjects s ON s.id=ar.subject_id WHERE ar.user_id=?', 'i', [$uid]);

$pageTitle = 'Student Dashboard';
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow"><?= date('l, F j, Y') ?> · Tech Fest Student Platform</div>
<h1><?= e($greeting) ?>, <?= e(explode(' ', $user['name'])[0]) ?>.</h1>
<p>Track your technical skill growth, identify focus areas, and prepare your verified resume.</p>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<a class="button button-quiet" href="resume.php">My Resume <span>↗</span></a>
<a class="button button-quiet" href="certificate.php">My Certificate 📜</a>
<a class="button button-primary" href="practice.php">Practice Test <span>↗</span></a>
</div>
</div>

<!-- Key Performance Stats Grid -->
<div class="stat-grid">
<div class="stat-card">
<small>Questions Solved</small>
<strong><?= number_format($stats['solved']) ?></strong>
<?php if((int)$stats['solved'] > 0): ?>
<span class="delta"><?= $stats['tests_taken'] ?> assessments completed</span>
<?php else: ?>
<span class="delta muted-delta">Take your first test</span>
<?php endif; ?>
</div>

<div class="stat-card">
<small>Total Score Points</small>
<strong><?= number_format($stats['points']) ?></strong>
<span class="delta"><?= number_format($monthly['points']) ?> pts this month</span>
</div>

<div class="stat-card">
<small>Average Accuracy</small>
<strong><?= round($stats['accuracy']) ?>%</strong>
<span class="delta"><?= round($stats['accuracy']) >= 75 ? 'Strong Proficiency' : 'Developing Baseline' ?></span>
</div>

<div class="stat-card">
<small>Campus Rank</small>
<strong><?= ((int)$stats['solved'] > 0 ? '#' . e((string)($rank['rank_no'] ?? '1')) : '—') ?></strong>
<span class="delta"><a href="leaderboard.php" style="color:var(--teal);text-decoration:underline">View Leaderboard ♛</a></span>
</div>
</div>

<!-- Two-Column Main Dashboard Grid -->
<div class="dashboard-grid">
<section class="panel">
<div class="panel-head">
<div>
<h2>Verified Skill Snapshot</h2>
<small>Calculated with weighted assessment scoring</small>
</div>
<a class="text-link" href="analytics.php">In-Depth AI Analysis →</a>
</div>

<?php if ($skills): ?>
<?php foreach($skills as $skill): 
    $lvlBadge = match($skill['level']) {
        'Expert' => 'pill lime',
        'Advanced' => 'pill',
        'Intermediate' => 'pill',
        default => 'pill'
    };
?>
<div class="skill-row">
<div class="skill-label">
<strong><?= e($skill['name']) ?></strong>
<span>
<span class="<?= $lvlBadge ?>" style="font-size:0.65rem;margin-right:6px"><?= e($skill['level']) ?></span>
<?= round($skill['score']) ?>%
</span>
</div>
<div class="progress">
<i data-progress="<?= round($skill['score']) ?>"></i>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty-state">
<strong>Your skill snapshot is empty.</strong>
<span>Complete a practice assessment to start tracking your verified technical competencies.</span>
<a class="text-link" href="practice.php">Start practicing now →</a>
</div>
<?php endif; ?>
</section>

<!-- Achievements Section -->
<section class="panel">
<div class="panel-head">
<div>
<h2>Earned Proof Points</h2>
<small>Badges & Milestones</small>
</div>
<span class="pill lime"><?= count($achievements) ?> Earned</span>
</div>

<?php if ($achievements): ?>
<?php foreach($achievements as $item): ?>
<div class="achievement">
<span style="font-size:1.1rem"><?= e($item['icon']) ?></span>
<div style="flex:1">
<strong><?= e($item['title']) ?></strong>
<small><?= e($item['description']) ?></small>
</div>
<small class="muted" style="font-size:0.7rem"><?= date('M d', strtotime($item['earned_at'])) ?></small>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty-state">
<strong>Your first achievement is within reach.</strong>
<span>Complete practice assessments with 80%+ accuracy to unlock milestone badges.</span>
</div>
<?php endif; ?>
</section>
</div>

<!-- Recent Assessment History & Academic Progress -->
<div class="dashboard-grid" style="margin-top:20px">
<section class="panel">
<div class="panel-head">
<div>
<h2>Recent Assessment Activity</h2>
<small>Instant scores and accuracy</small>
</div>
<a class="text-link" href="practice.php">New Test →</a>
</div>

<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Subject</th>
<th>Score</th>
<th>Accuracy</th>
<th>Date</th>
</tr>
</thead>
<tbody>
<?php if ($recentTests): ?>
<?php foreach ($recentTests as $test): ?>
<tr>
<td><strong><?= e($test['subject_name']) ?></strong></td>
<td><?= $test['score'] ?> / <?= $test['total_marks'] ?> pts</td>
<td>
<span class="pill <?= $test['accuracy'] >= 75 ? 'lime' : '' ?>">
<?= round($test['accuracy']) ?>%
</span>
</td>
<td class="muted"><?= date('M d, Y', strtotime($test['created_at'])) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4" class="muted" style="text-align:center;padding:25px">No assessments taken yet. Take your first test from the Practice studio!</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>

<section class="panel">
<div class="panel-head">
<div>
<h2>Academic Coursework</h2>
<small>Semester overview</small>
</div>
<a class="text-link" href="profile.php">Manage →</a>
</div>

<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Subject</th>
<th>Attendance</th>
<th>Syllabus</th>
</tr>
</thead>
<tbody>
<?php if ($records): ?>
<?php foreach ($records as $r): ?>
<tr>
<td><strong><?= e($r['name']) ?></strong></td>
<td><?= round($r['attendance']) ?>%</td>
<td>
<div class="progress" style="width:100px;display:inline-block;vertical-align:middle;margin-right:8px">
<i data-progress="<?= round($r['syllabus_progress']) ?>"></i>
</div>
<small><?= round($r['syllabus_progress']) ?>%</small>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="3" class="muted" style="text-align:center;padding:25px">Academic records will appear here.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>
</div>

<?php include 'includes/footer.php'; ?>
