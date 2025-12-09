<?php
// Simple redirect handler - no output before headers
@ini_set('display_errors', 0);
@error_reporting(0);

// Clean all output buffers
while (@ob_get_level() > 0) {
    @ob_end_clean();
}

session_start();
$orderId = (int)($_GET['id'] ?? 0);
if ($orderId > 0) {
    @header('Location: view.php?id=' . $orderId . '&placed=1', true, 302);
    @exit;
}
@header('Location: checkout.php', true, 302);
@exit;

