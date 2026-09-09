<?php require_once 'auth.php';
$user = current_user();
if ($user) redirect($user['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php');

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SkillRank | Grow with proof</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=5">
</head>
<body class="landing">
<header class="site-header">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span>
</span>
</a>
<nav>
<a href="about.php">For students</a>
<a href="login.php">Sign in</a>
<a class="button button-dark" href="register.php">Build your profile <span>↗</span>
</a>
</nav>
</header>
<section class="hero">
<div class="hero-copy">
<div class="eyebrow">
<span class="pulse">
</span> A clearer path from study to career</div>
<h1>Make your progress <em>count.</em>
</h1>
<p>SkillRank helps university students measure technical skills, improve with focused practice, and present their progress professionally.</p>
<div class="hero-actions">
<a class="button button-primary" href="register.php">Start your student profile <span>↗</span>
</a>
<a class="text-link" href="about.php">Learn about SkillRank <span>→</span>
</a>
</div>
<div class="trusted">
<strong>Built for university students</strong>
<span>Study smarter. Show<br>what you know.</span>
</div>
</div>
<div class="hero-visual">
<div class="profile-preview">
<div class="preview-heading preview-title">
<span class="preview-avatar">S</span>
<div>
<small>SkillRank platform</small>
<strong>Your progress, organised</strong>
</div>
</div>
<div class="preview-divider"></div>
<div class="preview-row"><span><b class="preview-number">01</b> Practice</span><strong>Build skills</strong></div>
<div class="preview-row"><span><b class="preview-number">02</b> Measure</span><strong>Track growth</strong></div>
<div class="preview-row"><span><b class="preview-number">03</b> Present</span><strong>Show progress</strong></div>
<div class="preview-footer">A professional space for student growth <span>→</span></div>
</div>
</div>
</section>
<section class="feature-strip">
<div>
<span>01</span>
<strong>Practice</strong>
<small>Build confidence with structured practice.</small>
</div>
<div>
<span>02</span>
<strong>Track</strong>
<small>Understand your strengths and gaps.</small>
</div>
<div>
<span>03</span>
<strong>Share</strong>
<small>Present verified progress to opportunities.</small>
</div>
</section>
</body>
</html>
