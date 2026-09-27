<?php
require_once __DIR__ . '/auth.php';
require_login('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('practice.php');
}

$uid = current_user()['id'];
$subjectId = (int)($_POST['subject_id'] ?? 0);
$answers = $_POST['answers'] ?? [];
$presentedQids = $_POST['presented_questions'] ?? array_keys($answers);
$startTime = (int)($_POST['start_time'] ?? time() - 120);
$timeTaken = max(5, time() - $startTime);

if (empty($presentedQids)) {
    redirect('practice.php');
}

$subject = one('SELECT * FROM subjects WHERE id=?', 'i', [$subjectId]);
if (!$subject) redirect('practice.php');

$reviewQuestions = [];
$score = 0;
$totalMarks = 0;
$correctCount = 0;

foreach ($presentedQids as $qid) {
    $qid = (int)$qid;
    $question = one('SELECT q.*, t.name as topic_name FROM questions q LEFT JOIN topics t ON t.id=q.topic_id WHERE q.id=?', 'i', [$qid]);
    if (!$question) continue;

    $selected = $answers[$qid] ?? null;
    $isCorrect = ($selected !== null && strtoupper(trim($selected)) === strtoupper(trim($question['correct_answer'])));
    
    $marks = (int)$question['marks'];
    $totalMarks += $marks;

    if ($isCorrect) {
        $score += $marks;
        $correctCount++;
    }

    $reviewQuestions[] = [
        'question' => $question,
        'selected' => $selected,
        'is_correct' => $isCorrect
    ];
}

$questionsCount = count($reviewQuestions);
$accuracy = ($questionsCount > 0) ? round(($correctCount / $questionsCount) * 100, 2) : 0;
$passed = ($accuracy >= 60);

// Insert into quiz_attempts
query(
    'INSERT INTO quiz_attempts (user_id, subject_id, score, total_marks, questions_count, accuracy, time_taken_seconds) VALUES (?, ?, ?, ?, ?, ?, ?)',
    'iiiiidi',
    [$uid, $subjectId, $score, $totalMarks, $questionsCount, $accuracy, $timeTaken]
);
$attemptId = db()->insert_id;

// Insert answer records
foreach ($reviewQuestions as $item) {
    $q = $item['question'];
    $sel = $item['selected'] ? substr($item['selected'], 0, 1) : null;
    $ok = $item['is_correct'] ? 1 : 0;
    query(
        'INSERT INTO answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)',
        'iisi',
        [$attemptId, $q['id'], $sel, $ok]
    );
}

// Update skill rating & level
$skill = one('SELECT id FROM skills WHERE name=?', 's', [$subject['name']]);
$newScore = $accuracy;
$previousScore = null;
$previousLevel = 'Beginner';
$level = 'Beginner';

if ($skill) {
    $existing = one('SELECT score, level FROM student_skills WHERE user_id=? AND skill_id=?', 'ii', [$uid, $skill['id']]);
    if ($existing) {
        $previousScore = round((float)$existing['score']);
        $previousLevel = $existing['level'];
        // Weighted average: 70% prior mastery, 30% recent performance
        $newScore = round(($existing['score'] * 0.70) + ($accuracy * 0.30), 2);
    } else {
        $newScore = $accuracy;
    }

    if ($newScore >= 90) $level = 'Expert';
    elseif ($newScore >= 75) $level = 'Advanced';
    elseif ($newScore >= 55) $level = 'Intermediate';
    else $level = 'Beginner';

    query(
        'INSERT INTO student_skills (user_id, skill_id, score, level) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE score=VALUES(score), level=VALUES(level)',
        'iids',
        [$uid, $skill['id'], $newScore, $level]
    );
}

// Check & award milestone achievements
$totalAttempts = one('SELECT COUNT(*) total FROM quiz_attempts WHERE user_id=?', 'i', [$uid])['total'] ?? 1;
if ($totalAttempts == 1) {
    query('INSERT IGNORE INTO achievements (user_id, title, description, icon, earned_at) VALUES (?, "First Assessment Cleared", "Completed initial skill benchmark test", "🎯", CURRENT_DATE)', 'i', [$uid]);
}
if ($accuracy >= 80) {
    query('INSERT IGNORE INTO achievements (user_id, title, description, icon, earned_at) VALUES (?, "Precision Mastery", "Scored 80%+ accuracy on technical sprint", "⚡", CURRENT_DATE)', 'i', [$uid]);
}

$pageTitle = 'Assessment Result · ' . $subject['name'];
include 'includes/header.php';
?>
<div class="narrow-content" style="margin:0 auto;max-width:850px;padding:30px 20px">
<div class="page-head" style="margin-bottom:24px">
<div>
<div class="eyebrow"><?= e($subject['code']) ?> Assessment Completed</div>
<h1>Your <?= e($subject['name']) ?> Performance</h1>
<p>Test evaluated automatically. Your skill profile and leaderboard rankings have been updated.</p>
</div>
<span class="pill <?= $passed ? 'lime' : '' ?>" style="font-size:0.9rem;padding:7px 15px">
<?= $passed ? '✓ Passed (Proficient)' : '⚠ Needs Improvement' ?>
</span>
</div>

<!-- Score Cards Grid -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:25px">
<div class="stat-card">
<small>Total Score</small>
<strong><?= $score ?> <span style="font-size:1rem;color:var(--muted)">/ <?= $totalMarks ?></span></strong>
<span class="delta"><?= $correctCount ?> of <?= $questionsCount ?> correct</span>
</div>
<div class="stat-card">
<small>Accuracy Rate</small>
<strong><?= round($accuracy) ?>%</strong>
<span class="delta"><?= $passed ? 'Above benchmark' : 'Below benchmark' ?></span>
</div>
<div class="stat-card">
<small>Time Taken</small>
<strong><?= sprintf('%dm %02ds', floor($timeTaken/60), $timeTaken%60) ?></strong>
<span class="delta">Completed</span>
</div>
<div class="stat-card">
<small>Skill Status</small>
<strong style="font-size:1.4rem;color:var(--teal)"><?= e($level) ?></strong>
<span class="delta"><?= round($newScore) ?>% verified</span>
</div>
</div>

<!-- Skill Update Banner -->
<div class="panel" style="background:#eef6cf;border-color:#d5e5a6;margin-bottom:25px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px">
<div>
<strong style="color:var(--teal);font-size:1.05rem">Skill Progression Updated: <?= e($subject['name']) ?></strong>
<p style="margin:2px 0 0;color:#2a5043;font-size:0.85rem">
Current Level: <strong><?= e($level) ?> (<?= round($newScore) ?>%)</strong>
<?php if ($previousScore !== null): ?>
· Prior Rating: <?= $previousScore ?>% (<?= $newScore >= $previousScore ? '↑ Improved' : '↓ Adjusted' ?>)
<?php endif; ?>
</p>
</div>
<a href="resume.php" class="button button-dark" style="font-size:0.82rem;padding:9px 15px">View on Resume ↗</a>
</div>

<!-- Detailed Question-by-Question Review -->
<section class="panel" style="margin-bottom:25px">
<div class="panel-head">
<h2>Detailed Answer Review</h2>
<small>Learn from each explanation</small>
</div>

<div style="display:grid;gap:18px">
<?php foreach ($reviewQuestions as $idx => $item): 
    $q = $item['question'];
    $ok = $item['is_correct'];
    $userSel = $item['selected'];
    $correctKey = $q['correct_answer'];
    $correctText = $q['option_' . strtolower($correctKey)] ?? '';
?>
<div style="border:1px solid <?= $ok ? '#d5e5a6' : '#ffd0c4' ?>;border-radius:10px;padding:18px;background:<?= $ok ? '#fafdf5' : '#fff9f8' ?>">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
<span style="font-weight:700;font-size:0.85rem">Question <?= ($idx + 1) ?></span>
<span class="pill" style="background:<?= $ok ? '#eef6cf;color:#1e6355' : '#ffe8e1;color:#a64123' ?>">
<?= $ok ? '✓ Correct (+'.$q['marks'].' pts)' : '× Incorrect (0 pts)' ?>
</span>
</div>

<p style="font-size:1rem;color:var(--ink);font-weight:600;margin:0 0 12px"><?= e($q['question']) ?></p>

<div style="font-size:0.85rem;line-height:1.6;margin-bottom:10px">
<div>Your Answer: <strong><?= $userSel ? e($userSel . ': ' . ($q['option_' . strtolower($userSel)] ?? '')) : '<span style="color:#a64123">Skipped / No Selection</span>' ?></strong></div>
<div>Correct Answer: <strong style="color:var(--teal)"><?= e($correctKey . ': ' . $correctText) ?></strong></div>
</div>

<?php if (!empty($q['explanation'])): ?>
<div style="background:#ffffffcc;padding:10px 14px;border-radius:6px;border-left:3px solid <?= $ok ? 'var(--teal)' : 'var(--orange)' ?>;font-size:0.8rem;color:var(--muted)">
<strong>💡 Explanation:</strong> <?= e($q['explanation']) ?>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>

<!-- Next Action CTAs -->
<div style="display:flex;gap:12px;flex-wrap:wrap">
<a class="button button-primary" href="practice.php">Practice Another Subject ↗</a>
<a class="button button-dark" href="analytics.php">View In-Depth Analytics & AI Feedback →</a>
<a class="button button-quiet" href="certificate.php?subject=<?= $subjectId ?>" target="_blank">View Certificate 📜</a>
<a class="button button-quiet" href="flashcards.php?subject=<?= $subjectId ?>">Review Flashcards 🂠</a>
<a class="button button-quiet" href="leaderboard.php">Leaderboard ♛</a>
</div>
</div>
<?php include 'includes/footer.php'; ?>