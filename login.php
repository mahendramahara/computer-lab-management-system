<?php
// ── Bootstrap ─────────────────────────────────────────────────
require_once __DIR__ . '/config/db.php';   // loads session, cookies, DB, DEV_MODE

// If already logged in via session or cookie, redirect immediately
if (isset($_SESSION['user'])) {
    if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'lab_assistant'], true)) {
        header('Location: ' . BASE_URL . 'admin/');
    } else {
        header('Location: ' . BASE_URL . 'index.php');
    }
    exit;
}

// ── DEV_MODE auto-login (Un-comment the block below for instant auto-login) ──
/*
if (defined('DEV_MODE') && DEV_MODE === true && !isset($_SESSION['user'])) {
    $devUsername = DEV_USERNAME;
    $devRole     = DEV_ROLE;

    if ($devRole === 'admin') {
        $devUser = getRows($conn,
            "SELECT * FROM users WHERE email = ? AND role = 'admin' AND status = 'active' LIMIT 1",
            [$devUsername], 's'
        );
    } else {
        $devUser = getRows($conn,
            "SELECT * FROM users WHERE (roll_number = ? OR email = ?) AND role = 'student' AND status = 'active' LIMIT 1",
            [$devUsername, $devUsername], 'ss'
        );
    }

    if (!empty($devUser)) {
        $_SESSION['user'] = $devUser[0];
        $_SESSION['user']['full_name'] = $devUser[0]['name'];
        if ($devRole === 'admin') {
            header('Location: ' . BASE_URL . 'admin/');
        } else {
            header('Location: ' . BASE_URL . 'index.php');
        }
        exit;
    }
}
*/

// ── Handle Login Form Submission ──────────────────────────────
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    if (empty($username) || empty($password)) {
        $loginError = 'Please enter your username/email and password.';
    } else {
        // Query user by email OR roll_number
        $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR roll_number = ?) AND status = 'active' LIMIT 1");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $userRow = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if ($userRow && password_verify($password, $userRow['password'])) {
            // Establish session
            $_SESSION['user'] = $userRow;
            $_SESSION['user']['full_name'] = $userRow['name'];
            $_SESSION['user_id']   = $userRow['id'];
            $_SESSION['user_role'] = $userRow['role'];
            $_SESSION['user_name'] = $userRow['name'];

            // Handle Remember Me Cookie (30 days)
            if ($remember) {
                setcookie('clms_user_id', (string)$userRow['id'], [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            } else {
                setcookie('clms_user_id', '', time() - 3600, '/');
            }

            // Redirect based on user role: admin and lab assistant directly to admin/
            if (in_array($userRow['role'], ['admin', 'lab_assistant'], true)) {
                header('Location: ' . BASE_URL . 'admin/');
            } else {
                header('Location: ' . BASE_URL . 'index.php');
            }
            exit;
        } else {
            $loginError = 'Invalid roll number / username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Computer Lab Management System</title>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Page Specific Stylesheet -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-body">

    <!-- Main Container -->
    <main class="login-page">

        <!-- Outer Layout Frame -->
        <div class="login-frame">

            <!-- Left Hero Section -->
            <section class="login-hero">

                <!-- Campus Branding -->
                <div class="login-brand">
                    <div class="login-brand-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="login-brand-text">
                        <h2>Mahendra Multiple Campus</h2>
                        <p>Computer Laboratory Management System</p>
                    </div>
                </div>

                <!-- Hero Content -->
                <div class="login-hero-content">
                    <h1 class="login-hero-title">
                        Manage <span>•</span> Monitor <span>•</span> Maintain<br>
                        Our Computer Labs
                    </h1>

                    <p class="login-hero-desc">
                        Report issues, check computer availability and help us keep the lab running smoothly.
                    </p>

                    <!-- Feature List -->
                    <div class="login-features">

                        <div class="login-feature-item">
                            <div class="login-feature-icon">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>
                            <div class="login-feature-text">
                                <h4>Easy Problem Reporting</h4>
                                <p>Report computer issues in a few clicks.</p>
                            </div>
                        </div>

                        <div class="login-feature-item">
                            <div class="login-feature-icon">
                                <i class="fa-solid fa-clipboard-list"></i>
                            </div>
                            <div class="login-feature-text">
                                <h4>Track Your Reports</h4>
                                <p>Know the status of your reported issues.</p>
                            </div>
                        </div>

                        <div class="login-feature-item">
                            <div class="login-feature-icon">
                                <i class="fa-solid fa-computer"></i>
                            </div>
                            <div class="login-feature-text">
                                <h4>Better Lab Experience</h4>
                                <p>Help us maintain a reliable computer lab.</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Hero Footer -->
                <div class="login-hero-footer">
                    <p>© <?= date("Y"); ?> Mahendra Multiple Campus. All rights reserved.</p>
                </div>

            </section>

            <!-- Right Login Form Area -->
            <section class="login-form-area">

                <!-- Floating Login Card -->
                <div class="login-card">

                    <!-- Card Header -->
                    <div class="login-card-header">
                        <h2 class="login-card-title">Welcome Back</h2>
                        <p class="login-card-subtitle">Login to your student account</p>
                    </div>

                    <?php if (!empty($loginError)): ?>
                        <div class="login-error-alert">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?= htmlspecialchars($loginError) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Authentication Form -->
                    <form action="login.php" method="POST" id="login-form" class="login-form">

                        <!-- Roll Number or Email -->
                        <div class="login-input-group">
                            <label for="login-username" class="login-input-label">Roll Number / Username *</label>
                            <div class="login-input-box" id="box-login-username">
                                <i class="fa-regular fa-user login-input-icon"></i>
                                <input
                                    type="text"
                                    id="login-username"
                                    name="username"
                                    class="login-input-control"
                                    placeholder="Roll Number / Email / Username"
                                    autocomplete="username"
                                    value="<?= htmlspecialchars($username ?? ''); ?>"
                                    required
                                >
                            </div>
                            <span class="field-error-msg" id="err-login-username">Please enter your roll number, email, or username.</span>
                        </div>

                        <!-- Password -->
                        <div class="login-input-group">
                            <label for="login-password" class="login-input-label">Password *</label>
                            <div class="login-input-box" id="box-login-password">
                                <i class="fa-solid fa-lock login-input-icon"></i>
                                <input
                                    type="password"
                                    id="login-password"
                                    name="password"
                                    class="login-input-control"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    value=""
                                    required
                                >
                                <button
                                    type="button"
                                    class="login-password-toggle"
                                    onclick="toggleLoginPassword()"
                                    aria-label="Toggle password visibility"
                                >
                                    <i class="fa-regular fa-eye" id="login-eye-icon"></i>
                                </button>
                            </div>
                            <span class="field-error-msg" id="err-login-password">Please enter your password.</span>
                        </div>

                        <!-- Remember Me -->
                        <div class="login-remember-row">
                            <label class="login-remember-label">
                                <input type="checkbox" name="remember" value="1" checked>
                                <span>Remember me</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="login-submit-btn" id="login-submit-btn">
                            Login
                        </button>

                    </form>

                    <!-- Register Prompt -->
                    <div class="login-register-prompt">
                        <p>
                            Don't have an account?
                            <a href="<?= BASE_URL ?>register.php" class="login-register-link">Create Account</a>
                        </p>
                    </div>

                    <!-- Visual Divider -->
                    <div class="login-divider">
                        <span>OR</span>
                    </div>

                    <!-- Admin Access Link -->
                    <a href="<?= BASE_URL ?>admin/login.php" class="login-admin-btn">
                        <i class="fa-solid fa-shield-halved"></i>
                        Login as Admin
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>

                    <!-- Card Branding Footer -->
                    <div class="login-card-footer">
                        <div class="login-footer-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div class="login-footer-text">
                            <h4>Mahendra Multiple Campus</h4>
                            <p>Computer Lab Management System</p>
                        </div>
                    </div>

                </div>

            </section>

        </div>

    </main>

    <!-- Page Specific Script -->
    <script src="assets/js/login.js"></script>

</body>
</html>
