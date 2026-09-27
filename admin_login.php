<?php
require_once 'auth.php';
if (current_user()) redirect('admin_dashboard.php');
$error = '';

if (isset($_GET['demo'])) {
    $found = one('SELECT * FROM users WHERE email=\'admin@skillrank.demo\' AND role=\'admin\'');
    if ($found) {
        $_SESSION['user_id'] = $found['id'];
        redirect('admin_dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $found = one('SELECT * FROM users WHERE email=? AND role=\'admin\'', 's', [$email]);
    if ($found && password_verify($password, $found['password_hash'])) {
        $_SESSION['user_id'] = $found['id'];
        redirect('admin_dashboard.php');
    }
    $error = 'Invalid admin credentials. Please verify your email and password.';
}

$pageTitle = 'Admin Sign In';
include 'includes/header.php';
?>
<div class="auth-page">
<div class="auth-panel">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span></span>
</a>
<div class="auth-heading">
<div class="eyebrow">Faculty & Operations Console</div>
<h1>Run the learning loop.</h1>
<p>Manage curriculum, curate questions with AI, and review student readiness.</p>
</div>

<?php if($error): ?>
<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="form-stack">
<label>Admin email
<input type="email" name="email" value="<?= e($_POST['email'] ?? 'admin@skillrank.demo') ?>" required>
</label>
<label>Admin password
<input type="password" name="password" value="demo123" required>
</label>
<button class="button button-primary full" type="submit">Enter admin console ↗</button>
</form>

<div style="margin-top:20px;padding:15px;background:#edf5f7;border-radius:10px;border:1px solid #d3e5ea">
<small style="display:block;font-weight:700;color:var(--teal);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em">⚡ Instant Demo Sign-In (Tech Fest):</small>
<a href="admin_login.php?demo=admin" class="button button-dark full" style="font-size:0.85rem;padding:9px 12px">Login as Admin (Full Control) ↗</a>
</div>

<p class="form-note" style="margin-top:20px">
Student? <a href="login.php">Switch to student sign in →</a>
</p>
</div>
<div class="auth-art" style="background:var(--navy)">
<div class="auth-quote">“Empower students with transparent skill signals, actionable diagnostics, and automated credentials.”
<small>SkillRank Tech Fest Administrative Suite</small>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>