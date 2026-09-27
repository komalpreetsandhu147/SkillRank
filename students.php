<?php 
require_once __DIR__ . '/auth.php'; 
require_login('admin');

$search = trim($_GET['search'] ?? '');
$sql = 'SELECT u.id, u.name, u.email, u.created_at, 
               st.university, st.course, st.semester, st.phone,
               COALESCE(SUM(qa.questions_count), 0) solved, 
               COALESCE(ROUND(AVG(qa.accuracy)), 0) accuracy,
               COUNT(qa.id) tests_taken
        FROM users u 
        JOIN students st ON st.user_id=u.id 
        LEFT JOIN quiz_attempts qa ON qa.user_id=u.id 
        WHERE u.role=\'student\'';

$params = [];
$types = '';

if ($search) {
    $sql .= ' AND (u.name LIKE ? OR u.email LIKE ? OR st.university LIKE ? OR st.course LIKE ?)';
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}

$sql .= ' GROUP BY u.id ORDER BY u.created_at DESC';
$students = many($sql, $types, $params);

$pageTitle = 'Student Directory & Reports';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Learner Directory</div>
<h1>Enrolled Students</h1>
<p>Review individual learning profiles, test attempt histories, verified skills, and academic readiness.</p>
</div>
<span class="pill lime"><?= count($students) ?> Registered Students</span>
</div>

<!-- Search Bar -->
<div class="panel" style="padding:15px;margin-bottom:20px;background:#fff">
<form method="get" style="display:flex;gap:12px;align-items:center">
<input type="text" name="search" placeholder="Search by student name, email, university, or course..." value="<?= e($search) ?>" style="margin:0;flex:1">
<button class="button button-primary" type="submit" style="padding:10px 18px">Search ↗</button>
<?php if ($search): ?>
<a href="students.php" class="button button-quiet" style="padding:10px 14px">Clear</a>
<?php endif; ?>
</form>
</div>

<section class="panel">
<div class="panel-head">
<h2>Student Profiles & Progress</h2>
<small>Click 'Generate Report' to view comprehensive student analytics</small>
</div>

<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>Student</th>
<th>University / Major</th>
<th>Semester</th>
<th>Questions Solved</th>
<th>Avg Accuracy</th>
<th style="text-align:right">Action</th>
</tr>
</thead>
<tbody>
<?php if ($students): ?>
<?php foreach($students as $st): ?>
<tr>
<td>
<strong><?= e($st['name']) ?></strong>
<small style="display:block;color:var(--muted)"><?= e($st['email']) ?></small>
</td>
<td>
<?= e($st['university']) ?>
<small style="display:block;color:var(--muted)"><?= e($st['course']) ?></small>
</td>
<td><?= e($st['semester']) ?></td>
<td>
<strong><?= $st['solved'] ?></strong> solved
<small style="display:block;color:var(--muted)"><?= $st['tests_taken'] ?> tests completed</small>
</td>
<td>
<span class="pill <?= $st['accuracy'] >= 75 ? 'lime' : '' ?>">
<?= $st['accuracy'] ?>%
</span>
</td>
<td style="text-align:right">
<a class="button button-quiet" style="padding:6px 12px;font-size:0.8rem" href="student_profile.php?id=<?= $st['id'] ?>">
Student Report →
</a>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="6" class="muted" style="text-align:center;padding:30px">No students found matching your search.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>

<?php include 'includes/footer.php'; ?>