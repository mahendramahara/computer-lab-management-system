<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../computers.php');
    exit;
}

$task     = $_POST['task'] ?? '';
$id       = (int) ($_POST['computer_id'] ?? 0);
$statuses = ['available', 'in_use', 'under_maintenance', 'out_of_service'];
$error    = '';
$success  = '';

// Input payload fields mapped to database schema
$fields = [
    'computer_code', 'computer_name', 'lab_id', 'status', 'processor', 'ram', 'storage',
    'ip_address', 'operating_system', 'monitor', 'keyboard', 'mouse', 'purchase_date', 'remarks',
];

$data = [];
foreach ($fields as $field) {
    $value = trim($_POST[$field] ?? '');
    $data[$field] = $value === '' ? null : $value;
}

try {
    // Delete computer record
    if ($task === 'delete') {
        $stmt = $conn->prepare("DELETE FROM computers WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $success = 'Computer deleted successfully.';
    }
    // Add or edit computer record
    elseif ($task === 'add' || $task === 'edit') {
        $date = $data['purchase_date'] ? DateTime::createFromFormat('Y-m-d', $data['purchase_date']) : true;

        if ($data['computer_code'] === null || strlen($data['computer_code']) > 30) {
            $error = 'Please enter a valid Computer ID (max 30 characters).';
        } elseif ((int) $data['lab_id'] <= 0) {
            $error = 'Please select a laboratory.';
        } elseif (!in_array($data['status'], $statuses, true)) {
            $error = 'Please select a valid computer status.';
        } elseif ($data['ip_address'] !== null && !filter_var($data['ip_address'], FILTER_VALIDATE_IP)) {
            $error = 'Please enter a valid IP address.';
        } elseif (!$date) {
            $error = 'Please enter a valid purchase date.';
        } elseif ($task === 'edit' && $id <= 0) {
            $error = 'Invalid computer record selected.';
        } elseif ($task === 'add') {
            $columns = implode(', ', array_keys($data));
            $marks   = implode(', ', array_fill(0, count($data), '?'));
            $stmt = $conn->prepare("INSERT INTO computers ($columns) VALUES ($marks)");
            $stmt->bind_param(str_repeat('s', count($data)), ...array_values($data));
            $stmt->execute();
            $stmt->close();
            $success = 'Computer added successfully.';
        } else {
            $set = implode(' = ?, ', array_keys($data)) . ' = ?';
            $stmt = $conn->prepare("UPDATE computers SET $set WHERE id = ?");
            $stmt->bind_param(str_repeat('s', count($data)) . 'i', ...array_merge(array_values($data), [$id]));
            $stmt->execute();
            $stmt->close();
            $success = 'Computer updated successfully.';
        }
    } else {
        $error = 'Invalid action request.';
    }

    // Keep laboratory computer totals synchronized
    if ($success) {
        $conn->query("UPDATE labs SET total_computers = (SELECT COUNT(*) FROM computers WHERE computers.lab_id = labs.id)");
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        $error = 'This Computer ID is already assigned to another machine.';
    } elseif ($e->getCode() === 1451) {
        $error = 'This computer has linked fault reports or maintenance records and cannot be deleted. Change its status to "Out of Service" instead.';
    } else {
        $error = 'Database operation failed: ' . $e->getMessage();
    }
}

$_SESSION['flash_message'] = $error ?: $success;
$_SESSION['flash_type']    = $error ? 'danger' : 'success';
header('Location: ../computers.php');
exit;
