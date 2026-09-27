<?php 
require_once __DIR__ . '/auth.php'; 
require_once __DIR__ . '/ai.php';
require_login('admin');

$id = (int)($_GET['id'] ?? 0);
$student = one('SELECT u.id, u.name, u.email, u.created_at as joined_date, s.* FROM users u JOIN students s ON s.user_id=u.id WHERE u.id=?', 'i', [$id]);
if (!$student) redirect('students.php');

// Performance metrics
$stats = one('SELECT COALESCE(SUM(questions_count),0) solved, COALESCE(SUM(score),0) points, COALESCE(ROUND(AVG(accuracy)),0) accuracy, COUNT(id) tests_taken FROM quiz_attempts WHERE user_id=?', 'i', [$id]);

// Verified Skills
$skills = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score DESC', 'i', [$id]);

// Test Attempts
$attempts = many('SELECT qa.*, s.name as subject_name, s.code as subject_code FROM quiz_attempts qa JOIN subjects s ON s.id=qa.subject_id WHERE qa.user_id=? ORDER BY qa.created_at DESC', 'i', [$id]);

// Academic records
$records = many('SELECT s.name, ar.marks, ar.attendance, ar.syllabus_progress FROM academic_records ar JOIN subjects s ON s.id=ar.subject_id WHERE ar.user_id=?', 'i', [$id]);

// AI Performance evaluation for this student
$aiDiag = ai_analyze_student_performance($id);

$pageTitle = 'Student Report · ' . $student['name'];
include 'includes/header.php';
?>
<div class="page-head no-print">
<div>
<div class="eyebrow">Academic Diagnostic Record</div>
<h1>Student Assessment Report</h1>
<p>Comprehensive transcript of verified technical competencies, test attempts, and faculty recommendations.</p>
</div>
<div style="display:flex;gap:10px">
<a class="button button-quiet" href="students.php">← Back to Directory</a>
<button class="button button-primary" onclick="window.print()">Print Official Report <span>↓</span></button>
</div>
</div>

<article class="panel" style="padding:40px;margin-bottom:25px;border-top:6px solid var(--teal)">
<!-- Student Identity Header -->
<div style="display:flex;justify-content:space-between;align-items:start;border-bottom:1px solid var(--line);padding-bottom:20px;flex-wrap:wrap;gap:15px">
<div>
<h1 style="font-size:2.2rem;margin:0 0 6px"><?= e($student['name']) ?></h1>
<div style="font-size:1.05rem;font-weight:700;color:var(--teal);margin-bottom:6px">
<?= e($student['career_goal'] ?: 'Aspiring Software Developer') ?>
</div>
<p style="margin:0;color:var(--muted)">
<?= e($student['course']) ?> · <?= e($student['university']) ?> (<?= e($student['semester']) ?>)
</p>
<div style="display:flex;gap:15px;margin-top:8px;font-size:0.85rem;color:var(--muted)">
<span>✉ <?= e($student['email']) ?></span>
<?php if(!empty($student['phone'])): ?><span>📱 <?= e($student['phone']) ?></span><?php endif; ?>
<span>Enrolled: <?= date('M Y', strtotime($student['joined_date'])) ?></span>
</div>
</div>

<div style="text-align:right">
<div class="pill lime" style="font-size:0.85rem;padding:7px 15px">SkillRank Verified Student</div>
<div style="margin-top:6px;font-size:0.8rem;color:var(--muted)">Student ID: SR-<?= sprintf('%05d', $student['id']) ?></div>
</div>
</div>

<!-- Performance KPIs -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin:25px 0">
<div class="stat-card" style="background:#f8fafc">
<small>Questions Solved</small>
<strong><?= number_format($stats['solved']) ?></strong>
<span class="delta">Across <?= $stats['tests_taken'] ?> tests</span>
</div>
<div class="stat-card" style="background:#f8fafc">
<small>Total Score Points</small>
<strong><?= number_format($stats['points']) ?></strong>
<span class="delta">Assessed points</span>
</div>
<div class="stat-card" style="background:#f8fafc">
<small>Average Accuracy</small>
<strong><?= $stats['accuracy'] ?>%</strong>
<span class="delta"><?= $stats['accuracy'] >= 75 ? 'Above average' : 'Baseline level' ?></span>
</div>
<div class="stat-card" style="background:#f8fafc">
<small>Verified Skills</small>
<strong><?= count($skills) ?></strong>
<span class="delta">Skill tracks active</span>
</div>
</div>

<!-- AI Performance Diagnostics -->
<div style="background:#f0f9fa;border:1px solid #cce8ed;border-radius:10px;padding:20px;margin-bottom:25px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
<strong style="color:var(--teal);text-transform:uppercase;font-size:0.8rem;letter-spacing:0.05em">
✦ AI Diagnostic Assessment & Gap Analysis
</strong>
<span class="pill lime" style="font-size:0.65rem"><?= e($aiDiag['engine']) ?></span>
</div>
<p style="margin:0 0 14px;color:var(--ink);line-height:1.55;font-size:0.95rem">
<?= e($aiDiag['summary']) ?>
</p>

<div class="dashboard-grid" style="grid-template-columns:1fr 1fr;gap:12px">
<?php if (!empty($aiDiag['strength_insight'])): ?>
<div style="background:#fff;padding:12px;border-radius:8px;border-left:3px solid var(--teal)">
<strong style="font-size:0.85rem;color:var(--teal);display:block;margin-bottom:4px">Key Strength:</strong>
<small style="color:var(--muted);display:block;line-height:1.4"><?= e($aiDiag['strength_insight']) ?></small>
</div>
<?php endif; ?>

<?php if (!empty($aiDiag['gap_insight'])): ?>
<div style="background:#fff;padding:12px;border-radius:8px;border-left:3px solid #d88b62">
<strong style="font-size:0.85rem;color:#d88b62;display:block;margin-bottom:4px">Identified Skill Gap:</strong>
<small style="color:var(--muted);display:block;line-height:1.4"><?= e($aiDiag['gap_insight']) ?></small>
</div>
<?php endif; ?>
</div>
</div>

<!-- Verified Skills Breakdown -->
<div style="margin-bottom:25px">
<h3 style="font-size:1.1rem;margin-bottom:12px">Verified Technical Skill Proficiency</h3>
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:15px">
<?php foreach($skills as $sk): 
    $lvlBadge = match($sk['level']) {
        'Expert' => 'pill lime',
        'Advanced' => 'pill',
        'Intermediate' => 'pill',
        default => 'pill'
    };
?>
<div style="background:#fff;border:1px solid var(--line);padding:14px;border-radius:8px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
<strong><?= e($sk['name']) ?></strong>
<span class="<?= $lvlBadge ?>"><?= e($sk['level']) ?> (<?= round($sk['score']) ?>%)</span>
</div>
<div class="progress"><i data-progress="<?= round($sk['score']) ?>"></i></div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Test Attempt History -->
<div style="margin-bottom:25px">
<h3 style="font-size:1.1rem;margin-bottom:12px">Assessment Attempt History</h3>
<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Subject</th>
<th>Score</th>
<th>Accuracy</th>
<th>Time Taken</th>
<th>Attempt Date</th>
</tr>
</thead>
<tbody>
<?php if ($attempts): ?>
<?php foreach($attempts as $att): ?>
<tr>
<td><strong><?= e($att['subject_name']) ?> (<?= e($att['subject_code']) ?>)</strong></td>
<td><?= $att['score'] ?> / <?= $att['total_marks'] ?> pts</td>
<td>
<span class="pill <?= $att['accuracy'] >= 75 ? 'lime' : '' ?>">
<?= round($att['accuracy']) ?>%
</span>
</td>
<td><?= sprintf('%dm %02ds', floor($att['time_taken_seconds']/60), $att['time_taken_seconds']%60) ?></td>
<td class="muted"><?= date('M d, Y H:i', strtotime($att['created_at'])) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="5" class="muted" style="text-align:center;padding:20px">No test attempts logged yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- Academic Records -->
<?php if ($records): ?>
<div>
<h3 style="font-size:1.1rem;margin-bottom:12px">Coursework Academic Marks</h3>
<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Subject</th>
<th>Coursework Marks</th>
<th>Attendance</th>
<th>Syllabus Coverage</th>
</tr>
</thead>
<tbody>
<?php foreach($records as $rec): ?>
<tr>
<td><strong><?= e($rec['name']) ?></strong></td>
<td><?= round($rec['marks']) ?>%</td>
<td><?= round($rec['attendance']) ?>%</td>
<td><?= round($rec['syllabus_progress']) ?>%</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endif; ?>
</article>

<style>
@media print {
    body { background: #fff !important; margin: 0 !important; }
    .sidebar, .site-header, .page-head, .no-print, .button, .logout, footer { display: none !important; }
    .app-main { margin: 0 !important; padding: 0 !important; }
    .panel { border: none !important; box-shadow: none !important; padding: 0 !important; }
}
</style>

<?php include 'includes/footer.php'; ?>