<?php
require_once __DIR__ . '/config.php';

function db(): mysqli {
    static $connection;
    if (!$connection) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($connection->connect_errno) {
            die('Database connection failed. Import database.sql and confirm XAMPP MySQL is running.');
        }
        $connection->set_charset('utf8mb4');
    }
    return $connection;
}

function query(string $sql, string $types = '', array $params = []): mysqli_result|bool {
    $statement = db()->prepare($sql);
    if (!$statement) return false;
    if ($types && $params) $statement->bind_param($types, ...$params);
    $statement->execute();
    return $statement->get_result() ?: true;
}

function one(string $sql, string $types = '', array $params = []): ?array {
    $result = query($sql, $types, $params);
    return $result instanceof mysqli_result ? ($result->fetch_assoc() ?: null) : null;
}

function many(string $sql, string $types = '', array $params = []): array {
    $result = query($sql, $types, $params);
    return $result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
