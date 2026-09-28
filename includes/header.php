<?php // Shared authenticated page shell with navigation and common styles.
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../ai.php';
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
$active = basename($_SERVER['PHP_SELF']);
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
<script>
(function(){
  var t = localStorage.getItem('sr_theme');
  if(t === 'dark' || (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)){
    document.documentElement.setAttribute('data-theme', 'dark');
  }
})();
</script>
</head>
<body>
<?php if ($user): ?>
<aside class="sidebar">
<a class="brand" href="<?= $user['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php' ?>">
<span class="brand-mark">S</span>
<span>skill<span>rank</span></span>
</a>
<div class="profile-mini" style="display:flex;align-items:center;justify-content:space-between">
<div style="display:flex;align-items:center;gap:10px">
<div class="avatar">
<?= strtoupper(substr($user['name'], 0, 1)) ?>
</div>
<div>
<strong><?= e($user['name']) ?></strong>
<small><?= ucfirst($user['role']) ?> account</small>
</div>
</div>
<a href="logout.php" title="Sign out / Log out" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:var(--paper);border:1px solid var(--line);color:var(--muted);text-decoration:none;transition:0.2s all" onmouseover="this.style.color='#d88b62';this.style.borderColor='#d88b62'" onmouseout="this.style.color='var(--muted)';this.style.borderColor='var(--line)'">
↪
</a>
</div>
<nav>
<small class="nav-label">Workspace</small>
<?php 
$links = $user['role'] === 'admin' ? [
    ['admin_dashboard.php', 'Overview', '◈'],
    ['students.php', 'Students', '♙'],
    ['subjects.php', 'Subjects', '▦'],
    ['questions.php', 'Question Bank', '▤'],
    ['admin_analytics.php', 'Analytics', '◒'],
    ['leaderboard.php', 'Leaderboard', '♛'],
    ['admin_ai.php', 'AI Diagnostics', '✦']
] : [
    ['dashboard.php', 'Dashboard', '⌂'],
    ['practice.php', 'Practice Tests', '✦'],
    ['flashcards.php', 'Flashcards', '🂠'],
    ['playground.php', 'Code Sandbox', '⌨'],
    ['analytics.php', 'Analytics & AI', '◒'],
    ['leaderboard.php', 'Leaderboard', '♛'],
    ['resume.php', 'Skill Resume', '▧'],
    ['profile.php', 'My Profile', '👤']
]; 
foreach ($links as $link): 
    $isActive = ($active === $link[0]) || ($link[0] === 'questions.php' && $active === 'question_form.php');
?>
<a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= $link[0] ?>">
<span><?= $link[2] ?></span>
<?= $link[1] ?>
</a>
<?php endforeach; ?>
</nav>

<div style="margin-top:auto;padding-top:20px;display:flex;flex-direction:column;gap:12px">
<button type="button" id="themeToggleBtn" onclick="toggleSkillRankTheme()" class="theme-toggle-btn" style="display:flex;align-items:center;gap:8px;background:none;border:1px solid var(--line);padding:7px 12px;border-radius:8px;color:var(--ink);cursor:pointer;font-size:0.8rem;transition:all 0.2s">
<span id="themeToggleIcon">🌙</span> <span id="themeToggleText">Toggle Theme</span>
</button>
<div style="font-size:0.75rem"><?= ai_badge_html() ?></div>
<a class="logout" href="logout.php" style="display:flex;align-items:center;gap:8px;text-decoration:none;padding:8px 0">
<span>↪</span> <span>Sign out</span>
</a>
</div>
</aside>
<?php endif; ?>
<main class="<?= $user ? 'app-main' : '' ?>">
