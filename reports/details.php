<?php
require_once __DIR__ . '/../config/db.php';

// Authentication guard
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'lab_assistant'], true)) {
    header('Location: ' . BASE_URL . 'admin/');
    exit;
}

$userId   = (int) $_SESSION['user']['id'];
$reportId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$report   = null;
$history  = [];
$notFound = false;
$error    = '';

// Component icons matching database enum
$componentIcons = [
    'cpu'      => 'fa-microchip',
    'monitor'  => 'fa-display',
    'keyboard' => 'fa-keyboard',
    'mouse'    => 'fa-computer-mouse',
    'speaker'  => 'fa-volume-high',
    'network'  => 'fa-wifi',
    'software' => 'fa-window-maximize',
    'other'    => 'fa-gear',
];

$statuses = [
    'pending'      => 'Pending',
    'under_review' => 'Under Review',
    'in_progress'  => 'In Progress',
    'resolved'     => 'Resolved',
    'rejected'     => 'Rejected',
];

// Query report strictly belonging to the currently authenticated student
if ($reportId <= 0) {
    $notFound = true;
} else {
    try {
        $stmt = $conn->prepare(
            "SELECT r.id, r.user_id, r.computer_id, r.component, r.issue_title, r.description,
                    r.priority, r.status, r.assigned_to, r.admin_remarks, r.report_image, r.reported_at,
                    r.updated_at, r.resolved_at,
                    c.computer_code, c.computer_name, c.processor, c.ram, c.storage,
                    c.operating_system, c.ip_address, c.status AS computer_status,
                    l.id AS lab_id, l.lab_name, l.lab_code, l.room_number, l.location,
                    a.name AS assigned_name, a.email AS assigned_email,
                    u.name AS reporter_name, u.roll_number AS reporter_roll
             FROM fault_reports r
             JOIN computers c ON c.id = r.computer_id
             JOIN labs l ON l.id = c.lab_id
             JOIN users u ON u.id = r.user_id
             LEFT JOIN users a ON a.id = r.assigned_to
             WHERE r.id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
        } else {
            $notFound = true;
        }
        $stmt->close();

        // If report exists and belongs to current user, fetch full timeline
        if ($report) {
            $stmtHist = $conn->prepare(
                "SELECT h.id, h.status, h.note, h.created_at,
                        u.name AS changed_by_name, u.role AS changed_by_role
                 FROM report_status_history h
                 LEFT JOIN users u ON u.id = h.changed_by
                 WHERE h.fault_report_id = ?
                 ORDER BY h.created_at ASC"
            );
            $stmtHist->bind_param('i', $reportId);
            $stmtHist->execute();
            $history = $stmtHist->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtHist->close();
        }
    } catch (mysqli_sql_exception $e) {
        $error = 'An error occurred while loading the report. Please try again.';
    }
}

if ($notFound) {
    http_response_code(404);
}

$pageTitle  = $report ? ('Report #' . $report['id'] . ' Details') : 'Report Not Found';
$activePage = 'my-reports';
$pageCss    = 'report-details';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumb">
    <a href="<?= BASE_URL ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
    <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
    <a href="<?= BASE_URL ?>my-reports.php">My Reports</a>
    <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
    <span class="breadcrumb-current">
        <?= $report ? ('Report #' . e($report['id'])) : 'Not Found' ?>
    </span>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if ($notFound): ?>
    <!-- Report Not Found / Unauthorized Screen -->
    <div class="not-found-card">
        <div class="not-found-icon">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="not-found-badge">Error 404 &bull; Report Not Found</div>
        <h2>Report Does Not Exist</h2>
        <p>
            The report you requested could not be found, has been removed, or does not belong to your account.
            Students can only view reports they have personally submitted.
        </p>
        <div class="not-found-actions">
            <a href="<?= BASE_URL ?>my-reports.php" class="btn btn-primary">
                <i class="fa-solid fa-arrow-left"></i> Back to My Reports
            </a>
            <a href="<?= BASE_URL ?>report-issue.php" class="btn btn-light">
                <i class="fa-solid fa-circle-plus"></i> Submit a New Report
            </a>
        </div>
    </div>

<?php else: ?>

    <?php
    $curStatus = $statuses[$report['status']] ?? ucfirst($report['status']);
    $compIcon  = $componentIcons[$report['component']] ?? 'fa-gear';
    ?>

    <!-- Header Card -->
    <div class="report-header-card">
        <div class="report-header-left">
            <div class="report-id-chip">
                <i class="fa-solid fa-hashtag"></i> Report #<?= e($report['id']) ?>
            </div>
            <h1 class="report-header-title"><?= e($report['issue_title']) ?></h1>
            <div class="report-header-sub">
                <span>
                    <i class="fa-regular fa-calendar-check"></i>
                    Reported on <?= date('d M Y, h:i A', strtotime($report['reported_at'])) ?>
                </span>
                <span>&bull;</span>
                <span>
                    <i class="fa-solid fa-desktop"></i>
                    <?= e($report['computer_code']) ?> &mdash; <?= e($report['lab_name']) ?>
                </span>
            </div>
        </div>

        <div class="report-header-badges">
            <span class="badge badge-lg badge-status-<?= e($report['status']) ?>">
                <i class="fa-solid fa-circle-dot"></i> <?= e($curStatus) ?>
            </span>
            <span class="badge badge-lg badge-priority-<?= e($report['priority']) ?>">
                <i class="fa-solid fa-flag"></i> <?= ucfirst(e($report['priority'])) ?> Priority
            </span>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="report-grid">

        <!-- Left Column: Details & Timeline -->
        <div class="report-main-col">

            <!-- Issue Summary Card -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <i class="fa-solid fa-clipboard-question"></i> Issue Overview
                    </span>
                    <span class="badge badge-lg" style="background: var(--bg); color: var(--text); border: 1px solid var(--border);">
                        <i class="fa-solid <?= $compIcon ?>"></i> Component: <?= ucfirst(e($report['component'])) ?>
                    </span>
                </div>

                <div class="detail-info-row">
                    <div class="detail-info-item">
                        <small>Affected Computer</small>
                        <strong>
                            <i class="fa-solid fa-desktop" style="color: var(--primary);"></i>
                            <?= e($report['computer_code']) ?>
                        </strong>
                    </div>
                    <div class="detail-info-item">
                        <small>Laboratory</small>
                        <strong>
                            <i class="fa-solid fa-flask" style="color: var(--purple);"></i>
                            <?= e($report['lab_name']) ?>
                        </strong>
                    </div>
                    <div class="detail-info-item">
                        <small>Priority Level</small>
                        <strong>
                            <i class="fa-solid fa-flag" style="color: <?= $report['priority'] === 'high' ? 'var(--danger)' : ($report['priority'] === 'medium' ? 'var(--warning)' : 'var(--muted)') ?>;"></i>
                            <?= ucfirst(e($report['priority'])) ?>
                        </strong>
                    </div>
                </div>

                <div class="detail-description-box">
                    <label><i class="fa-regular fa-file-lines"></i> Problem Description</label>
                    <div class="detail-description-content"><?= e($report['description']) ?></div>
                </div>

                <?php if (!empty($report['report_image'])): ?>
                    <?php
                    $imgSrc = '';
                    if (str_starts_with($report['report_image'], 'http://') || str_starts_with($report['report_image'], 'https://')) {
                        $imgSrc = $report['report_image'];
                    } elseif (str_starts_with($report['report_image'], 'uploads/')) {
                        $imgSrc = BASE_URL . $report['report_image'];
                    } else {
                        $imgSrc = BASE_URL . 'uploads/reports/' . basename($report['report_image']);
                    }
                    ?>
                    <div class="detail-description-box" style="margin-top: 18px;">
                        <label><i class="fa-solid fa-camera"></i> Attached Photo / Evidence</label>
                        <div style="margin-top: 12px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border); background: #0f172a; text-align: center; position: relative;">
                            <a href="<?= e($imgSrc) ?>" target="_blank" title="Click to view full image in new tab">
                                <img src="<?= e($imgSrc) ?>" alt="Fault Evidence Photo"
                                     style="max-width: 100%; max-height: 380px; width: 100%; object-fit: contain; display: block; margin: 0 auto; background: #0b1120;">
                            </a>
                            <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(15, 23, 42, 0.85); color: #fff; padding: 5px 12px; border-radius: 6px; font-size: 11.5px; pointer-events: none;">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Full Image
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Staff Remarks & Assignment Card -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <i class="fa-solid fa-user-gear"></i> Technical Staff & Remarks
                    </span>
                    <?php if (!empty($report['resolved_at'])): ?>
                        <span class="badge badge-resolved">
                            <i class="fa-regular fa-circle-check"></i> Resolved on <?= date('d M Y, h:i A', strtotime($report['resolved_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="detail-info-row">
                    <div class="detail-info-item">
                        <small>Assigned Technician</small>
                        <strong>
                            <i class="fa-solid fa-id-badge" style="color: var(--primary);"></i>
                            <?= e($report['assigned_name'] ?: 'Not assigned yet') ?>
                        </strong>
                    </div>
                    <div class="detail-info-item">
                        <small>Current Status</small>
                        <strong>
                            <i class="fa-solid fa-arrows-rotate" style="color: var(--purple);"></i>
                            <?= e($curStatus) ?>
                        </strong>
                    </div>
                </div>

                <div class="remarks-banner">
                    <i class="fa-solid fa-comment-dots"></i>
                    <div class="remarks-banner-text">
                        <strong>Admin / Lab Assistant Remarks</strong>
                        <p><?= e($report['admin_remarks'] ?: 'No administrative remarks have been added for this report yet.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Status Timeline Card -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <i class="fa-solid fa-timeline"></i> Status Timeline & History
                    </span>
                </div>

                <div class="timeline-list">
                    <?php if (empty($history)): ?>
                        <div class="timeline-step">
                            <div class="timeline-dot"><i class="fa-solid fa-check"></i></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="timeline-status"><?= e($curStatus) ?></span>
                                    <span class="timeline-date"><?= date('d M Y, h:i A', strtotime($report['reported_at'])) ?></span>
                                </div>
                                <div class="timeline-note">Initial fault report submitted by student.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <?php
                            $stepStatus = $statuses[$h['status']] ?? ucfirst($h['status']);
                            $stepClass  = $h['status'] === 'resolved' ? 'resolved' : ($h['status'] === 'rejected' ? 'rejected' : '');
                            ?>
                            <div class="timeline-step <?= $stepClass ?>">
                                <div class="timeline-dot">
                                    <?php if ($h['status'] === 'resolved'): ?>
                                        <i class="fa-solid fa-check"></i>
                                    <?php elseif ($h['status'] === 'rejected'): ?>
                                        <i class="fa-solid fa-xmark"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle-dot"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-status"><?= e($stepStatus) ?></span>
                                        <span class="timeline-date"><?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></span>
                                    </div>
                                    <?php if (!empty($h['note'])): ?>
                                        <div class="timeline-note"><?= e($h['note']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($h['changed_by_name'])): ?>
                                        <div class="timeline-actor">
                                            <i class="fa-regular fa-user"></i> Updated by: <?= e($h['changed_by_name']) ?> (<?= ucfirst(e($h['changed_by_role'] ?? 'Staff')) ?>)
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right Column: Computer Specs & Quick Actions -->
        <div class="report-side-col">

            <!-- Computer Card -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <i class="fa-solid fa-desktop"></i> Computer Info
                    </span>
                    <span class="badge badge-<?= e($report['computer_status']) ?>">
                        <?= ucfirst(str_replace('_', ' ', e($report['computer_status']))) ?>
                    </span>
                </div>

                <div class="computer-spec-list">
                    <div class="computer-spec-item">
                        <span><i class="fa-solid fa-barcode"></i> Code</span>
                        <strong><?= e($report['computer_code']) ?></strong>
                    </div>
                    <div class="computer-spec-item">
                        <span><i class="fa-solid fa-flask"></i> Lab</span>
                        <strong><?= e($report['lab_name']) ?></strong>
                    </div>
                    <div class="computer-spec-item">
                        <span><i class="fa-solid fa-door-open"></i> Room</span>
                        <strong>Room <?= e($report['room_number']) ?></strong>
                    </div>
                    <div class="computer-spec-item">
                        <span><i class="fa-solid fa-microchip"></i> Processor</span>
                        <strong><?= e($report['processor'] ?: 'Standard CPU') ?></strong>
                    </div>
                    <div class="computer-spec-item">
                        <span><i class="fa-solid fa-memory"></i> RAM</span>
                        <strong><?= e($report['ram'] ?: 'Standard RAM') ?></strong>
                    </div>
                    <div class="computer-spec-item">
                        <span><i class="fa-solid fa-hard-drive"></i> Storage</span>
                        <strong><?= e($report['storage'] ?: 'Standard HDD/SSD') ?></strong>
                    </div>
                    <div class="computer-spec-item">
                        <span><i class="fa-brands fa-windows"></i> OS</span>
                        <strong><?= e($report['operating_system'] ?: 'Windows 11 Pro') ?></strong>
                    </div>
                </div>

                <div style="margin-top: 16px;">
                    <a href="<?= BASE_URL ?>computers.php?lab=<?= (int)$report['lab_id'] ?>" class="btn btn-light btn-block btn-sm">
                        <i class="fa-solid fa-list-check"></i> View Lab Computers
                    </a>
                </div>
            </div>

            <!-- Reporter Profile Card -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <i class="fa-solid fa-user-graduate"></i> Reporter
                    </span>
                </div>
                <div class="computer-spec-list">
                    <div class="computer-spec-item">
                        <span><i class="fa-regular fa-user"></i> Name</span>
                        <strong><?= e($report['reporter_name']) ?></strong>
                    </div>
                    <div class="computer-spec-item">
                        <span><i class="fa-solid fa-id-card"></i> Roll No.</span>
                        <strong><?= e($report['reporter_roll']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="detail-card side-actions-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <i class="fa-solid fa-bolt"></i> Actions
                    </span>
                </div>
                <a href="<?= BASE_URL ?>my-reports.php" class="btn btn-outline btn-block">
                    <i class="fa-solid fa-arrow-left"></i> Back to My Reports
                </a>
                <a href="<?= BASE_URL ?>report-issue.php?computer_id=<?= (int)$report['computer_id'] ?>" class="btn btn-primary btn-block">
                    <i class="fa-solid fa-circle-plus"></i> Report Another Issue
                </a>
            </div>

        </div>

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
