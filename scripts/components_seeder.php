<?php
// ============================================================
// COMPONENTS SEEDER — Mahendra Multiple Campus
// Seeds: Hardware components (CPU, Monitor, Keyboard, Mouse, Speaker)
// ============================================================

require_once __DIR__ . '/../config/db.php';

function seedComponents(mysqli $conn): array {
    $cRes = $conn->query("SELECT id, computer_code FROM computers");
    $totalCount = 0;

    while ($pc = $cRes->fetch_assoc()) {
        $pcId = (int)$pc['id'];
        $code = $pc['computer_code'];

        $components = [
            ['cpu', 'Intel Core i5/i7 System Unit', 'good'],
            ['monitor', 'Dell 24" IPS FHD Monitor', (in_array($code, ['PC-009', 'PC-025'], true) ? 'under_repair' : 'good')],
            ['keyboard', 'Logitech K120 USB Keyboard', (in_array($code, ['PC-004', 'PC-006'], true) ? 'under_repair' : 'good')],
            ['mouse', 'Logitech B100 Optical Mouse', 'good'],
            ['speaker', 'Realtek HD Audio Output', 'good'],
        ];

        foreach ($components as $cmp) {
            $cType  = $cmp[0];
            $cModel = $cmp[1];
            $cCond  = $cmp[2];

            $cChk = $conn->prepare("SELECT id FROM computer_components WHERE computer_id = ? AND component_type = ? LIMIT 1");
            $cChk->bind_param('is', $pcId, $cType);
            $cChk->execute();
            $chkRes = $cChk->get_result();

            if ($chkRes && $chkRes->num_rows > 0) {
                $cId = (int)$chkRes->fetch_assoc()['id'];
                $cUp = $conn->prepare("UPDATE computer_components SET model_name = ?, condition_status = ? WHERE id = ?");
                $cUp->bind_param('ssi', $cModel, $cCond, $cId);
                $cUp->execute();
                $cUp->close();
            } else {
                $cIns = $conn->prepare("INSERT INTO computer_components (computer_id, component_type, model_name, condition_status) VALUES (?, ?, ?, ?)");
                $cIns->bind_param('isss', $pcId, $cType, $cModel, $cCond);
                $cIns->execute();
                $cIns->close();
            }
            $cChk->close();
            $totalCount++;
        }
    }

    return ['total_components' => $totalCount];
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = seedComponents($conn);
    echo "Components Seeded: Total {$res['total_components']}\n";
}
