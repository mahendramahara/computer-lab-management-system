<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../maintenance.php');
    exit;
}

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
            throw new Exception('Please select a computer and enter the problem description.');
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

            // Update computer status if requested
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

header('Location: ../maintenance.php');
exit;
