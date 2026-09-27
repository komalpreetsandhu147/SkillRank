<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai.php';
require_login('admin');

$notice = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $qid = (int)$_GET['delete'];
    query('DELETE FROM questions WHERE id=?', 'i', [$qid]);
    redirect('questions.php?msg=deleted');
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $notice = 'Question deleted from question bank.';
}

// Handle AI Bulk Question Generation and Insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_ai_questions') {
    $subId = (int)$_POST['ai_subject_id'];
    $diff = $_POST['ai_difficulty'] ?? 'Medium';
    $subObj = one('SELECT name FROM subjects WHERE id=?', 'i', [$subId]);
    $subName = $subObj['name'] ?? 'PHP';
    
    // Get first topic for subject
    $topicObj = one('SELECT id, name FROM topics WHERE subject_id=? LIMIT 1', 'i', [$subId]);
    $topicId = $topicObj['id'] ?? 1;
    $topicName = $topicObj['name'] ?? 'Core Concepts';

    $gen = ai_generate_questions($subName, $diff, $topicName, 3);
    $insertedCount = 0;

    if (!empty($gen['questions'])) {
        foreach ($gen['questions'] as $q) {
            $stmt = db()->prepare('INSERT INTO questions (subject_id, topic_id, difficulty, question, option_a, option_b, option_c, option_d, correct_answer, marks, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $marks = (int)($q['marks'] ?? 10);
            $stmt->bind_param('iisssssssis', $subId, $topicId, $diff, $q['question'], $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'], $q['correct_answer'], $marks, $q['explanation']);
            if ($stmt->execute()) {
                $insertedCount++;
            }
        }
        $notice = "Successfully generated and imported $insertedCount questions via {$gen['source']} into $subName ($diff)!";
    } else {
        $error = 'Could not generate questions. Please try again or create manually.';
    }
}

// Handle JSON Bulk Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_json_questions') {
    $rawJson = trim($_POST['json_data'] ?? '');
    $targetSubId = (int)($_POST['import_subject_id'] ?? 1);
    $parsed = json_decode($rawJson, true);
    if (!is_array($parsed)) {
        $error = 'Invalid JSON format. Please provide a valid JSON array of questions.';
    } else {
        $imported = 0;
        foreach ($parsed as $item) {
            if (!empty($item['question']) && !empty($item['option_a']) && !empty($item['option_b']) && !empty($item['correct_answer'])) {
                $subId = $targetSubId ?: 1;
                $topicId = 1;
                $diff = in_array($item['difficulty'] ?? '', ['Easy', 'Medium', 'Hard']) ? $item['difficulty'] : 'Medium';
                $marks = (int)($item['marks'] ?? 10);
                $expl = $item['explanation'] ?? '';
                $correct = strtoupper(substr(trim($item['correct_answer']), 0, 1));

                $stmt = db()->prepare('INSERT INTO questions (subject_id, topic_id, difficulty, question, option_a, option_b, option_c, option_d, correct_answer, marks, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('iisssssssis', $subId, $topicId, $diff, $item['question'], $item['option_a'], $item['option_b'], $item['option_c'], $item['option_d'], $correct, $marks, $expl);
                if ($stmt->execute()) $imported++;
            }
        }
        $notice = "Successfully imported $imported questions from JSON!";
    }
}

// Filters
$subjectFilter = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;
$difficultyFilter = isset($_GET['difficulty']) ? trim($_GET['difficulty']) : '';

$sql = 'SELECT q.*, s.name as subject, s.code as subject_code, t.name as topic 
        FROM questions q 
        JOIN subjects s ON s.id=q.subject_id 
        LEFT JOIN topics t ON t.id=q.topic_id 
        WHERE 1=1';
$params = [];
$types = '';

if ($subjectFilter > 0) {
    $sql .= ' AND q.subject_id=?';
    $params[] = $subjectFilter;
    $types .= 'i';
}
if ($difficultyFilter && in_array($difficultyFilter, ['Easy', 'Medium', 'Hard'])) {
    $sql .= ' AND q.difficulty=?';
    $params[] = $difficultyFilter;
    $types .= 's';
}

$sql .= ' ORDER BY q.id DESC';
$questions = many($sql, $types, $params);
$subjects = many('SELECT * FROM subjects ORDER BY name ASC');

$pageTitle = 'Question Bank Management';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Academic Content Management</div>
<h1>Question Bank Management</h1>
<p>Curate, filter, import, and expand technical challenges using GenAI or direct entry.</p>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<button class="button button-quiet" onclick="document.getElementById('ai-modal').style.display='block'">
✨ AI Generator
</button>
<button class="button button-quiet" onclick="document.getElementById('import-modal').style.display='block'">
📥 Import JSON
</button>
<a class="button button-quiet" href="question_export.php" title="Download all questions as backup JSON">
📤 Export JSON
</a>
<a class="button button-primary" href="question_form.php">
+ Add Question <span>↗</span>
</a>
</div>
</div>

<?php if($notice): ?>
<div class="alert" style="background:#eef6cf;color:var(--teal);font-weight:700">✓ <?= e($notice) ?></div>
<?php endif; ?>
<?php if($error): ?>
<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<!-- AI Generator Panel (Toggleable / Expandable) -->
<div id="ai-modal" class="panel" style="display:none;background:#f0f9fa;border:2px solid var(--teal);margin-bottom:24px">
<div class="panel-head">
<div>
<h2 style="color:var(--ink)">✨ GenAI Question Generator</h2>
<small>Automatically generates balanced multiple-choice questions with options & explanations</small>
</div>
<button class="button button-quiet" style="padding:4px 10px;font-size:0.8rem" onclick="document.getElementById('ai-modal').style.display='none'">✕ Close</button>
</div>

<form method="post" class="form-grid" style="align-items:end">
<input type="hidden" name="action" value="generate_ai_questions">
<label>Target Subject
<select name="ai_subject_id" required>
<?php foreach ($subjects as $sub): ?>
<option value="<?= $sub['id'] ?>"><?= e($sub['name']) ?> (<?= e($sub['code']) ?>)</option>
<?php endforeach; ?>
</select>
</label>

<label>Difficulty Tier
<select name="ai_difficulty">
<option value="Easy">Easy (Syntax & Definitions)</option>
<option value="Medium" selected>Medium (Application & Logic)</option>
<option value="Hard">Hard (Architectural & Edge Cases)</option>
</select>
</label>

<button class="button button-primary" type="submit" style="height:48px">
Generate & Import 3 Questions ↗
</button>
</form>
<small style="display:block;margin-top:10px;color:var(--muted)">
Powered by Gemini API when configured, with seamless built-in procedural generation fallback.
</small>
</div>

<!-- Bulk JSON Import Panel (Toggleable) -->
<div id="import-modal" class="panel" style="display:none;background:#fff8f6;border:2px solid #e06d53;margin-bottom:24px">
<div class="panel-head">
<div>
<h2 style="color:var(--ink)">📥 Bulk JSON Question Import</h2>
<small>Paste a JSON array containing question items with options and correct answers</small>
</div>
<button class="button button-quiet" style="padding:4px 10px;font-size:0.8rem" onclick="document.getElementById('import-modal').style.display='none'">✕ Close</button>
</div>

<form method="post" class="form-stack">
<input type="hidden" name="action" value="import_json_questions">
<div class="form-grid">
<label>Target Subject
<select name="import_subject_id" required>
<?php foreach ($subjects as $sub): ?>
<option value="<?= $sub['id'] ?>"><?= e($sub['name']) ?> (<?= e($sub['code']) ?>)</option>
<?php endforeach; ?>
</select>
</label>
<div></div>
</div>

<label>JSON Array Payload
<textarea name="json_data" rows="6" placeholder='[
  {
    "question": "What does SQL stand for?",
    "option_a": "Structured Query Language",
    "option_b": "Simple Query Logic",
    "option_c": "Sequential Query List",
    "option_d": "System Query Line",
    "correct_answer": "A",
    "marks": 10,
    "difficulty": "Easy",
    "explanation": "SQL stands for Structured Query Language."
  }
]' required></textarea>
</label>

<button class="button button-dark" type="submit" style="align-self:start">
Execute Batch JSON Import ↗
</button>
</form>
</div>

<!-- Filter Bar -->
<div class="panel" style="padding:15px;margin-bottom:20px;background:#fff">
<form method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
<label style="margin:0;font-size:0.8rem;font-weight:700;display:flex;align-items:center;gap:6px">
Filter Subject:
<select name="subject" onchange="this.form.submit()" style="margin:0;width:auto;min-width:160px;padding:8px">
<option value="0">All Subjects (<?= count($subjects) ?>)</option>
<?php foreach($subjects as $s): ?>
<option value="<?= $s['id'] ?>" <?= $subjectFilter == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
<?php endforeach; ?>
</select>
</label>

<label style="margin:0;font-size:0.8rem;font-weight:700;display:flex;align-items:center;gap:6px">
Difficulty:
<select name="difficulty" onchange="this.form.submit()" style="margin:0;width:auto;min-width:140px;padding:8px">
<option value="">All Difficulties</option>
<option value="Easy" <?= $difficultyFilter === 'Easy' ? 'selected' : '' ?>>Easy</option>
<option value="Medium" <?= $difficultyFilter === 'Medium' ? 'selected' : '' ?>>Medium</option>
<option value="Hard" <?= $difficultyFilter === 'Hard' ? 'selected' : '' ?>>Hard</option>
</select>
</label>

<?php if($subjectFilter || $difficultyFilter): ?>
<a href="questions.php" class="text-link" style="margin-left:auto;font-size:0.8rem">Clear Filters ✕</a>
<?php endif; ?>
</form>
</div>

<!-- Questions Table -->
<section class="panel">
<div class="panel-head">
<h2>Active Questions List</h2>
<small>Showing <?= count($questions) ?> questions</small>
</div>

<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th style="width:40px">#</th>
<th>Question</th>
<th>Subject / Topic</th>
<th>Level</th>
<th>Answer</th>
<th>Marks</th>
<th style="text-align:right">Actions</th>
</tr>
</thead>
<tbody>
<?php if ($questions): ?>
<?php foreach($questions as $index => $q): 
    $diffColor = match($q['difficulty']) {
        'Hard' => '#ffe8e1;color:#a64123',
        'Medium' => '#fef3c7;color:#92400e',
        default => '#eef6cf;color:#1e6355'
    };
?>
<tr>
<td><?= ($index + 1) ?></td>
<td style="max-width:380px;white-space:normal">
<strong><?= e($q['question']) ?></strong>
<div style="font-size:0.75rem;color:var(--muted);margin-top:4px">
A: <?= e($q['option_a']) ?> | B: <?= e($q['option_b']) ?> | C: <?= e($q['option_c']) ?> | D: <?= e($q['option_d']) ?>
</div>
<?php if (!empty($q['explanation'])): ?>
<small style="display:block;color:var(--teal);margin-top:2px">💡 <?= e(substr($q['explanation'], 0, 70)) ?>...</small>
<?php endif; ?>
</td>
<td>
<strong><?= e($q['subject']) ?></strong>
<small style="display:block;color:var(--muted)"><?= e($q['topic'] ?: 'General') ?></small>
</td>
<td>
<span class="pill" style="background:<?= $diffColor ?>"><?= e($q['difficulty']) ?></span>
</td>
<td>
<span class="pill lime" style="font-weight:800;font-size:0.75rem"><?= e($q['correct_answer']) ?></span>
</td>
<td><?= $q['marks'] ?> pts</td>
<td style="text-align:right">
<a class="text-link" href="question_form.php?id=<?= $q['id'] ?>" style="margin-right:8px">Edit</a>
<a class="text-link" style="color:#a64123" href="questions.php?delete=<?= $q['id'] ?>" onclick="return confirm('Permanently delete this question from the bank?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7" class="muted" style="text-align:center;padding:30px">No questions matched the selected filters.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>

<?php include 'includes/footer.php'; ?>