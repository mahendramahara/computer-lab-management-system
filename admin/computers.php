<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Computer Management';
$activePage = 'computers';
$pageCss    = 'computers.css';
$pageJs     = 'computers.js';

$statuses = [
    'available'         => 'Available',
    'in_use'            => 'In Use',
    'under_maintenance' => 'Under Maintenance',
    'out_of_service'    => 'Out of Service',
];

// Flash messages from CRUD operations
$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'danger';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// ── Handle Actions Directly at the Top of this File ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task     = $_POST['task'] ?? '';
    $id       = (int) ($_POST['computer_id'] ?? 0);
    $validStatuses = ['available', 'in_use', 'under_maintenance', 'out_of_service'];
    $error    = '';
    $success  = '';

    $fields = [
        'computer_code', 'computer_name', 'lab_id', 'status', 'processor', 'ram', 'storage',
        'ip_address', 'operating_system', 'monitor', 'keyboard', 'mouse', 'purchase_date', 'remarks',
    ];

    $data = [];
    foreach ($fields as $field) {
        $value = trim($_POST[$field] ?? '');
        $data[$field] = $value === '' ? null : $value;
    }

    try {
        if ($task === 'delete') {
            $stmt = $conn->prepare("DELETE FROM computers WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Computer deleted successfully.';
        } elseif ($task === 'add' || $task === 'edit') {
            $date = $data['purchase_date'] ? DateTime::createFromFormat('Y-m-d', $data['purchase_date']) : true;

            if ($data['computer_code'] === null || strlen($data['computer_code']) > 30) {
                $error = 'Please enter a valid Computer ID (max 30 characters).';
            } elseif ((int) $data['lab_id'] <= 0) {
                $error = 'Please select a laboratory.';
            } elseif (!in_array($data['status'], $validStatuses, true)) {
                $error = 'Please select a valid computer status.';
            } elseif ($data['ip_address'] !== null && !filter_var($data['ip_address'], FILTER_VALIDATE_IP)) {
                $error = 'Please enter a valid IP address.';
            } elseif (!$date) {
                $error = 'Please enter a valid purchase date.';
            } elseif ($task === 'edit' && $id <= 0) {
                $error = 'Invalid computer record selected.';
            } elseif ($task === 'add') {
                $columns = implode(', ', array_keys($data));
                $marks   = implode(', ', array_fill(0, count($data), '?'));
                $stmt = $conn->prepare("INSERT INTO computers ($columns) VALUES ($marks)");
                $stmt->bind_param(str_repeat('s', count($data)), ...array_values($data));
                $stmt->execute();
                $stmt->close();
                $success = 'Computer added successfully.';
            } else {
                $set = implode(' = ?, ', array_keys($data)) . ' = ?';
                $stmt = $conn->prepare("UPDATE computers SET $set WHERE id = ?");
                $stmt->bind_param(str_repeat('s', count($data)) . 'i', ...array_merge(array_values($data), [$id]));
                $stmt->execute();
                $stmt->close();
                $success = 'Computer updated successfully.';
            }
        } else {
            $error = 'Invalid action request.';
        }

        if ($success) {
            $conn->query("UPDATE labs SET total_computers = (SELECT COUNT(*) FROM computers WHERE computers.lab_id = labs.id)");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $error = 'This Computer ID is already assigned to another machine.';
        } elseif ($e->getCode() === 1451) {
            $error = 'This computer has linked fault reports or maintenance records. Change status to "Out of Service" instead.';
        } else {
            $error = 'Database operation failed: ' . $e->getMessage();
        }
    }

    $_SESSION['flash_message'] = $error ?: $success;
    $_SESSION['flash_type']    = $error ? 'danger' : 'success';
    header('Location: computers.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

// Filter parameters from query string
$search  = trim($_GET['search'] ?? '');
$labId   = (int) ($_GET['lab'] ?? 0);
$status  = $_GET['status'] ?? '';
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;

if (!isset($statuses[$status])) {
    $status = '';
}

// Build SQL WHERE condition dynamically
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

try {
    $labs = $conn->query("SELECT id, lab_name FROM labs ORDER BY lab_name")->fetch_all(MYSQLI_ASSOC);

    // Total count for pagination
    $stmt = $conn->prepare("SELECT COUNT(*) FROM computers c" . $where);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // Paginated list of computers
    $stmt = $conn->prepare(
        "SELECT c.*, l.lab_name
         FROM computers c
         JOIN labs l ON l.id = c.lab_id" . $where . "
         ORDER BY c.computer_code
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
    $stmt->execute();
    $computers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $message = 'Could not load computers. Please try again.';
    $type    = 'danger';
}

$filters = array_filter(['search' => $search, 'lab' => $labId, 'status' => $status]);
?>

<div class="computers-head">
    <div>
        <h2>Computer Management</h2>
        <small><a href="index.php">Dashboard</a> &gt; Computers</small>
    </div>
    <button type="button" class="btn" id="computerAdd">
        <i class="fa-solid fa-plus"></i> Add Computer
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="get" class="admin-filters">
    <div class="admin-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Search by Computer ID..."
               value="<?= htmlspecialchars($search) ?>">
    </div>

    <select name="lab" class="form-control" onchange="this.form.submit()">
        <option value="0">All Labs</option>
        <?php foreach ($labs as $lab): ?>
            <option value="<?= $lab['id'] ?>" <?= $labId === (int) $lab['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($lab['lab_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="status" class="form-control" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach ($statuses as $key => $label): ?>
            <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="admin-filters-btn">
        <i class="fa-solid fa-magnifying-glass"></i> Search
    </button>

    <?php if (!empty($search) || $labId > 0 || !empty($status)): ?>
        <a href="computers.php" class="admin-filters-clear" title="Clear Filters">
            <i class="fa-solid fa-xmark"></i> Clear Filters
        </a>
    <?php endif; ?>
</form>

<?php if (empty($computers)): ?>
    <div class="card"><p class="empty-text">No computers found.</p></div>
<?php else: ?>
    <div class="computers-grid">
        <?php foreach ($computers as $pc): ?>
            <div class="computers-card">

                <!-- Left Corner: Edit Tool -->
                <button type="button" class="computers-tool computers-edit-corner computers-edit" title="Edit Computer"
                        data-computer="<?= htmlspecialchars(json_encode($pc), ENT_QUOTES) ?>">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>

                <!-- Status Pip -->
                <span class="card-status-pip pip-<?= $pc['status'] ?>" title="<?= $statuses[$pc['status']] ?>">
                    <i class="fa-solid fa-diamond"></i>
                </span>

                <!-- Right Corner: Delete Tool -->
                <form action="computers.php" method="post" class="computers-delete-corner"
                      data-name="<?= htmlspecialchars($pc['computer_code']) ?>">
                    <input type="hidden" name="task" value="delete">
                    <input type="hidden" name="computer_id" value="<?= $pc['id'] ?>">
                    <button type="submit" class="computers-tool computers-tool-danger" title="Delete Computer">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </form>

                <i class="fa-solid fa-desktop computers-monitor monitor-status-<?= $pc['status'] ?>"></i>
                <strong><?= htmlspecialchars($pc['computer_code']) ?></strong>
                <small><?= htmlspecialchars($pc['lab_name']) ?></small>
                <?php if (!empty($pc['processor']) || !empty($pc['ram'])): ?>
                    <small style="font-size: 11px; color: #94a3b8; display: block; margin-top: -2px;">
                        <?= htmlspecialchars($pc['processor'] ?? '') ?><?= !empty($pc['ram']) ? ' &bull; ' . htmlspecialchars($pc['ram']) : '' ?>
                    </small>
                <?php endif; ?>
                <span class="badge computers-badge-<?= $pc['status'] ?>"><?= $statuses[$pc['status']] ?></span>

            </div>
        <?php endforeach; ?>
    </div>

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

<!-- Add / Edit Computer Modal Popup -->
<div class="computers-modal" id="computerModal">
    <form action="computers.php" method="post" class="computers-dialog" id="computerForm">
        <input type="hidden" name="task" value="add">
        <input type="hidden" name="computer_id" value="">

        <div class="card-title">
            <span id="computerModalTitle">Add Computer</span>
            <button type="button" class="btn btn-light btn-sm" id="computerClose"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="computers-form-grid">
            <div class="form-group">
                <label for="computer_code">Computer ID *</label>
                <input type="text" name="computer_code" id="computer_code" class="form-control" maxlength="30" placeholder="PC-001" required>
            </div>
            <div class="form-group">
                <label for="computer_name">Computer Name</label>
                <input type="text" name="computer_name" id="computer_name" class="form-control" maxlength="100">
            </div>

            <div class="form-group">
                <label for="lab_id">Lab *</label>
                <select name="lab_id" id="lab_id" class="form-control" required>
                    <option value="">Select lab</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?= $lab['id'] ?>"><?= htmlspecialchars($lab['lab_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" class="form-control" required>
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="processor">Processor</label>
                <input type="text" name="processor" id="processor" class="form-control" maxlength="100" placeholder="Intel Core i5">
            </div>
            <div class="form-group">
                <label for="ram">RAM</label>
                <input type="text" name="ram" id="ram" class="form-control" maxlength="50" placeholder="8 GB">
            </div>

            <div class="form-group">
                <label for="storage">Storage</label>
                <input type="text" name="storage" id="storage" class="form-control" maxlength="50" placeholder="500 GB SSD">
            </div>
            <div class="form-group">
                <label for="operating_system">Operating System</label>
                <input type="text" name="operating_system" id="operating_system" class="form-control" maxlength="100" placeholder="Windows 11">
            </div>

            <div class="form-group">
                <label for="ip_address">IP Address</label>
                <input type="text" name="ip_address" id="ip_address" class="form-control" maxlength="45" placeholder="192.168.1.10">
            </div>
            <div class="form-group">
                <label for="purchase_date">Purchase Date</label>
                <input type="date" name="purchase_date" id="purchase_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="monitor">Monitor</label>
                <input type="text" name="monitor" id="monitor" class="form-control" maxlength="100">
            </div>
            <div class="form-group">
                <label for="keyboard">Keyboard</label>
                <input type="text" name="keyboard" id="keyboard" class="form-control" maxlength="100">
            </div>

            <div class="form-group">
                <label for="mouse">Mouse</label>
                <input type="text" name="mouse" id="mouse" class="form-control" maxlength="100">
            </div>
            <div class="form-group">
                <label for="remarks">Remarks</label>
                <input type="text" name="remarks" id="remarks" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn">Save Computer</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
