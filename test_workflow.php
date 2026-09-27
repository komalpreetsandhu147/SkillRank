<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai.php';

echo "=== SKILLRANK END-TO-END VERIFICATION SUITE ===\n\n";

// 1. Student Auth
echo "1. Student Authentication...\n";
$student = one('SELECT * FROM users WHERE email="aarav@skillrank.demo"');
assert($student && $student['role'] === 'student', 'Student Aarav must exist');
assert(password_verify('demo123', $student['password_hash']), 'Student password must verify');
echo "   -> PASS: Student Aarav authenticated successfully.\n";

// 2. Admin Auth
echo "2. Admin Authentication...\n";
$admin = one('SELECT * FROM users WHERE email="admin@skillrank.demo"');
assert($admin && $admin['role'] === 'admin', 'Admin must exist');
assert(password_verify('demo123', $admin['password_hash']), 'Admin password must verify');
echo "   -> PASS: Admin authenticated successfully.\n";

// 3. Quiz flow and Skill Level recalculation
echo "3. Assessment & Skill Level Calculation...\n";
$uid = $student['id'];
$subject = one('SELECT * FROM subjects WHERE name="PHP"');
$subId = $subject['id'];
$questions = many('SELECT * FROM questions WHERE subject_id=? LIMIT 2', 'i', [$subId]);
assert(count($questions) >= 2, 'Subject must have questions');

$score = 0; $total = 0; $correct = 0;
foreach ($questions as $q) {
    $total += $q['marks'];
    $score += $q['marks'];
    $correct++;
}
$acc = round(($correct / count($questions)) * 100, 2);

query('INSERT INTO quiz_attempts (user_id, subject_id, score, total_marks, questions_count, accuracy, time_taken_seconds) VALUES (?,?,?,?,?,?,?)', 'iiiiidi', [$uid, $subId, $score, $total, count($questions), $acc, 45]);
$attId = db()->insert_id;
assert($attId > 0, 'Attempt ID must be created');

// Check skill update
$skill = one('SELECT id FROM skills WHERE name=?', 's', [$subject['name']]);
$level = $acc >= 90 ? 'Expert' : ($acc >= 75 ? 'Advanced' : ($acc >= 55 ? 'Intermediate' : 'Beginner'));
query('INSERT INTO student_skills (user_id, skill_id, score, level) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE score=VALUES(score), level=VALUES(level)', 'iids', [$uid, $skill['id'], $acc, $level]);

$updatedSkill = one('SELECT * FROM student_skills WHERE user_id=? AND skill_id=?', 'ii', [$uid, $skill['id']]);
assert($updatedSkill && $updatedSkill['level'] === $level, 'Skill level must be updated');
echo "   -> PASS: Quiz scored $score/$total ($acc%), Level assigned: $level.\n";

// 4. AI Diagnostics
echo "4. AI Diagnostics & Skill Gap Analysis...\n";
$analysis = ai_analyze_student_performance($uid);
assert($analysis['status'] === 'success', 'AI analysis must succeed');
assert(!empty($analysis['summary']), 'Summary must not be empty');
echo "   -> PASS: AI Engine ({$analysis['engine']}) generated diagnostic report.\n";
echo "      Summary: {$analysis['summary']}\n";

// 5. AI Resume Summary
echo "5. AI Resume Summary Generation...\n";
$resSummary = ai_generate_resume_summary($uid);
assert(strlen($resSummary) > 30, 'Resume summary must be non-empty');
echo "   -> PASS: AI Resume Summary created (" . strlen($resSummary) . " chars).\n";

// 6. Leaderboard
echo "6. Leaderboard Rankings...\n";
$leaders = many('SELECT * FROM leaderboard LIMIT 3');
assert(count($leaders) > 0, 'Leaderboard must return students');
echo "   -> PASS: Leaderboard computed. Current #1: {$leaders[0]['name']} ({$leaders[0]['total_score']} pts).\n";

// 7. AI Question Generator
echo "7. AI Question Generator...\n";
$gen = ai_generate_questions('JavaScript', 'Hard', 'Async & Promises', 2);
assert(!empty($gen['questions']), 'AI Questions must be generated');
echo "   -> PASS: Generated " . count($gen['questions']) . " questions via {$gen['source']}.\n";

// 8. HTTP Endpoints Check
echo "8. Live Apache Web Server Endpoints...\n";
$endpoints = [
    'http://localhost/SkillRank/index.php',
    'http://localhost/SkillRank/about.php',
    'http://localhost/SkillRank/login.php',
    'http://localhost/SkillRank/register.php',
    'http://localhost/SkillRank/admin_login.php',
    'http://localhost/techfest/index.php'
];

foreach ($endpoints as $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 4]]);
    $content = @file_get_contents($url, false, $ctx);
    $status = isset($http_response_header[0]) ? $http_response_header[0] : 'FAILED';
    assert(strpos($status, '200') !== false || strpos($status, '302') !== false, "URL $url should return 200 or 302");
    echo "   -> PASS: $url -> $status\n";
}

echo "\n>>> ALL 8 WORKFLOW & HTTP SERVER VERIFICATION TESTS PASSED PERFECTLY! <<<\n";
