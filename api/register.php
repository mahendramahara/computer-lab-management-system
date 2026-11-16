<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'register.php');
    exit;
}

$name            = trim($_POST['name'] ?? '');
$rollNumber      = trim($_POST['roll_number'] ?? '');
$program         = trim($_POST['program'] ?? 'BCA');
$semester        = trim($_POST['semester'] ?? '4th Semester');
$email           = trim($_POST['email'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Save entered data in session to preserve on validation error
$_SESSION['register_data'] = [
    'name'        => $name,
    'roll_number' => $rollNumber,
    'program'     => $program,
    'semester'    => $semester,
    'email'       => $email,
];

// Validation checks
if (empty($name) || empty($rollNumber) || empty($email) || empty($password)) {
    $_SESSION['register_error'] = 'Please fill in all required fields.';
    header('Location: ' . BASE_URL . 'register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Please provide a valid email address.';
    header('Location: ' . BASE_URL . 'register.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['register_error'] = 'Password must be at least 6 characters.';
    header('Location: ' . BASE_URL . 'register.php');
    exit;
}

if ($password !== $confirmPassword) {
    $_SESSION['register_error'] = 'Password and Confirm Password do not match.';
    header('Location: ' . BASE_URL . 'register.php');
    exit;
}

try {
    // Check for existing email
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $checkStmt->close();
        $_SESSION['register_error'] = 'An account with this email address already exists.';
        header('Location: ' . BASE_URL . 'register.php');
        exit;
    }
    $checkStmt->close();

    // Check for existing roll number
    $rollStmt = $conn->prepare("SELECT id FROM users WHERE roll_number = ? LIMIT 1");
    $rollStmt->bind_param('s', $rollNumber);
    $rollStmt->execute();
    if ($rollStmt->get_result()->num_rows > 0) {
        $rollStmt->close();
        $_SESSION['register_error'] = 'This roll number is already registered.';
        header('Location: ' . BASE_URL . 'register.php');
        exit;
    }
    $rollStmt->close();

    // Hash password and insert into users table
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt   = $conn->prepare(
        "INSERT INTO users (name, email, roll_number, password, program, semester, role, status)
         VALUES (?, ?, ?, ?, ?, ?, 'student', 'active')"
    );
    $insertStmt->bind_param('ssssss', $name, $email, $rollNumber, $passwordHash, $program, $semester);
    $insertStmt->execute();
    $newUserId = $conn->insert_id;
    $insertStmt->close();

    // Fetch newly created user record
    $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $userStmt->bind_param('i', $newUserId);
    $userStmt->execute();
    $userRow = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    if ($userRow) {
        // Direct auto-login: set session
        $_SESSION['user']              = $userRow;
        $_SESSION['user']['full_name'] = $userRow['name'];
        $_SESSION['user_id']           = $userRow['id'];
        $_SESSION['user_role']         = $userRow['role'];
        $_SESSION['user_name']         = $userRow['name'];

        // Set persistent cookie for 30 days
        setcookie('clms_user_id', (string)$userRow['id'], [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Add welcome notification
        $welcomeMsg = "Welcome to Mahendra Multiple Campus Computer Lab Management System! You can now check computer availability and report faults.";
        $nStmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'info')");
        $nStmt->bind_param('is', $newUserId, $welcomeMsg);
        $nStmt->execute();
        $nStmt->close();

        // Clear register cache
        unset($_SESSION['register_data'], $_SESSION['register_error']);

        $_SESSION['flash_message'] = "Welcome, " . htmlspecialchars($userRow['name']) . "! Your account has been created and you are now logged in.";
        $_SESSION['flash_type']    = 'success';

        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['register_error'] = 'Database error during registration: ' . $e->getMessage();
}

header('Location: ' . BASE_URL . 'register.php');
exit;
