<?php
require_once __DIR__ . '/auth.php';
require_login('student');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('practice.php');
$uid = current_user()['id']; $subjectId = (int)($_POST['subject_id'] ?? 0); $answers = $_POST['answers'] ?? [];
$questions = [];
foreach ($answers as $qid => $answer) { $question = one('SELECT * FROM questions WHERE id=?', 'i', [(int)$qid]); if ($question) $questions[] = ['q' => $question, 'answer' => $answer]; }
if (!$questions) redirect('practice.php');
$score = 0; $total = 0; $correct = 0;
foreach ($questions as $item) { $question = $item['q']; $total += (int)$question['marks']; if ($item['answer'] === $question['correct_answer']) { $score += (int)$question['marks']; $correct++; } }
$accuracy = round(($correct / count($questions)) * 100, 2);
query('INSERT INTO quiz_attempts (user_id,subject_id,score,total_marks,questions_count,accuracy) VALUES (?,?,?,?,?,?)', 'iiiiid', [$uid,$subjectId,$score,$total,count($questions),$accuracy]);
$attemptId = db()->insert_id;
foreach ($questions as $item) { $question = $item['q']; $ok = $item['answer'] === $question['correct_answer']; query('INSERT INTO answers (attempt_id,question_id,selected_answer,is_correct) VALUES (?,?,?,?)', 'iisi', [$attemptId,$question['id'],$item['answer'],$ok]); }
$subject = one('SELECT name FROM subjects WHERE id=?', 'i', [$subjectId]);
$skill = one('SELECT id FROM skills WHERE name=?', 's', [$subject['name']]);
if ($skill) {
    $existing = one('SELECT score FROM student_skills WHERE user_id=? AND skill_id=?', 'ii', [$uid,$skill['id']]);
    $newScore = $existing ? round(($existing['score'] * 0.7) + ($accuracy * 0.3), 2) : $accuracy;
    $level = $newScore >= 90 ? 'Expert' : ($newScore >= 75 ? 'Advanced' : ($newScore >= 55 ? 'Intermediate' : 'Beginner'));
    query('INSERT INTO student_skills (user_id,skill_id,score,level) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE score=VALUES(score),level=VALUES(level)', 'iids', [$uid,$skill['id'],$newScore,$level]);
}
$pageTitle = 'Assessment result'; include 'includes/header.php';
?><div class="narrow-content" style="margin:auto"><div class="eyebrow">Assessment complete</div><h1><?=e($subject['name'])?> result.</h1><div class="stat-grid"><div class="stat-card"><small>Score</small><strong><?=$score?>/<?=$total?></strong></div><div class="stat-card"><small>Accuracy</small><strong><?=$accuracy?>%</strong></div><div class="stat-card"><small>Correct</small><strong><?=$correct?>/<?=count($questions)?></strong></div><div class="stat-card"><small>Skill update</small><strong style="font-size:1.25rem">Saved</strong></div></div><section class="panel"><h2>Answer review</h2><?php foreach($questions as $item):$question=$item['q'];$ok=$item['answer']===$question['correct_answer'];?><div class="achievement"><span style="background:<?=$ok?'#eef6cf':'#ffe8e1'?>;color:<?=$ok?'#1e6355':'#a64123'?>"><?=$ok?'✓':'×'?></span><div><strong><?=e($question['question'])?></strong><small><?= $ok?'Correct':'Correct answer: '.$question['correct_answer'] ?> · Your answer: <?=e($item['answer']??'Skipped')?></small></div></div><?php endforeach; ?></section><div class="hero-actions"><a class="button button-primary" href="practice.php">Practice another ↗</a><a class="text-link" href="analytics.php">See my analytics →</a></div></div><?php include 'includes/footer.php';?>