<?php
require_once __DIR__ . '/../config/db.php';

// If already logged in as admin or lab assistant, redirect to admin dashboard
if (isset($_SESSION['user'])) {
    if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'lab_assistant'], true)) {
        header('Location: index.php');
        exit;
    } else {
        // Logged in as student; redirect to student dashboard
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// ── Cookie Auto-Login for Admin ───────────────────────────────
if (!isset($_SESSION['user']) && isset($_COOKIE['clms_admin_id'])) {
    $adminCookieId = (int)$_COOKIE['clms_admin_id'];
    if ($adminCookieId > 0) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'lab_assistant') AND status = 'active' LIMIT 1");
        $stmt->bind_param('i', $adminCookieId);
        $stmt->execute();
        $cookieUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($cookieUser) {
            $_SESSION['user'] = $cookieUser;
            $_SESSION['user']['full_name'] = $cookieUser['name'];
            $_SESSION['user_id']   = $cookieUser['id'];
            $_SESSION['user_role'] = $cookieUser['role'];
            $_SESSION['user_name'] = $cookieUser['name'];
            header('Location: index.php');
            exit;
        }
    }
}

// ── Handle Login Submission ───────────────────────────────────
$loginError = '';
$username   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    if (empty($username) || empty($password)) {
        $loginError = 'Please enter your administrator email/username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR roll_number = ?) AND status = 'active' LIMIT 1");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $userRow = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$userRow) {
            $loginError = 'No active administrator or staff account found with those credentials.';
        } elseif (!in_array($userRow['role'], ['admin', 'lab_assistant'], true)) {
            // Role restriction guard
            $loginError = 'Access restricted: This portal is reserved for Administrators and Lab Assistants. Students please sign in through the Student Portal.';
        } elseif (password_verify($password, $userRow['password'])) {
            // Establish session
            $_SESSION['user'] = $userRow;
            $_SESSION['user']['full_name'] = $userRow['name'];
            $_SESSION['user_id']   = $userRow['id'];
            $_SESSION['user_role'] = $userRow['role'];
            $_SESSION['user_name'] = $userRow['name'];

            // Handle Remember Me Cookie
            if ($remember) {
                setcookie('clms_admin_id', (string)$userRow['id'], [
                    'expires'  => time() + (86400 * 30),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            } else {
                setcookie('clms_admin_id', '', time() - 3600, '/');
            }

            header('Location: index.php');
            exit;
        } else {
            $loginError = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin & Staff Login | MMC Computer Lab</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin-root.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #071b40 0%, #0b2a5b 50%, #0e3489 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #0f1b3d;
        }

        .admin-login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .admin-login-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 36px 32px;
            box-shadow: 0 16px 40px rgba(7, 27, 64, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .admin-login-brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .admin-brand-icon-box {
            width: 58px;
            height: 58px;
            background: #e8f0fe;
            color: #1d63e8;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 12px;
        }

        .admin-login-brand h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0f1b3d;
            margin-bottom: 4px;
        }

        .admin-login-brand p {
            font-size: 13px;
            color: #64748b;
        }

        .admin-chip {
            display: inline-block;
            background: #efe9fd;
            color: #6d4aff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #fde8e8;
            color: #dc2626;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #0f1b3d;
            margin-bottom: 6px;
        }

        .form-input-wrap {
            position: relative;
        }

        .form-input-wrap i.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            height: 44px;
            padding: 10px 42px 10px 40px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #0f1b3d;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #1d63e8;
            box-shadow: 0 0 0 3px rgba(29, 99, 232, 0.15);
        }

        .form-toggle-eye {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
        }

        .form-toggle-eye:hover {
            color: #0f1b3d;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 13px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            color: #475569;
        }

        .checkbox-label input {
            cursor: pointer;
            accent-color: #1d63e8;
        }

        .btn-submit {
            width: 100%;
            height: 46px;
            background: #1d63e8;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-submit:hover {
            background: #1557d0;
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        .portal-switch {
            text-align: center;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            color: #64748b;
        }

        .portal-switch a {
            color: #1d63e8;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .portal-switch a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="admin-login-wrapper">

    <div class="admin-login-card">

        <div class="admin-login-brand">
            <div class="admin-brand-icon-box">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2>Mahendra Multiple Campus</h2>
            <p>Computer Laboratory Management System</p>
            <span class="admin-chip"><i class="fa-solid fa-shield-halved"></i> Staff & Admin Portal</span>
        </div>

        <?php if ($loginError): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= e($loginError) ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">

            <div class="form-group">
                <label class="form-label" for="username">Email / Username</label>
                <div class="form-input-wrap">
                    <i class="fa-regular fa-envelope input-icon"></i>
                    <input type="text"
                           id="username"
                           name="username"
                           class="form-input"
                           placeholder="admin@mmc.edu.np"
                           value="<?= e($username) ?>"
                           required
                           autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="form-input-wrap">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input"
                           placeholder="Enter admin password"
                           required>
                    <button type="button" class="form-toggle-eye" id="togglePassword" title="Show / Hide password">
                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1" checked>
                    <span>Remember me on this device</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Admin Panel
            </button>

        </form>

        <div class="portal-switch">
            Are you a student? 
            <a href="<?= BASE_URL ?>login.php">
                <i class="fa-solid fa-arrow-left"></i> Go to Student Login
            </a>
        </div>

    </div>

</div>

    <script src="assets/js/admin-login.js"></script>

</body>
</html>
