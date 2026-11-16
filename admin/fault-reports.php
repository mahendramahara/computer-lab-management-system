<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Report Management';
$activePage = 'reports';
$pageJs     = 'fault-reports.js';

$statusLabels = [
    'pending'      => 'Pending',
    'under_review' => 'Under Review',
    'in_progress'  => 'In Progress',
    'resolved'     => 'Resolved',
    'rejected'     => 'Rejected',
];

$priorityLabels = [
    'low'    => 'Low',
    'medium' => 'Medium',
    'high'   => 'High',
];

$components = [
    'cpu'      => 'CPU / Processor',
    'monitor'  => 'Monitor / Display',
    'keyboard' => 'Keyboard',
    'mouse'    => 'Mouse',
    'speaker'  => 'Speaker / Audio',
    'network'  => 'Network / LAN',
    'software' => 'Operating System / Software',
    'other'    => 'Other Hardware',
];

// Flash notification messages
$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'danger';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// ── Handle Actions Directly at the Top of this File ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task        = $_POST['task'] ?? '';
    $validStatus = ['pending', 'under_review', 'in_progress', 'resolved', 'rejected'];

    try {
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
                    $uploadDir = __DIR__ . '/../uploads/reports/';
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

            // Notify administrators of new fault report
            $pcInfoQuery = $conn->query("SELECT c.computer_code, l.lab_name, l.id AS lab_id FROM computers c JOIN labs l ON l.id = c.lab_id WHERE c.id = " . $computerId);
            $pcInfo      = $pcInfoQuery ? $pcInfoQuery->fetch_assoc() : null;
            $compCode    = $pcInfo['computer_code'] ?? 'PC';
            $labName     = $pcInfo['lab_name'] ?? 'Lab';
            $labIdVal    = $pcInfo['lab_id'] ?? null;

            $adminNotice = "New fault report on " . $compCode . " (" . $labName . "): " . $issueTitle . " [" . ucfirst($priority) . " Priority]";
            $stmtAdmNotif = $conn->prepare("INSERT INTO notifications (user_id, lab_id, message, type) VALUES (NULL, ?, ?, 'danger')");
            $stmtAdmNotif->bind_param('is', $labIdVal, $adminNotice);
            $stmtAdmNotif->execute();
            $stmtAdmNotif->close();

            $_SESSION['flash_message'] = 'Fault report #FR-' . str_pad($newId, 4, '0', STR_PAD_LEFT) . ' created successfully.';
            $_SESSION['flash_type']    = 'success';
        } elseif ($task === 'update_status') {
            $reportId   = (int) ($_POST['report_id'] ?? 0);
            $status     = $_POST['status'] ?? '';
            $assignedTo = !empty($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
            $remarks    = trim($_POST['admin_remarks'] ?? '');

            if ($reportId <= 0 || !in_array($status, $validStatus, true)) {
                throw new Exception('Invalid report or status selection.');
            }

            $stmt = $conn->prepare("SELECT r.user_id, r.computer_id, r.status, c.computer_code, l.lab_name FROM fault_reports r JOIN computers c ON c.id = r.computer_id JOIN labs l ON l.id = c.lab_id WHERE r.id = ?");
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
            if (!empty($report['user_id'])) {
                $notifMsg = "Your fault report #FR-" . str_pad($reportId, 4, '0', STR_PAD_LEFT) . " for " . $report['computer_code'] . " status is now " . ucfirst(str_replace('_', ' ', $status)) . ".";
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

    header('Location: fault-reports.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

// Filter parameters
$statusFilter   = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$search         = trim($_GET['search'] ?? '');
$page           = max(1, (int) ($_GET['page'] ?? 1));
$perPage        = 10;

// Dynamic WHERE clauses
$where  = " WHERE 1 = 1";
$params = [];
$types  = "";

if ($statusFilter !== '' && isset($statusLabels[$statusFilter])) {
    $where   .= " AND r.status = ?";
    $params[] = $statusFilter;
    $types   .= "s";
}

if ($priorityFilter !== '' && isset($priorityLabels[$priorityFilter])) {
    $where   .= " AND r.priority = ?";
    $params[] = $priorityFilter;
    $types   .= "s";
}

if ($search !== '') {
    $where   .= " AND (r.issue_title LIKE ? OR r.description LIKE ? OR c.computer_code LIKE ? OR u.name LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= "ssss";
}

$reports      = [];
$staffUsers   = [];
$allComputers = [];
$totalCount   = 0;
$totalPages   = 1;

try {
    // Computers for Add Report modal
    $allComputers = $conn->query(
        "SELECT c.id, c.computer_code, l.lab_name 
         FROM computers c 
         JOIN labs l ON l.id = c.lab_id 
         ORDER BY c.computer_code"
    )->fetch_all(MYSQLI_ASSOC);

    // Staff for assignment dropdown
    $staffUsers = $conn->query(
        "SELECT id, name, role FROM users WHERE role IN ('admin', 'lab_assistant') AND status = 'active' ORDER BY name"
    )->fetch_all(MYSQLI_ASSOC);

    // Count total rows for pagination
    $countSql = "SELECT COUNT(*) FROM fault_reports r
                 JOIN computers c ON c.id = r.computer_id
                 JOIN users u ON u.id = r.user_id" . $where;
    $stmt = $conn->prepare($countSql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $totalCount = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // Fetch reports
    $dataSql = "SELECT r.*, c.computer_code, l.lab_name, l.room_number, u.name AS student_name, u.roll_number, u.email AS student_email,
                       t.name AS assigned_name
                FROM fault_reports r
                JOIN computers c ON c.id = r.computer_id
                JOIN labs l ON l.id = c.lab_id
                JOIN users u ON u.id = r.user_id
                LEFT JOIN users t ON t.id = r.assigned_to" . $where . "
                ORDER BY r.reported_at DESC
                LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($dataSql);
    $typesWithPaging = $types . "ii";
    $paramsWithPaging = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($typesWithPaging, ...$paramsWithPaging);
    $stmt->execute();
    $reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $message = 'Error loading reports: ' . $e->getMessage();
    $type    = 'danger';
}

$filters = array_filter(['status' => $statusFilter, 'priority' => $priorityFilter, 'search' => $search]);
?>

<div class="computers-head">
    <div>
        <h2>Report Management</h2>
        <small><a href="index.php">Dashboard</a> &gt; Fault Reports</small>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-light" onclick="window.print()"><i class="fa-solid fa-file-export"></i> Export</button>
        <button type="button" class="btn" id="openAddReportBtn"><i class="fa-solid fa-plus"></i> Add Report</button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Search and Filter Bar -->
<form method="get" class="admin-filters">
    <div class="admin-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Search by Computer ID, issue, or student..."
               value="<?= htmlspecialchars($search) ?>">
    </div>

    <select name="status" class="form-control" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach ($statusLabels as $k => $label): ?>
            <option value="<?= $k ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>

    <select name="priority" class="form-control" onchange="this.form.submit()">
        <option value="">All Priorities</option>
        <?php foreach ($priorityLabels as $k => $label): ?>
            <option value="<?= $k ?>" <?= $priorityFilter === $k ? 'selected' : '' ?>><?= $label ?> Priority</option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="admin-filters-btn">
        <i class="fa-solid fa-magnifying-glass"></i> Search
    </button>

    <?php if (!empty($search) || !empty($statusFilter) || !empty($priorityFilter)): ?>
        <a href="fault-reports.php" class="admin-filters-clear" title="Clear Filters">
            <i class="fa-solid fa-xmark"></i> Clear Filters
        </a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 70px; text-align: center;">Photo</th>
                    <th style="width: 110px;">Computer ID</th>
                    <th style="max-width: 210px; width: 210px;">Issue</th>
                    <th style="width: 150px;">Reported By</th>
                    <th style="width: 125px; text-align: center;">Status</th>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 75px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="8" class="empty-cell">No fault reports match your criteria.</td></tr>
                <?php endif; ?>

                <?php foreach ($reports as $idx => $r): ?>
                    <?php
                    $sColor = match($r['status']) {
                        'pending'      => 'warning',
                        'under_review' => 'purple',
                        'in_progress'  => 'primary',
                        'resolved'     => 'success',
                        'rejected'     => 'danger',
                        default        => 'primary'
                    };

                    $imgUrl = '';
                    if (!empty($r['report_image'])) {
                        if (str_starts_with($r['report_image'], 'http://') || str_starts_with($r['report_image'], 'https://')) {
                            $imgUrl = $r['report_image'];
                        } elseif (str_starts_with($r['report_image'], 'uploads/')) {
                            $imgUrl = BASE_URL . $r['report_image'];
                        } else {
                            $imgUrl = BASE_URL . 'uploads/reports/' . basename($r['report_image']);
                        }
                    }
                    ?>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">#<?= str_pad($offset + $idx + 1, 3, '0', STR_PAD_LEFT) ?></td>
                        <td style="text-align: center;">
                            <?php if ($imgUrl): ?>
                                <a href="javascript:void(0)"
                                   onclick="viewFaultPhoto('<?= htmlspecialchars($imgUrl, ENT_QUOTES) ?>', '<?= htmlspecialchars($r['computer_code'] . ' - ' . $r['issue_title'], ENT_QUOTES) ?>')"
                                   class="report-thumb-box"
                                   title="Click to view attached evidence">
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Fault Photo" class="report-thumb-img" loading="lazy">
                                </a>
                            <?php else: ?>
                                <span class="report-thumb-box report-thumb-placeholder" title="No photo attached">
                                    <i class="fa-regular fa-image"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($r['computer_code']) ?></strong></td>
                        <td style="max-width: 210px; width: 210px;">
                            <strong style="color: #0f1b3d; display: block; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"
                                    title="<?= htmlspecialchars($r['issue_title']) ?>">
                                <?= htmlspecialchars($r['issue_title']) ?>
                            </strong>
                            <p style="font-size: 12px; color: #64748b; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"
                               title="<?= htmlspecialchars($r['description']) ?>">
                                <?= htmlspecialchars($r['description']) ?>
                            </p>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($r['student_name']) ?></strong>
                            <small style="display: block; color: #64748b; font-size: 11.5px;"><?= htmlspecialchars($r['roll_number'] ?? 'Student') ?></small>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-<?= $sColor ?>"><?= $statusLabels[$r['status']] ?? ucfirst($r['status']) ?></span>
                        </td>
                        <td style="font-size: 12.5px; color: #64748b;"><?= date('M d, Y', strtotime($r['reported_at'])) ?></td>
                        <td style="text-align: center;">
                            <button type="button" class="labs-tool review-report-btn" style="margin: 0 auto;"
                                    data-report="<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>"
                                    title="View & Review Report">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="computers-pagination">
            <a class="computers-page <?= $page <= 1 ? 'disabled' : '' ?>"
               href="?<?= http_build_query($filters + ['page' => $page - 1]) ?>">
                <i class="fa-solid fa-chevron-left"></i>
            </a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="computers-page <?= $i === $page ? 'active' : '' ?>"
                   href="?<?= http_build_query($filters + ['page' => $i]) ?>"><?= $i ?></a>
            <?php endfor; ?>

            <a class="computers-page <?= $page >= $totalPages ? 'disabled' : '' ?>"
               href="?<?= http_build_query($filters + ['page' => $page + 1]) ?>">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Review & Status Update Big Modal -->
<div class="labs-modal" id="reviewModal">
    <form action="fault-reports.php" method="post" class="labs-dialog modal-big-dialog" id="reviewForm">
        <input type="hidden" name="task" value="update_status">
        <input type="hidden" name="report_id" id="modal_report_id" value="">

        <!-- Modal Top Bar -->
        <div class="card-title modal-big-title">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span id="reviewModalTitle" style="font-size: 17px; font-weight: 700; color: #0f172a;">Review Fault Report</span>
                <span id="modal_status_badge" class="badge badge-warning">Pending</span>
                <span id="modal_priority_badge" class="badge badge-danger">High Priority</span>
            </div>
            <button type="button" class="btn btn-light btn-sm" id="reviewClose" title="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Modal Body Content: 2 Columns -->
        <div class="modal-big-body">
            
            <!-- Left Column: Photo Evidence & Student Info -->
            <div class="modal-col-left">
                
                <!-- Attached Photo Card -->
                <div class="modal-evidence-card">
                    <div class="evidence-header">
                        <i class="fa-solid fa-camera"></i> Attached Photo / Evidence
                    </div>
                    
                    <div id="modal_image_wrap" class="evidence-img-container" title="Click to view full image">
                        <img id="modal_image_src" src="" alt="Fault Photo" class="evidence-preview-img">
                        <div class="evidence-zoom-hint">
                            <i class="fa-solid fa-magnifying-glass-plus"></i> View Full Image
                        </div>
                    </div>
                    
                    <div id="modal_no_image" class="evidence-placeholder" style="display: none;">
                        <i class="fa-regular fa-image"></i>
                        <span>No photo attached with this report</span>
                    </div>
                </div>

                <!-- Reporter Student Details -->
                <div class="modal-info-card">
                    <div class="info-card-header"><i class="fa-solid fa-user-graduate"></i> Reporter Details</div>
                    <div class="info-card-row">
                        <span class="info-label">Name:</span>
                        <strong id="modal_student_name" class="info-val">Ram Kumar Gurung</strong>
                    </div>
                    <div class="info-card-row">
                        <span class="info-label">Roll Number:</span>
                        <span id="modal_student_roll" class="info-val">MMC-BCA-2026-001</span>
                    </div>
                    <div class="info-card-row">
                        <span class="info-label">Email:</span>
                        <span id="modal_student_email" class="info-val" style="font-size: 11.5px; color: #64748b;">ram@mmc.edu.np</span>
                    </div>
                    <div class="info-card-row">
                        <span class="info-label">Date:</span>
                        <span id="modal_report_date" class="info-val">Nov 14, 2026</span>
                    </div>
                </div>

            </div>

            <!-- Right Column: Issue Details & Admin Action Form -->
            <div class="modal-col-right">

                <!-- Hardware & Location Info -->
                <div class="hardware-info-bar">
                    <div class="hardware-stat">
                        <small>Computer ID</small>
                        <strong id="modal_pc_code" style="color: #1d63e8;">PC-014</strong>
                    </div>
                    <div class="hardware-stat">
                        <small>Laboratory</small>
                        <strong id="modal_lab_name">Computer Lab - 1</strong>
                    </div>
                    <div class="hardware-stat">
                        <small>Component</small>
                        <strong id="modal_component_name" style="text-transform: capitalize;">Keyboard</strong>
                    </div>
                </div>

                <!-- Issue Details Card -->
                <div class="issue-details-card">
                    <div class="issue-card-title">
                        <small>Problem Summary</small>
                        <h4 id="modal_issue_title">Keyboard not working</h4>
                    </div>
                    <div class="issue-card-desc">
                        <small>Detailed Description</small>
                        <p id="modal_issue_desc"></p>
                    </div>
                </div>

                <!-- Status Update & Assignment Section -->
                <div class="action-section-box">
                    <h5 class="action-section-title"><i class="fa-solid fa-sliders"></i> Update Status & Assignment</h5>
                    
                    <div class="labs-form-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 12px;">
                        <div class="form-group">
                            <label for="modal_status" style="font-weight: 600; font-size: 12.5px;">Update Status *</label>
                            <select name="status" id="modal_status" class="form-control" required>
                                <?php foreach ($statusLabels as $k => $label): ?>
                                    <option value="<?= $k ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="modal_assigned_to" style="font-weight: 600; font-size: 12.5px;">Assign Staff / Technician</label>
                            <select name="assigned_to" id="modal_assigned_to" class="form-control">
                                <option value="">Unassigned</option>
                                <?php foreach ($staffUsers as $staff): ?>
                                    <option value="<?= $staff['id'] ?>">
                                        <?= htmlspecialchars($staff['name']) ?> (<?= $staff['role'] === 'admin' ? 'Admin' : 'Lab Assistant' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="modal_admin_remarks" style="font-weight: 600; font-size: 12.5px;">Resolution Notes / Remarks</label>
                        <textarea name="admin_remarks" id="modal_admin_remarks" class="form-control" rows="3"
                                  placeholder="Add repair findings, replacement details, or internal notes..."></textarea>
                    </div>
                </div>

            </div>

        </div>

        <!-- Modal Footer Actions -->
        <div class="modal-big-footer">
            <button type="button" class="btn btn-light" id="modalCancelBtn">
                <i class="fa-solid fa-xmark"></i> Close
            </button>
            <button type="submit" class="btn btn-primary" style="background: #1d63e8;">
                <i class="fa-solid fa-floppy-disk"></i> Save Status Changes
            </button>
        </div>
    </form>
</div>

<!-- Modal Dialog: Admin Direct Log Fault Report -->
<div class="labs-modal" id="addReportModal">
    <form action="fault-reports.php" method="post" enctype="multipart/form-data" class="labs-dialog" style="max-width: 620px;">
        <input type="hidden" name="task" value="add_report">

        <div class="card-title">
            <span><i class="fa-solid fa-plus-circle" style="color: #1d63e8;"></i> Log New Fault Report</span>
            <button type="button" class="btn btn-light btn-sm" id="closeAddReportBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="labs-form-grid">
            <div class="form-group">
                <label for="add_pc">Select Computer *</label>
                <select name="computer_id" id="add_pc" class="form-control" required>
                    <option value="">Choose Computer</option>
                    <?php foreach ($allComputers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['computer_code']) ?> (<?= htmlspecialchars($c['lab_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="add_comp">Faulty Component *</label>
                <select name="component" id="add_comp" class="form-control" required>
                    <?php foreach ($components as $k => $label): ?>
                        <option value="<?= $k ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="add_priority">Priority Level</label>
                <select name="priority" id="add_priority" class="form-control">
                    <option value="low">Low Priority</option>
                    <option value="medium" selected>Medium Priority</option>
                    <option value="high">High Priority</option>
                </select>
            </div>

            <div class="form-group">
                <label for="add_status">Initial Status</label>
                <select name="status" id="add_status" class="form-control">
                    <option value="pending" selected>Pending</option>
                    <option value="under_review">Under Review</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="add_title">Issue Title</label>
            <input type="text" name="issue_title" id="add_title" class="form-control" placeholder="e.g. Keyboard missing keys, Monitor flickering">
        </div>

        <div class="form-group">
            <label for="add_desc">Problem Description *</label>
            <textarea name="description" id="add_desc" class="form-control" rows="3" placeholder="Describe the fault or failure in detail..." required></textarea>
        </div>

        <div class="labs-form-grid">
            <div class="form-group">
                <label for="add_assign">Assign Technician</label>
                <select name="assigned_to" id="add_assign" class="form-control">
                    <option value="">Unassigned</option>
                    <?php foreach ($staffUsers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="add_img"><i class="fa-solid fa-camera"></i> Photo Attachment</label>
                <input type="file" name="report_image" id="add_img" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
            <button type="button" class="btn btn-light" id="cancelAddReportBtn">Cancel</button>
            <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Submit Report</button>
        </div>
    </form>
</div>

<!-- Photo Lightbox Modal -->
<div class="labs-modal" id="photoModal">
    <div class="labs-dialog" style="max-width: 640px; text-align: center; padding: 22px;">
        <div class="card-title" style="margin-bottom: 14px;">
            <span id="photoModalTitle"><i class="fa-solid fa-camera" style="color: #1d63e8;"></i> Fault Photo Evidence</span>
            <button type="button" class="btn btn-light btn-sm" id="closePhotoModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="background: #0f172a; border-radius: 10px; overflow: hidden; max-height: 460px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            <img id="photoModalImg" src="" alt="Fault Photo" style="max-width: 100%; max-height: 460px; object-fit: contain;">
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
