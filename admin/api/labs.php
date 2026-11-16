<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../labs.php');
    exit;
}

$task     = $_POST['task'] ?? '';
$id       = (int) ($_POST['lab_id'] ?? 0);
$statuses = ['active', 'maintenance', 'inactive'];
$error    = '';
$success  = '';

// Sanitize inputs
$fields = ['lab_name', 'lab_code', 'room_number', 'location', 'opening_hours', 'status', 'description'];
$data   = [];
foreach ($fields as $field) {
    $value        = trim($_POST[$field] ?? '');
    $data[$field] = $value === '' ? null : $value;
}

$data['opening_hours'] = $data['opening_hours'] ?? 'Sunday - Friday, 7:00 AM - 5:00 PM';

try {
    if ($task === 'delete') {
        $stmt = $conn->prepare("DELETE FROM labs WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $success = 'Lab deleted successfully.';
    } elseif ($task === 'add' || $task === 'edit') {
        if ($data['lab_name'] === null || strlen($data['lab_name']) > 100) {
            $error = 'Please enter a lab name (maximum 100 characters).';
        } elseif ($data['lab_code'] === null || strlen($data['lab_code']) > 30) {
            $error = 'Please enter a lab code (maximum 30 characters).';
        } elseif ($data['room_number'] === null || strlen($data['room_number']) > 50) {
            $error = 'Please enter a room number (maximum 50 characters).';
        } elseif (strlen($data['location'] ?? '') > 150) {
            $error = 'Location must be within 150 characters.';
        } elseif (!in_array($data['status'], $statuses, true)) {
            $error = 'Please select a valid status.';
        } elseif ($task === 'edit' && $id <= 0) {
            $error = 'Invalid lab.';
        } elseif ($task === 'add') {
            $columns = implode(', ', array_keys($data));
            $marks   = implode(', ', array_fill(0, count($data), '?'));

            $stmt = $conn->prepare("INSERT INTO labs ($columns) VALUES ($marks)");
            $stmt->bind_param(str_repeat('s', count($data)), ...array_values($data));
            $stmt->execute();
            $stmt->close();

            $success = 'Lab added successfully.';
        } else {
            $set = implode(' = ?, ', array_keys($data)) . ' = ?';

            $stmt = $conn->prepare("UPDATE labs SET $set WHERE id = ?");
            $stmt->bind_param(str_repeat('s', count($data)) . 'i', ...array_merge(array_values($data), [$id]));
            $stmt->execute();
            $stmt->close();

            $success = 'Lab updated successfully.';
        }
    } else {
        $error = 'Invalid request.';
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        $error = 'This lab code already exists.';
    } elseif ($e->getCode() === 1451) {
        $error = 'This lab has computers, so it cannot be deleted. Set its status to Inactive instead.';
    } else {
        $error = 'Something went wrong. Please try again.';
    }
}

$_SESSION['flash_message'] = $error ?: $success;
$_SESSION['flash_type']    = $error ? 'danger' : 'success';
header('Location: ../labs.php');
exit;
