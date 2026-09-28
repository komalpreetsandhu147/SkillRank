<?php
// SkillRank Health Check Endpoint for Railway / Cloud Monitoring
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

$dbStatus = 'disconnected';
$dbHost = defined('DB_HOST') ? DB_HOST : (getenv('MYSQLHOST') ?: '127.0.0.1');

try {
    require_once __DIR__ . '/db.php';
    if (db()) {
        $dbStatus = 'connected';
    }
} catch (Throwable $e) {
    $dbStatus = 'error: ' . $e->getMessage();
}

echo json_encode([
    'status' => 'healthy',
    'app' => 'SkillRank',
    'version' => '1.0.0',
    'database' => $dbStatus,
    'db_host' => $dbHost,
    'timestamp' => time(),
    'php' => PHP_VERSION
], JSON_PRETTY_PRINT);
exit;
