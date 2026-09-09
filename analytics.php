<?php require_once __DIR__ . '/auth.php';
require_login('student');
$uid=current_user()['id'];
$subjectStats=many('SELECT s.name,ROUND(AVG(qa.accuracy)) accuracy, SUM(qa.questions_count) solved FROM quiz_attempts qa JOIN subjects s ON s.id=qa.subject_id WHERE qa.user_id=? GROUP BY s.id ORDER BY accuracy DESC','i',[$uid]);
$history=many('SELECT DATE_FORMAT(created_at,\'%b %d\') day,accuracy FROM quiz_attempts WHERE user_id=? ORDER BY created_at','i',[$uid]);
$weak=many('SELECT s.name,ROUND(AVG(qa.accuracy)) accuracy FROM quiz_attempts qa JOIN subjects s ON s.id=qa.subject_id WHERE qa.user_id=? GROUP BY s.id ORDER BY accuracy ASC LIMIT 2','i',[$uid]);
$pageTitle='Analytics';
include 'includes/header.php';

?>
<div class="page-head">
<div>
<div class="eyebrow">Your signal, decoded</div>
<h1>Performance analytics.</h1>
<p>See where consistency is compounding and where a focused hour will help most.</p>
</div>
</div>
<div class="dashboard-grid">
<section class="panel">
<div class="panel-head">
<div>
<h2>Accuracy over time</h2>
<small>Recent assessment attempts</small>
</div>
<?php if($history): ?><span class="pill lime">Live</span><?php else: ?><span class="pill">No data yet</span><?php endif; ?>
</div>
<?php if($history): ?>
<canvas class="chart" data-chart='<?=json_encode(array_column($history,'accuracy'))?>'></canvas>
<div style="display:flex;justify-content:space-between;color:var(--muted);font-size:.7rem">
<?php foreach(array_slice($history,-6) as $item): ?><span><?=e($item['day'])?></span><?php endforeach; ?>
</div>
<?php else: ?><div class="empty-state chart-empty"><strong>Your accuracy trend will appear here.</strong><span>Complete an assessment to start tracking performance over time.</span><a class="text-link" href="practice.php">Start an assessment →</a></div><?php endif; ?>
</section>
<section class="panel">
<div class="eyebrow">Smart recommendation</div>
<h2>Practice the edges.</h2>
<?php if($subjectStats): ?><p>Your strongest area is <?=e($subjectStats[0]['name'])?>. The biggest opportunity is <?=e($weak[0]['name'])?> at <?=round($weak[0]['accuracy'])?>%.</p><?php else: ?><p>Complete your first assessment to unlock personalised strengths, gaps, and recommendations.</p><?php endif; ?>
<a href="practice.php" class="button button-primary">Take recommended test ↗</a>
</section>
</div>
<section class="panel" style="margin-top:18px">
<div class="panel-head"><h2>Subject performance</h2><small>Based on assessed answers</small></div>
<?php foreach($subjectStats as $item):
?>
<div class="skill-row">
<div class="skill-label">
<strong>
<?=e($item['name'])
?>
</strong>
<span>
<?=round($item['accuracy'])
?>% · <?=$item['solved']
?> solved</span>
</div>
<div class="progress">
<i data-progress="<?=round($item['accuracy'])
?>">
</i>
</div>
</div>
<?php endforeach;

?>
<?php if(!$subjectStats): ?><div class="empty-state"><strong>No subject performance yet.</strong><span>Your subject scores will appear after your first assessment.</span></div><?php endif; ?>
</section>
<?php include 'includes/footer.php';

?>
