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

$pageTitle  = 'My Reports';
$activePage = 'my-reports';
$pageCss    = 'my-reports';

require_once __DIR__ . '/includes/header.php';

$statuses = [
    'pending'      => 'Pending',
    'under_review' => 'Under Review',
    'in_progress'  => 'In Progress',
    'resolved'     => 'Resolved',
    'rejected'     => 'Rejected',
];

$userId  = (int) $_SESSION['user']['id'];
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;

$reports    = [];
$totalPages = 1;
$offset     = 0;
$error      = '';

// Flash message
$flashMsg  = $_SESSION['flash_message'] ?? '';
$flashType = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

try {
    // Count my reports for pagination
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE user_id = ?");
    $stmtCount->bind_param('i', $userId);
    $stmtCount->execute();
    $total = $stmtCount->get_result()->fetch_row()[0];
    $stmtCount->close();

    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // Reports of the current page
    $stmt = $conn->prepare(
        "SELECT r.id, r.component, r.issue_title, r.description, r.priority, r.status,
                r.admin_remarks, r.report_image, r.reported_at,
                c.computer_code, l.lab_name, a.name AS assigned_name
         FROM fault_reports r
         JOIN computers c ON c.id = r.computer_id
         JOIN labs l ON l.id = c.lab_id
         LEFT JOIN users a ON a.id = r.assigned_to
         WHERE r.user_id = ?
         ORDER BY r.reported_at DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('iii', $userId, $perPage, $offset);
    $stmt->execute();
    $reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $error = 'Could not load your reports. Please try again.';
}
?>

<div class="page-heading">
    <h2>My Reports</h2>
    <p>Track the progress and status of your reported computer issues.</p>
</div>

<?php if ($flashMsg): ?>
    <div class="alert alert-<?= e($flashType) ?>"><?= e($flashMsg) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card my-reports-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Computer ID</th>
                    <th>Component</th>
                    <th>Issue Title</th>
                    <th>Reported Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="7" class="empty-text">
                            You have not submitted any reports yet. 
                            <a href="<?= BASE_URL ?>report-issue.php" style="color: var(--primary); font-weight: 600;">Report an issue here</a>.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($reports as $i => $r): ?>
                    <?php
                    $curStatus = $statuses[$r['status']] ?? ucfirst($r['status']);
                    ?>
                    <tr>
                        <td><?= $offset + $i + 1 ?></td>
                        <td><strong><?= e($r['computer_code']) ?></strong> <small>(<?= e($r['lab_name']) ?>)</small></td>
                        <td><?= ucfirst(e($r['component'])) ?></td>
                        <td>
                            <?= e($r['issue_title']) ?>
                            <?php if (!empty($r['report_image'])): ?>
                                <i class="fa-solid fa-camera" style="color: var(--primary); margin-left: 6px; font-size: 12px;" title="Includes photo attachment"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($r['reported_at'])) ?></td>
                        <td>
                            <span class="badge my-reports-badge-<?= e($r['status']) ?>"><?= e($curStatus) ?></span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>reports/details.php?id=<?= (int)$r['id'] ?>" class="my-reports-view-link">
                                <i class="fa-regular fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
    <div class="my-reports-pagination">
        <a class="my-reports-page <?= $page <= 1 ? 'disabled' : '' ?>" href="?page=<?= $page - 1 ?>">
            <i class="fa-solid fa-chevron-left"></i>
        </a>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="my-reports-page <?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>

        <a class="my-reports-page <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?page=<?= $page + 1 ?>">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
