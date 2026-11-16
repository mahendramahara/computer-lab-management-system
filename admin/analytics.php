<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Reports & Analytics';
$activePage = 'analytics';

require_once __DIR__ . '/includes/header.php';

$activeTab = $_GET['tab'] ?? 'overview';

// Aggregate stats
$totalComputers = 0;
$totalReports   = 0;
$totalMaint     = 0;
$statusCounts   = ['available' => 32, 'in_use' => 1, 'under_maintenance' => 5, 'out_of_service' => 2];
$labStats       = [];

try {
    $res = $conn->query("SELECT status, COUNT(*) as cnt FROM computers GROUP BY status");
    while ($row = $res->fetch_assoc()) {
        $statusCounts[$row['status']] = (int) $row['cnt'];
    }
    $totalComputers = array_sum($statusCounts);
    $totalReports   = (int) $conn->query("SELECT COUNT(*) FROM fault_reports")->fetch_row()[0];
    $totalMaint     = (int) $conn->query("SELECT COUNT(*) FROM maintenance_records")->fetch_row()[0];

    $labStats = $conn->query(
        "SELECT l.lab_name, l.total_computers,
                COUNT(c.id) AS real_total,
                COALESCE(SUM(c.status = 'available'), 0) AS available_count,
                COALESCE(SUM(c.status = 'under_maintenance'), 0) AS maint_count
         FROM labs l
         LEFT JOIN computers c ON c.lab_id = l.id
         GROUP BY l.id
         ORDER BY l.lab_name"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    // Continue
}
?>

<div class="computers-head">
    <div>
        <h2>Reports & Analytics</h2>
        <small><a href="index.php">Dashboard</a> &gt; Reports</small>
    </div>
    <div style="position: relative;">
        <button type="button" class="btn btn-light" onclick="window.print()">
            <i class="fa-solid fa-file-export"></i> Export Report <i class="fa-solid fa-chevron-down" style="font-size: 11px;"></i>
        </button>
    </div>
</div>

<!-- Tab Navigation Pills -->
<div class="admin-tabs-nav">
    <a href="?tab=overview" class="admin-tab-btn <?= $activeTab === 'overview' ? 'active' : '' ?>">Overview</a>
    <a href="?tab=maintenance" class="admin-tab-btn <?= $activeTab === 'maintenance' ? 'active' : '' ?>">Maintenance</a>
    <a href="?tab=faults" class="admin-tab-btn <?= $activeTab === 'faults' ? 'active' : '' ?>">Fault Reports</a>
    <a href="?tab=usage" class="admin-tab-btn <?= $activeTab === 'usage' ? 'active' : '' ?>">Computer Usage</a>
</div>

<!-- Main 2-Card Analytics Row Matching UI Mockup -->
<div class="dashboard-row-tables" style="grid-template-columns: 1.4fr 1fr; margin-bottom: 22px;">

    <!-- Left: Computer Usage Trend Line Chart -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <span class="card-title-text">Computer Usage Trend</span>
            <div class="chart-legend-keys">
                <span><i class="chart-key-dot" style="background: #1d63e8;"></i> In-Use</span>
                <span><i class="chart-key-dot" style="background: #16a34a;"></i> Available</span>
            </div>
        </div>

        <div class="performance-chart-container">
            <svg viewBox="0 0 500 210" class="performance-svg" width="100%" height="200">
                <!-- Grid Lines & Y-ticks -->
                <line x1="30" y1="20" x2="480" y2="20" class="chart-grid-line"/>
                <text x="22" y="24" class="chart-axis-label">40</text>

                <line x1="30" y1="60" x2="480" y2="60" class="chart-grid-line"/>
                <text x="22" y="64" class="chart-axis-label">30</text>

                <line x1="30" y1="100" x2="480" y2="100" class="chart-grid-line"/>
                <text x="22" y="104" class="chart-axis-label">20</text>

                <line x1="30" y1="140" x2="480" y2="140" class="chart-grid-line"/>
                <text x="22" y="144" class="chart-axis-label">10</text>

                <line x1="30" y1="175" x2="480" y2="175" class="chart-grid-line"/>
                <text x="22" y="179" class="chart-axis-label">0</text>

                <!-- Available (Green Curve) -->
                <path d="M 40,75 C 100,50 170,40 240,48 C 310,55 380,80 470,75"
                      fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"/>
                <circle cx="40" cy="75" r="4" fill="#16a34a" stroke="#ffffff" stroke-width="2"/>
                <circle cx="110" cy="62" r="4" fill="#16a34a" stroke="#ffffff" stroke-width="2"/>
                <circle cx="180" cy="50" r="4" fill="#16a34a" stroke="#ffffff" stroke-width="2"/>
                <circle cx="250" cy="48" r="4" fill="#16a34a" stroke="#ffffff" stroke-width="2"/>
                <circle cx="320" cy="58" r="4" fill="#16a34a" stroke="#ffffff" stroke-width="2"/>
                <circle cx="395" cy="74" r="4" fill="#16a34a" stroke="#ffffff" stroke-width="2"/>
                <circle cx="470" cy="75" r="4" fill="#16a34a" stroke="#ffffff" stroke-width="2"/>

                <!-- In-Use (Blue Curve) -->
                <path d="M 40,120 C 100,105 170,95 240,85 C 310,95 380,125 470,120"
                      fill="none" stroke="#1d63e8" stroke-width="2.5" stroke-linecap="round"/>
                <circle cx="40" cy="120" r="4" fill="#1d63e8" stroke="#ffffff" stroke-width="2"/>
                <circle cx="110" cy="112" r="4" fill="#1d63e8" stroke="#ffffff" stroke-width="2"/>
                <circle cx="180" cy="100" r="4" fill="#1d63e8" stroke="#ffffff" stroke-width="2"/>
                <circle cx="250" cy="85" r="4" fill="#1d63e8" stroke="#ffffff" stroke-width="2"/>
                <circle cx="320" cy="98" r="4" fill="#1d63e8" stroke="#ffffff" stroke-width="2"/>
                <circle cx="395" cy="115" r="4" fill="#1d63e8" stroke="#ffffff" stroke-width="2"/>
                <circle cx="470" cy="120" r="4" fill="#1d63e8" stroke="#ffffff" stroke-width="2"/>

                <!-- Days of Week X-Axis -->
                <text x="40" y="198" text-anchor="middle" class="chart-day-label">Mon</text>
                <text x="110" y="198" text-anchor="middle" class="chart-day-label">Tue</text>
                <text x="180" y="198" text-anchor="middle" class="chart-day-label">Wed</text>
                <text x="250" y="198" text-anchor="middle" class="chart-day-label">Thu</text>
                <text x="320" y="198" text-anchor="middle" class="chart-day-label">Fri</text>
                <text x="395" y="198" text-anchor="middle" class="chart-day-label">Sat</text>
                <text x="470" y="198" text-anchor="middle" class="chart-day-label">Sun</text>
            </svg>
        </div>
    </div>

    <!-- Right: Top Issues Donut Chart -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <span class="card-title-text">Top Issues</span>
        </div>

        <div class="donut-chart-layout" style="gap: 18px; padding-top: 12px;">
            <div class="donut-chart-graphic" style="width: 140px; height: 140px; background: conic-gradient(#1d63e8 0% 40%, #06b6d4 40% 70%, #f59e0b 70% 90%, #dc2626 90% 100%);">
                <div class="donut-center-circle" style="width: 86px; height: 86px;">
                    <strong style="font-size: 20px;">100%</strong>
                    <small>Issues</small>
                </div>
            </div>

            <ul class="donut-legend-list" style="gap: 12px;">
                <li>
                    <span class="legend-dot" style="background: #1d63e8;"></span>
                    <span class="legend-name">Keyboard</span>
                    <span class="legend-val">40%</span>
                </li>
                <li>
                    <span class="legend-dot" style="background: #06b6d4;"></span>
                    <span class="legend-name">Mouse</span>
                    <span class="legend-val">30%</span>
                </li>
                <li>
                    <span class="legend-dot" style="background: #f59e0b;"></span>
                    <span class="legend-name">Monitor</span>
                    <span class="legend-val">20%</span>
                </li>
                <li>
                    <span class="legend-dot" style="background: #dc2626;"></span>
                    <span class="legend-name">Others</span>
                    <span class="legend-val">10%</span>
                </li>
            </ul>
        </div>
    </div>

</div>

<!-- Laboratory Utilization Table -->
<div class="card">
    <div class="card-title">
        <span><i class="fa-solid fa-building"></i> Lab Utilization & Health</span>
    </div>
    <div class="table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Laboratory</th>
                    <th style="text-align: center;">Total PCs</th>
                    <th style="text-align: center;">Available</th>
                    <th style="text-align: center;">Maintenance</th>
                    <th style="width: 180px;">Operational Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($labStats as $l): ?>
                    <?php
                    $tot = max(1, (int)$l['real_total']);
                    $avl = (int)$l['available_count'];
                    $pct = round(($avl / $tot) * 100);
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($l['lab_name']) ?></strong></td>
                        <td style="text-align: center; font-weight: 600;"><?= $l['real_total'] ?></td>
                        <td style="text-align: center; color: #16a34a; font-weight: 600;"><?= $avl ?></td>
                        <td style="text-align: center; color: #d97706;"><?= $l['maint_count'] ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="lab-progress-bar" style="flex: 1;">
                                    <span class="progress-fill bar-green" style="width: <?= $pct ?>%;"></span>
                                </div>
                                <span style="font-size: 12px; font-weight: 700; color: #0f1b3d;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
