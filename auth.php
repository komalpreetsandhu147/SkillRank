<?php
require_once __DIR__ . '/db.php';

function current_user(bool $refresh = false): ?array {
    static $user;
    if ($refresh) $user = null;
    if (isset($user)) return $user;
    $user = !empty($_SESSION['user_id'])
        ? one('SELECT u.*, s.university, s.course, s.semester, s.phone, s.avatar, s.bio, s.career_goal FROM users u LEFT JOIN students s ON s.user_id=u.id WHERE u.id=?', 'i', [(int) $_SESSION['user_id']])
        : null;
    return $user;
}

function require_login(string $role = ''): void {
    $user = current_user();
    if (!$user || ($role && $user['role'] !== $role)) {
        header('Location: ' . ($role === 'admin' ? 'admin_login.php' : 'login.php'));
        exit;
    }
}

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path); exit; }
