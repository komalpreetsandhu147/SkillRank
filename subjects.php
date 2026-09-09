<?php require_once __DIR__ . '/auth.php';
require_login('admin');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
	$name=trim($_POST['name']??'');
	$code=strtoupper(trim($_POST['code']??''));
	if(!$name||!$code) $error='Add a subject name and short code.';
	elseif(one('SELECT id FROM subjects WHERE name=? OR code=?','ss',[$name,$code])) $error='A subject with that name or code already exists.';
	else { query('INSERT INTO subjects (name,code) VALUES (?,?)','ss',[$name,$code]); redirect('subjects.php'); }
}
$subjects=many('SELECT s.*,COUNT(q.id) question_count FROM subjects s LEFT JOIN questions q ON q.subject_id=s.id GROUP BY s.id ORDER BY s.name');
$pageTitle='Subjects';
include 'includes/header.php';
?>
<div class="page-head">
<div><div class="eyebrow">Curriculum management</div><h1>Subjects.</h1><p>Add and organise the subjects used in assessments.</p></div>
<a class="button button-primary" href="#add-subject">Add subject <span>↗</span></a>
</div>
<?php if($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
<div class="dashboard-grid">
<section class="panel">
<div class="panel-head"><h2>Subject library</h2><small><?=count($subjects)?> subjects</small></div>
<div class="subject-admin-list">
<?php foreach($subjects as $subject): ?>
<div class="achievement"><span class="icon-tile lime">▦</span><div><strong><?=e($subject['name'])?></strong><small><?=e($subject['code'])?> · <?=$subject['question_count']?> questions</small></div></div>
<?php endforeach; ?>
</div>
</section>
<section class="panel" id="add-subject">
<div class="panel-head"><h2>Add subject</h2></div>
<form method="post" class="form-stack">
<label>Subject name<input name="name" required placeholder="Data Structures"></label>
<label>Short code<input name="code" required maxlength="20" placeholder="DSA"></label>
<button class="button button-primary full" type="submit">Add subject <span>↗</span></button>
</form>
</section>
</div>
<?php include 'includes/footer.php'; ?>

