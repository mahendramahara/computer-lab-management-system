<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Maintenance Management';
$activePage = 'maintenance';
$pageJs     = 'maintenance.js';

$types = [
    'repair'      => 'Repair',
    'replacement' => 'Replacement',
    'inspection'  => 'Inspection',
    'scheduled'   => 'Scheduled Maintenance',
];

$statusLabels = [
    'pending'     => 'Pending',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
];

// Flash messages
$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'danger';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// ── Handle Actions Directly at the Top of this File ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = $_POST['task'] ?? '';
    $id   = (int) ($_POST['maintenance_id'] ?? 0);

    try {
        if ($task === 'delete') {
            $stmt = $conn->prepare("DELETE FROM maintenance_records WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_message'] = 'Maintenance record deleted successfully.';
            $_SESSION['flash_type']    = 'success';
        } elseif ($task === 'add' || $task === 'edit') {
            $computerId         = (int) ($_POST['computer_id'] ?? 0);
            $technicianId       = !empty($_POST['technician_id']) ? (int) $_POST['technician_id'] : null;
            $component          = trim($_POST['component'] ?? 'General');
            $maintenanceType    = $_POST['maintenance_type'] ?? 'repair';
            $problemDescription = trim($_POST['problem_description'] ?? '');
            $repairDescription  = trim($_POST['repair_description'] ?? '');
            $repairCost         = max(0.00, (float) ($_POST['repair_cost'] ?? 0.00));
            $maintenanceDate    = !empty($_POST['maintenance_date']) ? $_POST['maintenance_date'] : date('Y-m-d');
            $status             = $_POST['status'] ?? 'pending';
            $updateComputer     = !empty($_POST['update_computer_status']) ? $_POST['update_computer_status'] : '';

            if ($computerId <= 0 || empty($problemDescription)) {
                throw new Exception('Please select a computer and enter problem description.');
            }

            if ($task === 'add') {
                $stmt = $conn->prepare(
                    "INSERT INTO maintenance_records 
                     (computer_id, technician_id, component, maintenance_type, problem_description, repair_description, repair_cost, maintenance_date, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('iissssiss', $computerId, $technicianId, $component, $maintenanceType, $problemDescription, $repairDescription, $repairCost, $maintenanceDate, $status);
                $stmt->execute();
                $newId = $stmt->insert_id;
                $stmt->close();

                if ($updateComputer && in_array($updateComputer, ['available', 'under_maintenance', 'out_of_service'], true)) {
                    $cStmt = $conn->prepare("UPDATE computers SET status = ? WHERE id = ?");
                    $cStmt->bind_param('si', $updateComputer, $computerId);
                    $cStmt->execute();
                    $cStmt->close();
                }

                $_SESSION['flash_message'] = 'Maintenance record #MN-' . str_pad($newId, 3, '0', STR_PAD_LEFT) . ' added successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                $stmt = $conn->prepare(
                    "UPDATE maintenance_records 
                     SET computer_id = ?, technician_id = ?, component = ?, maintenance_type = ?, 
                         problem_description = ?, repair_description = ?, repair_cost = ?, maintenance_date = ?, status = ?
                     WHERE id = ?"
                );
                $stmt->bind_param('iissssissi', $computerId, $technicianId, $component, $maintenanceType, $problemDescription, $repairDescription, $repairCost, $maintenanceDate, $status, $id);
                $stmt->execute();
                $stmt->close();

                if ($updateComputer && in_array($updateComputer, ['available', 'under_maintenance', 'out_of_service'], true)) {
                    $cStmt = $conn->prepare("UPDATE computers SET status = ? WHERE id = ?");
                    $cStmt->bind_param('si', $updateComputer, $computerId);
                    $cStmt->execute();
                    $cStmt->close();
                }

                $_SESSION['flash_message'] = 'Maintenance record #MN-' . str_pad($id, 3, '0', STR_PAD_LEFT) . ' updated successfully.';
                $_SESSION['flash_type']    = 'success';
            }
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Operation error: ' . $e->getMessage();
        $_SESSION['flash_type']    = 'danger';
    }

    header('Location: maintenance.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

// Filter parameters
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int) ($_GET['page'] ?? 1));
$perPage      = 10;

$where  = " WHERE 1 = 1";
$params = [];
$typesStr = "";

if ($statusFilter !== '' && isset($statusLabels[$statusFilter])) {
    $where    .= " AND m.status = ?";
    $params[]  = $statusFilter;
    $typesStr .= "s";
}

if ($search !== '') {
    $where    .= " AND (c.computer_code LIKE ? OR m.component LIKE ? OR m.problem_description LIKE ? OR t.name LIKE ?)";
    $like      = "%$search%";
    $params[]  = $like;
    $params[]  = $like;
    $params[]  = $like;
    $params[]  = $like;
    $typesStr .= "ssss";
}

$records    = [];
$computers  = [];
$staffUsers = [];
$totalCount = 0;
$totalPages = 1;

try {
    // Dropdown sources
    $computers = $conn->query("SELECT id, computer_code FROM computers ORDER BY computer_code")->fetch_all(MYSQLI_ASSOC);
    $staffUsers = $conn->query("SELECT id, name, role FROM users WHERE role IN ('admin', 'lab_assistant') AND status = 'active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

    // Count for pagination
    $cStmt = $conn->prepare(
        "SELECT COUNT(*) FROM maintenance_records m
         JOIN computers c ON c.id = m.computer_id
         LEFT JOIN users t ON t.id = m.technician_id" . $where
    );
    if ($params) {
        $cStmt->bind_param($typesStr, ...$params);
    }
    $cStmt->execute();
    $totalCount = $cStmt->get_result()->fetch_row()[0];
    $cStmt->close();

    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // Fetch records
    $stmt = $conn->prepare(
        "SELECT m.*, c.computer_code, t.name AS technician_name
         FROM maintenance_records m
         JOIN computers c ON c.id = m.computer_id
         LEFT JOIN users t ON t.id = m.technician_id" . $where . "
         ORDER BY m.maintenance_date DESC, m.id DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param($typesStr . "ii", ...array_merge($params, [$perPage, $offset]));
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $message = 'Error loading maintenance records: ' . $e->getMessage();
    $type    = 'danger';
}

$filters = array_filter(['status' => $statusFilter, 'search' => $search]);
?>

<div class="computers-head">
    <div>
        <h2>Maintenance Management</h2>
        <small><a href="index.php">Dashboard</a> &gt; Maintenance</small>
    </div>
    <button type="button" class="btn" id="addMaintBtn"><i class="fa-solid fa-plus"></i> Add Maintenance</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Search and Filter Bar -->
<form method="get" class="computers-filters" style="grid-template-columns: 1fr 200px;">
    <div class="computers-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Search by computer, component, or technician..."
               value="<?= htmlspecialchars($search) ?>">
    </div>

    <select name="status" class="form-control" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach ($statusLabels as $k => $label): ?>
            <option value="<?= $k ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th style="width: 120px;">Computer ID</th>
                    <th>Issue</th>
                    <th>Action Taken</th>
                    <th style="width: 140px;">Technician</th>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 110px; text-align: center;">Status</th>
                    <th style="width: 80px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="8" class="empty-cell">No maintenance records found.</td></tr>
                <?php endif; ?>

                <?php foreach ($records as $idx => $r): ?>
                    <?php
                    $sColor = match($r['status']) {
                        'pending'     => 'warning',
                        'in_progress' => 'purple',
                        'completed'   => 'success',
                        default       => 'primary'
                    };
                    $descText = !empty($r['repair_description']) ? $r['repair_description'] : 'Under maintenance inspection';
                    ?>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">#<?= str_pad($offset + $idx + 1, 3, '0', STR_PAD_LEFT) ?></td>
                        <td><strong><?= htmlspecialchars($r['computer_code']) ?></strong></td>
                        <td>
                            <strong style="color: #0f1b3d;"><?= htmlspecialchars($r['problem_description']) ?></strong>
                            <small style="display: block; color: #64748b;"><?= htmlspecialchars($r['component']) ?> (<?= ucfirst($r['maintenance_type']) ?>)</small>
                        </td>
                        <td>
                            <span class="truncate-cell" title="<?= htmlspecialchars($descText) ?>">
                                <?= htmlspecialchars($descText) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($r['technician_name'] ?? 'Technician') ?>
                        </td>
                        <td style="font-size: 12.5px; color: #64748b;"><?= date('M d, Y', strtotime($r['maintenance_date'])) ?></td>
                        <td style="text-align: center;">
                            <span class="badge badge-<?= $sColor ?>"><?= $statusLabels[$r['status']] ?? ucfirst($r['status']) ?></span>
                        </td>
                        <td style="text-align: center;">
                            <div class="labs-tools" style="justify-content: center;">
                                <button type="button" class="labs-tool edit-maint-btn" title="Edit"
                                        data-maint="<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form action="maintenance.php" method="post" onsubmit="return confirm('Delete this maintenance record?');">
                                    <input type="hidden" name="task" value="delete">
                                    <input type="hidden" name="maintenance_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="labs-tool labs-tool-danger" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="computers-pagination" style="display: flex; justify-content: center; gap: 8px; margin-top: 20px;">
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
</div>

<!-- Add / Edit Maintenance Modal -->
<div class="labs-modal" id="maintModal">
    <form action="maintenance.php" method="post" class="labs-dialog" id="maintForm" style="max-width: 620px;">
        <input type="hidden" name="task" id="maintTask" value="add">
        <input type="hidden" name="maintenance_id" id="maintId" value="">

        <div class="card-title">
            <span id="maintModalTitle">New Maintenance Record</span>
            <button type="button" class="btn btn-light btn-sm" id="maintClose"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="labs-form-grid">
            <div class="form-group">
                <label for="form_computer_id">Computer *</label>
                <select name="computer_id" id="form_computer_id" class="form-control" required>
                    <option value="">Select Computer</option>
                    <?php foreach ($computers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['computer_code']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="form_technician_id">Assigned Technician</label>
                <select name="technician_id" id="form_technician_id" class="form-control">
                    <option value="">Select Technician</option>
                    <?php foreach ($staffUsers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="form_component">Component *</label>
                <input type="text" name="component" id="form_component" class="form-control" placeholder="e.g. Keyboard, Monitor, RAM" required>
            </div>

            <div class="form-group">
                <label for="form_type">Maintenance Type *</label>
                <select name="maintenance_type" id="form_type" class="form-control" required>
                    <?php foreach ($types as $k => $label): ?>
                        <option value="<?= $k ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="form_date">Maintenance Date *</label>
                <input type="date" name="maintenance_date" id="form_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label for="form_status">Status *</label>
                <select name="status" id="form_status" class="form-control" required>
                    <?php foreach ($statusLabels as $k => $label): ?>
                        <option value="<?= $k ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="form_problem">Problem Description *</label>
            <textarea name="problem_description" id="form_problem" class="form-control" rows="2" placeholder="Describe the fault or scheduled task..." required></textarea>
        </div>

        <div class="form-group">
            <label for="form_repair">Repair / Action Taken</label>
            <textarea name="repair_description" id="form_repair" class="form-control" rows="2" placeholder="Details of repair, replacement, or cleaning done..."></textarea>
        </div>

        <div class="labs-form-grid">
            <div class="form-group">
                <label for="form_cost">Repair Cost (NPR)</label>
                <input type="number" step="0.01" name="repair_cost" id="form_cost" class="form-control" value="0.00">
            </div>

            <div class="form-group">
                <label for="form_update_comp">Update Computer Status</label>
                <select name="update_computer_status" id="form_update_comp" class="form-control">
                    <option value="">Leave unchanged</option>
                    <option value="under_maintenance">Set Under Maintenance</option>
                    <option value="available">Set Available</option>
                    <option value="out_of_service">Set Out of Service</option>
                </select>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
            <button type="button" class="btn btn-light" id="maintCancel">Cancel</button>
            <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Save Record</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
