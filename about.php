<?php 
require_once __DIR__ . '/auth.php';
$user = current_user();
$pageTitle = 'How SkillRank Works'; 
include 'includes/header.php'; 
?>
<div class="public-page" style="max-width:1100px;margin:0 auto;padding:40px 20px">
<header class="site-header" style="padding:0 0 40px;border-bottom:1px solid var(--line);margin-bottom:50px">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span></span>
</a>
<nav>
<a href="index.php">Home</a>
<a href="about.php" style="color:var(--teal);font-weight:700">How It Works</a>
<a href="login.php">Sign In</a>
<a href="admin_login.php">Admin</a>
<a class="button button-dark" href="register.php">Get Started <span>↗</span></a>
</nav>
</header>

<div class="narrow-content" style="padding:0;max-width:850px">
<div class="eyebrow">The Continuous Growth Loop</div>
<h1 style="font-size:3.2rem;line-height:1.1;margin:15px 0 20px">
Every assessment tells you exactly where to grow next.
</h1>
<p class="lead" style="font-size:1.15rem;color:var(--muted);line-height:1.65;margin-bottom:45px">
SkillRank replaces subjective resumes with verified technical competency proof. Built specifically for computer science and BCA university students, our continuous loop turns small practice sprints into accredited career readiness.
</p>

<!-- 7-Step Core Architecture Strip -->
<div style="background:#f0f9fa;border:1px solid #cce8ed;border-radius:12px;padding:24px;margin-bottom:50px">
<div class="eyebrow" style="color:var(--teal)">Continuous Learning Architecture</div>
<h3 style="margin:8px 0 16px;font-size:1.25rem">The 7-Step SkillRank Loop</h3>
<div style="display:flex;flex-wrap:wrap;gap:8px;font-size:0.82rem;font-weight:700">
<span class="pill lime">1. ASSESS</span> <span style="color:var(--muted)">→</span>
<span class="pill lime">2. ANALYSE</span> <span style="color:var(--muted)">→</span>
<span class="pill" style="background:#ffe8e1;color:#a64123">3. IDENTIFY GAPS</span> <span style="color:var(--muted)">→</span>
<span class="pill lime">4. PRACTICE</span> <span style="color:var(--muted)">→</span>
<span class="pill lime">5. IMPROVE</span> <span style="color:var(--muted)">→</span>
<span class="pill lime">6. RANK</span> <span style="color:var(--muted)">→</span>
<span class="pill lime">7. SHOWCASE</span>
</div>
</div>

<div class="steps" style="margin:40px 0;display:grid;grid-template-columns:repeat(3,1fr);gap:30px">
<article style="border-top:3px solid var(--teal);padding-top:20px">
<span style="font-weight:800;color:var(--teal);font-size:1.2rem">01</span>
<h3 style="margin:10px 0 8px">Take Technical Sprints</h3>
<p style="color:var(--muted);font-size:0.92rem;line-height:1.6">
Choose subjects like PHP, JavaScript, DBMS, or HTML/CSS. Take timed 5-to-10 question tests with multiple-choice questions curated by faculty and GenAI.
</p>
</article>

<article style="border-top:3px solid var(--teal);padding-top:20px">
<span style="font-weight:800;color:var(--teal);font-size:1.2rem">02</span>
<h3 style="margin:10px 0 8px">AI Diagnostics & Skill Gaps</h3>
<p style="color:var(--muted);font-size:0.92rem;line-height:1.6">
Our AI diagnostic engine calculates your accuracy, identifies exact weak concepts, and assigns progressive mastery tiers: Beginner, Intermediate, Advanced, and Expert.
</p>
</article>

<article style="border-top:3px solid var(--teal);padding-top:20px">
<span style="font-weight:800;color:var(--teal);font-size:1.2rem">03</span>
<h3 style="margin:10px 0 8px">Auto-Generated Resume</h3>
<p style="color:var(--muted);font-size:0.92rem;line-height:1.6">
Your verified assessment metrics sync directly into a printable skill-based resume complete with AI professional summary, campus leaderboard ranking, and PDF export.
</p>
</article>
</div>

<div style="background:var(--ink);color:#fff;border-radius:14px;padding:35px;margin-top:50px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px">
<div>
<h2 style="color:#fff;margin:0 0 6px">Ready to benchmark your skills?</h2>
<p style="color:#b8c8bd;margin:0">Create your student profile in 30 seconds and start your first assessment.</p>
</div>
<div style="display:flex;gap:12px">
<a class="button button-primary" href="register.php">Create Profile ↗</a>
<a class="button button-quiet" href="login.php">Student Sign In</a>
</div>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>