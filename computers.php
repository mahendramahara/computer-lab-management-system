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

$pageTitle  = 'View Computers';
$activePage = 'computers';
$pageCss    = 'computers';

require_once __DIR__ . '/includes/header.php';

// Status key => [label, icon]
$statuses = [
    'available'         => ['Available', 'fa-circle-check'],
    'in_use'            => ['In Use', 'fa-user'],
    'under_maintenance' => ['Under Maintenance', 'fa-wrench'],
    'out_of_service'    => ['Out of Service', 'fa-ban'],
];

// URL query filters
$search  = trim($_GET['search'] ?? '');
$labId   = (int) ($_GET['lab'] ?? 0);
$status  = trim($_GET['status'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;

if (!isset($statuses[$status])) {
    $status = '';
}

// Build query conditions
$where  = ' WHERE 1 = 1';
$types  = '';
$params = [];

if ($search !== '') {
    $where   .= ' AND (c.computer_code LIKE ? OR c.computer_name LIKE ?)';
    $types   .= 'ss';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($labId > 0) {
    $where   .= ' AND c.lab_id = ?';
    $types   .= 'i';
    $params[] = $labId;
}
if ($status !== '') {
    $where   .= ' AND c.status = ?';
    $types   .= 's';
    $params[] = $status;
}

$labs       = [];
$computers  = [];
$totalPages = 1;
$error      = '';

try {
    // Load active labs for filter dropdown
    $labs = $conn->query("SELECT id, lab_name FROM labs WHERE status = 'active' ORDER BY lab_name")
                 ->fetch_all(MYSQLI_ASSOC);

    // Count rows for pagination
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM computers c" . $where);
    if (!empty($params)) {
        $stmtCount->bind_param($types, ...$params);
    }
    $stmtCount->execute();
    $total = $stmtCount->get_result()->fetch_row()[0];
    $stmtCount->close();

    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // Fetch computers for the current page
    $sql = "SELECT c.id, c.computer_code, c.computer_name, c.processor, c.ram, c.status, l.lab_name, l.location
            FROM computers c
            JOIN labs l ON l.id = c.lab_id" . $where . "
            ORDER BY c.computer_code
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $bindTypes  = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $computers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $error = 'Could not load computers. Please try again.';
}

// Filter query parameters for pagination links
$filterParams = [];
if ($search !== '') {
    $filterParams['search'] = $search;
}
if ($labId > 0) {
    $filterParams['lab'] = $labId;
}
if ($status !== '') {
    $filterParams['status'] = $status;
}
?>

<div class="computers-head">
    <div>
        <h2>View Computers</h2>
        <small><a href="index.php">Dashboard</a> &gt; Computers</small>
    </div>
    <a href="report-issue.php" class="btn">
        <i class="fa-solid fa-plus"></i> Report an Issue
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<!-- Filter Form -->
<form method="get" class="computers-filters" id="computersFilterForm">
    <div class="computers-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Search by Computer Code or Name..."
               value="<?= e($search) ?>" onkeydown="if(event.key === 'Enter') this.form.submit()">
    </div>

    <select name="lab" class="form-control" onchange="this.form.submit()">
        <option value="0">All Labs</option>
        <?php foreach ($labs as $lab): ?>
            <option value="<?= $lab['id'] ?>" <?= $labId === (int) $lab['id'] ? 'selected' : '' ?>>
                <?= e($lab['lab_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="status" class="form-control" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach ($statuses as $key => $info): ?>
            <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= $info[0] ?></option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (empty($computers)): ?>
    <div class="card"><p class="empty-text">No computers found matching your filter criteria.</p></div>
<?php else: ?>
    <!-- Computers Grid (4x2 matching admin layout) -->
    <div class="computers-grid">
        <?php foreach ($computers as $pc): ?>
            <?php 
                $stInfo = $statuses[$pc['status']] ?? ['Available', 'fa-circle-check'];
                $label  = $stInfo[0];
            ?>
            <a href="report-issue.php?computer_id=<?= (int)$pc['id'] ?>" class="computers-card" title="Click to report an issue for <?= e($pc['computer_code']) ?>">
                <span class="card-status-pip pip-<?= e($pc['status']) ?>" title="<?= e($label) ?>">
                    <i class="fa-solid fa-diamond"></i>
                </span>

                <i class="fa-solid fa-desktop computers-monitor monitor-status-<?= e($pc['status']) ?>"></i>
                <strong class="computers-code"><?= e($pc['computer_code']) ?></strong>
                <small class="computers-lab"><?= e($pc['lab_name']) ?></small>
                <?php if (!empty($pc['processor']) || !empty($pc['ram'])): ?>
                    <small style="font-size: 11px; color: #94a3b8; display: block; margin-top: -2px;">
                        <?= e($pc['processor'] ?? '') ?><?= !empty($pc['ram']) ? ' &bull; ' . e($pc['ram']) : '' ?>
                    </small>
                <?php endif; ?>
                <span class="badge computers-badge-<?= e($pc['status']) ?>"><?= e($label) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="computers-pagination">
            <a class="computers-page <?= $page <= 1 ? 'disabled' : '' ?>"
               href="?<?= http_build_query(array_merge($filterParams, ['page' => $page - 1])) ?>">
                <i class="fa-solid fa-chevron-left"></i>
            </a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="computers-page <?= $i === $page ? 'active' : '' ?>"
                   href="?<?= http_build_query(array_merge($filterParams, ['page' => $i])) ?>"><?= $i ?></a>
            <?php endfor; ?>

            <a class="computers-page <?= $page >= $totalPages ? 'disabled' : '' ?>"
               href="?<?= http_build_query(array_merge($filterParams, ['page' => $page + 1])) ?>">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
