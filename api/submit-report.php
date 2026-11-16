<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$userId = (int) $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $computerId  = (int) ($_POST['computer_id'] ?? 0);
    $component   = trim($_POST['component'] ?? '');
    $issueTitle  = trim($_POST['issue_title'] ?? '');
    $priority    = trim($_POST['priority'] ?? 'medium');
    $description = trim($_POST['description'] ?? '');

    $validComponents = ['cpu', 'monitor', 'keyboard', 'mouse', 'speaker', 'network', 'software', 'other'];

    if ($computerId > 0 && in_array($component, $validComponents, true) && !empty($description)) {
        if (empty($issueTitle)) {
            $issueTitle = ucfirst($component) . ' Issue';
        }
        $issueTitle = mb_substr($issueTitle, 0, 150);

        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $priority = 'medium';
        }

        try {
            // Handle optional image upload
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
                    $uploadDir = __DIR__ . '/../uploads/reports/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
                        $reportImagePath = 'uploads/reports/' . $fileName;
                    }
                }
            }

            $stmt = $conn->prepare(
                "INSERT INTO fault_reports (user_id, computer_id, component, issue_title, description, priority, status, report_image)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)"
            );
            $stmt->bind_param('iisssss', $userId, $computerId, $component, $issueTitle, $description, $priority, $reportImagePath);
            $stmt->execute();
            $reportId = $conn->insert_id;
            $stmt->close();

            $stmtHist = $conn->prepare(
                "INSERT INTO report_status_history (fault_report_id, status, changed_by, note)
                 VALUES (?, 'pending', ?, 'Report submitted by student')"
            );
            $stmtHist->bind_param('ii', $reportId, $userId);
            $stmtHist->execute();
            $stmtHist->close();

            // Fetch computer & lab details for system notifications
            $pcInfoQuery = $conn->query("SELECT c.computer_code, l.lab_name, l.id AS lab_id FROM computers c JOIN labs l ON l.id = c.lab_id WHERE c.id = " . (int)$computerId);
            $pcInfo      = $pcInfoQuery ? $pcInfoQuery->fetch_assoc() : null;
            $compCode    = $pcInfo['computer_code'] ?? 'PC';
            $labName     = $pcInfo['lab_name'] ?? 'Lab';
            $labIdVal    = $pcInfo['lab_id'] ?? null;

            // Notify administrators of new fault report
            $adminNotice = "New fault report on " . $compCode . " (" . $labName . "): " . $issueTitle . " [" . ucfirst($priority) . " Priority]";
            $stmtAdmNotif = $conn->prepare("INSERT INTO notifications (user_id, lab_id, message, type) VALUES (NULL, ?, ?, 'danger')");
            $stmtAdmNotif->bind_param('is', $labIdVal, $adminNotice);
            $stmtAdmNotif->execute();
            $stmtAdmNotif->close();

            // Insert confirmation notification for the reporting student
            $studentNotice = "Your report #" . $reportId . " for " . $compCode . " (" . $issueTitle . ") has been submitted and is pending review.";
            $stmtStuNotif = $conn->prepare("INSERT INTO notifications (user_id, lab_id, message, type) VALUES (?, ?, ?, 'info')");
            $stmtStuNotif->bind_param('iis', $userId, $labIdVal, $studentNotice);
            $stmtStuNotif->execute();
            $stmtStuNotif->close();

            $_SESSION['flash_message'] = 'Your fault report has been submitted successfully!';
            $_SESSION['flash_type']    = 'success';
            header('Location: ' . BASE_URL . 'my-reports.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $_SESSION['flash_message'] = 'Failed to submit report. Please try again.';
            $_SESSION['flash_type']    = 'danger';
        }
    } else {
        $_SESSION['flash_message'] = 'Please fill in all required fields.';
        $_SESSION['flash_type']    = 'danger';
    }
}

header('Location: ' . BASE_URL . 'report-issue.php');
exit;
