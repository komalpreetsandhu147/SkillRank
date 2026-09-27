<?php 
require_once __DIR__ . '/auth.php'; 
require_login('admin');

// Overall Assessment Metrics
$totalAttempts = one('SELECT COUNT(*) total FROM quiz_attempts')['total'] ?? 0;
$avgAccuracy = one('SELECT ROUND(AVG(accuracy)) val FROM quiz_attempts')['val'] ?? 0;
$passedAttempts = one('SELECT COUNT(*) total FROM quiz_attempts WHERE accuracy >= 60')['total'] ?? 0;
$passRate = ($totalAttempts > 0) ? round(($passedAttempts / $totalAttempts) * 100) : 0;

// Subject Performance Analytics
$subjects = many(
    'SELECT s.id, s.name, s.code, 
            COUNT(DISTINCT q.id) as question_count,
            COUNT(DISTINCT qa.id) as attempts,
            ROUND(AVG(qa.accuracy)) as avg_accuracy,
            ROUND((SUM(CASE WHEN qa.accuracy >= 60 THEN 1 ELSE 0 END) / COUNT(qa.id)) * 100) as pass_rate
     FROM subjects s 
     LEFT JOIN questions q ON q.subject_id=s.id 
     LEFT JOIN quiz_attempts qa ON qa.subject_id=s.id 
     GROUP BY s.id 
     ORDER BY avg_accuracy DESC'
);

// Identified Weak Subjects / Areas for Academic Intervention
$weak = many(
    'SELECT s.name, s.code, ROUND(AVG(qa.accuracy)) accuracy, COUNT(qa.id) attempts 
     FROM subjects s 
     JOIN quiz_attempts qa ON qa.subject_id=s.id 
     GROUP BY s.id 
     ORDER BY accuracy ASC 
     LIMIT 3'
);

// Questions Difficulty Distribution
$diffStats = many(
    'SELECT difficulty, COUNT(*) count, ROUND(AVG(marks)) avg_marks 
     FROM questions 
     GROUP BY difficulty 
     ORDER BY FIELD(difficulty, "Easy", "Medium", "Hard")'
);

$pageTitle = 'Institutional Skill Analytics';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Institutional Analytics</div>
<h1>SkillRank Performance Intelligence</h1>
<p>Measure campus-wide student technical proficiency, pass rates, and identified curriculum gaps.</p>
</div>
<span class="pill lime">📊 Campus Intelligence Signal</span>
</div>

<!-- Macro Stat Grid -->
<div class="stat-grid">
<div class="stat-card">
<small>Total Assessments Evaluated</small>
<strong><?= number_format($totalAttempts) ?></strong>
<span class="delta">Across all subjects</span>
</div>
<div class="stat-card">
<small>Mean Institutional Accuracy</small>
<strong><?= $avgAccuracy ?>%</strong>
<span class="delta"><?= $avgAccuracy >= 75 ? 'Healthy Proficiency' : 'Requires Reinforcement' ?></span>
</div>
<div class="stat-card">
<small>Overall Assessment Pass Rate</small>
<strong><?= $passRate ?>%</strong>
<span class="delta">Scored &gt;= 60%</span>
</div>
<div class="stat-card">
<small>Active Question Inventory</small>
<strong><?= array_sum(array_column($subjects, 'question_count')) ?></strong>
<span class="delta">Peer-reviewed items</span>
</div>
</div>

<div class="dashboard-grid">
<!-- Subject Mastery Comparison Table -->
<section class="panel">
<div class="panel-head">
<div>
<h2>Subject Performance & Pass Rates</h2>
<small>Aggregated across all registered students</small>
</div>
</div>

<div style="display:grid;gap:18px">
<?php foreach($subjects as $item): 
    $acc = round($item['avg_accuracy'] ?? 0);
    $pr = round($item['pass_rate'] ?? 0);
?>
<div style="border-bottom:1px solid var(--line);padding-bottom:14px">
<div class="skill-label" style="margin-bottom:6px">
<div>
<strong><?= e($item['name']) ?> (<?= e($item['code']) ?>)</strong>
<small style="display:block;color:var(--muted)"><?= $item['attempts'] ?> assessments taken · <?= $item['question_count'] ?> questions</small>
</div>
<div style="text-align:right">
<span style="font-weight:700"><?= $acc ?>% Accuracy</span>
<small style="display:block;color:var(--teal)"><?= $pr ?>% Pass Rate</small>
</div>
</div>
<div class="progress">
<i data-progress="<?= $acc ?>"></i>
</div>
</div>
<?php endforeach; ?>
</div>
</section>

<!-- Curriculum Intervention & Gaps -->
<section class="panel">
<div class="eyebrow" style="color:var(--orange)">Targeted Interventions</div>
<h2 style="margin-top:6px">Critical Skill Gaps</h2>
<p>Subjects where student cohorts demonstrate lower average mastery, indicating topics for targeted lectures or workshops:</p>

<div style="display:grid;gap:12px;margin:18px 0">
<?php foreach($weak as $item): ?>
<div class="achievement" style="padding:14px;background:#fff8f6;border:1px solid #ffdcd4;border-radius:8px">
<span style="background:#ffe8e1;color:#a64123;font-weight:800;font-size:1.1rem">!</span>
<div style="flex:1">
<div style="display:flex;justify-content:space-between">
<strong><?= e($item['name']) ?> (<?= e($item['code']) ?>)</strong>
<span class="pill" style="background:#ffe8e1;color:#a64123"><?= $item['accuracy'] ?>% Avg</span>
</div>
<small style="color:var(--muted);display:block;margin-top:4px">
Lowest cohort accuracy. Recommended: Curate 5 additional conceptual questions or host a refresher session.
</small>
</div>
</div>
<?php endforeach; ?>
</div>

<div style="margin-top:20px;border-top:1px solid var(--line);padding-top:16px">
<h3 style="font-size:1rem;margin-bottom:10px">Question Bank Difficulty Mix</h3>
<div style="display:flex;gap:10px">
<?php foreach ($diffStats as $ds): ?>
<div style="flex:1;background:#f8fafc;padding:10px;border-radius:6px;border:1px solid var(--line);text-align:center">
<small style="color:var(--muted);text-transform:uppercase;font-size:0.7rem;font-weight:700"><?= e($ds['difficulty']) ?></small>
<strong style="display:block;font-size:1.2rem;margin-top:3px"><?= $ds['count'] ?></strong>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
</div>

<?php include 'includes/footer.php'; ?>