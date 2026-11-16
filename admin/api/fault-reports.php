<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../fault-reports.php');
    exit;
}

$userId     = (int) $currentUserId;
$reportId   = (int) ($_POST['report_id'] ?? 0);
$status     = $_POST['status'] ?? '';
$priority   = $_POST['priority'] ?? '';
$assignedTo = (int) ($_POST['assigned_to'] ?? 0) ?: null;
$remarks    = trim($_POST['admin_remarks'] ?? '');
$remarks    = $remarks === '' ? null : $remarks;
$error      = '';

// Validate the form
if ($reportId <= 0) {
    $error = 'Invalid report.';
} elseif (!in_array($status, ['pending', 'under_review', 'in_progress', 'resolved', 'rejected'], true)) {
    $error = 'Please select a valid status.';
} elseif (!in_array($priority, ['low', 'medium', 'high'], true)) {
    $error = 'Please select a valid priority.';
} elseif (mb_strlen($remarks ?? '') > 255) {
    $error = 'Remarks must be within 255 characters.';
}

if (!$error) {
    try {
        $conn->begin_transaction();

        // Current status of the report
        $stmt = $conn->prepare("SELECT status, computer_id FROM fault_reports WHERE id = ?");
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();

        if (!$report) {
            throw new Exception('Report not found.');
        }

        // Update the report (resolved date is set only when resolved)
        $stmt = $conn->prepare(
            "UPDATE fault_reports
             SET status = ?, priority = ?, assigned_to = ?, admin_remarks = ?,
                 resolved_at = CASE WHEN ? = 'resolved' THEN COALESCE(resolved_at, NOW()) ELSE NULL END
             WHERE id = ?"
        );
        $stmt->bind_param('ssissi', $status, $priority, $assignedTo, $remarks, $status, $reportId);
        $stmt->execute();

        // Only when the status really changed: save history and update the computer
        if ($report['status'] !== $status) {
            $stmt = $conn->prepare(
                "INSERT INTO report_status_history (fault_report_id, status, changed_by, note) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('isis', $reportId, $status, $userId, $remarks);
            $stmt->execute();

            $computerSql = '';
            if ($status === 'in_progress') {
                $computerSql = "UPDATE computers SET status = 'under_maintenance' WHERE id = ? AND status IN ('available', 'in_use')";
            } elseif ($status === 'resolved') {
                $computerSql = "UPDATE computers SET status = 'available' WHERE id = ? AND status = 'under_maintenance'";
            }

            if ($computerSql) {
                $stmt = $conn->prepare($computerSql);
                $stmt->bind_param('i', $report['computer_id']);
                $stmt->execute();
            }
        }

        $conn->commit();

        $_SESSION['flash_message'] = 'Report updated successfully.';
        $_SESSION['flash_type']    = 'success';
        header('Location: ../fault-reports.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e instanceof mysqli_sql_exception ? 'Something went wrong. Please try again.' : $e->getMessage();
    }
}

$_SESSION['flash_message'] = $error;
$_SESSION['flash_type']    = 'danger';
header('Location: ../fault-reports.php');
exit;