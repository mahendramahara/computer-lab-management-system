<?php
require_once __DIR__ . '/../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../fault-reports.php');
    exit;
}

$task        = $_POST['task'] ?? '';
$validStatus = ['pending', 'under_review', 'in_progress', 'resolved', 'rejected'];

try {
    // -------------------------------------------------------------
    // Task: Add New Fault Report (By Admin / Staff)
    // -------------------------------------------------------------
    if ($task === 'add_report') {
        $computerId  = (int) ($_POST['computer_id'] ?? 0);
        $component   = trim($_POST['component'] ?? 'other');
        $issueTitle  = trim($_POST['issue_title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority    = $_POST['priority'] ?? 'medium';
        $status      = $_POST['status'] ?? 'pending';
        $assignedTo  = !empty($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        $remarks     = trim($_POST['admin_remarks'] ?? '');

        if ($computerId <= 0 || empty($description)) {
            throw new Exception('Please select a computer and enter problem description.');
        }

        if (empty($issueTitle)) {
            $issueTitle = ucfirst($component) . ' Fault';
        }
        $issueTitle = mb_substr($issueTitle, 0, 150);

        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $priority = 'medium';
        }
        if (!in_array($status, $validStatus, true)) {
            $status = 'pending';
        }

        // Handle image upload
        $reportImagePath = null;
        if (!empty($_FILES['report_image']['name']) && $_FILES['report_image']['error'] === UPLOAD_ERR_OK) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $fileTmp      = $_FILES['report_image']['tmp_name'];
            $fileSize     = $_FILES['report_image']['size'];
            $finfo        = new finfo(FILEINFO_MIME_TYPE);
            $mimeType     = $finfo->file($fileTmp);

            if (in_array($mimeType, $allowedMimes, true) && $fileSize <= 5 * 1024 * 1024) {
                $ext = match ($mimeType) {
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    default      => 'jpg',
                };
                $fileName  = 'fault_' . bin2hex(random_bytes(6)) . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../../uploads/reports/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
                    $reportImagePath = 'uploads/reports/' . $fileName;
                }
            }
        }

        $resolvedAt = ($status === 'resolved') ? date('Y-m-d H:i:s') : null;

        $stmt = $conn->prepare(
            "INSERT INTO fault_reports (user_id, computer_id, component, issue_title, description, priority, status, assigned_to, admin_remarks, report_image, resolved_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iisssssisss', $currentUserId, $computerId, $component, $issueTitle, $description, $priority, $status, $assignedTo, $remarks, $reportImagePath, $resolvedAt);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        // Log timeline history
        $note = "Report logged by administrator";
        $histStmt = $conn->prepare("INSERT INTO report_status_history (fault_report_id, status, changed_by, note) VALUES (?, ?, ?, ?)");
        $histStmt->bind_param('isis', $newId, $status, $currentUserId, $note);
        $histStmt->execute();
        $histStmt->close();

        $_SESSION['flash_message'] = 'Fault report #FR-' . str_pad($newId, 4, '0', STR_PAD_LEFT) . ' created successfully.';
        $_SESSION['flash_type']    = 'success';
    }

    // -------------------------------------------------------------
    // Task: Update Status & Assignment
    // -------------------------------------------------------------
    elseif ($task === 'update_status') {
        $reportId   = (int) ($_POST['report_id'] ?? 0);
        $status     = $_POST['status'] ?? '';
        $assignedTo = !empty($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        $remarks    = trim($_POST['admin_remarks'] ?? '');

        if ($reportId <= 0 || !in_array($status, $validStatus, true)) {
            throw new Exception('Invalid report or status selection.');
        }

        $stmt = $conn->prepare("SELECT user_id, computer_id, status FROM fault_reports WHERE id = ?");
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$report) {
            throw new Exception('Report not found.');
        }

        $oldStatus  = $report['status'];
        $resolvedAt = ($status === 'resolved') ? date('Y-m-d H:i:s') : null;

        $updateStmt = $conn->prepare(
            "UPDATE fault_reports 
             SET status = ?, assigned_to = ?, admin_remarks = ?, resolved_at = ?
             WHERE id = ?"
        );
        $updateStmt->bind_param('sissi', $status, $assignedTo, $remarks, $resolvedAt, $reportId);
        $updateStmt->execute();
        $updateStmt->close();

        if ($oldStatus !== $status || !empty($remarks)) {
            $note = $remarks ?: "Status updated to " . ucfirst(str_replace('_', ' ', $status));
            $histStmt = $conn->prepare("INSERT INTO report_status_history (fault_report_id, status, changed_by, note) VALUES (?, ?, ?, ?)");
            $histStmt->bind_param('isis', $reportId, $status, $currentUserId, $note);
            $histStmt->execute();
            $histStmt->close();
        }

        // Notify student user if applicable
        if ($report['user_id']) {
            $notifMsg = "Your fault report #FR-" . str_pad($reportId, 4, '0', STR_PAD_LEFT) . " status is now " . ucfirst(str_replace('_', ' ', $status)) . ".";
            $notifType = ($status === 'resolved') ? 'success' : (($status === 'rejected') ? 'danger' : 'info');
            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
            $notifStmt->bind_param('iss', $report['user_id'], $notifMsg, $notifType);
            $notifStmt->execute();
            $notifStmt->close();
        }

        $_SESSION['flash_message'] = 'Fault report #FR-' . str_pad($reportId, 4, '0', STR_PAD_LEFT) . ' updated successfully.';
        $_SESSION['flash_type']    = 'success';
    }
} catch (Exception $e) {
    $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
    $_SESSION['flash_type']    = 'danger';
}

header('Location: ../fault-reports.php');
exit;
