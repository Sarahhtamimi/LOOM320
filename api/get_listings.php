<?php
/**
 * api/get_listings.php
 * GET  — يرجع كل الـ SecondUseItems كـ JSON
 * يُستدعى من SecondUse.html عند تحميل الصفحة
 */
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed.');
}

$pdo = getDB();

$stmt = $pdo->query(
    'SELECT secondUseItem_id AS id,
            user_id,
            title,
            photo   AS image,
            description,
            price,
            contact_method AS contact
     FROM SecondUseItem
     ORDER BY secondUseItem_id DESC'
);

$items = $stmt->fetchAll();

// نحوّل price لـ string عشان يتطابق مع شكل الـ frontend
foreach ($items as &$item) {
    $item['id']    = (int) $item['id'];
    $item['price'] = number_format((float) $item['price'], 2, '.', '');
}
unset($item);

// إذا المستخدم مسجّل دخول، نجيب bookmarks حقته عشان يعرف أيش محفوظ
$bookmarkedIds = [];
if (!empty($_SESSION['user_id'])) {
    $bStmt = $pdo->prepare(
        'SELECT secondUseItem_id FROM Bookmark WHERE user_id = ?'
    );
    $bStmt->execute([$_SESSION['user_id']]);
    $bookmarkedIds = array_column($bStmt->fetchAll(), 'secondUseItem_id');
    $bookmarkedIds = array_map('intval', $bookmarkedIds);
}

jsonResponse(true, 'ok', [
    'items'         => $items,
    'bookmarkedIds' => $bookmarkedIds,
    'username'      => $_SESSION['username'] ?? null,
]);
