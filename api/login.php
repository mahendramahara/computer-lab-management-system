<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR roll_number = ?) AND status = 'active' LIMIT 1");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $userRow = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if ($userRow && password_verify($password, $userRow['password'])) {
            $_SESSION['user'] = $userRow;
            $_SESSION['user']['full_name'] = $userRow['name'];

            if ($remember) {
                setcookie('clms_user_id', (string)$userRow['id'], [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            if ($userRow['role'] === 'admin') {
                header('Location: ' . BASE_URL . 'admin/');
            } else {
                header('Location: ' . BASE_URL . 'index.php');
            }
            exit;
        }
    }
}

header('Location: ' . BASE_URL . 'login.php');
exit;
