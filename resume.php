<?php 
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai.php';
require_login('student');

$user = current_user();
$uid = $user['id'];

// Get student details
$student = one('SELECT * FROM students WHERE user_id=?', 'i', [$uid]);

// Get verified skills
$skills = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score DESC', 'i', [$uid]);

// Get stats
$stats = one('SELECT COALESCE(SUM(questions_count),0) solved, COALESCE(ROUND(AVG(accuracy)),0) accuracy, COUNT(id) tests_taken FROM quiz_attempts WHERE user_id=?', 'i', [$uid]);

// Get rank
$rank = one('SELECT COUNT(*)+1 rank_no FROM (SELECT u.id, COALESCE(SUM(qa.score),0) points FROM users u LEFT JOIN quiz_attempts qa ON qa.user_id=u.id WHERE u.role=\'student\' GROUP BY u.id HAVING points > (SELECT COALESCE(SUM(score),0) FROM quiz_attempts WHERE user_id=?)) ranks', 'i', [$uid]);

// Get achievements
$achievements = many('SELECT * FROM achievements WHERE user_id=? ORDER BY earned_at DESC LIMIT 3', 'i', [$uid]);

// Get or initialize resume record
$resume = one('SELECT * FROM resumes WHERE user_id=?', 'i', [$uid]);
if (!$resume) {
    $defaultSummary = ai_generate_resume_summary($uid);
    $defaultProjects = json_encode([
        ['title' => 'SkillRank Assessment Platform', 'tech' => 'PHP, MySQL, CSS Grid, Vanilla JS', 'description' => 'Architected a responsive skill verification web application with adaptive assessments, real-time analytics, and automated resume generation.'],
        ['title' => 'E-Commerce Inventory Manager', 'tech' => 'PHP, PDO, MariaDB, REST API', 'description' => 'Engineered a secure inventory tracking system implementing prepared statements, ACID transactions, and role-based access control.']
    ]);
    $defaultEdu = json_encode([
        ['degree' => $user['course'] ?? 'BCA / B.Tech CS', 'school' => $user['university'] ?? 'Northbridge University', 'year' => '2023 - 2026', 'score' => 'CGPA: 8.8 / 10']
    ]);
    query('INSERT INTO resumes (user_id, headline, summary, ai_summary, projects, education) VALUES (?, ?, ?, ?, ?, ?)',
        'isssss',
        [$uid, 'Aspiring Full-Stack Software Developer', $defaultSummary, $defaultSummary, $defaultProjects, $defaultEdu]
    );
    $resume = one('SELECT * FROM resumes WHERE user_id=?', 'i', [$uid]);
}

$notice = '';

// Handle AI Summary Generation
if (isset($_POST['action']) && $_POST['action'] === 'generate_ai_summary') {
    $newSummary = ai_generate_resume_summary($uid);
    query('UPDATE resumes SET summary=?, ai_summary=? WHERE user_id=?', 'ssi', [$newSummary, $newSummary, $uid]);
    $resume['summary'] = $newSummary;
    $resume['ai_summary'] = $newSummary;
    $notice = 'AI professional resume summary refreshed using your latest verified skills!';
}

// Handle Manual Save
if (isset($_POST['action']) && $_POST['action'] === 'save_resume') {
    $headline = trim($_POST['headline'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $github = trim($_POST['github_url'] ?? '');
    $linkedin = trim($_POST['linkedin_url'] ?? '');
    query('UPDATE resumes SET headline=?, summary=?, github_url=?, linkedin_url=? WHERE user_id=?', 'ssssi', [$headline, $summary, $github, $linkedin, $uid]);
    $resume = one('SELECT * FROM resumes WHERE user_id=?', 'i', [$uid]);
    $notice = 'Resume details updated successfully.';
}

$projectsList = json_decode($resume['projects'] ?? '[]', true) ?: [];
$educationList = json_decode($resume['education'] ?? '[]', true) ?: [];

$pageTitle = 'Skill-Based Resume';
include 'includes/header.php';
?>
<!-- Web Action Bar (Hidden on print) -->
<div class="page-head no-print">
<div>
<div class="eyebrow">Auto-Verified Credential</div>
<h1>Skill-Based Career Resume</h1>
<p>Every test you complete verifies your proficiency levels and automatically syncs to this profile.</p>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<a href="verify_credential.php?id=<?= $uid ?>" target="_blank" class="button button-quiet" title="Open publicly shareable verified certificate">
🎓 Public Verification ↗
</a>
<a href="certificate.php?id=<?= $uid ?>" target="_blank" class="button button-quiet" title="View printable diploma-style achievement certificate">
📜 Official Certificate ↗
</a>
<form method="post" style="display:inline">
<input type="hidden" name="action" value="generate_ai_summary">
<button type="submit" class="button button-quiet" title="Generate summary using verified skill scores">
✨ Generate AI Summary
</button>
</form>
<button class="button button-primary" onclick="window.print()">
Print / Save PDF <span>↓</span>
</button>
</div>
</div>

<?php if($notice): ?>
<div class="alert no-print" style="background:#eef6cf;color:var(--teal);font-weight:700">✓ <?= e($notice) ?></div>
<?php endif; ?>

<!-- Printable Resume Document Canvas -->
<article class="resume" id="resume-canvas" style="border-radius:12px;border:1px solid #d9e2ea;padding:50px 60px">
<!-- Top Header -->
<div class="resume-top" style="align-items:start">
<div>
<h1 style="font-size:2.4rem;margin:0 0 6px;letter-spacing:-0.03em"><?= e($user['name']) ?></h1>
<div style="font-size:1.1rem;font-weight:700;color:var(--teal);margin-bottom:6px">
<?= e($resume['headline'] ?: ($student['career_goal'] ?: 'Aspiring Software Developer')) ?>
</div>
<p style="margin:0 0 4px;font-size:0.95rem;color:var(--muted)">
<?= e($user['course']) ?> · <?= e($user['university']) ?> (<?= e($user['semester']) ?>)
</p>
<div style="display:flex;gap:15px;flex-wrap:wrap;font-size:0.85rem;color:var(--muted);margin-top:6px">
<span>✉ <?= e($user['email']) ?></span>
<?php if(!empty($student['phone'])): ?><span>📱 <?= e($student['phone']) ?></span><?php endif; ?>
<?php if(!empty($resume['github_url'])): ?><span>🐙 <?= e($resume['github_url']) ?></span><?php endif; ?>
<?php if(!empty($resume['linkedin_url'])): ?><span>💼 <?= e($resume['linkedin_url']) ?></span><?php endif; ?>
</div>
</div>

<div style="text-align:right">
<div class="pill lime" style="font-size:0.8rem;padding:8px 16px;border:1px solid #c2e2a8">
✓ SkillRank Verified Profile
</div>
<small style="display:block;margin-top:8px;color:var(--muted)">Campus ID: SR-<?= sprintf('%05d', $user['id']) ?></small>
</div>
</div>

<!-- Professional Summary -->
<div class="resume-section">
<h3>Professional Summary</h3>
<p style="font-size:0.95rem;line-height:1.65;color:#2c3e50">
<?= nl2br(e($resume['summary'])) ?>
</p>
</div>

<!-- Verified Technical Skills -->
<div class="resume-section">
<h3>Verified Technical Skills</h3>
<small style="display:block;color:var(--muted);margin-bottom:12px">Evaluated through progressive multiple-choice technical sprint assessments</small>
<div class="tag-list" style="gap:10px">
<?php if ($skills): ?>
<?php foreach($skills as $skill): 
    $lvlColor = match($skill['level']) {
        'Expert' => '#e6f4ea;color:#137333;border:1px solid #ceead6',
        'Advanced' => '#e8f0fe;color:#1a73e8;border:1px solid #d2e3fc',
        'Intermediate' => '#fef7e0;color:#b06000;border:1px solid #fce8b2',
        default => '#f1f3f4;color:#5f6368;border:1px solid #dadce0'
    };
?>
<span class="tag" style="background:<?= $lvlColor ?>;font-size:0.85rem;padding:8px 14px;border-radius:6px">
<strong><?= e($skill['name']) ?></strong> — <?= e($skill['level']) ?> (<?= round($skill['score']) ?>%)
</span>
<?php endforeach; ?>
<?php else: ?>
<span class="tag">Technical skills will appear here after taking your first practice test.</span>
<?php endif; ?>
</div>
</div>

<!-- Verified Assessment Outcomes -->
<div class="resume-section">
<h3>SkillRank Assessment Proof Metrics</h3>
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-top:10px">
<div class="stat-card" style="padding:15px;background:#f8fafc">
<small>Questions Solved</small>
<strong style="font-size:1.6rem"><?= number_format($stats['solved']) ?></strong>
<span class="delta">Across <?= $stats['tests_taken'] ?> tests</span>
</div>
<div class="stat-card" style="padding:15px;background:#f8fafc">
<small>Average Accuracy</small>
<strong style="font-size:1.6rem"><?= $stats['accuracy'] ?>%</strong>
<span class="delta">Evaluated</span>
</div>
<div class="stat-card" style="padding:15px;background:#f8fafc">
<small>College Rank</small>
<strong style="font-size:1.6rem">#<?= e((string)($rank['rank_no'] ?? '1')) ?></strong>
<span class="delta">Campus Leaderboard</span>
</div>
</div>
</div>

<!-- Featured Academic Projects -->
<div class="resume-section">
<h3>Technical Projects</h3>
<div style="display:grid;gap:14px;margin-top:10px">
<?php foreach ($projectsList as $proj): ?>
<div style="border-left:3px solid var(--teal);padding-left:14px">
<div style="display:flex;justify-content:space-between;align-items:center">
<strong style="font-size:1rem;color:var(--ink)"><?= e($proj['title']) ?></strong>
<small style="color:var(--teal);font-weight:700"><?= e($proj['tech']) ?></small>
</div>
<p style="margin:4px 0 0;font-size:0.88rem;color:var(--muted);line-height:1.5">
<?= e($proj['description']) ?>
</p>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Education -->
<div class="resume-section">
<h3>Education</h3>
<div style="margin-top:10px">
<?php foreach ($educationList as $edu): ?>
<div style="display:flex;justify-content:space-between;align-items:center">
<div>
<strong><?= e($edu['degree']) ?></strong> — <span class="muted"><?= e($edu['school']) ?></span>
</div>
<span class="pill lime" style="font-size:0.75rem"><?= e($edu['score']) ?></span>
</div>
<small style="color:var(--muted);display:block;margin-top:2px"><?= e($edu['year']) ?></small>
<?php endforeach; ?>
</div>
</div>

<!-- Achievements -->
<?php if ($achievements): ?>
<div class="resume-section">
<h3>Earned Proof Badges</h3>
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px">
<?php foreach ($achievements as $ach): ?>
<div style="display:inline-flex;align-items:center;gap:6px;background:#f8fafc;border:1px solid #d9e2ea;padding:6px 12px;border-radius:6px;font-size:0.82rem">
<span><?= e($ach['icon']) ?></span>
<strong><?= e($ach['title']) ?></strong>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
</article>

<!-- Customizer Accordion (Hidden on print) -->
<section class="panel no-print" style="max-width:850px;margin:24px auto">
<div class="panel-head">
<div>
<h2>Customize Resume Profile</h2>
<small>Edit headline, bio summary, and external links</small>
</div>
</div>

<form method="post" class="form-stack">
<input type="hidden" name="action" value="save_resume">

<div class="form-grid">
<label>Professional Headline
<input name="headline" value="<?= e($resume['headline']) ?>" placeholder="e.g. Full-Stack Developer & Database Specialist">
</label>
<label>GitHub Profile Link
<input name="github_url" value="<?= e($resume['github_url']) ?>" placeholder="https://github.com/yourusername">
</label>
</div>

<label>LinkedIn Profile Link
<input name="linkedin_url" value="<?= e($resume['linkedin_url']) ?>" placeholder="https://linkedin.com/in/yourusername">
</label>

<label>Professional Summary
<textarea name="summary" rows="4"><?= e($resume['summary']) ?></textarea>
</label>

<button class="button button-primary" type="submit" style="align-self:start">Save Resume Updates ↗</button>
</form>
</section>

<!-- Print Styles for Standard PDF Output -->
<style>
@media print {
    body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
    .sidebar, .site-header, .page-head, .no-print, .button, .logout, footer { display: none !important; }
    .app-main { margin: 0 !important; padding: 0 !important; }
    .resume { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; border-top: none !important; }
    .stat-card { border: 1px solid #ccc !important; }
}
</style>

<?php include 'includes/footer.php'; ?>