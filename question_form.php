<?php 
require_once __DIR__ . '/auth.php'; 
require_login('admin');

$subjects = many('SELECT * FROM subjects ORDER BY name ASC');
$topics = many('SELECT * FROM topics ORDER BY subject_id, name ASC');

$questionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$question = $questionId ? one('SELECT * FROM questions WHERE id=?', 'i', [$questionId]) : null;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = (int)($_POST['subject_id'] ?? 1);
    $topicId = (int)($_POST['topic_id'] ?? 1);
    $difficulty = in_array($_POST['difficulty'] ?? '', ['Easy', 'Medium', 'Hard']) ? $_POST['difficulty'] : 'Easy';
    $questionText = trim($_POST['question'] ?? '');
    $optA = trim($_POST['option_a'] ?? '');
    $optB = trim($_POST['option_b'] ?? '');
    $optC = trim($_POST['option_c'] ?? '');
    $optD = trim($_POST['option_d'] ?? '');
    $correct = strtoupper(trim($_POST['correct_answer'] ?? 'A'));
    $marks = max(1, (int)($_POST['marks'] ?? 10));
    $explanation = trim($_POST['explanation'] ?? '');
    $editId = (int)($_POST['id'] ?? 0);

    if (!$questionText || !$optA || !$optB || !$optC || !$optD) {
        $error = 'Please complete the question prompt and all four options.';
    } else {
        if ($editId > 0) {
            // 11 params: i i s s s s s s s i s + i (id) = 12 params
            $stmt = db()->prepare('UPDATE questions SET subject_id=?, topic_id=?, difficulty=?, question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_answer=?, marks=?, explanation=? WHERE id=?');
            $stmt->bind_param('iisssssssisi', $subjectId, $topicId, $difficulty, $questionText, $optA, $optB, $optC, $optD, $correct, $marks, $explanation, $editId);
            $stmt->execute();
        } else {
            // 11 params: i i s s s s s s s i s
            $stmt = db()->prepare('INSERT INTO questions (subject_id, topic_id, difficulty, question, option_a, option_b, option_c, option_d, correct_answer, marks, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iisssssssis', $subjectId, $topicId, $difficulty, $questionText, $optA, $optB, $optC, $optD, $correct, $marks, $explanation);
            $stmt->execute();
        }
        redirect('questions.php');
    }
}

$pageTitle = $question ? 'Edit Question' : 'Add New Question';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Question Studio</div>
<h1><?= e($pageTitle) ?></h1>
<p>Ensure questions have clear wording, plausible distractors, and helpful explanations.</p>
</div>
<a class="button button-quiet" href="questions.php">← Back to Question Bank</a>
</div>

<?php if($error): ?>
<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<section class="panel" style="max-width:850px">
<form method="post" class="form-stack">
<input type="hidden" name="id" value="<?= $question['id'] ?? '' ?>">

<div class="form-grid">
<label>Subject
<select name="subject_id" id="subject-select" required>
<?php foreach($subjects as $s): ?>
<option value="<?= $s['id'] ?>" <?= ($question['subject_id'] ?? 1) == $s['id'] ? 'selected' : '' ?>>
<?= e($s['name']) ?> (<?= e($s['code']) ?>)
</option>
<?php endforeach; ?>
</select>
</label>

<label>Curriculum Topic
<select name="topic_id" id="topic-select" required>
<?php foreach($topics as $t): ?>
<option value="<?= $t['id'] ?>" data-subject="<?= $t['subject_id'] ?>" <?= ($question['topic_id'] ?? 1) == $t['id'] ? 'selected' : '' ?>>
<?= e($t['name']) ?>
</option>
<?php endforeach; ?>
</select>
</label>
</div>

<div class="form-grid">
<label>Difficulty Tier
<select name="difficulty">
<option value="Easy" <?= ($question['difficulty'] ?? '') === 'Easy' ? 'selected' : '' ?>>Easy (Syntax & Basics)</option>
<option value="Medium" <?= ($question['difficulty'] ?? 'Medium') === 'Medium' ? 'selected' : '' ?>>Medium (Application & Logic)</option>
<option value="Hard" <?= ($question['difficulty'] ?? '') === 'Hard' ? 'selected' : '' ?>>Hard (Architecture & Edge Cases)</option>
</select>
</label>

<label>Marks / Weightage (pts)
<input type="number" name="marks" value="<?= $question['marks'] ?? 10 ?>" min="1" max="50" required>
</label>
</div>

<label>Question Prompt
<textarea name="question" rows="4" required placeholder="Type the question prompt clearly..."><?= e($question['question'] ?? '') ?></textarea>
</label>

<div class="form-grid">
<label>Option A
<input name="option_a" required value="<?= e($question['option_a'] ?? '') ?>" placeholder="First choice">
</label>
<label>Option B
<input name="option_b" required value="<?= e($question['option_b'] ?? '') ?>" placeholder="Second choice">
</label>
</div>

<div class="form-grid">
<label>Option C
<input name="option_c" required value="<?= e($question['option_c'] ?? '') ?>" placeholder="Third choice">
</label>
<label>Option D
<input name="option_d" required value="<?= e($question['option_d'] ?? '') ?>" placeholder="Fourth choice">
</label>
</div>

<div class="form-grid">
<label>Correct Answer Key
<select name="correct_answer" required>
<option value="A" <?= ($question['correct_answer'] ?? '') === 'A' ? 'selected' : '' ?>>Option A</option>
<option value="B" <?= ($question['correct_answer'] ?? 'B') === 'B' ? 'selected' : '' ?>>Option B</option>
<option value="C" <?= ($question['correct_answer'] ?? '') === 'C' ? 'selected' : '' ?>>Option C</option>
<option value="D" <?= ($question['correct_answer'] ?? '') === 'D' ? 'selected' : '' ?>>Option D</option>
</select>
</label>

<div><!-- spacer --></div>
</div>

<label>Detailed Answer Explanation (Shown to student after test submission)
<textarea name="explanation" rows="3" placeholder="Explain why the correct answer is valid and clarify common misconceptions..."><?= e($question['explanation'] ?? '') ?></textarea>
</label>

<div style="display:flex;gap:12px;margin-top:10px">
<button class="button button-primary" type="submit">
<?= $question ? 'Save Question Changes ↗' : 'Add Question to Bank ↗' ?>
</button>
<a class="button button-quiet" href="questions.php">Cancel</a>
</div>
</form>
</section>

<script>
// Filter topic options when subject changes
const subSelect = document.getElementById('subject-select');
const topSelect = document.getElementById('topic-select');

function filterTopics() {
    const selectedSub = subSelect.value;
    let firstValid = null;
    Array.from(topSelect.options).forEach(opt => {
        const matches = opt.dataset.subject === selectedSub;
        opt.style.display = matches ? 'block' : 'none';
        if (matches && !firstValid) firstValid = opt;
    });
    if (topSelect.selectedOptions[0] && topSelect.selectedOptions[0].dataset.subject !== selectedSub && firstValid) {
        firstValid.selected = true;
    }
}
subSelect.addEventListener('change', filterTopics);
filterTopics();
</script>

<?php include 'includes/footer.php'; ?>