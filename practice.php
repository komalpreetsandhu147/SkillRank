<?php require_once __DIR__ . '/auth.php';
require_login('student');
$subjects=many('SELECT s.*,COUNT(q.id) question_count FROM subjects s LEFT JOIN questions q ON q.subject_id=s.id GROUP BY s.id ORDER BY s.id');
$weak=many('SELECT sk.name,ss.score,ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score ASC LIMIT 2','i',[current_user()['id']]);
$pageTitle='Practice';
include 'includes/header.php';

?>
<div class="page-head">
<div>
<div class="eyebrow">Practice studio</div>
<h1>Choose your next challenge.</h1>
<p>Recommendations are shaped by the topics where you have the most room to grow.</p>
</div>
</div>
<div class="dashboard-grid">
<section>
<div class="panel" style="margin-bottom:18px;background:var(--ink);color:#fff">
<div class="eyebrow" style="color:var(--lime)">Recommended for you</div>
<h2 style="color:#fff;margin-top:10px">Close your biggest gap.</h2>
<p style="color:#b8c8bd">Your <?=e($weak[0]['name']??'DBMS')
?> score is <?=round($weak[0]['score']??64)
?>%. A focused assessment can turn this into your next win.</p>
<a class="button button-primary" href="quiz.php?subject=<?=array_search($weak[0]['name']??'',array_column($subjects,'name'))!==false ? $subjects[array_search($weak[0]['name'],array_column($subjects,'name'))]['id'] : 3
?>">Start adaptive test ↗</a>
</div>
<div class="panel">
<div class="panel-head">
<h2>Subject library</h2>
<small>
<?=count($subjects)
?> tracks</small>
</div>
<?php foreach($subjects as $subject):
?>
<div class="achievement">
<span class="icon-tile lime">✦</span>
<div>
<strong>
<?=e($subject['name'])
?>
</strong>
<small>
<?=e($subject['code'])
?> · <?=e($subject['question_count'])
?> questions available</small>
</div>
<a class="button button-quiet" style="margin-left:auto" href="quiz.php?subject=<?=$subject['id']
?>">Practice →</a>
</div>
<?php endforeach;

?>
</div>
</section>
<aside class="focus-panel">
<div class="eyebrow">Your next focus</div>
<h2><?=e($weak[0]['name']??'DBMS')?> practice</h2>
<p>Start with the area that has the most room to improve.</p>
<div class="focus-score">
<div class="focus-score-label"><span>Current level</span><strong><?=round($weak[0]['score']??64)?>%</strong></div>
<div class="progress"><i style="width:<?=round($weak[0]['score']??64)?>%"></i></div>
</div>
<div class="focus-note"><span>Recommended</span> 10 focused questions</div>
<a class="button button-dark full" href="quiz.php?subject=<?=array_search($weak[0]['name']??'',array_column($subjects,'name'))!==false ? $subjects[array_search($weak[0]['name'],array_column($subjects,'name'))]['id'] : 3?>">Start practice <span>→</span></a>
</aside>
</div>
<?php include 'includes/footer.php';

?>
