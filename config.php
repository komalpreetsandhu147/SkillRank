<?php
// SkillRank System Configuration - Hybrid Local XAMPP & Railway/Cloud Support
$rawDbHost = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: '127.0.0.1';
$rawDbPort = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';
$rawDbName = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'skillrank';
$rawDbUser = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$rawDbPass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';

// Auto-parse DATABASE_URL if present (e.g., standard Railway/Heroku MySQL addon format)
if ($dbUrl = getenv('DATABASE_URL')) {
    $parsed = parse_url($dbUrl);
    if ($parsed) {
        $rawDbHost = $parsed['host'] ?? $rawDbHost;
        $rawDbPort = $parsed['port'] ?? $rawDbPort;
        $rawDbUser = $parsed['user'] ?? $rawDbUser;
        $rawDbPass = $parsed['pass'] ?? $rawDbPass;
        $rawDbName = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $rawDbName;
    }
}

defined('DB_HOST') or define('DB_HOST', $rawDbHost);
defined('DB_PORT') or define('DB_PORT', (int)$rawDbPort);
defined('DB_NAME') or define('DB_NAME', $rawDbName);
defined('DB_USER') or define('DB_USER', $rawDbUser);
defined('DB_PASS') or define('DB_PASS', $rawDbPass);
defined('APP_NAME') or define('APP_NAME', 'SkillRank');

// Google Gemini API Key for Generative AI Features
// Leave blank to run in fast smart algorithmic mode, or enter your key from Google AI Studio.
defined('GEMINI_API_KEY') or define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
