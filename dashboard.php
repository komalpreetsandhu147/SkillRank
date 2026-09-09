<?php require_once __DIR__ . '/auth.php';
require_login('student');
$user=current_user();
$uid=$user['id'];
$stats=one('SELECT COALESCE(SUM(questions_count),0) solved, COALESCE(SUM(score),0) points, COALESCE(AVG(accuracy),0) accuracy FROM quiz_attempts WHERE user_id=?','i',[$uid]);
$monthly=one('SELECT COALESCE(SUM(questions_count),0) solved, COALESCE(SUM(score),0) points, COALESCE(AVG(accuracy),0) accuracy FROM quiz_attempts WHERE user_id=? AND created_at >= DATE_FORMAT(CURRENT_DATE,\'%Y-%m-01\')','i',[$uid]);
$rank=one('SELECT COUNT(*)+1 rank_no FROM (SELECT u.id,COALESCE(SUM(qa.score),0) points FROM users u LEFT JOIN quiz_attempts qa ON qa.user_id=u.id WHERE u.role=\'student\' GROUP BY u.id HAVING points>(SELECT COALESCE(SUM(score),0) FROM quiz_attempts WHERE user_id=?)) ranks','i',[$uid]);
$skills=many('SELECT sk.name,ss.score,ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score DESC','i',[$uid]);
$achievements=many('SELECT * FROM achievements WHERE user_id=? ORDER BY earned_at DESC LIMIT 3','i',[$uid]);
$records=many('SELECT s.name,ar.marks,ar.attendance,ar.syllabus_progress FROM academic_records ar JOIN subjects s ON s.id=ar.subject_id WHERE ar.user_id=?','i',[$uid]);
$pageTitle='Dashboard';
$hour=(int) date('G');
$greeting=$hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
include 'includes/header.php';

?>
<div class="page-head">
<div>
<div class="eyebrow"><?=date('l, F j, Y')?></div>
<h1><?=e($greeting)?>, <?=e(explode(' ',$user['name'])[0])
?>.</h1>
<p>Review your progress, strengthen your skills, and keep moving forward.</p>
</div>
<a class="button button-primary" href="practice.php">Practice now <span>↗</span>
</a>
</div>
<div class="stat-grid">
<div class="stat-card">
<small>Questions solved</small>
<strong>
<?=number_format($stats['solved'])
?>
</strong>
<?php if((int)$monthly['solved']>0): ?><span class="delta"><?=number_format($monthly['solved'])?> this month</span><?php else: ?><span class="delta muted-delta">No activity this month</span><?php endif; ?>
</div>
<div class="stat-card">
<small>Total score</small>
<strong>
<?=number_format($stats['points'])
?>
</strong>
<?php if((int)$monthly['solved']>0): ?><span class="delta"><?=number_format($monthly['points'])?> points this month</span><?php else: ?><span class="delta muted-delta">No activity this month</span><?php endif; ?>
</div>
<div class="stat-card">
<small>Average accuracy</small>
<strong>
<?=round($stats['accuracy'])
?>%</strong>
<?php if((int)$monthly['solved']>0): ?><span class="delta"><?=round($monthly['accuracy'])?>% this month</span><?php else: ?><span class="delta muted-delta">No activity this month</span><?php endif; ?>
</div>
<div class="stat-card">
<small>Current rank</small>
<strong><?=((int)$stats['solved']>0 ? '#'.e((string)($rank['rank_no']??'—')) : '—')
?>
</strong>
<?php if((int)$stats['solved']>0): ?><span class="delta">Top 10% of students</span><?php else: ?><span class="delta muted-delta">Complete a quiz to rank</span><?php endif; ?>
</div>
</div>
<div class="dashboard-grid">
<section class="panel">
<div class="panel-head">
<div>
<h2>Skill snapshot</h2>
<small>Updated after every assessment</small>
</div>
<a class="text-link" href="analytics.php">View all →</a>
</div>
<?php foreach($skills as $skill):
?>
<div class="skill-row">
<div class="skill-label">
<strong>
<?=e($skill['name'])
?>
</strong>
<span>
<?=e($skill['level'])
?> · <?=round($skill['score'])
?>%</span>
</div>
<div class="progress">
<i data-progress="<?=round($skill['score'])
?>">
</i>
</div>
</div>
<?php endforeach;

?>
<?php if(!$skills): ?><div class="empty-state"><strong>Your skill snapshot will appear here.</strong><span>Complete a practice assessment to start tracking your technical skills.</span><a class="text-link" href="practice.php">Start practicing →</a></div><?php endif; ?>
</section>
<section class="panel">
<div class="panel-head">
<div>
<h2>Achievements</h2>
<small>Proof points earned</small>
</div>
</div>
<?php foreach($achievements as $item):
?>
<div class="achievement">
<span>
<?=e($item['icon'])
?>
</span>
<div>
<strong>
<?=e($item['title'])
?>
</strong>
<small>
<?=e($item['description'])
?>
</small>
</div>
</div>
<?php endforeach;

?>
<?php if(!$achievements): ?><div class="empty-state"><strong>Your first achievement is within reach.</strong><span>Complete practice sessions to earn milestones and build your profile.</span></div><?php endif; ?>
</section>
</div>
<section class="panel" style="margin-top:18px">
<div class="panel-head">
<div>
<h2>Academic progress</h2>
<small>Keep your coursework in view</small>
</div>
<a class="text-link" href="analytics.php">Details →</a>
</div>
<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Subject</th>
<th>Marks</th>
<th>Attendance</th>
<th>Syllabus</th>
</tr>
</thead>
<tbody>
<?php foreach($records as $record):
?>
<tr>
<td>
<strong>
<?=e($record['name'])
?>
</strong>
</td>
<td>
<?=round($record['marks'])
?>%</td>
<td>
<?=round($record['attendance'])
?>%</td>
<td>
<div class="progress" style="width:140px">
<i data-progress="<?=round($record['syllabus_progress'])
?>">
</i>
</div>
</td>
</tr>
<?php endforeach;

?>
</tbody>
</table>
<?php if(!$records): ?><div class="empty-state table-empty"><strong>No academic records yet.</strong><span>Your coursework, marks, and attendance will appear here when added.</span></div><?php endif; ?>
</div>
</section>
<?php include 'includes/footer.php';

?>
