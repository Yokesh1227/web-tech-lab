<?php
require __DIR__ . '/db.php';

$in = read_json_body();

$name    = trim($in['name']    ?? '');
$mobile  = trim($in['mobile']  ?? '');
$orderId = trim($in['order_id'] ?? '');
$message = trim($in['message'] ?? '');

// ---- Validate (frontend rules-a server-layum repeat pannanum) ----
if (mb_strlen($name) < 2 || mb_strlen($name) > 80 || !preg_match("/^[a-zA-Z\s'.\-]+$/", $name)) {
    json_out(['ok' => false, 'error' => 'Invalid name'], 422);
}
if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    json_out(['ok' => false, 'error' => 'Invalid mobile number'], 422);
}
if ($orderId !== '' && !preg_match('/^[A-Za-z0-9\-]{1,20}$/', $orderId)) {
    json_out(['ok' => false, 'error' => 'Invalid order ID'], 422);
}
if (mb_strlen($message) < 5 || mb_strlen($message) > 2000) {
    json_out(['ok' => false, 'error' => 'Message must be 5-2000 characters'], 422);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO support_tickets (name, mobile, order_id, message) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $mobile, $orderId === '' ? null : strtoupper($orderId), $message]);

    json_out(['ok' => true, 'ticket_id' => (int) db()->lastInsertId()]);

} catch (Throwable $e) {
    error_log('save_ticket: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
