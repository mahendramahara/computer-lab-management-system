<?php
require_once __DIR__ . '/config/db.php';

// Authentication guard
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if (($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: ' . BASE_URL . 'admin/');
    exit;
}

$userId     = (int) $_SESSION['user']['id'];
$pageTitle  = 'Report an Issue';
$activePage = 'report-issue';
$pageCss    = 'report-issue';
$pageJs     = 'report-issue';

// Component list matching database enum
$components = [
    'cpu'      => ['CPU / System Unit', 'fa-microchip', 'System not powering on, overheating, freezing'],
    'monitor'  => ['Monitor / Display', 'fa-display', 'No display, flickering screen, distorted colors'],
    'keyboard' => ['Keyboard', 'fa-keyboard', 'Keys missing, unresponsive, sticky keys'],
    'mouse'    => ['Mouse', 'fa-computer-mouse', 'Cursor stuck, buttons not clicking, scroll broken'],
    'speaker'  => ['Speaker / Audio', 'fa-volume-high', 'No sound, crackling noise, headphone jack broken'],
    'network'  => ['Network / Internet', 'fa-wifi', 'No internet connection, high latency, cable loose'],
    'software' => ['Software / OS', 'fa-window-maximize', 'OS crash, blue screen, missing lab application'],
    'other'    => ['Other Hardware', 'fa-gear', 'Power cable, USB port, surge protector, etc.'],
];

// Priorities matching database enum
$priorities = [
    'low'    => 'Low Priority (Minor issue, PC is still usable)',
    'medium' => 'Medium Priority (Normal fault, affects one component)',
    'high'   => 'High Priority (Urgent, PC unusable or safety risk)',
];

// Handle form submission
$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

$selectedComputer  = (int) ($_GET['computer_id'] ?? 0);
$selectedComponent = '';
$issueTitle        = '';
$selectedPriority  = 'medium';
$description       = '';
$createdReportId   = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedComputer  = (int) ($_POST['computer_id'] ?? 0);
    $selectedComponent = trim($_POST['component'] ?? '');
    $issueTitle        = trim($_POST['issue_title'] ?? '');
    $selectedPriority  = trim($_POST['priority'] ?? 'medium');
    $description       = trim($_POST['description'] ?? '');

    // Validation
    if ($selectedComputer <= 0) {
        $message = 'Please select a computer from the list.';
        $type    = 'danger';
    } elseif (!isset($components[$selectedComponent])) {
        $message = 'Please select a valid faulty component.';
        $type    = 'danger';
    } elseif (empty($description)) {
        $message = 'Please provide a description of the problem.';
        $type    = 'danger';
    } elseif (mb_strlen($description) < 10) {
        $message = 'Please provide a more detailed problem description (at least 10 characters).';
        $type    = 'danger';
    } else {
        // Auto-generate title if left blank
        if (empty($issueTitle)) {
            $issueTitle = $components[$selectedComponent][0] . ' Issue';
        }
        $issueTitle = mb_substr($issueTitle, 0, 150);

        if (!isset($priorities[$selectedPriority])) {
            $selectedPriority = 'medium';
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
                    $uploadDir = __DIR__ . '/uploads/reports/';
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
            $stmt->bind_param('iisssss', $userId, $selectedComputer, $selectedComponent, $issueTitle, $description, $selectedPriority, $reportImagePath);
            $stmt->execute();
            $reportId = $conn->insert_id;
            $stmt->close();

            // Insert timeline history record
            $stmtHist = $conn->prepare(
                "INSERT INTO report_status_history (fault_report_id, status, changed_by, note)
                 VALUES (?, 'pending', ?, 'Report submitted by student')"
            );
            $stmtHist->bind_param('ii', $reportId, $userId);
            $stmtHist->execute();
            $stmtHist->close();

            // Fetch computer & lab details for system notifications
            $pcInfoQuery = $conn->query("SELECT c.computer_code, l.lab_name, l.id AS lab_id FROM computers c JOIN labs l ON l.id = c.lab_id WHERE c.id = " . (int)$selectedComputer);
            $pcInfo      = $pcInfoQuery ? $pcInfoQuery->fetch_assoc() : null;
            $compCode    = $pcInfo['computer_code'] ?? 'PC';
            $labName     = $pcInfo['lab_name'] ?? 'Lab';
            $labIdVal    = $pcInfo['lab_id'] ?? null;

            // 1. Notify administrators of new fault report
            $adminNotice = "New fault report on " . $compCode . " (" . $labName . "): " . $issueTitle . " [" . ucfirst($selectedPriority) . " Priority]";
            $stmtAdmNotif = $conn->prepare("INSERT INTO notifications (user_id, lab_id, message, type) VALUES (NULL, ?, ?, 'danger')");
            $stmtAdmNotif->bind_param('is', $labIdVal, $adminNotice);
            $stmtAdmNotif->execute();
            $stmtAdmNotif->close();

            // 2. Insert confirmation notification for the reporting student
            $studentNotice = "Your report #" . $reportId . " for " . $compCode . " (" . $issueTitle . ") has been submitted and is pending review.";
            $stmtStuNotif = $conn->prepare("INSERT INTO notifications (user_id, lab_id, message, type) VALUES (?, ?, ?, 'info')");
            $stmtStuNotif->bind_param('iis', $userId, $labIdVal, $studentNotice);
            $stmtStuNotif->execute();
            $stmtStuNotif->close();

            $message = 'Your fault report has been submitted successfully! Lab assistants have been notified.';
            $type    = 'success';
            $createdReportId = $reportId;

            // Reset form
            $selectedComputer  = 0;
            $selectedComponent = '';
            $issueTitle        = '';
            $selectedPriority  = 'medium';
            $description       = '';
        } catch (mysqli_sql_exception $e) {
            $message = 'Failed to submit report. Please try again.';
            $type    = 'danger';
        }
    }
}

// Fetch active computers for select list
$computers = [];
try {
    $computers = $conn->query(
        "SELECT c.id, c.computer_code, c.status, l.lab_name
         FROM computers c
         JOIN labs l ON l.id = c.lab_id
         ORDER BY l.lab_name, c.computer_code"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    $message = 'Could not load computers. Please try again.';
    $type    = 'danger';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h2>Report an Issue</h2>
    <p>Facing a hardware or software problem? Submit a report so lab staff can inspect and repair it.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= e($type) ?>" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
            <i class="fa-solid <?= $type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
            <?= e($message) ?>
        </div>
        <?php if ($type === 'success'): ?>
            <div style="display: flex; gap: 8px;">
                <?php if ($createdReportId > 0): ?>
                    <a href="reports/details.php?id=<?= $createdReportId ?>" class="btn btn-sm btn-light">
                        <i class="fa-regular fa-eye"></i> View Report #<?= $createdReportId ?>
                    </a>
                <?php endif; ?>
                <a href="my-reports.php" class="btn btn-sm btn-light">All My Reports &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="report-issue-layout">

    <!-- Report Form -->
    <form action="report-issue.php" method="post" enctype="multipart/form-data" class="card" id="reportForm">

        <div class="form-group">
            <label for="computer_id">Select Computer <span class="report-issue-required">*</span></label>
            <select name="computer_id" id="computer_id" class="form-control" required>
                <option value="">-- Choose Computer --</option>
                <?php foreach ($computers as $pc): ?>
                    <option value="<?= $pc['id'] ?>" <?= $selectedComputer === (int) $pc['id'] ? 'selected' : '' ?>>
                        <?= e($pc['computer_code'] . ' (' . $pc['lab_name'] . ')') ?>
                        <?= $pc['status'] !== 'available' ? ' [' . str_replace('_', ' ', ucfirst($pc['status'])) . ']' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="component">Faulty Component <span class="report-issue-required">*</span></label>
            <select name="component" id="component" class="form-control" required>
                <option value="">-- Choose Component --</option>
                <?php foreach ($components as $key => $info): ?>
                    <option value="<?= $key ?>" <?= $selectedComponent === $key ? 'selected' : '' ?>>
                        <?= e($info[0]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="issue_title">Issue Title (Brief Summary)</label>
            <input type="text" name="issue_title" id="issue_title" class="form-control"
                   placeholder="e.g. Keyboard keys not responding / Monitor flickers"
                   maxlength="150" value="<?= e($issueTitle) ?>">
        </div>

        <div class="form-group">
            <label for="priority">Priority Level</label>
            <select name="priority" id="priority" class="form-control">
                <?php foreach ($priorities as $pval => $plabel): ?>
                    <option value="<?= $pval ?>" <?= $selectedPriority === $pval ? 'selected' : '' ?>>
                        <?= e($plabel) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Problem Description <span class="report-issue-required">*</span></label>
            <textarea name="description" id="description" class="form-control" rows="5" maxlength="500"
                      placeholder="Describe what happened, any error messages, or steps to reproduce..." required><?= e($description) ?></textarea>
            <small class="report-issue-counter" id="reportCounter">0/500</small>
        </div>

        <div class="form-group">
            <label for="report_image"><i class="fa-solid fa-camera"></i> Attach Photo / Screenshot (Optional)</label>
            <input type="file" name="report_image" id="report_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg">
            <small style="color: var(--muted); display: block; margin-top: 4px;">Upload a photo of the damaged component or screen error (JPG, PNG, WebP up to 5MB).</small>
        </div>

        <button type="submit" class="btn btn-block" style="margin-top: 8px;">
            <i class="fa-solid fa-paper-plane"></i> Submit Report
        </button>
    </form>

    <!-- Side Panel: Component Guidance -->
    <aside class="report-issue-panel">
        <h3><i class="fa-solid fa-screwdriver-wrench"></i> Common Fault Guidelines</h3>

        <?php foreach ($components as $key => $info): ?>
            <div class="report-issue-item">
                <span class="report-issue-item-icon">
                    <i class="fa-solid <?= $info[1] ?>"></i>
                </span>
                <div>
                    <strong><?= e($info[0]) ?></strong>
                    <small style="color: var(--muted);"><?= e($info[2]) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </aside>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
