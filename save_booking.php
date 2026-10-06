<?php
require __DIR__ . '/db.php';

$in = read_json_body();

// ---- Server-side pricing (client anuppura total-a nambaadheenga) ----
$ORDER_ID = 'A-4471';   // real project-la idha session / URL-la irundhu eduthu, DB-la order lookup pannunga
$AMOUNT   = 300.00;
$FEE      = 8.50;
$TOTAL    = $AMOUNT + $FEE;

// ---- Validate ----
$method     = $in['method']     ?? '';
$identifier = trim($in['identifier'] ?? '');

if ($method === 'card') {
    // frontend "card ending 3456" mattum anuppum - full number never
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

    // Already paid-a nu check
    $check = $pdo->prepare('SELECT status FROM bookings WHERE order_id = ?');
    $check->execute([$ORDER_ID]);
    $existing = $check->fetch();

    if ($existing && $existing['status'] === 'paid') {
        json_out(['ok' => false, 'error' => 'This order is already paid'], 409);
    }

    if ($existing) {
        // pending / failed order-a retry pannaa update
        $stmt = $pdo->prepare(
            'UPDATE bookings SET method = ?, identifier = ?, status = ? WHERE order_id = ?'
        );
        $stmt->execute([$method, $identifier, 'paid', $ORDER_ID]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO bookings (order_id, amount, fee, total, method, identifier, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        // NOTE: 'paid' idhu demo-ku mattum. Real gateway (Razorpay etc.) use pannumbodhu
        // 'pending' nu save panni, gateway webhook confirm pannina appram 'paid' nu maathunga.
        $stmt->execute([$ORDER_ID, $AMOUNT, $FEE, $TOTAL, $method, $identifier, 'paid']);
    }

    json_out(['ok' => true, 'order_id' => $ORDER_ID]);

} catch (Throwable $e) {
    error_log('save_booking: ' . $e->getMessage());   // detail log-la, user-ku generic message
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
