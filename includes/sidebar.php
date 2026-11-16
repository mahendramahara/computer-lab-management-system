<?php
// Menu items: key => [link, icon, label]
if ($_SESSION['user']['role'] == 'student') {
    $menu = [
        'index'           => ['index.php', 'fa-house', 'Dashboard'],
        'computers'       => ['computers.php', 'fa-desktop', 'View Computers'],
        'report-issue'    => ['report-issue.php', 'fa-circle-plus', 'Report an Issue'],
        'my-reports'      => ['my-reports.php', 'fa-clipboard-list', 'My Reports'],
        'lab-information' => ['lab-information.php', 'fa-circle-info', 'Lab Information'],
        'profile'         => ['profile.php', 'fa-user', 'My Profile'],
    ];
} else {
    $menu = [
        'admin' => ['admin/index.php', 'fa-house', 'Issue Reports'],
    ];
}
?>
<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-logo"><i class="fa-solid fa-graduation-cap"></i></div>
        <div>
            <h2>Mahendra</h2>
            <p>Multiple Campus</p>
        </div>
    </div>

    <nav class="sidebar-menu">
        <?php foreach ($menu as $key => $item) { ?>
            <a href="<?= BASE_URL . $item[0] ?>" class="<?= $activePage == $key ? 'active' : '' ?>">
                <i class="fa-solid <?= $item[1] ?>"></i> <?= $item[2] ?>
            </a>
        <?php } ?>
    </nav>

    <!-- Faded campus building image -->
    <div class="sidebar-building"></div>

    <div class="sidebar-help">
        <i class="fa-solid fa-headset"></i>
        <div>
            <strong>Need Help?</strong>
            Contact Lab Assistant<br>
            lab@mmc.edu.np
        </div>
    </div>

</aside>