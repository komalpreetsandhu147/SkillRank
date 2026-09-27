<?php 
require_once __DIR__ . '/auth.php'; 
require_login(); // Accessible to both student and admin

$user = current_user();

// Fetch all curriculum subjects for filtering
$subjects = many('SELECT * FROM subjects ORDER BY name ASC');

$subjectFilter = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT u.id, u.name, u.email, st.university, st.course,
               COALESCE(SUM(qa.score), 0) points, 
               COALESCE(SUM(qa.questions_count), 0) solved, 
               COALESCE(ROUND(AVG(qa.accuracy)), 0) accuracy,
               COUNT(qa.id) tests_count
        FROM users u 
        LEFT JOIN students st ON st.user_id=u.id
        LEFT JOIN quiz_attempts qa ON qa.user_id=u.id';

$params = [];
$types = '';

if ($subjectFilter > 0) {
    $sql .= ' AND qa.subject_id=?';
    $params[] = $subjectFilter;
    $types .= 'i';
}

$sql .= ' WHERE u.role=\'student\'';

if ($search) {
    $sql .= ' AND (u.name LIKE ? OR st.university LIKE ? OR st.course LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

$sql .= ' GROUP BY u.id ORDER BY points DESC, accuracy DESC, solved DESC';
$leaders = many($sql, $types, $params);

$pageTitle = 'Campus Leaderboard'; 
include 'includes/header.php'; 
?>
<div class="page-head">
<div>
<div class="eyebrow">Institutional Rankings</div>
<h1>SkillRank College Leaderboard</h1>
<p>Benchmark your verified performance against peers across academic cohorts and tracks.</p>
</div>
<span class="pill lime">👑 Weekly Live Standings</span>
</div>

<!-- Podium for Top 3 Students (When viewing all or sufficient results) -->
<?php if (count($leaders) >= 3 && !$search): 
    $first = $leaders[0];
    $second = $leaders[1];
    $third = $leaders[2];
?>
<div style="display:grid;grid-template-columns:1fr 1.2fr 1fr;gap:15px;align-items:end;margin-bottom:30px;max-width:900px;margin-left:auto;margin-right:auto">
<!-- 2nd Place -->
<div class="panel" style="text-align:center;padding:24px 15px;background:#f8fafc;border-top:4px solid #94a3b8">
<div style="font-size:1.8rem">🥈</div>
<span class="pill" style="font-size:0.7rem;margin:5px 0">2nd Place</span>
<strong style="display:block;font-size:1.1rem;margin:6px 0 2px"><?= e($second['name']) ?></strong>
<small style="color:var(--muted);display:block"><?= e($second['course'] ?? 'BCA') ?></small>
<div style="margin-top:12px;font-size:1.2rem;font-weight:700;color:var(--teal)"><?= number_format($second['points']) ?> pts</div>
<small class="muted"><?= $second['accuracy'] ?>% accuracy</small>
</div>

<!-- 1st Place (Champion) -->
<div class="panel" style="text-align:center;padding:32px 18px;background:#fefce8;border:2px solid #facc15;border-top:6px solid #eab308;box-shadow:0 10px 25px #ca8a0420">
<div style="font-size:2.4rem">👑</div>
<span class="pill lime" style="font-size:0.75rem;margin:6px 0;font-weight:700">Campus Champion #1</span>
<strong style="display:block;font-size:1.3rem;margin:8px 0 2px;color:#713f12"><?= e($first['name']) ?></strong>
<small style="color:#854d0e;display:block"><?= e($first['university'] ?? 'Northbridge University') ?></small>
<div style="margin-top:14px;font-size:1.6rem;font-weight:800;color:var(--ink)"><?= number_format($first['points']) ?> pts</div>
<small style="color:#854d0e;font-weight:600"><?= $first['accuracy'] ?>% accuracy · <?= $first['solved'] ?> solved</small>
</div>

<!-- 3rd Place -->
<div class="panel" style="text-align:center;padding:24px 15px;background:#f8fafc;border-top:4px solid #b45309">
<div style="font-size:1.8rem">🥉</div>
<span class="pill" style="font-size:0.7rem;margin:5px 0">3rd Place</span>
<strong style="display:block;font-size:1.1rem;margin:6px 0 2px"><?= e($third['name']) ?></strong>
<small style="color:var(--muted);display:block"><?= e($third['course'] ?? 'BCA') ?></small>
<div style="margin-top:12px;font-size:1.2rem;font-weight:700;color:var(--teal)"><?= number_format($third['points']) ?> pts</div>
<small class="muted"><?= $third['accuracy'] ?>% accuracy</small>
</div>
</div>
<?php endif; ?>

<!-- Filter & Search Bar -->
<div class="panel" style="padding:15px;margin-bottom:22px;background:#fff">
<form method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
<div style="display:flex;align-items:center;gap:8px">
<label style="margin:0;font-size:0.8rem;font-weight:700">Filter Track:</label>
<select name="subject" onchange="this.form.submit()" style="margin:0;width:auto;padding:8px;font-size:0.85rem">
<option value="0">All Subjects (Combined)</option>
<?php foreach ($subjects as $s): ?>
<option value="<?= $s['id'] ?>" <?= $subjectFilter == $s['id'] ? 'selected' : '' ?>>
<?= e($s['name']) ?> Track
</option>
<?php endforeach; ?>
</select>
</div>

<div style="flex:1;display:flex;gap:8px;min-width:240px">
<input type="text" name="search" placeholder="Search student by name, course, or college..." value="<?= e($search) ?>" style="margin:0;padding:8px 12px;font-size:0.85rem">
<button type="submit" class="button button-primary" style="padding:8px 14px;font-size:0.82rem">Search</button>
</div>

<?php if ($subjectFilter || $search): ?>
<a href="leaderboard.php" class="text-link" style="font-size:0.82rem">Reset Filters ✕</a>
<?php endif; ?>
</form>
</div>

<!-- Full Rankings Table -->
<section class="panel">
<div class="panel-head">
<h2>Leaderboard Standings</h2>
<small>Showing <?= count($leaders) ?> students</small>
</div>

<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Rank</th>
<th>Student</th>
<th>University / Major</th>
<th>Accuracy</th>
<th>Solved</th>
<th style="text-align:right">Total Score</th>
</tr>
</thead>
<tbody>
<?php if ($leaders): ?>
<?php foreach($leaders as $index => $leader): 
    $isMe = ($user['id'] == $leader['id']);
    $rankNum = $index + 1;
    $medal = match($rankNum) {
        1 => '🥇 #1',
        2 => '🥈 #2',
        3 => '🥉 #3',
        default => '#' . $rankNum
    };
?>
<tr style="<?= $isMe ? 'background:#eef6cf;font-weight:700' : '' ?>">
<td style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.05rem">
<?= $medal ?>
</td>
<td>
<strong><?= e($leader['name']) ?></strong>
<?php if ($isMe): ?>
<span class="pill lime" style="font-size:0.6rem;margin-left:6px">YOU</span>
<?php endif; ?>
<?php if ($rankNum === 1): ?>
<span class="pill" style="background:#fef3c7;color:#92400e;font-size:0.65rem;margin-left:6px">👑 Star</span>
<?php endif; ?>
<small style="display:block;color:var(--muted)"><?= e($leader['email']) ?></small>
</td>
<td>
<?= e($leader['course'] ?? 'BCA') ?>
<small style="display:block;color:var(--muted)"><?= e($leader['university'] ?? 'College') ?></small>
</td>
<td>
<span class="pill <?= $leader['accuracy'] >= 75 ? 'lime' : '' ?>">
<?= $leader['accuracy'] ?>%
</span>
</td>
<td><?= $leader['solved'] ?> questions (<?= $leader['tests_count'] ?> tests)</td>
<td style="text-align:right;font-size:1.1rem;font-weight:700;color:var(--teal)">
<?= number_format($leader['points']) ?> pts
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="6" class="muted" style="text-align:center;padding:30px">No students found matching your filters.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>

<?php include 'includes/footer.php'; ?>