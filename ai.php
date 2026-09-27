<?php
// SkillRank AI Engine - Gemini 1.5 API Integration with Smart Algorithmic Fallback
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function ai_get_api_key(): string {
    if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY)) {
        return trim(GEMINI_API_KEY);
    }
    $env = getenv('GEMINI_API_KEY');
    return $env ? trim($env) : '';
}

function ai_is_available(): bool {
    $key = ai_get_api_key();
    return !empty($key) && strlen($key) >= 10;
}

/**
 * Call Gemini 1.5 Flash REST API
 */
function ai_call_gemini(string $prompt, bool $expect_json = false): ?string {
    $apiKey = ai_get_api_key();
    if (!$apiKey) return null;

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);

    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 1200
        ]
    ];

    if ($expect_json) {
        $payload['generationConfig']['responseMimeType'] = 'application/json';
    }

    $json_payload = json_encode($payload);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json_payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 9,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        if (!empty($data['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($data['candidates'][0]['content']['parts'][0]['text']);
        }
    }

    return null;
}

/**
 * Generate Multiple Choice Questions using Gemini AI or Algorithmic Generator
 */
function ai_generate_questions(string $subject, string $difficulty, string $topic, int $count = 3): array {
    $count = max(1, min(5, $count));
    
    if (ai_is_available()) {
        $prompt = "You are a senior computer science professor creating multiple-choice assessment questions for technical college students.
Generate exactly {$count} multiple-choice question(s) for the subject '{$subject}', topic '{$topic}', with difficulty '{$difficulty}'.
Respond ONLY with a valid JSON array of objects, with no markdown code fences, matching this schema:
[
  {
    \"question\": \"Question text here\",
    \"option_a\": \"Option A text\",
    \"option_b\": \"Option B text\",
    \"option_c\": \"Option C text\",
    \"option_d\": \"Option D text\",
    \"correct_answer\": \"A\",
    \"marks\": 10,
    \"explanation\": \"Clear explanation of why this answer is correct and others are wrong.\"
  }
]";

        $ai_res = ai_call_gemini($prompt, true);
        if ($ai_res) {
            $parsed = json_decode($ai_res, true);
            if (is_array($parsed) && count($parsed) > 0) {
                // Sanitize and validate
                $clean = [];
                foreach ($parsed as $item) {
                    if (!empty($item['question']) && !empty($item['option_a']) && !empty($item['option_b']) && !empty($item['correct_answer'])) {
                        $clean[] = [
                            'question' => strip_tags($item['question']),
                            'option_a' => strip_tags($item['option_a']),
                            'option_b' => strip_tags($item['option_b']),
                            'option_c' => strip_tags($item['option_c'] ?? 'None of the above'),
                            'option_d' => strip_tags($item['option_d'] ?? 'All of the above'),
                            'correct_answer' => strtoupper(trim($item['correct_answer'])),
                            'marks' => (int)($item['marks'] ?? ($difficulty === 'Hard' ? 15 : 10)),
                            'explanation' => strip_tags($item['explanation'] ?? 'Correct answer verified.')
                        ];
                    }
                }
                if (!empty($clean)) return ['source' => 'Gemini AI', 'questions' => $clean];
            }
        }
    }

    // Smart Algorithmic Fallback Generator
    $fallbackBank = [
        'PHP' => [
            [
                'question' => 'Which PHP superglobal is used to access uploaded file metadata like temporary name and file size?',
                'option_a' => '$_POST',
                'option_b' => '$_FILES',
                'option_c' => '$_ENV',
                'option_d' => '$_SERVER',
                'correct_answer' => 'B',
                'marks' => 10,
                'explanation' => '$_FILES is the associative array containing uploaded file items through HTTP POST.'
            ],
            [
                'question' => 'What is the return type of password_verify() when validating a password against a hash in PHP?',
                'option_a' => 'int (1 or 0)',
                'option_b' => 'bool (true or false)',
                'option_c' => 'string hash',
                'option_d' => 'array of user details',
                'correct_answer' => 'B',
                'marks' => 10,
                'explanation' => 'password_verify() returns boolean true if the password matches the hash, or false otherwise.'
            ],
            [
                'question' => 'Which method in MySQLi is used to bind input parameters to a prepared SQL statement in PHP?',
                'option_a' => 'bind_param()',
                'option_b' => 'bind_value()',
                'option_c' => 'set_param()',
                'option_d' => 'attach_var()',
                'correct_answer' => 'A',
                'marks' => 15,
                'explanation' => 'mysqli_stmt::bind_param() binds variables to parameter markers of the prepared statement.'
            ]
        ],
        'JavaScript' => [
            [
                'question' => 'Which array method in JavaScript creates a new array populated with the results of calling a provided function on every element?',
                'option_a' => 'forEach()',
                'option_b' => 'map()',
                'option_c' => 'filter()',
                'option_d' => 'reduce()',
                'correct_answer' => 'B',
                'marks' => 10,
                'explanation' => 'map() transforms each element and returns an array of identical length with transformed values.'
            ],
            [
                'question' => 'What will typeof NaN return in modern JavaScript?',
                'option_a' => '"nan"',
                'option_b' => '"number"',
                'option_c' => '"undefined"',
                'option_d' => '"object"',
                'correct_answer' => 'B',
                'marks' => 15,
                'explanation' => 'NaN stands for Not-a-Number, but its official JavaScript ECMAScript type is numeric ("number").'
            ],
            [
                'question' => 'How does the event loop handle microtasks (e.g. resolved Promises) compared to macrotasks (e.g. setTimeout)?',
                'option_a' => 'Macrotasks always execute before microtasks',
                'option_b' => 'Microtasks have priority and run immediately after the current script finishes before the next macrotask',
                'option_c' => 'They run simultaneously on separate CPU threads',
                'option_d' => 'They are queued in random order',
                'correct_answer' => 'B',
                'marks' => 15,
                'explanation' => 'The microtask queue is emptied completely between task executions, giving Promises higher execution priority.'
            ]
        ],
        'DBMS' => [
            [
                'question' => 'In relational database design, which anomaly occurs when deleting one piece of data inadvertently deletes unrelated data?',
                'option_a' => 'Insertion anomaly',
                'option_b' => 'Deletion anomaly',
                'option_c' => 'Update anomaly',
                'option_d' => 'Deadlock anomaly',
                'correct_answer' => 'B',
                'marks' => 10,
                'explanation' => 'A deletion anomaly occurs when unintended loss of data happens due to unnormalized table structures.'
            ],
            [
                'question' => 'What type of lock prevents other concurrent transactions from reading or writing a locked row?',
                'option_a' => 'Shared Lock (S-Lock)',
                'option_b' => 'Exclusive Lock (X-Lock)',
                'option_c' => 'Optimistic Lock',
                'option_d' => 'Intent Lock',
                'correct_answer' => 'B',
                'marks' => 15,
                'explanation' => 'Exclusive locks (X-Locks) guarantee only one transaction has write access and forbid both reads and writes from others.'
            ]
        ],
        'HTML & CSS' => [
            [
                'question' => 'Which CSS unit is relative to the font-size of the root element (<html>)?',
                'option_a' => 'em',
                'option_b' => 'rem',
                'option_c' => 'vh',
                'option_d' => 'pt',
                'correct_answer' => 'B',
                'marks' => 10,
                'explanation' => 'rem stands for Root EM and computes sizes relative to the root <html> font-size (default 16px).'
            ],
            [
                'question' => 'In CSS Grid, what does the repeat(auto-fit, minmax(200px, 1fr)) expression achieve?',
                'option_a' => 'Forces exactly two columns at all times',
                'option_b' => 'Creates a fully responsive grid that wraps columns automatically without media queries',
                'option_c' => 'Limits grid width to 200px',
                'option_d' => 'Hides overflow items',
                'correct_answer' => 'B',
                'marks' => 15,
                'explanation' => 'auto-fit with minmax is the modern CSS standard for flexible, responsive multi-column layouts.'
            ]
        ]
    ];

    $matched = $fallbackBank[$subject] ?? $fallbackBank['PHP'];
    $sliced = array_slice($matched, 0, $count);
    return ['source' => 'SkillRank Engine (Pre-Curated)', 'questions' => $sliced];
}

/**
 * Synthesize Student Performance & Skill Gap Analysis
 */
function ai_analyze_student_performance(int $user_id): array {
    $skills = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score DESC', 'i', [$user_id]);
    $attempts = many('SELECT s.name as subject, qa.score, qa.total_marks, qa.accuracy, qa.created_at FROM quiz_attempts qa JOIN subjects s ON s.id=qa.subject_id WHERE qa.user_id=? ORDER BY qa.created_at DESC LIMIT 6', 'i', [$user_id]);
    $stats = one('SELECT COALESCE(COUNT(*),0) total_tests, COALESCE(SUM(questions_count),0) solved, COALESCE(ROUND(AVG(accuracy)),0) avg_accuracy FROM quiz_attempts WHERE user_id=?', 'i', [$user_id]);

    if (!$skills && !$attempts) {
        return [
            'status' => 'no_data',
            'summary' => 'Complete your first practice sprint to generate real-time AI skill diagnostics and recommendations.',
            'strengths' => [],
            'gaps' => [],
            'recommendation' => 'Start with the PHP or Web Fundamentals tracks to build your baseline score.',
            'engine' => 'Ready'
        ];
    }

    $strongest = $skills[0] ?? null;
    $weakest = end($skills);
    if ($strongest && $weakest && $strongest['name'] === $weakest['name'] && count($skills) === 1) {
        $weakest = null;
    }

    // Try Gemini if active
    if (ai_is_available()) {
        $skills_txt = json_encode($skills);
        $attempts_txt = json_encode($attempts);
        $prompt = "You are an expert technical career advisor for university computer science students.
Here is the student's verified assessment data:
Verified Skills: $skills_txt
Recent Test Attempts: $attempts_txt
Overall Stats: Total tests: {$stats['total_tests']}, Questions solved: {$stats['solved']}, Avg accuracy: {$stats['avg_accuracy']}%.

Provide an insightful diagnostic evaluation in valid JSON with these keys:
{
  \"summary\": \"2 concise sentences summarizing their technical readiness and momentum.\",
  \"strength_insight\": \"Specific compliment on their highest scoring skill and why it matters in industry.\",
  \"gap_insight\": \"Constructive critique of their lowest scoring area with the exact technical concept to study.\",
  \"action_plan\": \"Specific recommendation for what topic/assessment they should tackle next to level up.\"
}";
        $res = ai_call_gemini($prompt, true);
        if ($res) {
            $parsed = json_decode($res, true);
            if ($parsed && !empty($parsed['summary'])) {
                return [
                    'status' => 'success',
                    'summary' => $parsed['summary'],
                    'strength_insight' => $parsed['strength_insight'] ?? '',
                    'gap_insight' => $parsed['gap_insight'] ?? '',
                    'action_plan' => $parsed['action_plan'] ?? '',
                    'strongest' => $strongest,
                    'weakest' => $weakest,
                    'engine' => 'Gemini 1.5 Flash'
                ];
            }
        }
    }

    // Smart Algorithmic Diagnostic Engine
    $strong_name = $strongest ? $strongest['name'] : 'Web Technologies';
    $strong_score = $strongest ? round($strongest['score']) : 80;
    $weak_name = $weakest ? $weakest['name'] : 'DBMS';
    $weak_score = $weakest ? round($weakest['score']) : 60;

    $summary = "You have established strong foundational accuracy ({$stats['avg_accuracy']}%) across {$stats['solved']} solved questions. Your verified competencies show strong aptitude in $strong_name with high accuracy.";
    $strength_insight = "Demonstrates verified {$strongest['level']} proficiency in $strong_name ($strong_score%), which is a core industry prerequisite for modern web architectures.";
    $gap_insight = "$weak_name is currently your primary growth vector at $weak_score%. Closing this gap through focused practice on schema design and indexing will round out your full-stack readiness.";
    $action_plan = "Complete a 10-question sprint in $weak_name to raise your competency level to Advanced before your next career review.";

    return [
        'status' => 'success',
        'summary' => $summary,
        'strength_insight' => $strength_insight,
        'gap_insight' => $gap_insight,
        'action_plan' => $action_plan,
        'strongest' => $strongest,
        'weakest' => $weakest,
        'engine' => 'SkillRank Smart Engine'
    ];
}

/**
 * Generate a Professional Skill-Based Resume Summary
 */
function ai_generate_resume_summary(int $user_id): string {
    $user = one('SELECT u.name, s.course, s.university, s.semester, s.career_goal FROM users u LEFT JOIN students s ON s.user_id=u.id WHERE u.id=?', 'i', [$user_id]);
    $skills = many('SELECT sk.name, ss.score, ss.level FROM student_skills ss JOIN skills sk ON sk.id=ss.skill_id WHERE ss.user_id=? ORDER BY ss.score DESC', 'i', [$user_id]);
    $stats = one('SELECT COALESCE(SUM(questions_count),0) solved, COALESCE(ROUND(AVG(accuracy)),0) accuracy FROM quiz_attempts WHERE user_id=?', 'i', [$user_id]);

    $name = $user['name'] ?? 'Candidate';
    $course = $user['course'] ?? 'Computer Applications';
    $goal = $user['career_goal'] ?: 'Full-Stack Software Engineer';

    $topSkills = array_slice($skills, 0, 3);
    $skillsList = implode(', ', array_map(function($s) {
        return "{$s['name']} ({$s['level']})";
    }, $topSkills));

    if (ai_is_available()) {
        $prompt = "You are a professional resume writer specializing in entry-level computer science & BCA university candidates.
Write a powerful 2 to 3 sentence resume summary for a candidate with the following verified credentials:
Name: $name
Degree: $course, {$user['university']}
Target Role: $goal
Verified Skills: $skillsList
Questions Solved: {$stats['solved']}, Overall Accuracy: {$stats['accuracy']}%

Guidelines:
- Keep it punchy, professional, and results-oriented.
- Highlight their verified technical skills and problem-solving readiness.
- Do NOT use buzzwords like 'synergy' or 'go-getter'.
- Return plain text only, no quotes, no markdown.";

        $ai_res = ai_call_gemini($prompt, false);
        if ($ai_res && strlen($ai_res) > 40) {
            return trim($ai_res);
        }
    }

    // High quality template fallback
    $skill_phrase = $skillsList ? "with demonstrated proficiency in $skillsList" : "with strong foundations in modern software development";
    return "Results-driven $course student at {$user['university']} $skill_phrase. Verified by SkillRank with an average assessment accuracy of {$stats['accuracy']}% across {$stats['solved']}+ technical challenges. Eager to contribute hands-on problem solving skills as an entry-level $goal.";
}

/**
 * Render visual badge for AI status
 */
function ai_badge_html(): string {
    if (ai_is_available()) {
        return '<span class="pill lime" title="Active Gemini 1.5 API"><span style="color:#d97706">✦</span> Gemini AI Active</span>';
    }
    return '<span class="pill" title="Running in fast algorithmic mode. Set GEMINI_API_KEY to activate generative features.">⚡ Smart Engine (AI Ready)</span>';
}

/**
 * Diagnostic ping test for Gemini API
 */
function ai_test_connection(): array {
    if (!ai_is_available()) {
        return [
            'status' => 'offline_fallback',
            'message' => 'No Gemini API key configured. System is operating on the built-in procedural algorithmic engine.',
            'model' => 'SkillRank Smart Algorithmic Engine (Local)',
            'latency' => 0
        ];
    }
    $start = microtime(true);
    $res = ai_call_gemini("Reply with only the word: PONG", false);
    $latency = round((microtime(true) - $start) * 1000);
    if ($res) {
        return [
            'status' => 'online',
            'message' => "Successfully connected to Google Gemini API (Ping: {$latency}ms).",
            'model' => 'gemini-1.5-flash',
            'latency' => $latency
        ];
    }
    return [
        'status' => 'error',
        'message' => 'Gemini API call timed out or returned invalid response. Gracefully falling back to local engine.',
        'model' => 'SkillRank Smart Algorithmic Engine (Fallback)',
        'latency' => $latency
    ];
}

