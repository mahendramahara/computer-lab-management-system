<?php
require_once __DIR__ . '/../includes/auth.php';

// Only full administrator can perform user mutations
if ($currentUserRole !== 'admin') {
    $_SESSION['flash_message'] = 'Only full administrators can modify user accounts.';
    $_SESSION['flash_type']    = 'danger';
    header('Location: ../users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../users.php');
    exit;
}

$task   = $_POST['task'] ?? '';
$userId = (int) ($_POST['user_id'] ?? 0);

try {
    if ($task === 'delete') {
        if ($userId === $currentUserId) {
            throw new Exception('You cannot delete your own logged-in account.');
        }
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash_message'] = 'User deleted successfully.';
        $_SESSION['flash_type']    = 'success';
    } elseif ($task === 'add' || $task === 'edit') {
        $name       = trim($_POST['name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $rollNumber = trim($_POST['roll_number'] ?? '');
        $role       = $_POST['role'] ?? 'student';
        $status     = $_POST['status'] ?? 'active';
        $program    = trim($_POST['program'] ?? '');
        $semester   = trim($_POST['semester'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($name) || empty($email)) {
            throw new Exception('Name and Email are required.');
        }

        $rollVal = $rollNumber !== '' ? $rollNumber : null;
        $progVal = $program !== '' ? $program : null;
        $semVal  = $semester !== '' ? $semester : null;

        if ($task === 'add') {
            if (empty($password)) {
                throw new Exception('Password is required for new accounts.');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, roll_number, password, program, semester, role, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('ssssssss', $name, $email, $rollVal, $hash, $progVal, $semVal, $role, $status);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_message'] = 'User created successfully.';
            $_SESSION['flash_type']    = 'success';
        } else {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "UPDATE users 
                     SET name = ?, email = ?, roll_number = ?, password = ?, program = ?, semester = ?, role = ?, status = ?
                     WHERE id = ?"
                );
                $stmt->bind_param('ssssssssi', $name, $email, $rollVal, $hash, $progVal, $semVal, $role, $status, $userId);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE users 
                     SET name = ?, email = ?, roll_number = ?, program = ?, semester = ?, role = ?, status = ?
                     WHERE id = ?"
                );
                $stmt->bind_param('sssssssi', $name, $email, $rollVal, $progVal, $semVal, $role, $status, $userId);
            }
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_message'] = 'User details updated successfully.';
            $_SESSION['flash_type']    = 'success';
        }
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        $_SESSION['flash_message'] = 'Email or Roll Number is already registered.';
    } elseif ($e->getCode() === 1451) {
        $_SESSION['flash_message'] = 'Cannot delete user because they have submitted reports. Set status to Inactive instead.';
    } else {
        $_SESSION['flash_message'] = 'Database error: ' . $e->getMessage();
    }
    $_SESSION['flash_type'] = 'danger';
} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type']    = 'danger';
}

header('Location: ../users.php');
exit;
