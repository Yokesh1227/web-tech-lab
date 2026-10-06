<?php
require __DIR__ . '/db.php';

$in = read_json_body();

// ---- Server-side pricing (browser anuppura total-a nambaadheenga) ----
$ORDER_ID = 'A-4471';
$TOTAL    = 308.50;   // 300 + 8.50 fee

// ---- Validate ----
$method     = $in['method']     ?? '';
$identifier = trim($in['identifier'] ?? '');

if ($method === 'card') {
    if (!preg_match('/^card ending \d{4}$/', $identifier)) {
        json_out(['ok' => false, 'error' => 'Invalid card reference'], 422);
    }
} elseif ($method === 'upi') {
    if (!preg_match('/^[\w.\-]{2,}@[a-zA-Z]{2,}$/', $identifier) || strlen($identifier) > 60) {
        json_out(['ok' => false, 'error' => 'Invalid UPI ID'], 422);
    }
} else {
    json_out(['ok' => false, 'error' => 'Invalid payment method'], 422);
}

try {
    $pdo = db();

    $check = $pdo->prepare('SELECT status FROM bookings WHERE order_id = ?');
    $check->execute([$ORDER_ID]);
    $existing = $check->fetch();

    if ($existing && $existing['status'] === 'paid') {
        json_out(['ok' => false, 'error' => 'This order is already paid'], 409);
    }

    if ($existing) {
        $stmt = $pdo->prepare(
            'UPDATE bookings SET total = ?, method = ?, identifier = ?, status = ? WHERE order_id = ?'
        );
        $stmt->execute([$TOTAL, $method, $identifier, 'paid', $ORDER_ID]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO bookings (order_id, total, method, identifier, status)
             VALUES (?, ?, ?, ?, ?)'
        );
        // 'paid' demo-ku mattum. Real gateway use pannumbodhu webhook confirm pannina appram 'paid' nu maathunga.
        $stmt->execute([$ORDER_ID, $TOTAL, $method, $identifier, 'paid']);
    }

    json_out(['ok' => true, 'order_id' => $ORDER_ID]);

} catch (Throwable $e) {
    error_log('save_booking: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
