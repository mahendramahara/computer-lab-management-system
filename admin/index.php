<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/includes/header.php';

// Status labels and badge color map
$statusLabels = [
    'pending'      => 'Pending',
    'under_review' => 'Under Review',
    'in_progress'  => 'In Progress',
    'resolved'     => 'Resolved',
    'rejected'     => 'Rejected',
];

$statusBadges = [
    'pending'      => 'warning',
    'under_review' => 'primary',
    'in_progress'  => 'purple',
    'resolved'     => 'success',
    'rejected'     => 'danger',
];

// Helper to format friendly relative time
function timeAgo($date) {
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    return floor($diff / 86400) . ' day ago';
}

$counts = ['available' => 32, 'in_use' => 0, 'under_maintenance' => 5, 'out_of_service' => 3];
$labs   = [];
$faults = [];
$records = [];
$activities = [];

try {
    // Computer status counts
    foreach ($conn->query("SELECT status, COUNT(*) AS total FROM computers GROUP BY status") as $row) {
        $counts[$row['status']] = (int) $row['total'];
    }

    // Laboratory computer availability
    $labs = $conn->query("SELECT * FROM v_lab_availability ORDER BY lab_name")->fetch_all(MYSQLI_ASSOC);

    // Latest student fault reports
    $faults = $conn->query(
        "SELECT r.id, r.issue_title, r.status, r.reported_at, c.computer_code, u.name
         FROM fault_reports r
         JOIN computers c ON c.id = r.computer_id
         JOIN users u ON u.id = r.user_id
         ORDER BY r.reported_at DESC
         LIMIT 4"
    )->fetch_all(MYSQLI_ASSOC);

    // Latest maintenance and repair records
    $records = $conn->query(
        "SELECT m.id, m.component, m.maintenance_type, m.maintenance_date, m.repair_description,
                c.computer_code, t.name AS technician
         FROM maintenance_records m
         JOIN computers c ON c.id = m.computer_id
         LEFT JOIN users t ON t.id = m.technician_id
         ORDER BY m.maintenance_date DESC, m.id DESC
         LIMIT 3"
    )->fetch_all(MYSQLI_ASSOC);

    // Recent activity audit trail
    $activities = $conn->query(
        "SELECT h.status, h.note, h.created_at, u.name, c.computer_code
         FROM report_status_history h
         JOIN fault_reports r ON r.id = h.fault_report_id
         JOIN computers c ON c.id = r.computer_id
         LEFT JOIN users u ON u.id = h.changed_by
         ORDER BY h.created_at DESC
         LIMIT 4"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    // Continue with resilient defaults
}

$totalComputers = array_sum($counts);
$pctAvailable   = $totalComputers > 0 ? round(($counts['available'] / $totalComputers) * 100) : 80;
$pctMaintenance = $totalComputers > 0 ? round(($counts['under_maintenance'] / $totalComputers) * 100) : 12;
$pctOutOfService = $totalComputers > 0 ? round(($counts['out_of_service'] / $totalComputers) * 100) : 8;

// Conic gradient degree stops for donut chart
$stopGreen  = $pctAvailable;
$stopOrange = $stopGreen + $pctMaintenance;
$donutGrad  = "var(--success) 0% {$stopGreen}%, var(--warning) {$stopGreen}% {$stopOrange}%, var(--danger) {$stopOrange}% 100%";
?>

<!-- Welcome Banner -->
<section class="admin-welcome-banner">
    <div class="banner-content">
        <h2>Welcome Back, <?= htmlspecialchars($adminName) ?>!</h2>
        <p>Manage your campus computer labs efficiently.</p>
        <div class="banner-underline"></div>
    </div>
</section>

<!-- 4 Key Metric Cards -->
<section class="stat-cards-grid">

    <!-- Total Computers -->
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-primary">
                <i class="fa-solid fa-desktop"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Computers</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $totalComputers ?></strong>
                    <span class="stat-trend trend-up"><i class="fa-solid fa-arrow-up"></i> 2%</span>
                </div>
            </div>
        </div>
        <div class="stat-sparkline">
            <svg viewBox="0 0 70 30" class="sparkline-svg">
                <path d="M0,22 Q15,8 30,18 T70,5" fill="none" stroke="#1d63e8" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <!-- Available Computers -->
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-success">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Available</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $counts['available'] ?></strong>
                    <span class="stat-trend trend-up"><i class="fa-solid fa-arrow-up"></i> 4%</span>
                </div>
            </div>
        </div>
        <div class="stat-sparkline">
            <svg viewBox="0 0 70 30" class="sparkline-svg">
                <path d="M0,25 Q18,12 35,20 T70,4" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <!-- Under Maintenance -->
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-warning">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Under Maintenance</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $counts['under_maintenance'] ?></strong>
                    <span class="stat-trend trend-down"><i class="fa-solid fa-arrow-down"></i> 1%</span>
                </div>
            </div>
        </div>
        <div class="stat-sparkline">
            <svg viewBox="0 0 70 30" class="sparkline-svg">
                <path d="M0,8 Q20,24 40,12 T70,22" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <!-- Out of Service -->
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Out of Service</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $counts['out_of_service'] ?></strong>
                    <span class="stat-trend trend-neutral"><i class="fa-solid fa-arrow-right"></i> 0%</span>
                </div>
            </div>
        </div>
        <div class="stat-sparkline">
            <svg viewBox="0 0 70 30" class="sparkline-svg">
                <path d="M0,15 Q25,24 45,16 T70,14" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

</section>

<!-- Middle Section: Overview, Performance Chart & Quick Actions -->
<section class="dashboard-row-middle">

    <!-- Status Overview Donut -->
    <div class="dashboard-card status-overview-card">
        <div class="card-header-flex">
            <div class="card-title-group">
                <i class="fa-solid fa-chart-simple card-title-icon"></i>
                <span class="card-title-text">Computer Status Overview</span>
            </div>
            <button type="button" class="pill-dropdown-btn">
                This Month <i class="fa-solid fa-chevron-down"></i>
            </button>
        </div>

        <div class="donut-chart-layout">
            <div class="donut-chart-graphic" style="background: conic-gradient(<?= $donutGrad ?>)">
                <div class="donut-center-circle">
                    <strong><?= $totalComputers ?></strong>
                    <small>Total</small>
                </div>
            </div>

            <ul class="donut-legend-list">
                <li>
                    <span class="legend-dot dot-success"></span>
                    <span class="legend-name">Available</span>
                    <span class="legend-val"><?= $counts['available'] ?> (<?= $pctAvailable ?>%)</span>
                </li>
                <li>
                    <span class="legend-dot dot-warning"></span>
                    <span class="legend-name">Under Maintenance</span>
                    <span class="legend-val"><?= $counts['under_maintenance'] ?> (<?= $pctMaintenance ?>%)</span>
                </li>
                <li>
                    <span class="legend-dot dot-danger"></span>
                    <span class="legend-name">Out of Service</span>
                    <span class="legend-val"><?= $counts['out_of_service'] ?> (<?= $pctOutOfService ?>%)</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Lab Performance Line Chart -->
    <div class="dashboard-card lab-performance-card">
        <div class="card-header-flex">
            <div class="card-title-group">
                <i class="fa-solid fa-chart-line card-title-icon"></i>
                <span class="card-title-text">Lab Performance</span>
            </div>
            <div class="chart-legend-keys">
                <span><i class="chart-key-dot key-this"></i> This Month</span>
                <span><i class="chart-key-dot key-last"></i> Last Month</span>
            </div>
        </div>

        <div class="performance-chart-container">
            <svg viewBox="0 0 500 200" class="performance-svg" width="100%" height="185">
                <defs>
                    <linearGradient id="chartAreaGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#1d63e8" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#1d63e8" stop-opacity="0.01"/>
                    </linearGradient>
                </defs>

                <!-- Horizontal grid lines and y-axis ticks -->
                <line x1="30" y1="20" x2="480" y2="20" class="chart-grid-line"/>
                <text x="22" y="24" class="chart-axis-label">40</text>

                <line x1="30" y1="60" x2="480" y2="60" class="chart-grid-line"/>
                <text x="22" y="64" class="chart-axis-label">30</text>

                <line x1="30" y1="100" x2="480" y2="100" class="chart-grid-line"/>
                <text x="22" y="104" class="chart-axis-label">20</text>

                <line x1="30" y1="140" x2="480" y2="140" class="chart-grid-line"/>
                <text x="22" y="144" class="chart-axis-label">10</text>

                <line x1="30" y1="170" x2="480" y2="170" class="chart-grid-line"/>
                <text x="22" y="174" class="chart-axis-label">0</text>

                <!-- Area fill under This Month curve -->
                <path d="M 40,140 C 90,80 130,95 180,110 C 230,125 280,70 340,55 C 400,45 440,75 470,60 L 470,170 L 40,170 Z"
                      fill="url(#chartAreaGradient)"/>

                <!-- Last Month background reference curve -->
                <path d="M 40,150 C 90,120 140,110 200,125 C 260,135 310,95 360,80 C 410,70 440,85 470,80"
                      fill="none" stroke="#93c5fd" stroke-width="2" stroke-dasharray="4,4"/>

                <!-- This Month primary smooth curve -->
                <path d="M 40,140 C 90,80 130,95 180,110 C 230,125 280,70 340,55 C 400,45 440,75 470,60"
                      fill="none" stroke="#1d63e8" stroke-width="2.6" stroke-linecap="round"/>

                <!-- Data points -->
                <circle cx="40" cy="140" r="4.5" class="chart-data-point"/>
                <circle cx="180" cy="110" r="4.5" class="chart-data-point"/>
                <circle cx="280" cy="122" r="4.5" class="chart-data-point"/>
                <circle cx="340" cy="55" r="4.5" class="chart-data-point"/>
                <circle cx="470" cy="60" r="4.5" class="chart-data-point"/>

                <!-- X-Axis Day Labels -->
                <text x="40" y="192" text-anchor="middle" class="chart-day-label">Mon</text>
                <text x="125" y="192" text-anchor="middle" class="chart-day-label">Tue</text>
                <text x="210" y="192" text-anchor="middle" class="chart-day-label">Wed</text>
                <text x="295" y="192" text-anchor="middle" class="chart-day-label">Fri</text>
                <text x="380" y="192" text-anchor="middle" class="chart-day-label">Sat</text>
                <text x="465" y="192" text-anchor="middle" class="chart-day-label">Sun</text>
            </svg>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="quick-actions-card">
        <div class="quick-action-icon-circle">
            <i class="fa-solid fa-desktop"></i>
            <i class="fa-solid fa-gear gear-badge"></i>
        </div>
        <h3 class="quick-title">Quick Actions</h3>
        <p class="quick-sub">Perform common tasks quickly</p>

        <div class="quick-btn-group">
            <a href="computers.php?action=add" class="quick-action-btn">
                <i class="fa-solid fa-plus"></i> Add Computer
            </a>
            <a href="maintenance.php" class="quick-action-btn">
                <i class="fa-solid fa-screwdriver-wrench"></i> Report Maintenance
            </a>
            <a href="fault-reports.php" class="quick-action-btn">
                <i class="fa-solid fa-chart-simple"></i> View Reports
            </a>
        </div>
    </div>

</section>

<!-- Tables Section: Recent Fault Reports & Maintenance Records -->
<section class="dashboard-row-tables">

    <!-- Recent Fault Reports Table Card -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div class="card-title-group">
                <i class="fa-solid fa-bullhorn card-title-icon"></i>
                <span class="card-title-text">Recent Fault Reports</span>
            </div>
            <a href="fault-reports.php" class="card-link-action">View All <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="table-wrapper">
            <table class="dashboard-table table-faults">
                <thead>
                    <tr>
                        <th class="th-num">#</th>
                        <th class="th-code">Computer ID</th>
                        <th class="th-issue">Issue</th>
                        <th class="th-user">Reported By</th>
                        <th class="th-status">Status</th>
                        <th class="th-date">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faults)): ?>
                        <tr><td colspan="6" class="empty-cell">No fault reports logged yet.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($faults as $i => $fault): ?>
                        <?php
                        $st = $fault['status'];
                        $badgeClass = $statusBadges[$st] ?? 'primary';
                        $label      = $statusLabels[$st] ?? ucfirst($st);
                        ?>
                        <tr>
                            <td class="td-num"><?= $i + 1 ?></td>
                            <td class="td-code"><strong>#FR-<?= str_pad($fault['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td class="td-issue">
                                <span class="truncate-cell" title="<?= htmlspecialchars($fault['issue_title']) ?>">
                                    <?= htmlspecialchars($fault['issue_title']) ?>
                                </span>
                            </td>
                            <td class="td-user">
                                <span class="truncate-cell" title="<?= htmlspecialchars($fault['name']) ?>">
                                    <?= htmlspecialchars($fault['name']) ?>
                                </span>
                            </td>
                            <td class="td-status"><span class="badge badge-<?= $badgeClass ?>"><?= $label ?></span></td>
                            <td class="td-date"><?= date('M d, Y', strtotime($fault['reported_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Maintenance Records Table Card -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div class="card-title-group">
                <i class="fa-solid fa-screwdriver-wrench card-title-icon"></i>
                <span class="card-title-text">Maintenance Records</span>
            </div>
            <a href="maintenance.php" class="card-link-action">View All <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="table-wrapper">
            <table class="dashboard-table table-maint">
                <thead>
                    <tr>
                        <th class="th-num">#</th>
                        <th class="th-code">Computer ID</th>
                        <th class="th-action">Action</th>
                        <th class="th-tech">Technician</th>
                        <th class="th-date">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="5" class="empty-cell">No maintenance records logged yet.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($records as $i => $rec): ?>
                        <?php
                        $actionText = ucfirst($rec['component'] ?? 'System') . ' ' . ucfirst($rec['maintenance_type'] ?? 'Repair');
                        if (!empty($rec['repair_description'])) {
                            $actionText = $rec['repair_description'];
                        }
                        ?>
                        <tr>
                            <td class="td-num"><?= $i + 1 ?></td>
                            <td class="td-code"><strong>#MN-<?= str_pad($rec['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                            <td class="td-action">
                                <span class="truncate-cell" title="<?= htmlspecialchars($actionText) ?>">
                                    <?= htmlspecialchars($actionText) ?>
                                </span>
                            </td>
                            <td class="td-tech">
                                <span class="truncate-cell" title="<?= htmlspecialchars($rec['technician'] ?: 'Ramesh KC') ?>">
                                    <?= htmlspecialchars($rec['technician'] ?: 'Ramesh KC') ?>
                                </span>
                            </td>
                            <td class="td-date"><?= date('M d, Y', strtotime($rec['maintenance_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</section>

<!-- Bottom Section: Lab Distribution, Activity & System Status -->
<section class="dashboard-row-bottom">

    <!-- Lab-wise Computer Distribution -->
    <div class="dashboard-card lab-dist-card">
        <div class="card-header-flex">
            <div class="card-title-group">
                <i class="fa-solid fa-building card-title-icon"></i>
                <span class="card-title-text">Lab-wise Computer Distribution</span>
            </div>
            <button type="button" class="pill-dropdown-btn">
                All Labs <i class="fa-solid fa-chevron-down"></i>
            </button>
        </div>

        <div class="lab-dist-grid">
            <?php foreach ($labs as $idx => $lab): ?>
                <?php
                $tot = (int)$lab['total_computers'];
                $avl = (int)$lab['available_computers'];
                $pct = $tot > 0 ? round(($avl / $tot) * 100) : 0;
                $barClass = ($idx === 0) ? 'bar-green' : 'bar-blue';
                ?>
                <div class="lab-dist-item">
                    <div class="lab-dist-head">
                        <i class="fa-solid fa-desktop lab-item-icon"></i>
                        <div class="lab-item-meta">
                            <strong><?= htmlspecialchars($lab['lab_name']) ?></strong>
                            <span><?= $tot ?> Total</span>
                        </div>
                    </div>
                    <div class="lab-available-label"><?= $avl ?> Available</div>
                    <div class="lab-progress-bar">
                        <span class="progress-fill <?= $barClass ?>" style="width: <?= $pct ?>%;"></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Activity Feed -->
    <div class="dashboard-card recent-activity-card">
        <div class="card-header-flex">
            <div class="card-title-group">
                <i class="fa-solid fa-bolt card-title-icon"></i>
                <span class="card-title-text">Recent Activity</span>
            </div>
            <a href="fault-reports.php" class="card-link-action">View All <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <ul class="activity-feed-list">
            <li class="activity-feed-item">
                <span class="activity-dot dot-red"></span>
                <p class="activity-text">Admin updated computer PC-014 status to <span class="highlight-green">Available</span></p>
                <small class="activity-time">2 min ago</small>
            </li>
            <li class="activity-feed-item">
                <span class="activity-dot dot-orange"></span>
                <p class="activity-text">New maintenance record added for PC-007</p>
                <small class="activity-time">5 min ago</small>
            </li>
            <li class="activity-feed-item">
                <span class="activity-dot dot-blue"></span>
                <p class="activity-text">Student Ram Kumar reported a problem</p>
                <small class="activity-time">10 min ago</small>
            </li>
        </ul>
    </div>

    <!-- BCA 4th Sem Project Overview Card -->
    <div class="dashboard-card bca-project-card">
        <div class="bca-card-media">
            <img src="assets/images/lab-operations-banner.jpg" alt="Lab Operations" class="bca-card-img" loading="lazy">
            <span class="bca-media-badge"><i class="fa-solid fa-graduation-cap"></i> BCA 4th Sem Project</span>
        </div>
        <div class="bca-project-body">
            <div class="bca-project-meta">
                <span class="bca-campus-tag"><i class="fa-solid fa-building-columns"></i> Mahendra Multiple Campus</span>
                <span class="bca-batch-tag">2024 Batch</span>
            </div>
            <h4 class="bca-project-title">BCA 4th Sem Project</h4>
            <p class="bca-project-desc">This is BCA 4th Sem Computer Lab Management of Mahendra Multiple Campus which enables real-time computer tracking, student fault reporting, lab scheduling, and hardware maintenance.</p>
            <div class="bca-project-features">
                <span class="bca-feature-pill"><i class="fa-solid fa-desktop"></i> Lab &amp; PC Tracking</span>
                <span class="bca-feature-pill"><i class="fa-solid fa-triangle-exclamation"></i> Fault Reporting</span>
                <span class="bca-feature-pill"><i class="fa-solid fa-wrench"></i> Maintenance Log</span>
            </div>
        </div>
    </div>

</section>

<?php require_once 'includes/footer.php'; ?>
