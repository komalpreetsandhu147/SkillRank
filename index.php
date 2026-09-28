<?php 
require_once 'auth.php';
$user = current_user();
if ($user) redirect($user['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SkillRank | AI-Powered Skill Assessment & Career Readiness Platform</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=11">
</head>
<body class="landing">
<header class="site-header">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span></span>
</a>
<nav>
<a href="about.php">How It Works</a>
<a href="login.php">Student Sign In</a>
<a class="button button-dark" href="register.php">Build Your Profile <span>↗</span></a>
</nav>
</header>
<?php if (!db()): ?>
<div style="background: rgba(239, 68, 68, 0.12); border-bottom: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 10px 20px; font-size: 0.85rem; text-align: center;">
    <strong>Railway Notice:</strong> MySQL database is not connected yet. In your Railway dashboard, click <b>+ Create &rarr; Database &rarr; Add MySQL</b> to connect persistent storage.
</div>
<?php endif; ?>

<section class="hero">
<div class="hero-copy">
<div class="eyebrow">
<span class="pulse"></span> College Tech Fest Innovation · BCA & B.Tech
</div>
<h1>Make your technical progress <em>count.</em></h1>
<p>
SkillRank helps university students assess their technical skills, identify weak areas with GenAI, practice targeted questions, track campus rankings, and generate a verified skill-based resume.
</p>

<div class="hero-actions">
<a class="button button-primary" href="register.php">Start Student Profile <span>↗</span></a>
<a class="button button-quiet" href="login.php">Student Sign In ↗</a>
<a class="text-link" href="about.php">Learn How It Works <span>→</span></a>
</div>

<div class="trusted">
<strong>Built for BCA & CS Students</strong>
<span>ASSESS → ANALYSE → IDENTIFY GAPS<br>PRACTICE → IMPROVE → RANK → SHOWCASE</span>
</div>
</div>

<div class="hero-visual">
<div class="profile-preview">
<div class="preview-heading preview-title">
<span class="preview-avatar">S</span>
<div>
<small>SkillRank Platform</small>
<strong>Continuous Learning Loop</strong>
</div>
<span class="status-badge">Live System</span>
</div>
<div class="preview-divider"></div>
<div class="preview-row">
<span><b class="preview-number">01</b> Technical Tests</span>
<strong>PHP, JS, DBMS, WEB</strong>
</div>
<div class="preview-row">
<span><b class="preview-number">02</b> AI Diagnostics</span>
<strong>Detect Skill Gaps</strong>
</div>
<div class="preview-row">
<span><b class="preview-number">03</b> Skill Levels</span>
<strong>Beginner to Expert</strong>
</div>
<div class="preview-row">
<span><b class="preview-number">04</b> Verified Resume</span>
<strong>Auto-Generated PDF</strong>
</div>
<div class="preview-footer">
Ready for College Tech Fest Presentation <span>→</span>
</div>
</div>
</div>
</section>

<section class="feature-strip">
<div>
<span>01</span>
<strong>Assess & Benchmark</strong>
<small>Timed multiple-choice sprints across programming and database subjects.</small>
</div>
<div>
<span>02</span>
<strong>GenAI Diagnostics</strong>
<small>Gemini-powered evaluation to pinpoint conceptual gaps and recommend study topics.</small>
</div>
<div>
<span>03</span>
<strong>Showcase & Rank</strong>
<small>Campus-wide leaderboard standings and print-ready skill-based resumes.</small>
</div>
</section>
<footer style="text-align:center;padding:25px 20px;color:var(--muted);font-size:0.8rem;border-top:1px solid var(--line)">
SkillRank © 2026 · Technical Assessment Platform · <a href="admin_login.php" style="color:var(--muted);text-decoration:underline">Faculty Access</a>
</footer>
</body>
</html>
