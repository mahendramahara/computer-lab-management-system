<?php
// ============================================================
// MASTER SEEDER RUNNER — Mahendra Multiple Campus
// Computer Laboratory Management System
// Runs automatically on /scripts or via CLI: php scripts/index.php
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/users_seeder.php';
require_once __DIR__ . '/labs_seeder.php';
require_once __DIR__ . '/computers_seeder.php';
require_once __DIR__ . '/components_seeder.php';
require_once __DIR__ . '/fault_reports_seeder.php';
require_once __DIR__ . '/maintenance_seeder.php';
require_once __DIR__ . '/notifications_seeder.php';

$isCli = php_sapi_name() === 'cli';

// ── Automatic Execution ──────────────────────────────────────
$stats = [];
$error = '';

try {
    $stats['users']         = seedUsers($conn);
    $stats['labs']          = seedLabs($conn);
    $stats['computers']     = seedComputers($conn);
    $stats['components']    = seedComponents($conn);
    $stats['fault_reports'] = seedFaultReports($conn);
    $stats['maintenance']   = seedMaintenance($conn);
    $stats['notifications'] = seedNotifications($conn);
} catch (Exception $e) {
    $error = $e->getMessage();
}

// ── CLI Mode ─────────────────────────────────────────────────
if ($isCli) {
    echo "============================================================\n";
    echo "  Mahendra Multiple Campus — Master Database Seeder\n";
    echo "============================================================\n\n";

    if ($error) {
        echo "[ERROR] Seeding failed: $error\n";
        exit(1);
    }

    echo "✅ 1. Users: {$stats['users']['total']} records processed.\n";
    echo "✅ 2. Labs: {$stats['labs']['total']} labs synchronized.\n";
    echo "✅ 3. Computers: {$stats['computers']['total']} workstations processed.\n";
    echo "✅ 4. Components: {$stats['components']['total_components']} hardware parts tracked.\n";
    echo "✅ 5. Fault Reports: {$stats['fault_reports']['total_reports']} reports with photo attachments.\n";
    echo "✅ 6. Maintenance: {$stats['maintenance']['total_maintenance']} repair/service records.\n";
    echo "✅ 7. Notifications: {$stats['notifications']['total_notifications']} announcements.\n\n";

    echo "============================================================\n";
    echo "  [SUCCESS] All separate seeders executed automatically!\n";
    echo "============================================================\n";
    exit(0);
}

// ── Web Interface Mode (Shown when visiting /scripts) ────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Seeder | Mahendra Multiple Campus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #1d63e8;
            --primary-dark: #0e1e38;
            --bg: #f4f6fb;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .seeder-container {
            width: 100%;
            max-width: 860px;
        }

        .header-card {
            background: linear-gradient(135deg, #0e1e38 0%, #1e3a8a 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-icon {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-title h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .brand-title p {
            font-size: 13px;
            color: #cbd5e1;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 8px;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .alert-success-banner {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 18px 22px;
            border-radius: 12px;
            font-size: 14.5px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .alert-danger-banner {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 18px 22px;
            border-radius: 12px;
            font-size: 14.5px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modules-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 26px 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .modules-card h2 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .modules-card p {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .seeder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
        }

        .seeder-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .seeder-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #e0e7ff;
            color: #4338ca;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .seeder-info strong {
            display: block;
            font-size: 13.5px;
            color: #0f172a;
        }

        .seeder-info small {
            font-size: 12px;
            color: #10b981;
            font-weight: 600;
        }

        .quick-links-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        .quick-links-bar a {
            font-size: 13px;
            color: #1d63e8;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .quick-links-bar a:hover {
            text-decoration: underline;
        }

        .footer-note {
            text-align: center;
            font-size: 12.5px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="seeder-container">

    <header class="header-card">
        <div class="brand-section">
            <div class="brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="brand-title">
                <h1>Mahendra Multiple Campus</h1>
                <p>Computer Laboratory Management System • Auto Seeder</p>
            </div>
        </div>
        <div class="nav-links">
            <a href="<?= BASE_URL ?>admin/">
                <i class="fa-solid fa-shield-halved"></i> Admin Panel
            </a>
            <a href="<?= BASE_URL ?>">
                <i class="fa-solid fa-house"></i> Student Portal
            </a>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="alert-danger-banner">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 20px;"></i>
            <span>Seeding Error: <?= htmlspecialchars($error) ?></span>
        </div>
    <?php else: ?>
        <div class="alert-success-banner">
            <i class="fa-solid fa-circle-check" style="font-size: 22px; color: #10b981;"></i>
            <div>
                <strong>Auto Seeder Executed Successfully!</strong>
                <p style="font-size: 13px; font-weight: normal; margin-top: 2px;">
                    All 7 modular seeder scripts have been automatically executed and synchronized into the database with authentic Nepali records and realistic fault photos.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="modules-card">
        <h2>Executed Seeder Modules</h2>
        <p>The following separate seeder modules were automatically imported and run:</p>

        <div class="seeder-grid">
            <div class="seeder-item">
                <div class="seeder-icon"><i class="fa-solid fa-users"></i></div>
                <div class="seeder-info">
                    <strong>users_seeder.php</strong>
                    <small>✓ <?= $stats['users']['total'] ?? 13 ?> Users Seeded</small>
                </div>
            </div>

            <div class="seeder-item">
                <div class="seeder-icon"><i class="fa-solid fa-building"></i></div>
                <div class="seeder-info">
                    <strong>labs_seeder.php</strong>
                    <small>✓ <?= $stats['labs']['total'] ?? 2 ?> Labs Configured</small>
                </div>
            </div>

            <div class="seeder-item">
                <div class="seeder-icon"><i class="fa-solid fa-computer"></i></div>
                <div class="seeder-info">
                    <strong>computers_seeder.php</strong>
                    <small>✓ <?= $stats['computers']['total'] ?? 40 ?> Terminals Setup</small>
                </div>
            </div>

            <div class="seeder-item">
                <div class="seeder-icon"><i class="fa-solid fa-microchip"></i></div>
                <div class="seeder-info">
                    <strong>components_seeder.php</strong>
                    <small>✓ <?= $stats['components']['total_components'] ?? 200 ?> Parts Tracked</small>
                </div>
            </div>

            <div class="seeder-item">
                <div class="seeder-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="seeder-info">
                    <strong>fault_reports_seeder.php</strong>
                    <small>✓ <?= $stats['fault_reports']['total_reports'] ?? 8 ?> Reports with Photos</small>
                </div>
            </div>

            <div class="seeder-item">
                <div class="seeder-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <div class="seeder-info">
                    <strong>maintenance_seeder.php</strong>
                    <small>✓ <?= $stats['maintenance']['total_maintenance'] ?? 3 ?> Maintenance Records</small>
                </div>
            </div>

            <div class="seeder-item">
                <div class="seeder-icon"><i class="fa-solid fa-bullhorn"></i></div>
                <div class="seeder-info">
                    <strong>notifications_seeder.php</strong>
                    <small>✓ <?= $stats['notifications']['total_notifications'] ?? 3 ?> Announcements</small>
                </div>
            </div>
        </div>

        <div class="quick-links-bar">
            <span style="font-size: 13px; color: #64748b; font-weight: 600;">Direct Links:</span>
            <a href="<?= BASE_URL ?>reports/details.php?id=7" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Test Report #7 View
            </a>
            <a href="<?= BASE_URL ?>admin/fault-reports.php" target="_blank">
                <i class="fa-solid fa-list-check"></i> Admin Fault Reports
            </a>
            <a href="<?= BASE_URL ?>admin/computers.php" target="_blank">
                <i class="fa-solid fa-desktop"></i> Admin Computer Management
            </a>
        </div>
    </div>

    <footer class="footer-note">
        <p>© <?= date('Y') ?> Mahendra Multiple Campus • Computer Laboratory Management System</p>
    </footer>

</div>

</body>
</html>
