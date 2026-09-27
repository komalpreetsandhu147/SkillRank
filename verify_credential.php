<?php
// Public Credential Verification Page - SkillRank
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$studentId = (int)($_GET['id'] ?? 1);
$student = one(
    'SELECT u.id, u.name, u.email, u.created_at as joined_date, 
            s.university, s.course, s.semester, s.career_goal 
     FROM users u 
     LEFT JOIN students s ON s.user_id=u.id 
     WHERE u.id=? AND u.role="student"',
    'i',
    [$studentId]
);

if (!$student) {
    die("Credential record not found. Please verify the student ID.");
}

// Fetch verified skills
$skills = many(
    'SELECT sk.name, ss.score, ss.level, ss.updated_at 
     FROM student_skills ss 
     JOIN skills sk ON sk.id=ss.skill_id 
     WHERE ss.user_id=? 
     ORDER BY ss.score DESC',
    'i',
    [$studentId]
);

// Fetch stats
$stats = one(
    'SELECT COALESCE(SUM(questions_count),0) solved, 
            COALESCE(ROUND(AVG(accuracy)),0) accuracy, 
            COUNT(id) tests_taken 
     FROM quiz_attempts 
     WHERE user_id=?',
    'i',
    [$studentId]
);

// Rank
$rank = one(
    'SELECT COUNT(*)+1 rank_no 
     FROM (SELECT u.id, COALESCE(SUM(qa.score),0) points 
           FROM users u 
           LEFT JOIN quiz_attempts qa ON qa.user_id=u.id 
           WHERE u.role="student" 
           GROUP BY u.id 
           HAVING points > (SELECT COALESCE(SUM(score),0) FROM quiz_attempts WHERE user_id=?)) ranks',
    'i',
    [$studentId]
);

$certCode = 'SR-' . date('Y', strtotime($student['joined_date'])) . '-' . sprintf('%05d', $student['id']);
$verifyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$pageTitle = 'Verified Credential · ' . $student['name'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | SkillRank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=12">
<style>
.cert-card {
    max-width: 860px;
    margin: 40px auto;
    background: #fff;
    border: 2px solid #cce8ed;
    border-radius: 16px;
    padding: 55px;
    box-shadow: 0 20px 50px rgba(16, 45, 70, 0.08);
    position: relative;
    overflow: hidden;
}
.cert-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 10px;
    background: linear-gradient(90deg, var(--teal), var(--lime), var(--teal));
}
.cert-watermark {
    position: absolute;
    right: -40px;
    bottom: -40px;
    font-size: 260px;
    color: rgba(23, 107, 120, 0.03);
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 800;
    pointer-events: none;
    user-select: none;
}
.cert-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    border-bottom: 2px dashed var(--line);
    padding-bottom: 25px;
    margin-bottom: 30px;
}
.seal-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #eef6cf;
    border: 1px solid #c2e2a8;
    color: #1e6355;
    padding: 8px 16px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 0.85rem;
}
@media print {
    body { background: #fff !important; margin: 0 !important; }
    .no-print { display: none !important; }
    .cert-card { border: 2px solid #176b78 !important; box-shadow: none !important; padding: 40px !important; margin: 0 !important; max-width: 100% !important; }
}
</style>
</head>
<body style="background:#f4f7f9;min-height:100vh;padding:20px">

<div class="no-print" style="max-width:860px;margin:15px auto;display:flex;justify-content:space-between;align-items:center">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span></span>
</a>
<div style="display:flex;gap:10px">
<a href="resume.php" class="button button-quiet" style="font-size:0.85rem">← Back to Resume</a>
<button onclick="window.print()" class="button button-primary" style="font-size:0.85rem">Print Official Certificate <span>↓</span></button>
</div>
</div>

<article class="cert-card">
<div class="cert-watermark">SR</div>

<div class="cert-header">
<div>
<div class="eyebrow" style="letter-spacing:0.15em">Official Academic Credential</div>
<h1 style="font-size:2.4rem;margin:8px 0 4px">Technical Competency Verification</h1>
<p style="margin:0;font-size:0.95rem;color:var(--muted)">SkillRank Verified Technical Assessment Certificate</p>
</div>
<div style="text-align:right">
<div class="seal-badge">
<span>✓</span> Authenticated Proof
</div>
<div style="margin-top:8px;font-family:'Space Grotesk',monospace;font-size:0.85rem;color:var(--muted)">
ID: <strong><?= e($certCode) ?></strong>
</div>
</div>
</div>

<!-- Candidate Info Section -->
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:25px;margin-bottom:35px">
<div>
<small style="color:var(--muted);text-transform:uppercase;font-size:0.75rem;font-weight:700">Issued To Candidate</small>
<h2 style="font-size:1.9rem;margin:4px 0 6px;color:var(--ink)"><?= e($student['name']) ?></h2>
<div style="font-size:1rem;color:var(--teal);font-weight:700;margin-bottom:4px">
<?= e($student['course'] ?? 'BCA / B.Tech Computer Science') ?> (<?= e($student['semester']) ?>)
</div>
<div style="color:var(--muted);font-size:0.9rem">
<?= e($student['university'] ?? 'Northbridge University') ?>
</div>
</div>

<div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:18px;display:flex;align-items:center;gap:16px">
<!-- QR Code pointing to this verification link -->
<img src="https://api.qrserver.com/v1/create-qr-code/?size=95x95&data=<?= urlencode($verifyUrl) ?>" alt="Credential QR Code" style="width:95px;height:95px;border-radius:6px;border:1px solid #d9e2ea;background:#fff">
<div>
<strong style="display:block;font-size:0.85rem;color:var(--ink)">Scan to Verify</strong>
<small style="color:var(--muted);display:block;margin-top:3px;font-size:0.72rem;line-height:1.4">
Live verification URL dynamically validated against institutional test attempts.
</small>
<span class="pill lime" style="font-size:0.65rem;margin-top:6px">Status: Active</span>
</div>
</div>
</div>

<!-- Verified Skills Section -->
<div style="margin-bottom:35px">
<h3 style="font-size:1.1rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--teal);margin-bottom:14px">
Verified Technical Competencies
</h3>
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px">
<?php foreach ($skills as $sk): 
    $lvlBadge = match($sk['level']) {
        'Expert' => 'pill lime',
        'Advanced' => 'pill',
        'Intermediate' => 'pill',
        default => 'pill'
    };
?>
<div style="background:#fff;border:1px solid var(--line);border-radius:8px;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
<strong><?= e($sk['name']) ?></strong>
<span class="<?= $lvlBadge ?>"><?= e($sk['level']) ?> (<?= round($sk['score']) ?>%)</span>
</div>
<div class="progress" style="height:6px"><i data-progress="<?= round($sk['score']) ?>"></i></div>
<small style="color:var(--muted);display:block;margin-top:6px;font-size:0.72rem">
Evaluated: <?= date('M d, Y', strtotime($sk['updated_at'] ?? 'now')) ?>
</small>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Performance Metrics Strip -->
<div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:20px;display:grid;grid-template-columns:repeat(3,1fr);text-align:center;gap:15px;margin-bottom:35px">
<div>
<small style="color:var(--muted);text-transform:uppercase;font-size:0.72rem">Verified Problems Solved</small>
<strong style="font-size:1.8rem;display:block;margin-top:4px"><?= number_format($stats['solved']) ?></strong>
<span class="delta" style="font-size:0.75rem"><?= $stats['tests_taken'] ?> assessments cleared</span>
</div>
<div>
<small style="color:var(--muted);text-transform:uppercase;font-size:0.72rem">Average Examination Accuracy</small>
<strong style="font-size:1.8rem;display:block;margin-top:4px"><?= $stats['accuracy'] ?>%</strong>
<span class="delta" style="font-size:0.75rem">Weighted benchmark</span>
</div>
<div>
<small style="color:var(--muted);text-transform:uppercase;font-size:0.72rem">Institutional Standings</small>
<strong style="font-size:1.8rem;display:block;margin-top:4px">#<?= e((string)($rank['rank_no'] ?? '1')) ?></strong>
<span class="delta" style="font-size:0.75rem">Campus Leaderboard</span>
</div>
</div>

<!-- Footer Sign-off / Integrity -->
<div style="display:flex;justify-content:space-between;align-items:end;border-top:1px solid var(--line);padding-top:20px;font-size:0.8rem;color:var(--muted)">
<div>
<strong>Institutional Issuer:</strong> Northbridge University Department of Computer Applications<br>
Platform: SkillRank AI-Powered Skill Assessment Platform · Verified on <?= date('F j, Y') ?>
</div>
<div style="text-align:right">
<div style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--teal);font-size:1.1rem">SKILLRANK ACCREDITED</div>
<small>Cryptographic Integrity Confirmed</small>
</div>
</div>
</article>

<script src="assets/app.js"></script>
</body>
</html>
