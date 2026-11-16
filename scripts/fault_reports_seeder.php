<?php
// ============================================================
// FAULT REPORTS SEEDER — Mahendra Multiple Campus
// Seeds: Realistic Fault Reports in Romanized Nepali with Photo Attachments
// ============================================================

require_once __DIR__ . '/../config/db.php';

function seedFaultReports(mysqli $conn): array {
    // Map users by email
    $users = [];
    $uRes = $conn->query("SELECT id, name, email FROM users");
    while ($row = $uRes->fetch_assoc()) {
        $users[$row['email']] = (int)$row['id'];
    }

    // Map computers by code
    $computers = [];
    $cRes = $conn->query("SELECT id, computer_code FROM computers");
    while ($row = $cRes->fetch_assoc()) {
        $computers[$row['computer_code']] = (int)$row['id'];
    }

    // Assign to new BCA 2024 batch students
    $anjanaId   = $users['anjana.kc@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);
    $babinId    = $users['babin.dangi@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);
    $barunId    = $users['barun.chaudhary@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);
    $dipendraId = $users['dipendra.oli@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);
    $grishId    = $users['grish.rana@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);
    $krishnaId  = $users['krishna.gorathoki@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);
    $kshitizId  = $users['kshitiz.thapa@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);
    $prajwolId  = $users['prajwol.chaudhary@gmail.com'] ?? ($users['user@gmail.com'] ?? 1);

    $reports = [
        [
            'id'            => 1,
            'user_id'       => $babinId,
            'computer_code' => 'PC-014',
            'component'     => 'keyboard',
            'issue_title'   => 'Keyboard chalna xodyo ani keys thichidaina',
            'description'   => 'Lab ma programming practical kam garda gardai khoi k problem vayexa keyboard chalna xodyo, spacebar ra enter key thichdai thichidaina, practical garnai garho vayo.',
            'priority'      => 'medium',
            'status'        => 'in_progress',
            'report_image'  => 'uploads/reports/fault_keyboard_keys.jpg',
            'reported_at'   => '2026-11-14 10:45:00',
        ],
        [
            'id'            => 2,
            'user_id'       => $anjanaId,
            'computer_code' => 'PC-007',
            'component'     => 'monitor',
            'issue_title'   => 'Monitor jhim-jhim garera screen black hunchha',
            'description'   => 'Database lab ma SQL query lekhda lekhdai monitor screen achanak jhim-jhim garchha ani black screen vaihalchha. Power cable halka hilayo vane ekchhin aaunchha feri bilkul janchha.',
            'priority'      => 'high',
            'status'        => 'pending',
            'report_image'  => 'uploads/reports/fault_monitor_flicker.jpg',
            'reported_at'   => '2026-11-14 09:20:00',
        ],
        [
            'id'            => 3,
            'user_id'       => $barunId,
            'computer_code' => 'PC-021',
            'component'     => 'mouse',
            'issue_title'   => 'Mouse cursor hudda herdai adkine ra click nahune',
            'description'   => 'Mouse ko optical sensor le ramrari kaam gareko xaina, cursor ekkai thau ma adkinchha ra left click 2-3 choti jabarjasti thichnu parxha, kaam garna asajilo vayo.',
            'priority'      => 'low',
            'status'        => 'under_review',
            'report_image'  => 'uploads/reports/fault_mouse_sensor.jpg',
            'reported_at'   => '2026-11-13 14:15:00',
        ],
        [
            'id'            => 4,
            'user_id'       => $dipendraId,
            'computer_code' => 'PC-003',
            'component'     => 'cpu',
            'issue_title'   => 'CPU power button thichda pani on hudai vayena',
            'description'   => 'Aaja bihana class ma aauda power button thichyo tara CPU bata kunai response aayena, fan pani ghumnna ani picheko green light pani balena, power supply bigreko jasto chha.',
            'priority'      => 'high',
            'status'        => 'resolved',
            'report_image'  => 'uploads/reports/fault_cpu_power.jpg',
            'reported_at'   => '2026-11-12 11:30:00',
        ],
        [
            'id'            => 5,
            'user_id'       => $grishId,
            'computer_code' => 'PC-018',
            'component'     => 'network',
            'issue_title'   => 'Ethernet cable clip bhachiyera internet chhutiranchha',
            'description'   => 'Network cable ko plastic clip bhachiyeko rahechha, table halka matra hallida pani internet disconnect vaihalchha ani lab project submit garna garho vayo.',
            'priority'      => 'medium',
            'status'        => 'in_progress',
            'report_image'  => 'uploads/reports/fault_network_cable.jpg',
            'reported_at'   => '2026-11-15 08:30:00',
        ],
        [
            'id'            => 6,
            'user_id'       => $krishnaId,
            'computer_code' => 'PC-012',
            'component'     => 'speaker',
            'issue_title'   => 'Front audio port vitra headphone pin bhachiyera adkiyo',
            'description'   => 'Headphone ko 3.5mm jack pin agadi ko port vitra nai bhachiyera thuniyo, nikalna pani milena ani sound pani suninna, port safa garidinus.',
            'priority'      => 'low',
            'status'        => 'under_review',
            'report_image'  => 'uploads/reports/fault_audio_jack.jpg',
            'reported_at'   => '2026-11-15 11:00:00',
        ],
        [
            'id'            => 7,
            'user_id'       => $kshitizId,
            'computer_code' => 'PC-004',
            'component'     => 'software',
            'issue_title'   => 'Kam garda gardai Blue Screen (BSOD) aayera restart vayo',
            'description'   => 'Lab evaluation ko lagi C++ program run garna lagda achanak blue screen (MEMORY_MANAGEMENT) error aayo ani PC restart vayo, gari rakheko adha project file save nai vayana.',
            'priority'      => 'high',
            'status'        => 'pending',
            'report_image'  => 'uploads/reports/fault_bsod_screen.jpg',
            'reported_at'   => '2026-11-16 09:15:00',
        ],
        [
            'id'            => 8,
            'user_id'       => $prajwolId,
            'computer_code' => 'PC-009',
            'component'     => 'other',
            'issue_title'   => 'Power strip ma spark aayera switch board tatyo',
            'description'   => 'Computer chalai rakheko bela switch strip ma chat-chat spark aayo ani plastic poleko jasto gandha aayo, short circuit huna sakchha turunta heridinus.',
            'priority'      => 'high',
            'status'        => 'in_progress',
            'report_image'  => 'uploads/reports/fault_power_strip.jpg',
            'reported_at'   => '2026-11-16 11:45:00',
        ],
    ];

    $count = 0;
    foreach ($reports as $r) {
        $cId = $computers[$r['computer_code']] ?? 1;

        $chk = $conn->prepare("SELECT id FROM fault_reports WHERE id = ? LIMIT 1");
        $chk->bind_param('i', $r['id']);
        $chk->execute();
        $res = $chk->get_result();

        if ($res && $res->num_rows > 0) {
            $up = $conn->prepare("UPDATE fault_reports SET user_id = ?, computer_id = ?, component = ?, issue_title = ?, description = ?, priority = ?, status = ?, report_image = ?, reported_at = ? WHERE id = ?");
            $up->bind_param('iisssssssi', $r['user_id'], $cId, $r['component'], $r['issue_title'], $r['description'], $r['priority'], $r['status'], $r['report_image'], $r['reported_at'], $r['id']);
            $up->execute();
            $up->close();
        } else {
            $ins = $conn->prepare("INSERT INTO fault_reports (id, user_id, computer_id, component, issue_title, description, priority, status, report_image, reported_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('iiisssssss', $r['id'], $r['user_id'], $cId, $r['component'], $r['issue_title'], $r['description'], $r['priority'], $r['status'], $r['report_image'], $r['reported_at']);
            $ins->execute();
            $ins->close();
        }
        $chk->close();

        // Seed status history for report #7 and #4
        if ($r['id'] === 7) {
            $conn->query("DELETE FROM report_status_history WHERE fault_report_id = 7");
            $conn->query("INSERT INTO report_status_history (fault_report_id, status, note, created_at) VALUES (7, 'pending', 'Student le Blue Screen samasya report gareko', '2026-11-16 09:15:00')");
        } elseif ($r['id'] === 4) {
            $conn->query("DELETE FROM report_status_history WHERE fault_report_id = 4");
            $conn->query("INSERT INTO report_status_history (fault_report_id, status, note, created_at) VALUES (4, 'pending', 'Student le power supply on nahune samasya pathayo', '2026-11-12 11:30:00')");
            $conn->query("INSERT INTO report_status_history (fault_report_id, status, note, created_at) VALUES (4, 'in_progress', 'Technician Ramesh KC le SMPS power rail check gardai', '2026-11-12 14:00:00')");
            $conn->query("INSERT INTO report_status_history (fault_report_id, status, note, created_at) VALUES (4, 'resolved', 'Naya power supply unit halera check gariyo, sabai thik chha', '2026-11-14 16:30:00')");
        }
        $count++;
    }

    return ['total_reports' => $count];
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = seedFaultReports($conn);
    echo "Fault Reports Seeded: Total {$res['total_reports']}\n";
}
