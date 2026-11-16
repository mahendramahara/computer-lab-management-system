<?php
require_once __DIR__ . '/../includes/auth.php';

$status = $_GET['status'] ?? '';

try {
    $sql = "SELECT r.id, c.computer_code, l.lab_name, r.component, r.issue_title, r.description,
                   u.name AS reporter, r.priority, r.status, r.reported_at, r.resolved_at
            FROM fault_reports r
            JOIN computers c ON c.id = r.computer_id
            JOIN labs l ON l.id = c.lab_id
            JOIN users u ON u.id = r.user_id";

    if ($status !== '') {
        $stmt = $conn->prepare($sql . " WHERE r.status = ? ORDER BY r.reported_at DESC");
        $stmt->bind_param('s', $status);
    } else {
        $stmt = $conn->prepare($sql . " ORDER BY r.reported_at DESC");
    }
    $stmt->execute();
    $result = $stmt->get_result();
} catch (mysqli_sql_exception $e) {
    die('Could not export reports.');
}

// Send the file as a CSV download (opens in Excel)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="fault-reports-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Report ID', 'Computer', 'Lab', 'Component', 'Issue', 'Description', 'Reported By', 'Priority', 'Status', 'Reported At', 'Resolved At'], ',', '"', '');

foreach ($result as $row) {
    fputcsv($out, $row, ',', '"', '');
}

fclose($out);