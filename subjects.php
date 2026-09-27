<?php 
require_once __DIR__ . '/auth.php'; 
require_login('admin');

$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add_subject';

    if ($action === 'add_subject') {
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $desc = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '✦');

        if (!$name || !$code) {
            $error = 'Please provide both a subject name and a short code.';
        } elseif (one('SELECT id FROM subjects WHERE name=? OR code=?', 'ss', [$name, $code])) {
            $error = 'A subject with that name or code already exists in the curriculum.';
        } else { 
            query('INSERT INTO subjects (name, code, description, icon) VALUES (?, ?, ?, ?)', 'ssss', [$name, $code, $desc, $icon]); 
            // Also ensure skill exists
            query('INSERT IGNORE INTO skills (name) VALUES (?)', 's', [$name]);
            $notice = "Subject '$name' successfully added to curriculum and skills matrix.";
        }
    }

    if ($action === 'add_topic') {
        $subId = (int)$_POST['topic_subject_id'];
        $topicName = trim($_POST['topic_name'] ?? '');
        if ($subId && $topicName) {
            query('INSERT INTO topics (subject_id, name) VALUES (?, ?)', 'is', [$subId, $topicName]);
            $notice = "Topic '$topicName' added.";
        }
    }
}

$subjects = many(
    'SELECT s.*, COUNT(DISTINCT q.id) as question_count, COUNT(DISTINCT t.id) as topic_count 
     FROM subjects s 
     LEFT JOIN questions q ON q.subject_id=s.id 
     LEFT JOIN topics t ON t.subject_id=s.id 
     GROUP BY s.id 
     ORDER BY s.id ASC'
);

$topics = many('SELECT t.*, s.name as subject_name FROM topics t JOIN subjects s ON s.id=t.subject_id ORDER BY s.name, t.name');

$pageTitle = 'Curriculum & Subjects';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Curriculum Architecture</div>
<h1>Subjects & Topics Management</h1>
<p>Organize academic tracks, define syllabi topics, and structure skill verification modules.</p>
</div>
<span class="pill lime"><?= count($subjects) ?> Subjects Active</span>
</div>

<?php if($notice): ?>
<div class="alert" style="background:#eef6cf;color:var(--teal);font-weight:700">✓ <?= e($notice) ?></div>
<?php endif; ?>
<?php if($error): ?>
<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<div class="dashboard-grid">
<!-- Subjects List -->
<section class="panel">
<div class="panel-head">
<div>
<h2>Curriculum Tracks</h2>
<small>Live assessment tracks</small>
</div>
</div>

<div style="display:grid;gap:12px">
<?php foreach($subjects as $sub): ?>
<div class="achievement" style="padding:14px;background:#f8fafc;border:1px solid var(--line);border-radius:10px">
<span class="icon-tile lime" style="font-size:1.3rem"><?= e($sub['icon'] ?? '✦') ?></span>
<div style="flex:1">
<div style="display:flex;align-items:center;gap:10px">
<strong><?= e($sub['name']) ?></strong>
<span class="pill" style="font-size:0.65rem"><?= e($sub['code']) ?></span>
</div>
<small style="color:var(--muted);display:block;margin-top:4px"><?= e($sub['description']) ?></small>
<div style="display:flex;gap:15px;margin-top:8px;font-size:0.75rem;color:var(--muted)">
<span><strong><?= $sub['question_count'] ?></strong> Questions</span>
<span><strong><?= $sub['topic_count'] ?></strong> Topics</span>
</div>
</div>
<a class="button button-quiet" style="font-size:0.8rem;padding:7px 12px" href="questions.php?subject=<?= $sub['id'] ?>">View Questions →</a>
</div>
<?php endforeach; ?>
</div>
</section>

<!-- Forms Column -->
<div>
<!-- Add Subject Form -->
<section class="panel" style="margin-bottom:20px" id="add-subject">
<div class="panel-head">
<h2>Add New Subject Track</h2>
</div>
<form method="post" class="form-stack">
<input type="hidden" name="action" value="add_subject">
<div class="form-grid">
<label>Subject Name
<input name="name" required placeholder="Python Programming">
</label>
<label>Short Code
<input name="code" required maxlength="10" placeholder="PY">
</label>
</div>
<div class="form-grid">
<label>Icon Emoji
<input name="icon" value="🐍" placeholder="e.g. 🐍, ⚡, 🗄️">
</label>
<label>Brief Description
<input name="description" placeholder="Object-oriented scripting & APIs">
</label>
</div>
<button class="button button-primary full" type="submit">Create Subject Track ↗</button>
</form>
</section>

<!-- Add Topic Form -->
<section class="panel">
<div class="panel-head">
<h2>Add Syllabus Topic</h2>
</div>
<form method="post" class="form-stack">
<input type="hidden" name="action" value="add_topic">
<label>Target Subject
<select name="topic_subject_id" required>
<?php foreach($subjects as $s): ?>
<option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
<?php endforeach; ?>
</select>
</label>
<label>Topic Title
<input name="topic_name" required placeholder="e.g. Data Structures, Async/Await, Normalization">
</label>
<button class="button button-quiet full" type="submit">+ Add Topic to Subject</button>
</form>
</section>
</div>
</div>

<?php include 'includes/footer.php'; ?>
