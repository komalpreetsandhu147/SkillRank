<?php
// SkillRank Automatic Cloud Database Initializer (Railway / Cloud Deployments)
require_once __DIR__ . '/config.php';

echo ">>> Checking SkillRank Cloud Database Connection...\n";
echo "Host: " . DB_HOST . ":" . DB_PORT . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";

$maxRetries = 15;
$retryDelay = 2;
$conn = null;

// Wait for MySQL to become available
for ($i = 1; $i <= $maxRetries; $i++) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
        if (!$conn->connect_errno) {
            echo "Connected to MySQL server on attempt $i.\n";
            break;
        }
    } catch (Throwable $e) {
        // Retry
    }
    echo "Waiting for MySQL server ($i/$maxRetries)...\n";
    sleep($retryDelay);
}

if (!$conn || $conn->connect_errno) {
    echo "WARNING: Could not connect to MySQL server. Skipping auto-migration.\n";
    exit(0);
}

// Ensure database exists
$dbName = DB_NAME;
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbName);

// Check if users table already exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    echo "Database tables already exist. Skipping seed import.\n";
} else {
    echo "Initializing database schema from database.sql...\n";
    $sqlFile = __DIR__ . '/database.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        if ($conn->multi_query($sql)) {
            do {
                if ($res = $conn->store_result()) {
                    $res->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            echo "Schema and seeds imported successfully.\n";
        } else {
            echo "Error running schema: " . $conn->error . "\n";
        }
    }
}

// Run db_setup.php repair to ensure password hashes and full 50 questions
if (file_exists(__DIR__ . '/db_setup.php')) {
    echo "Running db_setup.php verification...\n";
    include_once __DIR__ . '/db_setup.php';
}

echo ">>> SkillRank Cloud Database is Ready!\n";
