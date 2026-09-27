<?php 
require_once __DIR__ . '/auth.php'; 
require_once __DIR__ . '/ai.php';
require_login('admin');

// High-level operations metrics
$students = one('SELECT COUNT(*) total FROM users WHERE role=\'student\'');
$questions = one('SELECT COUNT(*) total FROM questions');
$attempts = one('SELECT COUNT(*) total FROM quiz_attempts');
$avgAccuracy = one('SELECT ROUND(AVG(accuracy)) value FROM quiz_attempts');
$totalSubjects = one('SELECT COUNT(*) total FROM subjects');

// Recent assessments activity
$recent = many(
    'SELECT qa.id, u.id as user_id, u.name as student_name, s.name as subject_name, qa.score, qa.total_marks, qa.accuracy, qa.created_at 
     FROM quiz_attempts qa 
     JOIN users u ON u.id=qa.user_id 
     JOIN subjects s ON s.id=qa.subject_id 
     ORDER BY qa.created_at DESC 
     LIMIT 8'
);

// Skill statistics across network
$skillStats = many(
    'SELECT sk.name, ROUND(AVG(ss.score)) as avg_score, COUNT(ss.id) as assessed_students 
     FROM skills sk 
     LEFT JOIN student_skills ss ON ss.skill_id=sk.id 
     GROUP BY sk.id 
     ORDER BY avg_score DESC'
);

$pageTitle = 'Admin Operations Console';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Faculty & Operations Console</div>
<h1>SkillRank Administrative Overview</h1>
<p>Monitor real-time student assessment throughput, curriculum coverage, and network skill signals.</p>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<a class="button button-quiet" href="questions.php">+ AI Question Studio</a>
<a class="button button-primary" href="question_form.php">+ Add Question <span>↗</span></a>
</div>
</div>

<!-- Primary Metric Stat Cards -->
<div class="stat-grid">
<div class="stat-card">
<small>Enrolled Students</small>
<strong><?= number_format($students['total']) ?></strong>
<span class="delta">Active college learners</span>
</div>
<div class="stat-card">
<small>Question Bank</small>
<strong><?= number_format($questions['total']) ?></strong>
<span class="delta">Across <?= $totalSubjects['total'] ?> curriculum tracks</span>
</div>
<div class="stat-card">
<small>Tests Completed</small>
<strong><?= number_format($attempts['total']) ?></strong>
<span class="delta">Evaluated automatically</span>
</div>
<div class="stat-card">
<small>Network Accuracy</small>
<strong><?= $avgAccuracy['value'] ?? 0 ?>%</strong>
<span class="delta">Mean technical accuracy</span>
</div>
</div>

<!-- Quick Administration Tool Navigation -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;margin-bottom:28px">
<a href="students.php" class="panel" style="padding:16px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:0.2s transform">
<span class="icon-tile lime" style="font-size:1.2rem">♙</span>
<div>
<strong style="display:block;font-size:0.95rem">Students</strong>
<small style="color:var(--muted)">View profiles & reports</small>
</div>
</a>

<a href="questions.php" class="panel" style="padding:16px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:0.2s transform">
<span class="icon-tile lime" style="font-size:1.2rem">▤</span>
<div>
<strong style="display:block;font-size:0.95rem">Questions</strong>
<small style="color:var(--muted)">CRUD & AI generator</small>
</div>
</a>

<a href="subjects.php" class="panel" style="padding:16px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:0.2s transform">
<span class="icon-tile lime" style="font-size:1.2rem">▦</span>
<div>
<strong style="display:block;font-size:0.95rem">Subjects</strong>
<small style="color:var(--muted)">Manage tracks & topics</small>
</div>
</a>

<a href="admin_analytics.php" class="panel" style="padding:16px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:0.2s transform">
<span class="icon-tile lime" style="font-size:1.2rem">◒</span>
<div>
<strong style="display:block;font-size:0.95rem">Analytics</strong>
<small style="color:var(--muted)">Skill gaps & signals</small>
</div>
</a>

<a href="admin_ai.php" class="panel" style="padding:16px;text-decoration:none;display:flex;align-items:center;gap:12px;transition:0.2s transform">
<span class="icon-tile lime" style="font-size:1.2rem">✦</span>
<div>
<strong style="display:block;font-size:0.95rem">AI Diagnostics</strong>
<small style="color:var(--muted)">API ping & latency</small>
</div>
</a>
</div>

<!-- Two-Column Layout: Latest Assessments and Network Skill Stats -->
<div class="dashboard-grid">
<!-- Recent Activity Feed -->
<section class="panel">
<div class="panel-head">
<div>
<h2>Latest Assessment Submissions</h2>
<small>Live student evaluation stream</small>
</div>
<a class="text-link" href="students.php">All Students →</a>
</div>

<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Student</th>
<th>Subject</th>
<th>Score</th>
<th>Accuracy</th>
<th>When</th>
<th>Report</th>
</tr>
</thead>
<tbody>
<?php if ($recent): ?>
<?php foreach($recent as $item): ?>
<tr>
<td>
<strong><?= e($item['student_name']) ?></strong>
</td>
<td><?= e($item['subject_name']) ?></td>
<td><?= $item['score'] ?> / <?= $item['total_marks'] ?> pts</td>
<td>
<span class="pill <?= $item['accuracy'] >= 75 ? 'lime' : '' ?>">
<?= round($item['accuracy']) ?>%
</span>
</td>
<td class="muted"><?= date('M d, H:i', strtotime($item['created_at'])) ?></td>
<td>
<a class="text-link" href="student_profile.php?id=<?= $item['user_id'] ?>">View Report →</a>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="6" class="muted" style="text-align:center;padding:25px">No assessment activity logged yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>

<!-- Subject Skill Stats -->
<section class="panel">
<div class="panel-head">
<div>
<h2>Network Skill Statistics</h2>
<small>Average student mastery</small>
</div>
</div>

<div style="display:grid;gap:15px">
<?php foreach($skillStats as $sk): 
    $val = round($sk['avg_score'] ?? 0);
?>
<div class="skill-row" style="margin:6px 0">
<div class="skill-label">
<strong><?= e($sk['name']) ?></strong>
<span><?= $val ?>% avg (<?= $sk['assessed_students'] ?> learners)</span>
</div>
<div class="progress">
<i data-progress="<?= $val ?>"></i>
</div>
</div>
<?php endforeach; ?>
</div>

<div style="margin-top:24px;padding:15px;background:#f8fafc;border-radius:8px">
<strong style="font-size:0.85rem;display:block;margin-bottom:4px">Campus Leaderboard Status:</strong>
<p style="margin:0;font-size:0.8rem;color:var(--muted)">
Review campus student rankings and competitive standings across all academic cohorts.
</p>
<a href="leaderboard.php" class="text-link" style="display:inline-block;margin-top:8px">Open Full Leaderboard ↗</a>
</div>
</section>
</div>

<?php include 'includes/footer.php'; ?>