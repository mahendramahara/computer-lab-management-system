<?php
require_once __DIR__ . '/config/db.php';

// Unset all session variables
$_SESSION = [];

// Clear remember cookies
$cookieNames = ['clms_user_id', 'clms_admin_id', 'remember_token'];
foreach ($cookieNames as $cName) {
    setcookie($cName, '', [
        'expires'  => time() - 86400 * 7,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    if (isset($_COOKIE[$cName])) {
        unset($_COOKIE[$cName]);
    }
}

// Delete PHP session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redirect to login page
header("Location: " . BASE_URL . "login.php");
exit;
