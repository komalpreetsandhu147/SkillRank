<?php
// Question Bank JSON Exporter - Admin
require_once __DIR__ . '/auth.php';
require_login('admin');

$questions = many(
    'SELECT q.id, s.name as subject, s.code as subject_code, t.name as topic, 
            q.difficulty, q.question, q.option_a, q.option_b, q.option_c, q.option_d, 
            q.correct_answer, q.marks, q.explanation 
     FROM questions q 
     JOIN subjects s ON s.id=q.subject_id 
     LEFT JOIN topics t ON t.id=q.topic_id 
     ORDER BY q.subject_id, q.id'
);

$filename = 'skillrank_question_bank_' . date('Y-m-d') . '.json';
header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
