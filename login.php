<?php 
require_once 'auth.php';
if (current_user()) redirect('dashboard.php');
$error = '';
$notice = '';

// Handle quick demo button
if (isset($_GET['demo'])) {
    $demoEmail = $_GET['demo'] === 'meera' ? 'meera@skillrank.demo' : 'aarav@skillrank.demo';
    $found = one('SELECT * FROM users WHERE email=? AND role=\'student\'', 's', [$demoEmail]);
    if ($found) {
        $_SESSION['user_id'] = $found['id'];
        redirect('dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $found = $email ? one('SELECT * FROM users WHERE email=? AND role=\'student\'', 's', [$email]) : null;
    
    if ($found && password_verify($password, $found['password_hash'])) {
        $_SESSION['user_id'] = $found['id'];
        redirect('dashboard.php');
    }
    $error = 'We could not match those sign-in credentials. Please try again.';
}

$pageTitle = 'Student Sign In';
include 'includes/header.php';
?>
<div class="auth-page">
<div class="auth-panel">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span></span>
</a>
<div class="auth-heading">
<div class="eyebrow">Student Portal</div>
<h1>Welcome back to your progress.</h1>
<p>Sign in to assess your skills, practice questions, and grow your career score.</p>
</div>

<?php if($error): ?>
<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="form-stack">
<label>Email address
<input type="email" name="email" required placeholder="aarav@skillrank.demo" value="<?= e($_POST['email'] ?? 'aarav@skillrank.demo') ?>">
</label>
<label>Password
<input type="password" name="password" required placeholder="demo123" value="demo123">
</label>
<button class="button button-primary full" type="submit">Sign in <span>↗</span></button>
</form>

<div style="margin-top:20px;padding:15px;background:#edf5f7;border-radius:10px;border:1px solid #d3e5ea">
<small style="display:block;font-weight:700;color:var(--teal);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em">⚡ Instant Demo Logins (Tech Fest):</small>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="login.php?demo=aarav" class="button button-quiet" style="font-size:0.8rem;padding:7px 12px;flex:1;text-align:center">Student Aarav (Adv)</a>
<a href="login.php?demo=meera" class="button button-quiet" style="font-size:0.8rem;padding:7px 12px;flex:1;text-align:center">Student Meera (Active)</a>
</div>
</div>

<p class="form-note" style="margin-top:20px">
New student? <a href="register.php">Create your profile</a> · <a href="admin_login.php">Admin portal →</a>
</p>
</div>
<div class="auth-art">
<div class="auth-quote">“The clearest next step is the one your verified data can show you.”
<small>SkillRank Technical Assessment Platform</small>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>
