<?php 
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai.php';
require_login('student');

$uid = current_user()['id'];

// Subject performance stats
$subjectStats = many(
    'SELECT s.name, s.code, ROUND(AVG(qa.accuracy)) accuracy, SUM(qa.questions_count) solved, COUNT(qa.id) attempts 
     FROM quiz_attempts qa 
     JOIN subjects s ON s.id=qa.subject_id 
     WHERE qa.user_id=? 
     GROUP BY s.id 
     ORDER BY accuracy DESC',
    'i',
    [$uid]
);

// Assessment accuracy history over time (for Canvas chart)
$history = many(
    'SELECT DATE_FORMAT(created_at, "%b %d") day, accuracy 
     FROM quiz_attempts 
     WHERE user_id=? 
     ORDER BY created_at ASC 
     LIMIT 12',
    'i',
    [$uid]
);

// Strongest and Weakest skills
$skills = many(
    'SELECT sk.name, ss.score, ss.level 
     FROM student_skills ss 
     JOIN skills sk ON sk.id=ss.skill_id 
     WHERE ss.user_id=? 
     ORDER BY ss.score DESC',
    'i',
    [$uid]
);

$strongest = $skills[0] ?? null;
$weakest = (!empty($skills) && count($skills) > 1) ? end($skills) : null;

// Call AI Engine for real-time diagnostics
$aiAnalysis = ai_analyze_student_performance($uid);

$pageTitle = 'Performance Analytics & AI Insights';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Diagnostic Intelligence</div>
<h1>Performance Analytics & AI Insights</h1>
<p>Pinpoint your strongest competencies, discover critical skill gaps, and follow personalized learning paths.</p>
</div>
<div><?= ai_badge_html() ?></div>
</div>

<!-- AI Performance Diagnostic Card -->
<section class="panel" style="background:linear-gradient(135deg,#102d46,#176b78);color:#fff;margin-bottom:24px;border:none">
<div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:15px;margin-bottom:14px">
<div>
<div class="eyebrow" style="color:var(--lime)">✦ AI Diagnostic Advisor</div>
<h2 style="color:#fff;margin:6px 0 0">Continuous Skill Gap Analysis</h2>
</div>
<span class="pill" style="background:#ffffff20;color:#fff;border:1px solid #ffffff40">
Engine: <?= e($aiAnalysis['engine']) ?>
</span>
</div>

<p style="color:#e0eff2;font-size:1.05rem;line-height:1.6;margin-bottom:20px">
<?= e($aiAnalysis['summary']) ?>
</p>

<div class="dashboard-grid" style="grid-template-columns:1fr 1fr;gap:15px;margin-bottom:20px">
<?php if (!empty($aiAnalysis['strength_insight'])): ?>
<div style="background:#ffffff15;padding:16px;border-radius:10px;border-left:4px solid var(--lime)">
<strong style="color:var(--lime);display:block;margin-bottom:6px;font-size:0.9rem">💪 Verified Strength</strong>
<p style="color:#d5e8ec;font-size:0.88rem;margin:0"><?= e($aiAnalysis['strength_insight']) ?></p>
</div>
<?php endif; ?>

<?php if (!empty($aiAnalysis['gap_insight'])): ?>
<div style="background:#ffffff15;padding:16px;border-radius:10px;border-left:4px solid #ffaa88">
<strong style="color:#ffaa88;display:block;margin-bottom:6px;font-size:0.9rem">⚠ Identified Skill Gap</strong>
<p style="color:#d5e8ec;font-size:0.88rem;margin:0"><?= e($aiAnalysis['gap_insight']) ?></p>
</div>
<?php endif; ?>
</div>

<?php if (!empty($aiAnalysis['action_plan'])): ?>
<div style="background:#ffffff20;padding:14px 18px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
<div>
<small style="color:var(--lime);font-weight:700;text-transform:uppercase;letter-spacing:0.05em">Recommended Action Step:</small>
<div style="color:#fff;font-weight:600;font-size:0.92rem;margin-top:2px"><?= e($aiAnalysis['action_plan']) ?></div>
</div>
<a href="practice.php" class="button button-primary" style="padding:9px 16px;font-size:0.82rem">Take Recommended Test ↗</a>
</div>
<?php endif; ?>
</section>

<!-- Accuracy Chart & Strong/Weak Breakdown -->
<div class="dashboard-grid">
<!-- Accuracy Over Time Chart -->
<section class="panel">
<div class="panel-head">
<div>
<h2>Accuracy Trajectory</h2>
<small>Trend across recent assessments</small>
</div>
<?php if($history): ?>
<span class="pill lime">Live Signal</span>
<?php else: ?>
<span class="pill">No data yet</span>
<?php endif; ?>
</div>

<?php if($history): ?>
<div style="padding:10px 0">
<canvas class="chart" data-chart='<?= json_encode(array_column($history, 'accuracy')) ?>'></canvas>
<div style="display:flex;justify-content:space-between;color:var(--muted);font-size:0.75rem;margin-top:10px">
<?php foreach(array_slice($history, -6) as $item): ?>
<span><?= e($item['day']) ?> (<?= round($item['accuracy']) ?>%)</span>
<?php endforeach; ?>
</div>
</div>
<?php else: ?>
<div class="empty-state chart-empty">
<strong>Your accuracy trend will visualize here.</strong>
<span>Take a test in the practice studio to start plotting your progress.</span>
<a class="text-link" href="practice.php">Start an assessment →</a>
</div>
<?php endif; ?>
</section>

<!-- Strengths and Skill Gaps Card -->
<section class="panel">
<div class="panel-head">
<div>
<h2>Skill Gaps & Strengths</h2>
<small>Benchmark comparison</small>
</div>
</div>

<?php if ($strongest): ?>
<div style="padding:14px;border-radius:10px;background:#f4fbf7;border:1px solid #c9edd7;margin-bottom:14px">
<div style="display:flex;justify-content:space-between;align-items:center">
<small style="color:#1e6355;font-weight:700;text-transform:uppercase">Top Competency</small>
<span class="pill lime"><?= e($strongest['level']) ?></span>
</div>
<strong style="font-size:1.1rem;display:block;margin:6px 0 2px"><?= e($strongest['name']) ?></strong>
<div class="progress" style="height:6px;margin:8px 0"><i style="width:<?= round($strongest['score']) ?>%"></i></div>
<small class="muted">Verified score: <strong><?= round($strongest['score']) ?>%</strong></small>
</div>
<?php endif; ?>

<?php if ($weakest): ?>
<div style="padding:14px;border-radius:10px;background:#fff8f6;border:1px solid #ffdcd4;margin-bottom:14px">
<div style="display:flex;justify-content:space-between;align-items:center">
<small style="color:#a64123;font-weight:700;text-transform:uppercase">Identified Skill Gap</small>
<span class="pill" style="background:#ffe8e1;color:#a64123"><?= e($weakest['level']) ?></span>
</div>
<strong style="font-size:1.1rem;display:block;margin:6px 0 2px"><?= e($weakest['name']) ?></strong>
<div class="progress" style="height:6px;margin:8px 0"><i style="width:<?= round($weakest['score']) ?>%;background:#d88b62"></i></div>
<small class="muted">Current score: <strong><?= round($weakest['score']) ?>%</strong> · Priority for improvement</small>
</div>
<?php endif; ?>

<a href="practice.php" class="button button-quiet full" style="font-size:0.85rem">Practice Weak Area ↗</a>
</section>
</div>

<!-- Subject Wise Performance Table -->
<section class="panel" style="margin-top:20px">
<div class="panel-head">
<div>
<h2>Subject-Wise Performance</h2>
<small>Comprehensive accuracy breakdown</small>
</div>
<small class="muted"><?= count($subjectStats) ?> subjects assessed</small>
</div>

<?php if ($subjectStats): ?>
<?php foreach($subjectStats as $item): ?>
<div class="skill-row">
<div class="skill-label">
<strong><?= e($item['name']) ?> (<?= e($item['code']) ?>)</strong>
<span>
<strong><?= round($item['accuracy']) ?>%</strong> accuracy · <?= $item['solved'] ?> questions (<?= $item['attempts'] ?> tests)
</span>
</div>
<div class="progress">
<i data-progress="<?= round($item['accuracy']) ?>"></i>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty-state">
<strong>No subject assessments found.</strong>
<span>Complete an assessment to populate your subject-wise breakdown.</span>
</div>
<?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
