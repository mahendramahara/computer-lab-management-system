<?php
// Canonical location is reports/details.php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header('Location: reports/details.php' . ($id > 0 ? '?id=' . $id : ''), true, 301);
exit;
