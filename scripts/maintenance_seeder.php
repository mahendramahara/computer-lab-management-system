<?php
// ============================================================
// MAINTENANCE SEEDER — Mahendra Multiple Campus
// Seeds: Maintenance records, actions, technicians, and repair costs
// ============================================================

require_once __DIR__ . '/../config/db.php';

function seedMaintenance(mysqli $conn): array {
    $computers = [];
    $cRes = $conn->query("SELECT id, computer_code FROM computers");
    while ($row = $cRes->fetch_assoc()) {
        $computers[$row['computer_code']] = (int)$row['id'];
    }

    $staff = [];
    $sRes = $conn->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'lab_assistant')");
    while ($row = $sRes->fetch_assoc()) {
        $staff[$row['email']] = (int)$row['id'];
        $staff[$row['name']]  = (int)$row['id'];
    }

    $rameshId = $staff['ramesh.kc@gmail.com'] ?? ($staff['admin@gmail.com'] ?? 1);
    $sujanId  = $staff['sujan.bhandari@gmail.com'] ?? ($staff['admin@gmail.com'] ?? 1);

    $maintData = [
        [
            'id'            => 1,
            'computer_code' => 'PC-003',
            'technician_id' => $rameshId,
            'fault_id'      => 4,
            'component'     => 'CPU / Power Supply',
            'type'          => 'replacement',
            'problem'       => 'Power Supply replaced and faulty keyboard swapped with new Logitech unit.',
            'action'        => 'Keyboard Replaced',
            'cost'          => 1200.00,
            'date'          => '2026-11-14',
            'status'        => 'completed',
        ],
        [
            'id'            => 2,
            'computer_code' => 'PC-006',
            'technician_id' => $sujanId,
            'fault_id'      => null,
            'component'     => 'CPU / Chassis',
            'type'          => 'scheduled',
            'problem'       => 'Routine seasonal dust cleaning and heatsink thermal paste re-application.',
            'action'        => 'Dust Cleaning',
            'cost'          => 0.00,
            'date'          => '2026-11-10',
            'status'        => 'completed',
        ],
        [
            'id'            => 3,
            'computer_code' => 'PC-010',
            'technician_id' => $rameshId,
            'fault_id'      => null,
            'component'     => 'Monitor',
            'type'          => 'repair',
            'problem'       => 'Capacitor replacement on internal power board and display color calibration.',
            'action'        => 'Monitor Repair',
            'cost'          => 2500.00,
            'date'          => '2026-11-05',
            'status'        => 'completed',
        ],
    ];

    $count = 0;
    foreach ($maintData as $md) {
        $cId = $computers[$md['computer_code']] ?? 1;

        $chk = $conn->prepare("SELECT id FROM maintenance_records WHERE id = ? LIMIT 1");
        $chk->bind_param('i', $md['id']);
        $chk->execute();
        $res = $chk->get_result();

        if ($res && $res->num_rows > 0) {
            $up = $conn->prepare("UPDATE maintenance_records SET computer_id = ?, technician_id = ?, fault_report_id = ?, component = ?, maintenance_type = ?, problem_description = ?, repair_description = ?, repair_cost = ?, maintenance_date = ?, status = ? WHERE id = ?");
            $up->bind_param('iiissssdssi', $cId, $md['technician_id'], $md['fault_id'], $md['component'], $md['type'], $md['problem'], $md['action'], $md['cost'], $md['date'], $md['status'], $md['id']);
            $up->execute();
            $up->close();
        } else {
            $ins = $conn->prepare("INSERT INTO maintenance_records (id, computer_id, technician_id, fault_report_id, component, maintenance_type, problem_description, repair_description, repair_cost, maintenance_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('iiiissssdss', $md['id'], $cId, $md['technician_id'], $md['fault_id'], $md['component'], $md['type'], $md['problem'], $md['action'], $md['cost'], $md['date'], $md['status']);
            $ins->execute();
            $ins->close();
        }
        $chk->close();
        $count++;
    }

    return ['total_maintenance' => $count];
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = seedMaintenance($conn);
    echo "Maintenance Seeded: Total {$res['total_maintenance']}\n";
}
