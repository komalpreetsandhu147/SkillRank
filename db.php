<?php
require_once __DIR__ . '/config.php';

function db(): ?mysqli {
    static $connection = null;
    static $attempted = false;

    if ($attempted) {
        return $connection;
    }

    $attempted = true;
    try {
        $port = defined('DB_PORT') ? (int)DB_PORT : 3306;
        $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $name = defined('DB_NAME') ? DB_NAME : 'skillrank';

        // Suppress driver exception bubbling on initial boot
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($host, $user, $pass, $name, $port);
        if ($conn && !$conn->connect_errno) {
            $conn->set_charset('utf8mb4');
            $connection = $conn;
        } else {
            error_log('SkillRank Database Notice: Unable to connect to MySQL database at ' . $host . ':' . $port);
            $connection = null;
        }
    } catch (Throwable $e) {
        error_log('SkillRank Database Notice: ' . $e->getMessage());
        $connection = null;
    }

    return $connection;
}

function last_insert_id(): int {
    $conn = db();
    return $conn ? (int)$conn->insert_id : 0;
}

function query(string $sql, string $types = '', array $params = []): mysqli_result|bool {
    $db = db();
    if (!$db) return false;
    try {
        $statement = $db->prepare($sql);
        if (!$statement) return false;
        if ($types && $params) $statement->bind_param($types, ...$params);
        $statement->execute();
        return $statement->get_result() ?: true;
    } catch (Throwable $e) {
        error_log('SkillRank Query Notice: ' . $e->getMessage());
        return false;
    }
}

function one(string $sql, string $types = '', array $params = []): ?array {
    $result = query($sql, $types, $params);
    return $result instanceof mysqli_result ? ($result->fetch_assoc() ?: null) : null;
}

function many(string $sql, string $types = '', array $params = []): array {
    $result = query($sql, $types, $params);
    return $result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
