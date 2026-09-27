<?php
require_once __DIR__ . '/auth.php';
$u = current_user();
if ($u && $u['role'] === 'admin') {
    redirect('admin_analytics.php');
} elseif ($u) {
    redirect('analytics.php');
} else {
    redirect('login.php');
}