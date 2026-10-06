<?php
// db.php - shared by save_booking.php and save_ticket.php
// Idha public web folder-ku veliya vachaa best. Illana at least credentials maathunga.

const DB_HOST = 'localhost';
const DB_NAME = 'shop_db';
const DB_USER = 'your_db_user';
const DB_PASS = 'your_db_password';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

function json_out(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function read_json_body(): array {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_out(['ok' => false, 'error' => 'POST only'], 405);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        json_out(['ok' => false, 'error' => 'Invalid request'], 400);
    }
    return $data;
}
