<?php require_once 'auth.php';
if (current_user()) redirect('dashboard.php');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') { $email=filter_var(trim($_POST['email']??''),FILTER_VALIDATE_EMAIL);
    $password=$_POST['password']??'';
    $found=$email?one('SELECT * FROM users WHERE email=? AND role=\'student\'','s',[$email]):null;
    if ($found && password_verify($password,$found['password_hash'])) { $_SESSION['user_id']=$found['id'];
        redirect('dashboard.php');

    }    $error='We could not match those sign-in details.';

}$pageTitle='Student sign in';
include 'includes/header.php';

?>
<div class="auth-page">
<div class="auth-panel">
<a class="brand" href="index.php">
<span class="brand-mark">S</span>
<span>skill<span>rank</span>
</span>
</a>
<div class="auth-heading">
<div class="eyebrow">Welcome back</div>
<h1>Welcome back to your progress.</h1>
<p>Sign in to continue your learning loop.</p>
</div>
<?php if($error):
?>
<div class="alert error">
<?=e($error)
?>
</div>
<?php endif;

?>
<form method="post" class="form-stack">
<label>Email address<input type="email" name="email" required placeholder="you@university.edu">
</label>
<label>Password<input type="password" name="password" required placeholder="••••••••">
</label>
<button class="button button-primary full" type="submit">Sign in <span>↗</span>
</button>
</form>
<p class="form-note">New to SkillRank? <a href="register.php">Create your profile</a>
</p>
</div>
<div class="auth-art">
<div class="auth-quote">“The clearest next step is the one your data can show you.”<small>SkillRank growth principle</small>
</div>
</div>
</div>
<?php include 'includes/footer.php';

?>
