<?php 
require_once __DIR__ . '/auth.php';
require_login('student');
$user = current_user();
$uid = $user['id'];

// Get subjects and question counts
$subjects = many('SELECT s.*, COUNT(q.id) question_count FROM subjects s LEFT JOIN questions q ON q.subject_id=s.id GROUP BY s.id ORDER BY s.id');

// Get student's current skills
$userSkills = [];
$skillsData = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=?', 'i', [$uid]);
foreach ($skillsData as $row) {
    $userSkills[$row['name']] = $row;
}

// Find weakest skill (for targeted gap closing)
$weak = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score ASC LIMIT 1', 'i', [$uid]);
$recommendedSubject = null;

if (!empty($weak)) {
    foreach ($subjects as $sub) {
        if ($sub['name'] === $weak[0]['name']) {
            $recommendedSubject = $sub;
            break;
        }
    }
}
if (!$recommendedSubject && !empty($subjects)) {
    $recommendedSubject = $subjects[0];
}

$pageTitle = 'Skill Assessments & Practice';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Skill Verification Studio</div>
<h1>Select Your Assessment</h1>
<p>Complete adaptive technical assessments to benchmark proficiency, identify weak areas, and unlock verified resume badges.</p>
</div>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
<a class="button button-quiet" href="flashcards.php">🂠 Flashcards Studio</a>
<a class="button button-quiet" href="playground.php">⌨ Code Sandbox</a>
<span class="pill lime">⚡ Live Adaptive Engine</span>
</div>
</div>

<!-- Interactive Practice Modes Row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
<a href="flashcards.php" class="panel" style="padding:16px 20px;text-decoration:none;display:flex;align-items:center;gap:15px;transition:0.2s transform">
<div class="brand-mark" style="font-size:1.3rem;width:42px;height:42px;background:#eef6cf;color:#1e6355">🂠</div>
<div>
<strong style="display:block;font-size:1rem;color:var(--ink)">Active Recall Flashcards</strong>
<small style="color:var(--muted)">Flip cards, test technical concepts, and review verified solutions without test anxiety.</small>
</div>
</a>

<a href="playground.php" class="panel" style="padding:16px 20px;text-decoration:none;display:flex;align-items:center;gap:15px;transition:0.2s transform">
<div class="brand-mark" style="font-size:1.3rem;width:42px;height:42px;background:#b9e7f2;color:#102d46">⌨</div>
<div>
<strong style="display:block;font-size:1rem;color:var(--ink)">Live Code Sandbox</strong>
<small style="color:var(--muted)">Run real-time HTML, CSS & JavaScript components in an isolated browser preview lab.</small>
</div>
</a>
</div>

<div class="dashboard-grid">
<section>
<?php if ($recommendedSubject): ?>
<div class="panel" style="margin-bottom:20px;background:var(--ink);color:#fff">
<div class="eyebrow" style="color:var(--lime)">🎯 Personalized Practice Recommendation</div>
<h2 style="color:#fff;margin-top:10px">Close Your Skill Gap in <?= e($recommendedSubject['name']) ?></h2>
<p style="color:#b8c8bd">
<?php if (!empty($weak)): ?>
Your verified proficiency is currently at <strong><?= round($weak[0]['score']) ?>% (<?= e($weak[0]['level']) ?>)</strong>. 
Taking this focused 5-question sprint will target your lowest-scoring concepts and boost your standing on the college leaderboard.
<?php else: ?>
Begin with <?= e($recommendedSubject['name']) ?> to establish your baseline skill rating across core programming concepts.
<?php endif; ?>
</p>
<div style="display:flex;gap:12px;margin-top:16px;flex-wrap:wrap">
<a class="button button-primary" href="quiz.php?subject=<?= $recommendedSubject['id'] ?>&mode=sprint">Start 5-Question Sprint ↗</a>
<a class="button button-quiet" href="quiz.php?subject=<?= $recommendedSubject['id'] ?>&mode=full">Comprehensive 10-Q Test ↗</a>
</div>
</div>
<?php endif; ?>

<div class="panel">
<div class="panel-head">
<div>
<h2>Subject Assessment Tracks</h2>
<small><?= count($subjects) ?> skill modules available</small>
</div>
<small class="muted">Updated dynamically</small>
</div>

<div style="display:grid;gap:14px">
<?php foreach($subjects as $sub): 
    $skillInfo = $userSkills[$sub['name']] ?? null;
    $score = $skillInfo ? round($skillInfo['score']) : null;
    $level = $skillInfo ? $skillInfo['level'] : 'Not Assessed';
    $lvlClass = match($level) {
        'Expert' => 'pill lime',
        'Advanced' => 'pill',
        'Intermediate' => 'pill',
        default => 'pill'
    };
?>
<div class="achievement" style="padding:16px 12px;border-radius:10px;background:#fff;border:1px solid var(--line);display:flex;align-items:center;gap:14px">
<span class="icon-tile lime" style="font-size:1.2rem"><?= e($sub['icon'] ?? '✦') ?></span>
<div style="flex:1">
<div style="display:flex;align-items:center;gap:10px">
<strong><?= e($sub['name']) ?></strong>
<span class="pill" style="font-size:0.65rem"><?= e($sub['code']) ?></span>
</div>
<small style="color:var(--muted);display:block;margin-top:3px"><?= e($sub['description'] ?? ($sub['question_count'] . ' verified questions')) ?></small>
<div style="margin-top:6px;display:flex;align-items:center;gap:12px;font-size:0.75rem">
<span>Questions: <strong><?= $sub['question_count'] ?></strong></span>
<span>Status: <strong class="<?= $lvlClass ?>"><?= $score !== null ? "$level ($score%)" : 'Not Assessed' ?></strong></span>
</div>
</div>
<div style="display:flex;flex-direction:column;gap:6px">
<a class="button button-primary" style="padding:9px 14px;font-size:0.8rem;white-space:nowrap" href="quiz.php?subject=<?= $sub['id'] ?>">Assess Now ↗</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>

<aside class="focus-panel">
<div class="eyebrow">Career Readiness Focus</div>
<h2>Skill Mastery Levels</h2>
<p>Each quiz automatically re-computes your level using our weighted scoring algorithm:</p>

<div style="display:grid;gap:10px;margin:20px 0;font-size:0.82rem">
<div style="background:#ffffff15;padding:10px;border-radius:8px">
<strong style="color:var(--lime)">👑 Expert (90 - 100%)</strong>
<small style="display:block;color:#d2e3e7">Elite proficiency. Ready for production engineering & client projects.</small>
</div>
<div style="background:#ffffff15;padding:10px;border-radius:8px">
<strong style="color:#fff">⭐ Advanced (75 - 89%)</strong>
<small style="display:block;color:#d2e3e7">Independent problem solver. Solves complex algorithmic challenges.</small>
</div>
<div style="background:#ffffff15;padding:10px;border-radius:8px">
<strong style="color:#d2e3e7">🔷 Intermediate (55 - 74%)</strong>
<small style="display:block;color:#d2e3e7">Solid core syntax. Building depth in architectural edge cases.</small>
</div>
<div style="background:#ffffff15;padding:10px;border-radius:8px">
<strong style="color:#9ab5bb">🌱 Beginner (Below 55%)</strong>
<small style="display:block;color:#d2e3e7">Foundational stage. Recommended for daily sprint practice.</small>
</div>
</div>

<div class="focus-note">
<span>Impact on Resume</span> Verified levels are exported directly into your printable resume!
</div>
<a class="button button-dark full" href="resume.php">Preview My Resume <span>→</span></a>
</aside>
</div>

<?php include 'includes/footer.php'; ?>
