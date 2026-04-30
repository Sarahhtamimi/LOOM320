<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'loom');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_PORT', '8889');

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }

    return $pdo;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function jsonResponse(bool $success, string $message, array $data = []): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

function requireLogin(): int {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        jsonResponse(false, 'You must log in first.');
    }

    return (int) $_SESSION['user_id'];
}
?>