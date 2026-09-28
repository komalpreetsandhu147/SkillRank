<?php
// Printable Certificate of Technical Competence - SkillRank Tech Fest
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$currentUser = current_user();
$targetUid = get_scoped_student_id();

// If not logged in and requesting a certificate, allow public verification view
$student = one(
    'SELECT u.id, u.name, u.email, u.created_at as joined_date, 
            s.university, s.course, s.semester, s.career_goal 
     FROM users u 
     LEFT JOIN students s ON s.user_id=u.id 
     WHERE u.id=? AND u.role="student"',
    'i',
    [$targetUid]
);

if (!$student) {
    die("Student certificate record not found.");
}

$subjectId = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;
$subject = null;
if ($subjectId > 0) {
    $subject = one('SELECT * FROM subjects WHERE id=?', 'i', [$subjectId]);
}

// Fetch stats for this certificate
if ($subject) {
    $stats = one('SELECT COALESCE(ROUND(AVG(accuracy)),0) accuracy, COUNT(id) tests_taken, COALESCE(MAX(score),0) max_score FROM quiz_attempts WHERE user_id=? AND subject_id=?', 'ii', [$targetUid, $subjectId]);
    $skill = one('SELECT ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? AND sk.name=?', 'is', [$targetUid, $subject['name']]);
    $certTitle = 'Certificate of Technical Mastery';
    $certSpecialization = $subject['name'] . ' Engineering';
    $verifiedScore = round($skill['score'] ?? $stats['accuracy'] ?? 80);
    $verifiedLevel = $skill['level'] ?? 'Advanced';
} else {
    $stats = one('SELECT COALESCE(ROUND(AVG(accuracy)),0) accuracy, COUNT(id) tests_taken, COALESCE(SUM(score),0) total_points FROM quiz_attempts WHERE user_id=?', 'i', [$targetUid]);
    $certTitle = 'Certificate of Technical Excellence';
    $certSpecialization = 'Full-Stack Software Engineering & Computer Science';
    $verifiedScore = round($stats['accuracy'] ?? 85);
    $verifiedLevel = ($verifiedScore >= 90) ? 'Expert' : (($verifiedScore >= 75) ? 'Advanced' : 'Intermediate');
}

$certId = 'SR-CERT-' . date('Y', strtotime($student['joined_date'])) . '-' . sprintf('%04d', $student['id']) . ($subjectId ? '-' . $subjectId : '');
$verifyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/verify_credential.php?id=" . $student['id'];
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=' . urlencode($verifyUrl);

$pageTitle = 'Certificate · ' . $student['name'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | SkillRank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=12">
<style>
@media print {
  body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
  .no-print { display: none !important; }
  .cert-container { margin: 0 auto !important; box-shadow: none !important; border: 10px solid #102d46 !important; }
}
.cert-container {
  max-width: 900px;
  margin: 30px auto;
  background: #ffffff;
  border: 12px solid #102d46;
  outline: 3px solid #b9e7f2;
  outline-offset: -7px;
  padding: 45px 55px;
  position: relative;
  box-shadow: 0 16px 45px rgba(16,45,70,0.15);
  color: #102d46;
  font-family: 'DM Sans', sans-serif;
  text-align: center;
}
.cert-watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-25deg);
  font-size: 8rem;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  color: rgba(185, 231, 242, 0.12);
  pointer-events: none;
  z-index: 0;
  letter-spacing: 0.1em;
  white-space: nowrap;
}
</style>
</head>
<body style="background:var(--paper);padding:20px 10px">

<!-- Toolbar (Hidden on Print) -->
<div class="no-print" style="max-width:900px;margin:0 auto 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
<a href="resume.php" class="button button-quiet">← Back to Skill Resume</a>
<div style="display:flex;gap:10px">
<a href="verify_credential.php?id=<?= $student['id'] ?>" target="_blank" class="button button-quiet">Live Verification Page ↗</a>
<button type="button" class="button button-primary" onclick="window.print()">🖨 Print / Save PDF</button>
</div>
</div>

<!-- Formal Certificate Document -->
<div class="cert-container">
<div class="cert-watermark">SKILLRANK</div>

<!-- Header -->
<div style="position:relative;z-index:1">
<div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #d9e2ea;padding-bottom:15px;margin-bottom:25px">
<div style="text-align:left">
<strong style="font-family:'Space Grotesk';font-size:1.15rem;letter-spacing:-0.03em">SKILL<span>RANK</span></strong>
<small style="display:block;color:#647586;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.12em">National Tech Fest Platform 2026</small>
</div>

<div class="pill lime" style="font-size:0.75rem;padding:4px 12px;font-weight:700">
OFFICIAL VERIFIED CREDENTIAL
</div>

<div style="text-align:right">
<small style="display:block;color:#647586;font-size:0.7rem">Credential Serial ID</small>
<strong style="font-family:monospace;font-size:0.85rem;color:#176b78"><?= e($certId) ?></strong>
</div>
</div>

<div style="font-size:0.85rem;text-transform:uppercase;letter-spacing:0.2em;color:#176b78;font-weight:700;margin-bottom:6px">
Department of Computer Applications & Information Technology
</div>

<h1 style="font-family:'Playfair Display',serif;font-size:2.4rem;font-weight:700;letter-spacing:-0.02em;color:#102d46;margin:10px 0 15px">
<?= e($certTitle) ?>
</h1>

<p style="font-size:1rem;color:#647586;margin:0 0 20px">
This is to certify that the candidate named below has successfully undergone comprehensive algorithmic evaluation, practical benchmark testing, and demonstrated verified competence.
</p>

<!-- Student Name Block -->
<div style="border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;padding:16px 0;margin:20px 0">
<div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.14em;color:#647586">Presented with Honors to</div>
<h2 style="font-family:'Space Grotesk',sans-serif;font-size:2.2rem;color:#102d46;margin:6px 0">
<?= e($student['name']) ?>
</h2>
<p style="margin:0;font-size:0.95rem;color:#176b78;font-weight:600">
<?= e($student['university'] ?: 'Northbridge University') ?> · <?= e($student['course'] ?: 'BCA / B.Tech CS') ?>
</p>
</div>

<p style="font-size:1rem;line-height:1.6;max-width:700px;margin:0 auto 25px">
For demonstrating benchmark technical proficiency in <strong><?= e($certSpecialization) ?></strong>, achieving an evaluated accuracy of <strong><?= $verifiedScore ?>%</strong>, categorizing the candidate in the <strong style="color:#176b78"><?= e($verifiedLevel) ?> Tier</strong> of technical competency.
</p>

<!-- Verification & Signatures Row -->
<div style="display:grid;grid-template-columns:1fr 120px 1fr;gap:20px;align-items:center;margin-top:35px;padding-top:20px;border-top:1px solid #e2e8f0">
<!-- Left Signature -->
<div style="text-align:center">
<div style="font-family:'Playfair Display',serif;font-style:italic;font-size:1.3rem;color:#102d46;border-bottom:1px solid #a0aec0;padding-bottom:6px;max-width:180px;margin:0 auto 6px">
Dr. Rajesh Verma
</div>
<strong style="display:block;font-size:0.8rem">Head of Department, CS</strong>
<small style="color:#647586;font-size:0.7rem">Tech Fest Academic Jury</small>
</div>

<!-- Center QR Seal -->
<div style="text-align:center">
<img src="<?= $qrCodeUrl ?>" alt="Verification QR" style="width:85px;height:85px;border:2px solid #102d46;border-radius:6px;padding:3px;background:#fff">
<small style="display:block;font-size:0.65rem;color:#647586;margin-top:4px">Scan to Verify</small>
</div>

<!-- Right Signature -->
<div style="text-align:center">
<div style="font-family:'Playfair Display',serif;font-style:italic;font-size:1.3rem;color:#102d46;border-bottom:1px solid #a0aec0;padding-bottom:6px;max-width:180px;margin:0 auto 6px">
SkillRank Engine
</div>
<strong style="display:block;font-size:0.8rem">AI Assessment Evaluator</strong>
<small style="color:#647586;font-size:0.7rem">Issued: <?= date('F j, Y') ?></small>
</div>
</div>

</div>
</div>

</body>
</html>
