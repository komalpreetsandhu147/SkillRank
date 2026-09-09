<?php
require_once 'auth.php';
if (current_user()) redirect('admin_dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $found = one('SELECT * FROM users WHERE email=? AND role=\'admin\'', 's', [trim($_POST['email'] ?? '')]);
    if ($found && password_verify($_POST['password'] ?? '', $found['password_hash'])) { $_SESSION['user_id'] = $found['id']; redirect('admin_dashboard.php'); }
    $error = 'Admin credentials are not valid.';
}
$pageTitle = 'Admin sign in'; include 'includes/header.php';
?><div class="auth-page"><div class="auth-panel"><a class="brand" href="index.php"><span class="brand-mark">S</span><span>skill<span>rank</span></span></a><div class="auth-heading"><div class="eyebrow">Operations console</div><h1>Run the learning loop.</h1><p>Manage the question bank and see the student signal.</p></div><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post" class="form-stack"><label>Admin email<input type="email" name="email" value="admin@skillrank.demo"></label><label>Password<input type="password" name="password" required></label><button class="button button-primary full">Enter admin console ↗</button></form><p class="demo-note">Demo password: demo123</p></div><div class="auth-art"><div class="auth-quote">One view for every signal that helps students move forward.</div></div></div><?php include 'includes/footer.php'; ?>