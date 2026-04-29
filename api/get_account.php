<?php
/**
 * api/get_account.php
 * GET — يرجع بيانات المستخدم الحالي + bookmarks + listings
 * يتطلب تسجيل الدخول
 */
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed.');
}

$userId = requireLogin();

$pdo = getDB();

// ─── بيانات المستخدم ──────────────────────────────────────────────────────
$userStmt = $pdo->prepare('SELECT username, email FROM User WHERE user_id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

if (!$user) {
    http_response_code(404);
    jsonResponse(false, 'The user does not exist.');
}

// ─── Bookmarks ────────────────────────────────────────────────────────────
$bookmarkStmt = $pdo->prepare(
    'SELECT s.secondUseItem_id AS id,
            s.title,
            s.photo   AS image,
            s.description,
            s.price,
            s.contact_method AS contact
     FROM Bookmark b
     JOIN SecondUseItem s ON b.secondUseItem_id = s.secondUseItem_id
     WHERE b.user_id = ?
     ORDER BY b.bookmark_id DESC'
);
$bookmarkStmt->execute([$userId]);
$bookmarks = $bookmarkStmt->fetchAll();

foreach ($bookmarks as &$b) {
    $b['id']    = (int) $b['id'];
    $b['price'] = number_format((float) $b['price'], 2, '.', '');
}
unset($b);

// ─── My Listings ──────────────────────────────────────────────────────────
$listingStmt = $pdo->prepare(
    'SELECT secondUseItem_id AS id,
            title,
            photo   AS image,
            description,
            price,
            contact_method AS contact
     FROM SecondUseItem
     WHERE user_id = ?
     ORDER BY secondUseItem_id DESC'
);
$listingStmt->execute([$userId]);
$listings = $listingStmt->fetchAll();

foreach ($listings as &$l) {
    $l['id']    = (int) $l['id'];
    $l['price'] = number_format((float) $l['price'], 2, '.', '');
}
unset($l);

jsonResponse(true, 'ok', [
    'user'      => $user,
    'bookmarks' => $bookmarks,
    'listings'  => $listings,
]);
