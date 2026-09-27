<?php
// SkillRank AI Mentor Chatbot AJAX Endpoint
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? ($_POST['message'] ?? ''));

if (empty($message)) {
    echo json_encode(['error' => 'Message is empty']);
    exit;
}

// Fetch user's verified skills for personalized context
$skills = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=?', 'i', [$user['id']]);
$skillsStr = implode(', ', array_map(fn($s) => "{$s['name']}: {$s['level']} ({$s['score']}%)", $skills));

$reply = '';

if (ai_is_available()) {
    $prompt = "You are SkillRank AI Mentor, an empathetic, highly knowledgeable computer science faculty mentor and technical career advisor for university students.
Student Name: {$user['name']}
Enrolled Program: {$user['course']}, {$user['university']}
Current Verified Skills: $skillsStr

Student asked:
\"$message\"

Guidelines:
1. Provide a clear, technically accurate, encouraging response (max 3 short paragraphs).
2. If asking about a technical concept (e.g. SQL, PHP, JS, Normalization), provide a short code snippet or clear explanation.
3. If asking for career or skill advice, reference their verified skills and suggest targeted practice.
4. Keep the tone inspiring and concise.";

    $aiReply = ai_call_gemini($prompt, false);
    if ($aiReply && strlen($aiReply) > 20) {
        $reply = $aiReply;
    }
}

// Procedural Knowledge Base Fallback if Gemini API is not configured or offline
if (empty($reply)) {
    $m = strtolower($message);

    if (str_contains($m, 'normalization') || str_contains($m, '1nf') || str_contains($m, '2nf') || str_contains($m, '3nf') || str_contains($m, 'dbms')) {
        $reply = "Database Normalization organizes tables to reduce redundancy and eliminate update/delete anomalies:\n\n• **1NF**: Atomic values (no repeating groups).\n• **2NF**: In 1NF and all non-key attributes fully depend on the primary key.\n• **3NF**: In 2NF and no transitive dependencies (non-key columns depending on other non-key columns).\n\n💡 *SkillRank Tip: Review our DBMS track questions on Joins and Schema Design to boost your score!*";
    } elseif (str_contains($m, 'promise') || str_contains($m, 'async') || str_contains($m, 'await') || str_contains($m, 'javascript') || str_contains($m, 'js')) {
        $reply = "In JavaScript, a **Promise** represents an asynchronous operation with three states: *pending, fulfilled, or rejected*.\n\n```js\n// Example using async/await\nasync function loadData() {\n  try {\n    const res = await fetch('/api/skills');\n    const data = await res.json();\n    console.log(data);\n  } catch (err) {\n    console.error('Fetch failed:', err);\n  }\n}\n```\n\n💡 *Promise.all()* runs multiple promises concurrently and resolves when all succeed!";
    } elseif (str_contains($m, 'php') || str_contains($m, 'post') || str_contains($m, 'sql injection') || str_contains($m, 'prepared')) {
        $reply = "In modern PHP, always use **Prepared Statements** with PDO or MySQLi to prevent SQL injection completely:\n\n```php\n\$stmt = \$db->prepare('SELECT * FROM users WHERE email = ?');\n\$stmt->bind_param('s', \$email);\n\$stmt->execute();\n```\n\nPrepared statements separate query structure from user parameters, rendering SQL injection impossible.";
    } elseif (str_contains($m, 'gap') || str_contains($m, 'weak') || str_contains($m, 'improve') || str_contains($m, 'score')) {
        $weakest = end($skills);
        $weakName = $weakest['name'] ?? 'DBMS';
        $weakScore = round($weakest['score'] ?? 60);
        $reply = "Based on your verified SkillRank assessment data, your primary area for improvement is **$weakName** (current score: $weakScore%).\n\n**3-Step Sprint Plan:**\n1. Take a 5-question sprint in $weakName from the Practice page.\n2. Read the explanation for every question you miss.\n3. Retake the test after 24 hours to reinforce retention and reach Advanced tier!";
    } elseif (str_contains($m, 'resume') || str_contains($m, 'career') || str_contains($m, 'job')) {
        $reply = "To make your resume stand out for recruiters:\n\n1. **Highlight Verified Skills**: Your SkillRank verified scores prove hands-on competency over self-claimed keywords.\n2. **Include Technical Projects**: Detail the architecture, technologies used (e.g. PHP, MySQL, CSS Grid), and problem solved.\n3. **Use the PDF Export**: Go to 'Skill Resume' in your sidebar and click 'Print / Save PDF' for an ATS-formatted 1-page resume!";
    } else {
        $reply = "Hello {$user['name']}! As your SkillRank AI Mentor, I can help you understand tricky concepts in PHP, JavaScript, DBMS, and Web Architecture, explain questions you missed, or guide your skill progression.\n\nTry asking:\n• *'Explain 3NF Normalization with an example'*\n• *'How do async/await and Promises work in JS?'*\n• *'What is my biggest skill gap right now?'*";
    }
}

echo json_encode([
    'status' => 'success',
    'reply' => $reply,
    'source' => ai_is_available() ? 'Gemini 1.5 Flash' : 'SkillRank AI Engine (Offline)'
]);
