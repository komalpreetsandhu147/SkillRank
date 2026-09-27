<?php 
require_once __DIR__ . '/auth.php'; 
require_login('student'); 

$subjectId = (int)($_GET['subject'] ?? 1); 
$mode = $_GET['mode'] ?? 'sprint';
$limit = ($mode === 'full') ? 10 : 5;

$subject = one('SELECT * FROM subjects WHERE id=?', 'i', [$subjectId]); 
if (!$subject) redirect('practice.php');

// Fetch balanced questions for this subject (Easy, Medium, Hard)
$questions = many(
    'SELECT q.*, t.name as topic_name 
     FROM questions q 
     LEFT JOIN topics t ON t.id=q.topic_id 
     WHERE q.subject_id=? 
     ORDER BY FIELD(q.difficulty, "Easy", "Medium", "Hard"), RAND() 
     LIMIT ?', 
    'ii', 
    [$subjectId, $limit]
); 

if (!$questions) {
    redirect('practice.php');
}

$pageTitle = 'Skill Test · ' . $subject['name']; 
$durationMinutes = ($mode === 'full') ? 12 : 8;
include 'includes/header.php'; 
?>
<div class="page-head" style="margin-bottom:20px">
<div>
<div class="eyebrow"><?= e($subject['code']) ?> / Proctored Assessment</div>
<h1><?= e($subject['name']) ?> Skill Test</h1>
<p><?= count($questions) ?> Questions · Immediate Auto-Scoring · Verified Skill Progression</p>
</div>
<div style="display:flex;align-items:center;gap:10px">
<div id="quiz-timer" class="pill lime" style="font-size:1rem;padding:9px 16px;font-family:'Space Grotesk',sans-serif;font-weight:700">
⏱ <span id="time-display"><?= sprintf('%02d:00', $durationMinutes) ?></span>
</div>
</div>
</div>

<form method="post" action="result.php" id="quiz-form">
<input type="hidden" name="subject_id" value="<?= $subjectId ?>">
<input type="hidden" name="start_time" value="<?= time() ?>">
<?php foreach ($questions as $q): ?>
<input type="hidden" name="presented_questions[]" value="<?= $q['id'] ?>">
<?php endforeach; ?>

<div class="dashboard-grid" style="grid-template-columns:1fr 300px;align-items:start;gap:22px">
<!-- Left Column: Questions List -->
<div>
<?php foreach ($questions as $index => $question): 
    $diffColor = match($question['difficulty']) {
        'Hard' => '#ffe8e1;color:#a64123',
        'Medium' => '#fef3c7;color:#92400e',
        default => '#eef6cf;color:#1e6355'
    };
    $qNum = $index + 1;
?>
<section class="panel question-block" id="q-block-<?= $qNum ?>" style="margin-bottom:20px;scroll-margin-top:20px" data-qid="<?= $question['id'] ?>" data-qnum="<?= $qNum ?>">
<div class="panel-head" style="margin-bottom:12px">
<div style="display:flex;align-items:center;gap:10px">
<span class="pill" style="font-weight:800;font-size:0.8rem">Q<?= $qNum ?></span>
<small style="font-weight:700;color:var(--muted)">Question <?= $qNum ?> of <?= count($questions) ?></small>
</div>
<div style="display:flex;gap:8px;align-items:center">
<?php if (!empty($question['topic_name'])): ?>
<span class="pill" style="font-size:0.65rem"><?= e($question['topic_name']) ?></span>
<?php endif; ?>
<span class="pill" style="background:<?= $diffColor ?>"><?= e($question['difficulty']) ?> · <?= $question['marks'] ?> pts</span>
<button type="button" class="button button-quiet" style="padding:4px 9px;font-size:0.72rem" onclick="toggleFlag(<?= $qNum ?>)">
<span id="flag-icon-<?= $qNum ?>">⚐</span> Flag
</button>
</div>
</div>

<h3 style="font-size:1.12rem;line-height:1.5;margin-bottom:18px"><?= e($question['question']) ?></h3>

<div style="display:grid;gap:10px">
<?php 
$options = [
    'A' => $question['option_a'],
    'B' => $question['option_b'],
    'C' => $question['option_c'],
    'D' => $question['option_d']
];
foreach ($options as $key => $optText): 
?>
<label class="option option-label" style="margin:0;transition:all 0.15s ease">
<input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $key ?>" class="quiz-radio" data-qid="<?= $question['id'] ?>" data-qnum="<?= $qNum ?>">
<span style="font-weight:700;display:inline-grid;place-items:center;width:26px;height:26px;border-radius:6px;background:#e5f0f4;color:var(--teal);margin-right:10px"><?= $key ?></span>
<span style="flex:1"><?= e($optText) ?></span>
</label>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px">
<a href="practice.php" class="button button-quiet" onclick="return confirm('Exit this assessment? Any unsubmitted responses will be lost.');">← Abort Test</a>
<button class="button button-primary" type="button" onclick="confirmSubmit()" style="padding:14px 28px;font-size:1rem">
Submit Assessment & View Score ↗
</button>
</div>
</div>

<!-- Right Column: Sticky Question Navigator Palette -->
<aside style="position:sticky;top:20px">
<div class="panel" style="padding:20px">
<div class="panel-head" style="margin-bottom:14px">
<strong style="font-size:0.95rem">Assessment Navigator</strong>
<small id="nav-summary">0 / <?= count($questions) ?> Answered</small>
</div>

<!-- Progress bar -->
<div class="progress" style="height:6px;margin-bottom:18px">
<i id="nav-progress-bar" style="width:0%;background:var(--teal)"></i>
</div>

<!-- Question number buttons palette -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:18px">
<?php for ($i = 1; $i <= count($questions); $i++): ?>
<button type="button" id="pal-btn-<?= $i ?>" class="pal-btn" onclick="scrollToQuestion(<?= $i ?>)"><?= $i ?></button>
<?php endfor; ?>
</div>

<div style="font-size:0.75rem;color:var(--muted);display:grid;gap:6px;border-top:1px solid var(--line);padding-top:14px">
<div style="display:flex;align-items:center;gap:8px">
<span style="width:12px;height:12px;background:#176b78;border-radius:3px;display:inline-block"></span>
<span>Answered</span>
</div>
<div style="display:flex;align-items:center;gap:8px">
<span style="width:12px;height:12px;background:#f59e0b;border-radius:3px;display:inline-block"></span>
<span>Flagged for Review</span>
</div>
<div style="display:flex;align-items:center;gap:8px">
<span style="width:12px;height:12px;background:#e2e8f0;border-radius:3px;display:inline-block"></span>
<span>Unanswered</span>
</div>
</div>

<div style="margin-top:20px">
<button type="button" class="button button-primary full" onclick="confirmSubmit()">
Submit Test Now ↗
</button>
</div>
</div>
</aside>
</div>
</form>

<style>
.pal-btn {
    height: 38px;
    border-radius: 6px;
    border: 1px solid var(--line);
    background: #f8fafc;
    color: var(--ink);
    font-weight: 700;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}
.pal-btn:hover {
    border-color: var(--teal);
}
.pal-btn.answered {
    background: var(--teal) !important;
    color: #fff !important;
    border-color: var(--teal) !important;
}
.pal-btn.flagged {
    background: #f59e0b !important;
    color: #fff !important;
    border-color: #d97706 !important;
}
</style>

<script>
const totalQuestions = <?= count($questions) ?>;
const answeredSet = new Set();
const flaggedSet = new Set();

function scrollToQuestion(qNum) {
    const el = document.getElementById('q-block-' + qNum);
    if (el) el.scrollIntoView({ behavior: 'smooth' });
}

function toggleFlag(qNum) {
    const btn = document.getElementById('pal-btn-' + qNum);
    const icon = document.getElementById('flag-icon-' + qNum);
    if (flaggedSet.has(qNum)) {
        flaggedSet.delete(qNum);
        icon.textContent = '⚐';
        updatePalButton(qNum);
    } else {
        flaggedSet.add(qNum);
        icon.textContent = '⚑';
        btn.className = 'pal-btn flagged';
    }
}

function updatePalButton(qNum) {
    const btn = document.getElementById('pal-btn-' + qNum);
    if (flaggedSet.has(qNum)) {
        btn.className = 'pal-btn flagged';
    } else if (answeredSet.has(qNum)) {
        btn.className = 'pal-btn answered';
    } else {
        btn.className = 'pal-btn';
    }
}

document.querySelectorAll('.quiz-radio').forEach(radio => {
    radio.addEventListener('change', (e) => {
        const qNum = parseInt(e.target.dataset.qnum, 10);
        answeredSet.add(qNum);
        updatePalButton(qNum);

        // Highlight selected label
        const block = e.target.closest('.question-block');
        block.querySelectorAll('.option-label').forEach(lbl => {
            lbl.style.borderColor = 'var(--line)';
            lbl.style.background = '#fff';
        });
        const selectedLbl = e.target.closest('.option-label');
        selectedLbl.style.borderColor = 'var(--teal)';
        selectedLbl.style.background = '#eef6cf';

        // Update progress
        const pct = Math.round((answeredSet.size / totalQuestions) * 100);
        document.getElementById('nav-progress-bar').style.width = pct + '%';
        document.getElementById('nav-summary').textContent = `${answeredSet.size} / ${totalQuestions} Answered`;
    });
});

function confirmSubmit() {
    const unanswered = totalQuestions - answeredSet.size;
    let msg = 'Are you ready to submit your assessment?';
    if (unanswered > 0) {
        msg = `You have ${unanswered} unanswered question(s). Unanswered questions receive 0 marks. Submit now?`;
    } else if (flaggedSet.size > 0) {
        msg = `You have ${flaggedSet.size} question(s) flagged for review. Are you sure you wish to submit?`;
    }
    if (confirm(msg)) {
        document.getElementById('quiz-form').submit();
    }
}

// Countdown Timer
let totalSeconds = <?= $durationMinutes * 60 ?>;
const timerDisplay = document.getElementById('time-display');
const timerInterval = setInterval(() => {
    totalSeconds--;
    if (totalSeconds <= 0) {
        clearInterval(timerInterval);
        alert('Time is up! Submitting your assessment now.');
        document.getElementById('quiz-form').submit();
        return;
    }
    const mins = Math.floor(totalSeconds / 60);
    const secs = totalSeconds % 60;
    timerDisplay.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    if (totalSeconds <= 60) {
        document.getElementById('quiz-timer').style.background = '#ffe8e1';
        document.getElementById('quiz-timer').style.color = '#a64123';
    }
}, 1000);
</script>

<?php include 'includes/footer.php'; ?>