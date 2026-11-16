<?php
// ============================================================
// USER SEEDER — Mahendra Multiple Campus Computer Lab Management
// BCA 2024 Batch Students & Campus Staff
// Run CLI: php scripts/seeder.php
// ============================================================

require_once __DIR__ . '/users_seeder.php';

$res = seedUsers($conn);
echo "Users Seeded: Total {$res['total']} (Created: {$res['created']}, Updated: {$res['updated']})\n";
