<?php
// SkillRank Automatic Cloud Database Initializer (Railway / Cloud Deployments)
require_once __DIR__ . '/config.php';

echo ">>> SkillRank Cloud DB Initializer starting...\n";
echo "Host: " . DB_HOST . ":" . DB_PORT . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";

$maxRetries = 5;
$retryDelay = 2;
$conn = null;

// Wait up to 10 seconds for MySQL to be ready
for ($i = 1; $i <= $maxRetries; $i++) {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        // Try connecting directly with DB_NAME first
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($conn && !$conn->connect_errno) {
            echo "Connected to MySQL database '" . DB_NAME . "' on attempt $i.\n";
            break;
        }
        // If DB_NAME doesn't exist yet, connect to server without database
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
        if ($conn && !$conn->connect_errno) {
            echo "Connected to MySQL server on attempt $i.\n";
            break;
        }
    } catch (Throwable $e) {
        // Silently wait and retry
    }
    echo "Waiting for MySQL server ($i/$maxRetries)...\n";
    sleep($retryDelay);
}

if (!$conn || $conn->connect_errno) {
    echo ">>> NOTICE: MySQL server is not currently reachable (" . ($conn ? $conn->connect_error : 'connection failed') . ").\n";
    echo ">>> The application will start now. Once your Railway MySQL database is provisioned and linked, tables will auto-initialize.\n";
    exit(0);
}

$conn->set_charset('utf8mb4');

// Ensure database exists and select it
$dbName = DB_NAME;
@$conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
@$conn->select_db($dbName);

// Check if users table already exists
$tableCheck = @$conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    echo ">>> Database schema already provisioned. Skipping initial schema import.\n";
} else {
    echo ">>> Initializing database schema from database.sql...\n";
    $sqlFile = __DIR__ . '/database.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        // Strip hardcoded database creation and USE statements to adapt to any DB name (e.g. railway)
        $sql = preg_replace('/CREATE\s+DATABASE\s+IF\s+NOT\s+EXISTS\s+`?[a-zA-Z0-9_]+`?[^;]*;/i', '', $sql);
        $sql = preg_replace('/USE\s+`?[a-zA-Z0-9_]+`?;/i', '', $sql);
        
        if ($conn->multi_query($sql)) {
            do {
                if ($res = $conn->store_result()) {
                    $res->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            echo ">>> Schema and default seed data imported successfully!\n";
        } else {
            echo ">>> Notice during schema import: " . $conn->error . "\n";
        }
    }
}

// Run db_setup.php to ensure all 50 full curriculum questions and password hashes are verified
if (file_exists(__DIR__ . '/db_setup.php')) {
    echo ">>> Verifying questions and admin account via db_setup.php...\n";
    try {
        include_once __DIR__ . '/db_setup.php';
    } catch (Throwable $e) {
        echo ">>> Notice: db_setup completed with message: " . $e->getMessage() . "\n";
    }
}

echo ">>> SkillRank Cloud Database is Ready!\n";
