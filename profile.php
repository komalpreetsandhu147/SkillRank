<?php
require_once __DIR__ . '/auth.php';
require_login('student');
$user = current_user(); $message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $university = trim($_POST['university'] ?? ''); $course = trim($_POST['course'] ?? ''); $semester = trim($_POST['semester'] ?? '');
    if ($name && $university && $course && $semester) {
        query('UPDATE users SET name=? WHERE id=?', 'si', [$name, $user['id']]);
        query('UPDATE students SET university=?,course=?,semester=? WHERE user_id=?', 'sssi', [$university, $course, $semester, $user['id']]);
        $message = 'Profile updated successfully.';
        $user = one('SELECT u.*,s.university,s.course,s.semester,s.phone FROM users u JOIN students s ON s.user_id=u.id WHERE u.id=?', 'i', [$user['id']]);
    }
}
$pageTitle = 'Profile'; include 'includes/header.php';
?><div class="page-head"><div><div class="eyebrow">Your foundation</div><h1>Profile settings.</h1><p>Keep your academic identity ready for every opportunity.</p></div></div><?php if($message):?><div class="alert" style="background:#eef6cf;color:var(--teal)"><?=e($message)?></div><?php endif;?><section class="panel" style="max-width:760px"><form method="post" class="form-stack"><div class="form-grid"><label>Full name<input name="name" value="<?=e($user['name'])?>"></label><label>Email<input value="<?=e($user['email'])?>" disabled></label></div><div class="form-grid"><label>University<input name="university" value="<?=e($user['university'])?>"></label><label>Course<input name="course" value="<?=e($user['course'])?>"></label></div><label>Semester<input name="semester" value="<?=e($user['semester'])?>"></label><button class="button button-primary">Update profile ↗</button></form></section><?php include 'includes/footer.php';?>