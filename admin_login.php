<?php
require_once 'auth.php';

$user = current_user();
if ($user) {
    if ($user['role'] === 'admin') {
        redirect('admin_dashboard.php');
    } else {
        // Clear prior student session to guarantee clean single-role access
        $_SESSION = [];
        if (session_id()) session_destroy();
        session_start();
    }
}

$error = '';

if (isset($_GET['demo'])) {
    $found = one('SELECT * FROM users WHERE email=\'admin@skillrank.demo\' AND role=\'admin\'');
    if ($found) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $found['id'];
        redirect('admin_dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $found = one('SELECT * FROM users WHERE email=? AND role=\'admin\'', 's', [$email]);
    if ($found && password_verify($password, $found['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $found['id'];
        redirect('admin_dashboard.php');
    }
    $error = 'Invalid admin credentials. Please verify your email and password.';
}

$pageTitle = 'Faculty & Admin Console';
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
<label>Authorized Admin Email
<input type="email" name="email" placeholder="faculty@university.edu" value="<?= e($_POST['email'] ?? '') ?>" required>
</label>
<label>Admin Password
<input type="password" name="password" placeholder="Enter administrator password" required>
</label>
<button class="button button-primary full" type="submit">Enter Admin Console ↗</button>
</form>

<div style="margin-top:20px;padding:15px;background:#edf5f7;border-radius:10px;border:1px solid #d3e5ea">
<small style="display:block;font-weight:700;color:var(--teal);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em">⚡ Instant Faculty Demo:</small>
<a href="admin_login.php?demo=admin" class="button button-dark full" style="font-size:0.85rem;padding:9px 12px;text-align:center">Sign in as Faculty Admin (Full Control) ↗</a>
</div>
</div>
<div class="auth-art" style="background:var(--navy)">
<div class="auth-quote">“Empower students with transparent skill signals, actionable diagnostics, and automated credentials.”
<small>SkillRank Tech Fest Administrative Suite</small>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>