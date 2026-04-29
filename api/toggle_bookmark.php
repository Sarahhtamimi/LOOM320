<?php
/**
 * api/toggle_bookmark.php
 * POST (JSON body) — Add or Remove bookmark
 * Body: { "item_id": 3 }
 * Called from SecondUse.html and Productdetail.html
 * Requires: Login
 */
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$userId = requireLogin();

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$itemId = isset($body['item_id']) ? (int) $body['item_id'] : 0;

if ($itemId <= 0) {
    jsonResponse(false, 'Invalid item_id.');
}

$pdo = getDB();

// Verify the product exists
$check = $pdo->prepare('SELECT secondUseItem_id FROM SecondUseItem WHERE secondUseItem_id = ?');
$check->execute([$itemId]);
if (!$check->fetch()) {
    jsonResponse(false, 'Product not found.');
}

// Check if it is already bookmarked
$exists = $pdo->prepare(
    'SELECT bookmark_id FROM Bookmark WHERE user_id = ? AND secondUseItem_id = ?'
);
$exists->execute([$userId, $itemId]);
$row = $exists->fetch();

if ($row) {
    // Exists -> Remove it
    $del = $pdo->prepare('DELETE FROM Bookmark WHERE bookmark_id = ?');
    $del->execute([$row['bookmark_id']]);
    jsonResponse(true, 'Bookmark removed successfully.', ['bookmarked' => false]);
} else {
    // Does not exist -> Add it
    $ins = $pdo->prepare(
        'INSERT INTO Bookmark (user_id, secondUseItem_id) VALUES (?, ?)'
    );
    $ins->execute([$userId, $itemId]);
    jsonResponse(true, 'Bookmarked successfully.', ['bookmarked' => true]);
}
