<?php
/**
 * api/edit_listing.php
 * POST (multipart/form-data) — Edit Listing
 * Fields: item_id, title, description, price, contact, image (optional)
 * Requires: Login + Ownership of the listing
 */
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$userId = requireLogin();

$itemId      = (int) ($_POST['item_id']      ?? 0);
$title       = trim($_POST['title']          ?? '');
$description = trim($_POST['description']    ?? '');
$price       = trim($_POST['price']          ?? '');
$contact     = trim($_POST['contact']        ?? '');

// ─── Validation ───────────────────────────────────────────────────────────
if ($itemId <= 0)          { jsonResponse(false, 'Invalid item_id.'); }
if (!$title || !$description || !$price || !$contact) {
    jsonResponse(false, 'All fields are required.');
}
if (mb_strlen($title) > 150)       { jsonResponse(false, 'The title must be 150 characters or less.'); }
if (mb_strlen($description) > 500) { jsonResponse(false, 'The description must be 500 characters or less.'); }

$priceFloat = filter_var($price, FILTER_VALIDATE_FLOAT);
if ($priceFloat === false || $priceFloat <= 0) {
    jsonResponse(false, 'The price must be a positive number.');
}

$emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
$phoneRegex = '/^(05\d{8}|(\+966|966)5\d{8})$/';
if (!preg_match($emailRegex, $contact) && !preg_match($phoneRegex, $contact)) {
    jsonResponse(false, 'The contact method must be an email or a Saudi phone number.');
}

$pdo = getDB();

// Check ownership
$check = $pdo->prepare('SELECT photo FROM SecondUseItem WHERE secondUseItem_id = ? AND user_id = ?');
$check->execute([$itemId, $userId]);
$existing = $check->fetch();

if (!$existing) {
    http_response_code(403);
    jsonResponse(false, 'Listing not found or you do not have permission to edit it.');
}

$newImage = $existing['photo'];   // Keep original image if no new one is uploaded

// ─── Upload new image (optional) ──────────────────────────────────────────
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file      = $_FILES['image'];
    $maxBytes = 1.5 * 1024 * 1024;

    if ($file['size'] > $maxBytes) {
        jsonResponse(false, 'The image size must be less than 1.5 MB.');
    }

    $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
    $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        jsonResponse(false, 'Unsupported image type.');
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'uploads/' . uniqid('item_', true) . '.' . $ext;
    $savePath = __DIR__ . '/../' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $savePath)) {
        jsonResponse(false, 'Failed to upload image.');
    }

    // Delete old image if it exists in uploads
    if (str_starts_with($existing['photo'], 'uploads/')) {
        $oldPath = __DIR__ . '/../' . $existing['photo'];
        if (file_exists($oldPath)) @unlink($oldPath);
    }

    $newImage = $filename;
}

// ─── Update Database ──────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    'UPDATE SecondUseItem
     SET title = ?, description = ?, price = ?, contact_method = ?, photo = ?
     WHERE secondUseItem_id = ? AND user_id = ?'
);
$stmt->execute([$title, $description, $priceFloat, $contact, $newImage, $itemId, $userId]);

jsonResponse(true, 'Listing updated successfully.', [
    'item' => [
        'id'          => $itemId,
        'title'       => $title,
        'description' => $description,
        'price'       => number_format($priceFloat, 2, '.', ''),
        'contact'     => $contact,
        'image'       => $newImage,
    ]
]);
