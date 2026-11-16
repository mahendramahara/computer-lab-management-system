<?php
// Menu configuration matching administration UI layout
$menu = [
    'dashboard'   => ['index.php', 'fa-table-cells-large', 'Dashboard', false],
    'computers'   => ['computers.php', 'fa-desktop', 'Computer Management', false],
    'labs'        => ['labs.php', 'fa-flask', 'Lab Management', false],
    'reports'     => ['fault-reports.php', 'fa-shield-halved', 'Fault Reports', true],
    'maintenance' => ['maintenance.php', 'fa-screwdriver-wrench', 'Maintenance', false],
    'users'       => ['users.php', 'fa-user-group', 'User Management', 'dropdown'],
    'analytics'   => ['analytics.php', 'fa-file-lines', 'Reports & Analytics', 'dropdown'],
    'settings'    => ['settings.php', 'fa-gear', 'Settings', false],
];
?>
<aside class="admin-sidebar" id="adminSidebar">

    <div class="admin-brand">
        <div class="admin-brand-emblem">
            <svg viewBox="0 0 100 100" width="38" height="38" aria-hidden="true">
                <polygon points="50,5 61,35 95,35 68,55 79,88 50,68 21,88 32,55 5,35 39,35" fill="#3b82f6" stroke="#ffffff" stroke-width="2"/>
                <circle cx="50" cy="50" r="23" fill="#dc2626"/>
                <circle cx="50" cy="50" r="17" fill="#f59e0b"/>
                <circle cx="50" cy="50" r="11" fill="#ffffff"/>
                <path d="M50,42 Q54,48 50,58 Q46,48 50,42" fill="#dc2626"/>
            </svg>
        </div>
        <div class="admin-brand-text">
            <strong>Mahendra</strong>
            <span>Multiple Campus</span>
        </div>
    </div>

    <nav class="admin-nav">
        <?php foreach ($menu as $key => $item): ?>
            <?php
            $isActive = ($activePage === $key);
            $hasBadge = ($item[3] === true);
            $hasDrop  = ($item[3] === 'dropdown');
            ?>
            <a href="<?= $base . $item[0] ?>" class="admin-nav-item <?= $isActive ? 'active' : '' ?>">
                <i class="fa-solid <?= $item[1] ?>"></i>
                <span class="nav-label"><?= $item[2] ?></span>
                <?php if ($hasBadge): ?>
                    <span class="admin-nav-badge"><?= $pendingReports ?></span>
                <?php endif; ?>
                <?php if ($hasDrop): ?>
                    <i class="fa-solid fa-chevron-down nav-caret"></i>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <div style="margin: 10px 14px 6px; border-top: 1px solid rgba(255, 255, 255, 0.08);"></div>
        <a href="<?= $base ?>logout.php" class="admin-nav-item" style="color: #fca5a5;">
            <i class="fa-solid fa-right-from-bracket" style="color: #fca5a5;"></i>
            <span class="nav-label">Logout</span>
        </a>
    </nav>

    <!-- Campus building architectural showcase and version tag in sidebar -->
    <div class="sidebar-building-wrap">
        <div class="sidebar-building-frame">
            <img src="<?= $base ?>assets/images/campus-building-faded.jpg" alt="MMC Campus" class="sidebar-building-img" loading="lazy">
        </div>
        <div class="sidebar-system-note">
            <p class="sidebar-note-title">Mahendra Multiple Campus</p>
            <p class="sidebar-note-sub">Computer Lab System</p>
            <small class="sidebar-note-ver">v1.0.0</small>
        </div>
    </div>

</aside>
