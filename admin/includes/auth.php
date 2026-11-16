<?php
// Strict Admin Authentication Guard
require_once __DIR__ . '/../../config/db.php';

// Allowed administrative roles
$allowedRoles    = ['admin', 'lab_assistant'];
$currentUserRole = $_SESSION['user']['role'] ?? ($_SESSION['user_role'] ?? '');
$currentUserId   = (int)($_SESSION['user']['id'] ?? ($_SESSION['user_id'] ?? 0));

// If not authenticated as an admin or lab assistant, immediately redirect to admin login
if ($currentUserId <= 0 || !in_array($currentUserRole, $allowedRoles, true)) {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}
