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
    if (!$user) {
        header('Location: ' . ($role === 'admin' ? 'admin_login.php' : 'login.php'));
        exit;
    }
    // Admin has superuser access to everything
    if ($user['role'] === 'admin') {
        return;
    }
    // Students cannot access admin-only features
    if ($role === 'admin' && $user['role'] !== 'admin') {
        header('Location: admin_login.php');
        exit;
    }
}

// Role-based ID Scoper: Students can ONLY access their own profile; Admins can access any student's profile
function get_scoped_student_id(): int {
    $user = current_user();
    if (!$user) return 0;
    if ($user['role'] === 'student') {
        return (int)$user['id']; // Strictly locked to their own account
    }
    // Admin can inspect requested student or default to first student
    if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
        return (int)$_GET['id'];
    }
    return (int)$user['id'];
}

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path); exit; }
