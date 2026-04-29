<?php

require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$userId = requireLogin();

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$itemId = (int) ($body['item_id'] ?? 0);

if ($itemId <= 0) {
    jsonResponse(false, 'item_id Invalid.');
}

$pdo = getDB();

$check = $pdo->prepare('SELECT photo FROM SecondUseItem WHERE secondUseItem_id = ? AND user_id = ?');
$check->execute([$itemId, $userId]);
$item = $check->fetch();

if (!$item) {
    http_response_code(403);
    jsonResponse(false, 'Does not exist or you do not have permission to delete it.');
}

$pdo->prepare('DELETE FROM Bookmark WHERE secondUseItem_id = ?')->execute([$itemId]);

$pdo->prepare('DELETE FROM SecondUseItem WHERE secondUseItem_id = ? AND user_id = ?')->execute([$itemId, $userId]);

if (str_starts_with($item['photo'], 'uploads/')) {
    $oldPath = __DIR__ . '/../' . $item['photo'];
    if (file_exists($oldPath)) @unlink($oldPath);
}

jsonResponse(true, 'The advertisement has been successfully deleted.');
