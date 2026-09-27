<?php
require_once __DIR__ . '/auth.php';
require_login('student');

$subjects = many('SELECT * FROM subjects ORDER BY id ASC');
$subjectId = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;

$whereClause = '1=1';
$params = [];
$types = '';

if ($subjectId > 0) {
    $whereClause .= ' AND q.subject_id = ?';
    $params[] = $subjectId;
    $types .= 'i';
}

$sql = "SELECT q.*, s.name as subject_name, s.code as subject_code, t.name as topic_name 
        FROM questions q 
        JOIN subjects s ON s.id = q.subject_id 
        LEFT JOIN topics t ON t.id = q.topic_id 
        WHERE $whereClause 
        ORDER BY RAND() 
        LIMIT 30";

$cards = !empty($params) ? many($sql, $types, $params) : many($sql);

$pageTitle = 'Flashcard Revision Studio';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Active Recall & Spaced Repetition</div>
<h1>Technical Flashcards Studio</h1>
<p>Flip through verified technical concepts, master tricky interview questions, and reinforce memory before your assessments.</p>
</div>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
<span class="pill lime" id="masteredCountPill">0 / <?= count($cards) ?> Mastered</span>
<a class="button button-quiet" href="practice.php">Take Scored Quiz ↗</a>
</div>
</div>

<!-- Subject Track Selector -->
<div class="panel" style="margin-bottom:24px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
<span style="font-weight:700;font-size:0.85rem">Curriculum Filter:</span>
<a href="flashcards.php" class="pill <?= $subjectId === 0 ? 'lime' : '' ?>" style="text-decoration:none">All Tracks</a>
<?php foreach ($subjects as $s): ?>
<a href="flashcards.php?subject=<?= $s['id'] ?>" class="pill <?= $subjectId === (int)$s['id'] ? 'lime' : '' ?>" style="text-decoration:none">
<?= e($s['name']) ?>
</a>
<?php endforeach; ?>
</div>
<small class="muted">Tip: Use <strong>Space</strong> to flip, <strong>← / →</strong> keys to navigate</small>
</div>

<?php if (empty($cards)): ?>
<div class="panel" style="text-align:center;padding:50px 20px">
<h3>No questions available in this track yet.</h3>
<p>Select another track or visit the practice studio.</p>
<a href="flashcards.php" class="button button-primary">View All Tracks</a>
</div>
<?php else: ?>

<!-- Card Deck Progress Indicator -->
<div style="max-width:650px;margin:0 auto 16px;display:flex;justify-content:space-between;align-items:center;font-size:0.85rem">
<span id="deckCounter" style="font-weight:700">Card 1 of <?= count($cards) ?></span>
<span class="muted" id="currentTopic">Concept Review</span>
</div>

<div class="progress" style="max-width:650px;margin:0 auto 24px;height:6px">
<i id="deckProgressBar" style="width:<?= round(1 / count($cards) * 100) ?>%"></i>
</div>

<!-- 3D Flippable Flashcard -->
<div class="flashcard-wrap" onclick="flipCurrentCard()">
<div class="flashcard" id="flashcardElement">
<!-- Card Front: Question & Badges -->
<div class="flashcard-face flashcard-front">
<div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
<span class="pill lime" id="cardSubjectBadge">Subject</span>
<span class="pill" id="cardDifficultyBadge">Difficulty</span>
</div>
<small class="muted" style="text-transform:uppercase;letter-spacing:0.08em;font-weight:700;font-size:0.72rem" id="cardTopicText">Topic</small>
<h2 id="cardQuestionText" style="font-size:1.45rem;line-height:1.4;margin-top:10px;font-family:'Space Grotesk',sans-serif">
Loading question...
</h2>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;padding-top:16px;border-top:1px solid var(--line);font-size:0.82rem;color:var(--muted)">
<span>✦ Click card or press <strong>Space</strong> to reveal answer</span>
<span style="font-size:1.1rem">↺</span>
</div>
</div>

<!-- Card Back: Answer & Detailed Explanation -->
<div class="flashcard-face flashcard-back">
<div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
<span class="pill" style="background:#eef6cf;color:#1e6355;font-weight:700">✓ Verified Solution</span>
<span class="pill" id="cardCorrectKeyBadge" style="background:var(--navy);color:#fff">Answer</span>
</div>

<div style="background:var(--white);padding:14px 18px;border-radius:10px;border:1px solid var(--line);margin-bottom:14px">
<strong style="color:var(--teal);display:block;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.06em">Correct Option:</strong>
<p id="cardCorrectAnswerText" style="font-size:1.1rem;font-weight:700;color:var(--ink);margin:4px 0 0">
Correct Answer Text
</p>
</div>

<div style="background:#ffffffb0;padding:14px 18px;border-radius:10px;border-left:3px solid var(--teal);font-size:0.88rem;color:var(--muted);line-height:1.6">
<strong style="color:var(--ink)">💡 Technical Rationale:</strong>
<p id="cardExplanationText" style="margin:4px 0 0">
Explanation rationale goes here.
</p>
</div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid var(--line);font-size:0.82rem;color:var(--muted)">
<span>Click card to flip back</span>
<span style="color:var(--teal);font-weight:700">Self-Evaluation Mode</span>
</div>
</div>
</div>
</div>

<!-- Navigation & Interactive Toolbar -->
<div style="max-width:650px;margin:28px auto 0;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
<button type="button" class="button button-quiet" onclick="prevCard()" id="prevBtn" style="padding:11px 20px">
← Previous
</button>

<div style="display:flex;gap:10px">
<button type="button" class="button button-quiet" onclick="flipCurrentCard()" style="padding:11px 18px">
Flip ⟳
</button>
<button type="button" class="button button-quiet" onclick="toggleMastered()" id="masterBtn" style="padding:11px 18px">
⭐ Mark Mastered
</button>
<button type="button" class="button button-quiet" onclick="shuffleCards()" title="Shuffle Deck" style="padding:11px 16px">
🔀
</button>
</div>

<button type="button" class="button button-primary" onclick="nextCard()" id="nextBtn" style="padding:11px 22px">
Next →
</button>
</div>

<script>
const cardsData = <?= json_encode($cards, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
let currentIndex = 0;
const masteredSet = new Set();

function renderCard() {
    if (!cardsData || cardsData.length === 0) return;
    const card = cardsData[currentIndex];
    const el = document.getElementById('flashcardElement');
    el.classList.remove('flipped');

    document.getElementById('cardSubjectBadge').textContent = card.subject_name;
    document.getElementById('cardDifficultyBadge').textContent = card.difficulty;
    document.getElementById('cardTopicText').textContent = card.topic_name || card.subject_code;
    document.getElementById('cardQuestionText').textContent = card.question;

    const correctKey = (card.correct_answer || 'A').toUpperCase();
    const optionTextKey = 'option_' + correctKey.toLowerCase();
    const fullOptionText = card[optionTextKey] || 'Option ' + correctKey;

    document.getElementById('cardCorrectKeyBadge').textContent = 'Option ' + correctKey;
    document.getElementById('cardCorrectAnswerText').textContent = fullOptionText;
    document.getElementById('cardExplanationText').textContent = card.explanation || 'Mastering this core syntax and architectural concept is vital for high technical assessment performance.';

    document.getElementById('deckCounter').textContent = `Card ${currentIndex + 1} of ${cardsData.length}`;
    document.getElementById('currentTopic').textContent = card.topic_name || card.subject_name;
    document.getElementById('deckProgressBar').style.width = `${((currentIndex + 1) / cardsData.length) * 100}%`;

    const masterBtn = document.getElementById('masterBtn');
    if (masteredSet.has(card.id)) {
        masterBtn.style.background = '#eef6cf';
        masterBtn.style.color = '#1e6355';
        masterBtn.innerHTML = '★ Mastered';
    } else {
        masterBtn.style.background = '';
        masterBtn.style.color = '';
        masterBtn.innerHTML = '⭐ Mark Mastered';
    }

    document.getElementById('prevBtn').disabled = (currentIndex === 0);
    document.getElementById('nextBtn').disabled = (currentIndex === cardsData.length - 1);
}

function flipCurrentCard() {
    const el = document.getElementById('flashcardElement');
    el.classList.toggle('flipped');
}

function nextCard() {
    if (currentIndex < cardsData.length - 1) {
        currentIndex++;
        renderCard();
    }
}

function prevCard() {
    if (currentIndex > 0) {
        currentIndex--;
        renderCard();
    }
}

function toggleMastered() {
    const card = cardsData[currentIndex];
    if (masteredSet.has(card.id)) {
        masteredSet.delete(card.id);
    } else {
        masteredSet.add(card.id);
    }
    document.getElementById('masteredCountPill').textContent = `${masteredSet.size} / ${cardsData.length} Mastered`;
    renderCard();
}

function shuffleCards() {
    for (let i = cardsData.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [cardsData[i], cardsData[j]] = [cardsData[j], cardsData[i]];
    }
    currentIndex = 0;
    renderCard();
}

// Keyboard navigation listeners
document.addEventListener('keydown', (e) => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if (e.code === 'Space') {
        e.preventDefault();
        flipCurrentCard();
    } else if (e.code === 'ArrowRight') {
        nextCard();
    } else if (e.code === 'ArrowLeft') {
        prevCard();
    }
});

renderCard();
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
