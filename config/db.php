<?php
// Session initialization with standard secure cookie settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL for links and assets
if (!defined('BASE_URL')) {
    define('BASE_URL', '/computer-lab-management-system/');
}

// Production configuration: development autofill disabled
if (!defined('DEV_MODE')) {
    define('DEV_MODE', false);
    define('DEV_ROLE', '');
    define('DEV_USERNAME', '');
    define('DEV_PASSWORD', '');
}

// Database configuration
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "lab_management_system_db";

try {
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// ── Cookie-based Auto-Login ("Remember Me") ────────────────────
if (!isset($_SESSION['user'])) {
    $rememberId = 0;
    if (isset($_COOKIE['clms_admin_id'])) {
        $rememberId = (int)$_COOKIE['clms_admin_id'];
    } elseif (isset($_COOKIE['clms_user_id'])) {
        $rememberId = (int)$_COOKIE['clms_user_id'];
    }

    if ($rememberId > 0) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param('i', $rememberId);
        $stmt->execute();
        $res = $stmt->get_result();
        $cookieUser = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if ($cookieUser) {
            $_SESSION['user'] = $cookieUser;
            $_SESSION['user']['full_name'] = $cookieUser['name'];
            $_SESSION['user_id']   = $cookieUser['id'];
            $_SESSION['user_role'] = $cookieUser['role'];
            $_SESSION['user_name'] = $cookieUser['name'];
        }
    }
}

// ── Session user attribute aliases ────────────────────────────
if (isset($_SESSION['user'])) {
    if (!isset($_SESSION['user']['full_name']) && isset($_SESSION['user']['name'])) {
        $_SESSION['user']['full_name'] = $_SESSION['user']['name'];
    }
    if (!isset($_SESSION['user_id']))   $_SESSION['user_id']   = $_SESSION['user']['id'];
    if (!isset($_SESSION['user_role'])) $_SESSION['user_role'] = $_SESSION['user']['role'];
    if (!isset($_SESSION['user_name'])) $_SESSION['user_name'] = $_SESSION['user']['name'];
}

// ── Standard Helper Functions ─────────────────────────────────
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function getRows($conn, $sql, $params = [], $types = "") {
    if (empty($params)) {
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if ($types && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    return [];
}

function showMessage(): void {
    if (!empty($_SESSION['flash'])) {
        $type = e($_SESSION['flash']['type'] ?? 'info');
        $msg  = e($_SESSION['flash']['message'] ?? '');
        echo "<div class=\"alert alert-{$type}\">{$msg}</div>";
        unset($_SESSION['flash']);
    }
}

function statusLabel(string $role): string {
    return match ($role) {
        'admin'         => 'Administrator',
        'lab_assistant' => 'Lab Assistant',
        'student'       => 'Student',
        default         => ucfirst($role),
    };
}
