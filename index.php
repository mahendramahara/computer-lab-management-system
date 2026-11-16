<?php
// ── Bootstrap & Auth ──────────────────────────────────────────
require_once __DIR__ . '/config/db.php';

// Not logged in → login
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Admin / lab_assistant → admin panel
$role = $_SESSION['user']['role'] ?? '';
if ($role === 'admin' || $role === 'lab_assistant') {
    header('Location: ' . BASE_URL . 'admin/');
    exit;
}

// ── Page Setup ────────────────────────────────────────────────
$pageTitle  = 'Dashboard';
$activePage = 'index';

// ── Data: computer status counts ─────────────────────────────
$statusCounts = getRows($conn,
    "SELECT status, COUNT(*) AS total FROM computers GROUP BY status"
);
$counts = ['available' => 0, 'in_use' => 0, 'under_maintenance' => 0, 'out_of_service' => 0];
foreach ($statusCounts as $row) {
    $counts[$row['status']] = (int) $row['total'];
}
$totalComputers   = array_sum($counts);
$available        = $counts['available'];
$underMaintenance = $counts['under_maintenance'];
$outOfService     = $counts['out_of_service'];

// ── Data: lab availability ────────────────────────────────────
$labs = getRows($conn, "SELECT * FROM v_lab_availability ORDER BY lab_id");

// ── Data: recent notifications ───────────────────────────────
$notifications = getRows($conn,
    "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5"
);

// ── Donut chart percentages ───────────────────────────────────
$availablePct   = $totalComputers ? round($available / $totalComputers * 100) : 0;
$maintenancePct = $totalComputers ? round($underMaintenance / $totalComputers * 100) : 0;
$outPct         = $totalComputers ? 100 - $availablePct - $maintenancePct : 0;
$aStop          = $availablePct . '%';
$mStop          = ($availablePct + $maintenancePct) . '%';

// ── Helpers ───────────────────────────────────────────────────
function noticeClass(string $type): string {
    return match ($type) {
        'danger'  => 'index-notice-danger',
        'warning' => 'index-notice-warning',
        'success' => 'index-notice-success',
        default   => 'index-notice-info',
    };
}
function formatNotificationTime(string $datetime): string {
    $timestamp = strtotime($datetime);
    if (date('Y-m-d', $timestamp) === date('Y-m-d')) {
        return 'Today, ' . date('g:i A', $timestamp);
    }
    return date('M j, Y', $timestamp);
}

include __DIR__ . '/includes/header.php';
?>

    <!-- Hero Banner -->
    <div class="index-hero">
        <div class="index-hero-icon">
            <i class="fa-solid fa-desktop"></i>
        </div>
        <div>
            <span class="index-hero-tag">
                <i class="fa-solid fa-graduation-cap"></i>
                Mahendra Multiple Campus
            </span>
            <h2>Computer Lab<br>Management System</h2>
            <p>View available computers, report issues, and track maintenance in the campus computer lab.</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="index-stats">

        <div class="index-stat index-stat-primary">
            <div class="index-stat-icon"><i class="fa-solid fa-desktop"></i></div>
            <div>
                <span>Total Computers</span>
                <strong><?= $totalComputers ?></strong>
            </div>
        </div>

        <div class="index-stat index-stat-success">
            <div class="index-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <span>Available</span>
                <strong><?= $available ?></strong>
            </div>
        </div>

        <div class="index-stat index-stat-warning">
            <div class="index-stat-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div>
                <span>Under Maintenance</span>
                <strong><?= $underMaintenance ?></strong>
            </div>
        </div>

        <div class="index-stat index-stat-danger">
            <div class="index-stat-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div>
                <span>Out of Service</span>
                <strong><?= $outOfService ?></strong>
            </div>
        </div>

    </div>

    <!-- Middle Row: Lab Availability + Quick Actions -->
    <div class="index-grid">

        <div class="card">
            <div class="card-title">
                <span>Lab Availability</span>
                <a href="computers.php">View Labs <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <?php if (empty($labs)): ?>
                <p class="empty-text">No labs found.</p>
            <?php else: foreach ($labs as $lab): ?>
                <div class="index-lab">
                    <div class="index-lab-icon"><i class="fa-solid fa-desktop"></i></div>
                    <div class="index-lab-name">
                        <strong><?= e($lab['lab_name']) ?></strong>
                        <small><?= e($lab['location']) ?></small>
                    </div>
                    <span class="index-lab-total"><?= (int)$lab['total_computers'] ?> Computers</span>
                    <span class="badge badge-available"><?= (int)$lab['available_computers'] ?> Available</span>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="card">
            <div class="card-title"><span>Quick Actions</span></div>

            <a href="computers.php" class="index-action index-action-blue">
                <div class="index-action-icon"><i class="fa-solid fa-desktop"></i></div>
                <div class="index-action-text">
                    <strong>View All Computers</strong>
                    <small>Check status and details</small>
                </div>
                <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a href="report-issue.php" class="index-action index-action-purple">
                <div class="index-action-icon"><i class="fa-solid fa-plus"></i></div>
                <div class="index-action-text">
                    <strong>Report an Issue</strong>
                    <small>Facing a problem? Report here</small>
                </div>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>

    <!-- Bottom Row: Notifications + Status Donut -->
    <div class="index-grid" id="notifications">

        <div class="card">
            <div class="card-title">
                <span><i class="fa-solid fa-bell" style="color: var(--primary); margin-right: 8px;"></i> Recent Lab Notifications</span>
                <a href="notifications.php" style="font-size: 13px; font-weight: 600; color: var(--primary);">View All &rarr;</a>
            </div>

            <?php if (empty($notifications)): ?>
                <p class="empty-text">No notifications yet.</p>
            <?php else: foreach ($notifications as $n):
                $nc = noticeClass($n['type']);
            ?>
                <div class="index-notice">
                    <div class="index-notice-icon <?= $nc ?>">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <p><?= e($n['message']) ?></p>
                    <span class="index-notice-time"><?= formatNotificationTime($n['created_at']) ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="card">
            <div class="card-title"><span>System Status Overview</span></div>

            <div class="index-status">
                <div class="index-donut" style="--a:<?= $aStop ?>; --m:<?= $mStop ?>;">
                    <div>
                        <strong><?= $totalComputers ?></strong>
                        <small>Computers</small>
                    </div>
                </div>

                <ul class="index-legend">
                    <li>
                        <span class="index-dot index-dot-success"></span>
                        Available (<?= $available ?>)
                        <b><?= $availablePct ?>%</b>
                    </li>
                    <li>
                        <span class="index-dot index-dot-warning"></span>
                        Under Maintenance (<?= $underMaintenance ?>)
                        <b><?= $maintenancePct ?>%</b>
                    </li>
                    <li>
                        <span class="index-dot index-dot-danger"></span>
                        Out of Service (<?= $outOfService ?>)
                        <b><?= $outPct ?>%</b>
                    </li>
                </ul>
            </div>
        </div>

    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
