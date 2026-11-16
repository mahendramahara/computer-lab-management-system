<?php
require_once __DIR__ . '/config/db.php';

// If already logged in, redirect
if (isset($_SESSION['user'])) {
    if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'lab_assistant'], true)) {
        header('Location: ' . BASE_URL . 'admin/');
    } else {
        header('Location: ' . BASE_URL . 'index.php');
    }
    exit;
}

$registerError = '';
$oldData       = [];

// Handle registration request directly at the top of the file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = trim($_POST['name'] ?? '');
    $rollNumber      = trim($_POST['roll_number'] ?? '');
    $program         = trim($_POST['program'] ?? 'BCA');
    $semester        = trim($_POST['semester'] ?? '4th Semester');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $oldData = [
        'name'        => $name,
        'roll_number' => $rollNumber,
        'program'     => $program,
        'semester'    => $semester,
        'email'       => $email,
    ];

    if (empty($name) || empty($rollNumber) || empty($email) || empty($password)) {
        $registerError = 'Please fill in all required fields.';
    } elseif (mb_strlen($name) < 3) {
        $registerError = 'Full name must be at least 3 characters.';
    } elseif (mb_strlen($rollNumber) < 3) {
        $registerError = 'College roll number must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Please provide a valid email address.';
    } elseif (strlen($password) < 6) {
        $registerError = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $registerError = 'Password and Confirm Password do not match.';
    } elseif (empty($_POST['terms'])) {
        $registerError = 'You must confirm that the provided information is correct.';
    } else {
        try {
            // Check for existing email
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $checkStmt->close();
                $registerError = 'An account with this email address already exists.';
            } else {
                $checkStmt->close();

                // Check for existing roll number
                $rollStmt = $conn->prepare("SELECT id FROM users WHERE roll_number = ? LIMIT 1");
                $rollStmt->bind_param('s', $rollNumber);
                $rollStmt->execute();
                if ($rollStmt->get_result()->num_rows > 0) {
                    $rollStmt->close();
                    $registerError = 'This roll number is already registered.';
                } else {
                    $rollStmt->close();

                    // Create student account
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $insertStmt   = $conn->prepare(
                        "INSERT INTO users (name, email, roll_number, password, program, semester, role, status)
                         VALUES (?, ?, ?, ?, ?, ?, 'student', 'active')"
                    );
                    $insertStmt->bind_param('ssssss', $name, $email, $rollNumber, $passwordHash, $program, $semester);
                    $insertStmt->execute();
                    $newUserId = $conn->insert_id;
                    $insertStmt->close();

                    // 1. Notify campus administrators of new student registration
                    $adminNotice = "New student registered: " . $name . " (Roll: " . $rollNumber . ", " . $program . " - " . $semester . ")";
                    $stmtAdminNotif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (NULL, ?, 'info')");
                    $stmtAdminNotif->bind_param('s', $adminNotice);
                    $stmtAdminNotif->execute();
                    $stmtAdminNotif->close();

                    // 2. Insert personalized welcome notification for the student
                    $welcomeMsg = "Welcome to Mahendra Multiple Campus Computer Lab Management System! Your student account is active.";
                    $stmtUserNotif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'info')");
                    $stmtUserNotif->bind_param('is', $newUserId, $welcomeMsg);
                    $stmtUserNotif->execute();
                    $stmtUserNotif->close();

                    // Retrieve user record and authenticate session
                    $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                    $userStmt->bind_param('i', $newUserId);
                    $userStmt->execute();
                    $userRow = $userStmt->get_result()->fetch_assoc();
                    $userStmt->close();

                    if ($userRow) {
                        $_SESSION['user']              = $userRow;
                        $_SESSION['user']['full_name'] = $userRow['name'];
                        $_SESSION['user_id']           = $userRow['id'];
                        $_SESSION['user_role']         = $userRow['role'];
                        $_SESSION['user_name']         = $userRow['name'];

                        setcookie('clms_user_id', (string)$userRow['id'], [
                            'expires'  => time() + (86400 * 30),
                            'path'     => '/',
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);

                        $_SESSION['flash_message'] = "Welcome, " . htmlspecialchars($userRow['name']) . "! Your account has been created and you are now logged in.";
                        $_SESSION['flash_type']    = 'success';

                        header('Location: ' . BASE_URL . 'index.php');
                        exit;
                    }
                }
            }
        } catch (mysqli_sql_exception $e) {
            $registerError = 'Database error during registration: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Computer Lab Management System</title>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Page Specific Stylesheet -->
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body class="register-body">

    <!-- Main Container -->
    <main class="register-page">

        <!-- Outer Layout Frame -->
        <div class="register-frame">

            <!-- Left Hero Section -->
            <section class="register-hero">

                <!-- Campus Branding -->
                <div class="register-brand">
                    <div class="register-brand-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="register-brand-text">
                        <h2>Mahendra Multiple Campus</h2>
                        <p>Computer Laboratory Management System</p>
                    </div>
                </div>

                <!-- Hero Content -->
                <div class="register-hero-content">
                    <h1 class="register-hero-title">
                        Manage <span>•</span> Monitor <span>•</span> Maintain<br>
                        Our Computer Labs
                    </h1>

                    <p class="register-hero-desc">
                        Create your student account to check computer availability, report hardware issues, and track your maintenance requests.
                    </p>

                    <!-- Feature List -->
                    <div class="register-features">

                        <div class="register-feature-item">
                            <div class="register-feature-icon">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>
                            <div class="register-feature-text">
                                <h4>Easy Problem Reporting</h4>
                                <p>Report computer issues in a few clicks.</p>
                            </div>
                        </div>

                        <div class="register-feature-item">
                            <div class="register-feature-icon">
                                <i class="fa-solid fa-clipboard-list"></i>
                            </div>
                            <div class="register-feature-text">
                                <h4>Track Your Reports</h4>
                                <p>Know the status of your reported issues.</p>
                            </div>
                        </div>

                        <div class="register-feature-item">
                            <div class="register-feature-icon">
                                <i class="fa-solid fa-computer"></i>
                            </div>
                            <div class="register-feature-text">
                                <h4>Better Lab Experience</h4>
                                <p>Help us maintain a reliable computer lab.</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Hero Footer -->
                <div class="register-hero-footer">
                    <p>© <?php echo date("Y"); ?> Mahendra Multiple Campus. All rights reserved.</p>
                </div>

            </section>

            <!-- Right Registration Form Area -->
            <section class="register-form-area">

                <!-- Floating Registration Card -->
                <div class="register-card">

                    <!-- Card Header -->
                    <div class="register-card-header">
                        <h2 class="register-card-title">Create Account</h2>
                        <p class="register-card-subtitle">Register for your student account</p>
                    </div>

                    <?php if ($registerError): ?>
                        <div class="register-error-alert">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?= htmlspecialchars($registerError) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form action="register.php" method="POST" id="register-form" class="register-form">

                        <!-- Full Name -->
                        <div class="register-input-group">
                            <label for="register-name" class="register-input-label">Full Name *</label>
                            <div class="register-input-box" id="box-register-name">
                                <i class="fa-regular fa-user register-input-icon"></i>
                                <input
                                    type="text"
                                    id="register-name"
                                    name="name"
                                    class="register-input-control"
                                    placeholder="e.g. Samyukta Rijal"
                                    autocomplete="name"
                                    value="<?= htmlspecialchars($oldData['name'] ?? '') ?>"
                                    required
                                >
                            </div>
                            <span class="field-error-msg" id="err-register-name">Please enter your full name (at least 3 characters).</span>
                        </div>

                        <!-- Roll Number -->
                        <div class="register-input-group">
                            <label for="register-roll" class="register-input-label">College Roll Number *</label>
                            <div class="register-input-box" id="box-register-roll">
                                <i class="fa-solid fa-id-card register-input-icon"></i>
                                <input
                                    type="text"
                                    id="register-roll"
                                    name="roll_number"
                                    class="register-input-control"
                                    placeholder="e.g. 54012345 or MMC-BCA-2024-001"
                                    value="<?= htmlspecialchars($oldData['roll_number'] ?? '') ?>"
                                    required
                                >
                            </div>
                            <span class="field-error-msg" id="err-register-roll">Please enter a valid roll number (at least 3 characters).</span>
                        </div>

                        <!-- Academic Program & Semester -->
                        <div class="register-two-col">
                            <div class="register-input-group">
                                <label for="register-program" class="register-input-label">Program</label>
                                <div class="register-input-box">
                                    <i class="fa-solid fa-book-open register-input-icon"></i>
                                    <input
                                        type="text"
                                        id="register-program"
                                        name="program"
                                        class="register-input-control"
                                        placeholder="e.g. BCA, CSIT"
                                        value="<?= htmlspecialchars($oldData['program'] ?? 'BCA') ?>"
                                    >
                                </div>
                            </div>
                            <div class="register-input-group">
                                <label for="register-semester" class="register-input-label">Semester</label>
                                <div class="register-input-box">
                                    <i class="fa-solid fa-layer-group register-input-icon"></i>
                                    <input
                                        type="text"
                                        id="register-semester"
                                        name="semester"
                                        class="register-input-control"
                                        placeholder="e.g. 4th Semester"
                                        value="<?= htmlspecialchars($oldData['semester'] ?? '4th Semester') ?>"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="register-input-group">
                            <label for="register-email" class="register-input-label">Email Address *</label>
                            <div class="register-input-box" id="box-register-email">
                                <i class="fa-regular fa-envelope register-input-icon"></i>
                                <input
                                    type="email"
                                    id="register-email"
                                    name="email"
                                    class="register-input-control"
                                    placeholder="e.g. student@gmail.com"
                                    autocomplete="email"
                                    value="<?= htmlspecialchars($oldData['email'] ?? '') ?>"
                                    required
                                >
                            </div>
                            <span class="field-error-msg" id="err-register-email">Please enter a valid email address.</span>
                        </div>

                        <!-- Password & Confirm Password in Same Row -->
                        <div class="register-two-col">
                            <div class="register-input-group">
                                <label for="register-password" class="register-input-label">Password *</label>
                                <div class="register-input-box" id="box-register-password">
                                    <i class="fa-solid fa-lock register-input-icon"></i>
                                    <input
                                        type="password"
                                        id="register-password"
                                        name="password"
                                        class="register-input-control"
                                        placeholder="Min. 6 characters"
                                        autocomplete="new-password"
                                        minlength="6"
                                        required
                                    >
                                    <button
                                        type="button"
                                        class="register-password-toggle"
                                        onclick="toggleRegisterPassword('register-password', 'register-eye-1')"
                                        aria-label="Toggle password visibility"
                                    >
                                        <i class="fa-regular fa-eye" id="register-eye-1"></i>
                                    </button>
                                </div>
                                <span class="field-error-msg" id="err-register-password">Password must be at least 6 characters.</span>
                            </div>

                            <div class="register-input-group">
                                <label for="register-confirm-password" class="register-input-label">Confirm Password *</label>
                                <div class="register-input-box" id="box-register-confirm">
                                    <i class="fa-solid fa-lock register-input-icon"></i>
                                    <input
                                        type="password"
                                        id="register-confirm-password"
                                        name="confirm_password"
                                        class="register-input-control"
                                        placeholder="Repeat password"
                                        autocomplete="new-password"
                                        minlength="6"
                                        required
                                    >
                                    <button
                                        type="button"
                                        class="register-password-toggle"
                                        onclick="toggleRegisterPassword('register-confirm-password', 'register-eye-2')"
                                        aria-label="Toggle confirm password visibility"
                                    >
                                        <i class="fa-regular fa-eye" id="register-eye-2"></i>
                                    </button>
                                </div>
                                <span class="field-error-msg" id="err-register-confirm">Passwords do not match.</span>
                                <span class="field-success-msg" id="ok-register-confirm"><i class="fa-solid fa-check"></i> Passwords match</span>
                            </div>
                        </div>

                        <!-- Terms & Confirmation -->
                        <div class="register-terms-group">
                            <label class="register-terms-label">
                                <input type="checkbox" name="terms" id="register-terms" class="register-checkbox" required>
                                <span>I confirm that the information provided is correct.</span>
                            </label>
                        </div>
                        <span class="field-error-msg" id="err-register-terms" style="margin-top: -6px;">You must confirm this declaration.</span>

                        <!-- Submit Button -->
                        <button type="submit" class="register-submit-btn" id="register-submit-btn">
                            Create Account
                        </button>

                    </form>

                    <!-- Login Link -->
                    <div class="register-login-prompt">
                        <p>
                            Already have an account?
                            <a href="<?= BASE_URL ?>login.php" class="register-login-link">Login here</a>
                        </p>
                    </div>

                    <!-- Visual Divider -->
                    <div class="register-divider">
                        <span>OR</span>
                    </div>

                    <!-- Admin Access Link -->
                    <a href="<?= BASE_URL ?>admin/" class="register-admin-btn">
                        <i class="fa-solid fa-shield-halved"></i>
                        Login as Admin
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>

                    <!-- Card Branding Footer -->
                    <div class="register-card-footer">
                        <div class="register-footer-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div class="register-footer-text">
                            <h4>Mahendra Multiple Campus</h4>
                            <p>Computer Lab Management System</p>
                        </div>
                    </div>

                </div>

            </section>

        </div>

    </main>

    <!-- Page Specific Script -->
    <script src="assets/js/register.js"></script>

</body>
</html>
