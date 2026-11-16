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

$pageTitle  = 'Lab Information';
$activePage = 'lab-information';
$pageCss    = 'lab-information';

require_once __DIR__ . '/includes/header.php';

$labStatuses = [
    'active'      => 'Active',
    'maintenance' => 'Under Maintenance',
    'inactive'    => 'Inactive',
];

$userId        = (int) $_SESSION['user']['id'];
$labs          = [];
$announcements = [];
$error         = '';

try {
    // Every lab with the count of its computers by status
    $labs = $conn->query(
        "SELECT l.id, l.lab_name, l.lab_code, l.room_number, l.location, l.description,
                l.opening_hours, l.status,
                COUNT(c.id) AS total,
                COALESCE(SUM(c.status = 'available'), 0) AS available,
                COALESCE(SUM(c.status = 'in_use'), 0) AS in_use,
                COALESCE(SUM(c.status = 'under_maintenance'), 0) AS maintenance,
                COALESCE(SUM(c.status = 'out_of_service'), 0) AS out_of_service
         FROM labs l
         LEFT JOIN computers c ON c.lab_id = l.id
         WHERE l.status <> 'inactive'
         GROUP BY l.id
         ORDER BY l.lab_name"
    )->fetch_all(MYSQLI_ASSOC);

    // Lab announcements (displaying top 3 active announcements)
    $stmt = $conn->prepare(
        "SELECT n.message, n.type, n.created_at, l.lab_name
         FROM notifications n
         LEFT JOIN labs l ON l.id = n.lab_id
         WHERE n.user_id IS NULL
         ORDER BY n.created_at DESC
         LIMIT 3"
    );
    $stmt->execute();
    $announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $error = 'Could not load lab information. Please try again.';
}
?>

<div class="page-heading">
    <h2>Lab Information</h2>
    <p>View operating hours, location details, and real-time computer availability across our campus labs.</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="lab-information-layout">

    <!-- Lab cards -->
    <div class="lab-information-list">
        <?php if (empty($labs) && !$error): ?>
            <div class="card"><p class="empty-text">No active labs found.</p></div>
        <?php endif; ?>

        <?php foreach ($labs as $lab): ?>
            <?php 
                $percent = $lab['total'] > 0 ? round($lab['available'] / $lab['total'] * 100) : 0; 
                $statusLabel = $labStatuses[$lab['status']] ?? ucfirst($lab['status']);
            ?>
            <div class="card">

                <div class="lab-information-head">
                    <div>
                        <h3><?= e($lab['lab_name']) ?></h3>
                        <small style="font-weight: 600; color: var(--primary);"><?= e($lab['lab_code']) ?></small>
                    </div>
                    <span class="badge lab-information-badge-<?= e($lab['status']) ?>">
                        <?= e($statusLabel) ?>
                    </span>
                </div>

                <?php if ($lab['description']): ?>
                    <p class="lab-information-desc"><?= e($lab['description']) ?></p>
                <?php endif; ?>

                <ul class="lab-information-details">
                    <li><i class="fa-solid fa-door-open"></i> <?= e($lab['room_number']) ?></li>
                    <li><i class="fa-solid fa-location-dot"></i> <?= e($lab['location'] ?: '-') ?></li>
                    <li><i class="fa-solid fa-clock"></i> <?= e($lab['opening_hours']) ?></li>
                </ul>

                <div class="lab-information-stats">
                    <div><strong><?= (int)$lab['total'] ?></strong><small>Total PCs</small></div>
                    <div class="lab-information-available"><strong><?= (int)$lab['available'] ?></strong><small>Available</small></div>
                    <div class="lab-information-inuse"><strong><?= (int)$lab['in_use'] ?></strong><small>In Use</small></div>
                    <div class="lab-information-maintenance"><strong><?= (int)$lab['maintenance'] ?></strong><small>Maintenance</small></div>
                    <div class="lab-information-out"><strong><?= (int)$lab['out_of_service'] ?></strong><small>Out of Service</small></div>
                </div>

                <div class="lab-information-bar" title="<?= $percent ?>% available">
                    <span style="width: <?= $percent ?>%"></span>
                </div>

                <div class="lab-information-foot">
                    <small><strong><?= $percent ?>%</strong> computers currently available</small>
                    <div style="display: flex; gap: 8px;">
                        <a href="computers.php?lab=<?= $lab['id'] ?>" class="btn btn-light btn-sm">
                            <i class="fa-solid fa-desktop"></i> View Computers
                        </a>
                        <a href="report-issue.php" class="btn btn-sm">
                            <i class="fa-solid fa-plus"></i> Report Issue
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Announcements -->
    <aside class="card lab-information-news">
        <div class="card-title">
            <span><i class="fa-solid fa-bullhorn" style="color: var(--primary); margin-right: 6px;"></i> Announcements</span>
            <a href="notifications.php" style="font-size: 13px; font-weight: 600; color: var(--primary);">View All &rarr;</a>
        </div>

        <?php if (empty($announcements)): ?>
            <p class="empty-text">No announcements at this time.</p>
        <?php endif; ?>

        <?php foreach ($announcements as $note): ?>
            <div class="lab-information-note lab-information-note-<?= e($note['type']) ?>">
                <?php if (!empty($note['lab_name'])): ?>
                    <span class="lab-note-badge">
                        <i class="fa-solid fa-flask"></i> <?= e($note['lab_name']) ?>
                    </span>
                <?php else: ?>
                    <span class="lab-note-badge campus-wide">
                        <i class="fa-solid fa-bullhorn"></i> Campus Wide
                    </span>
                <?php endif; ?>
                <p><?= e($note['message']) ?></p>
                <small><i class="fa-regular fa-clock"></i> <?= date('d M Y, h:i A', strtotime($note['created_at'])) ?></small>
            </div>
        <?php endforeach; ?>
    </aside>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
