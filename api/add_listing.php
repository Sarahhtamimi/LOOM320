<?php
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$userId = requireLogin();

$title       = trim($_POST['title']       ?? '');
$description = trim($_POST['description'] ?? '');
$price       = trim($_POST['price']       ?? '');
$contact     = trim($_POST['contact']     ?? '');

if (!$title || !$description || !$price || !$contact) {
    jsonResponse(false, 'All fields are required.');
}
if (mb_strlen($title) > 150) {
    jsonResponse(false, 'The title must be 150 characters or less.');
}
if (mb_strlen($description) > 500) {
    jsonResponse(false, 'The description must be 500 characters or less.');
}

$priceFloat = filter_var($price, FILTER_VALIDATE_FLOAT);
if ($priceFloat === false || $priceFloat <= 0) {
    jsonResponse(false, 'The price must be a positive number.');
}

$emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
$phoneRegex = '/^(05\d{8}|(\+966|966)5\d{8})$/';
if (!preg_match($emailRegex, $contact) && !preg_match($phoneRegex, $contact)) {
    jsonResponse(false, 'The contact method must be an email or a Saudi phone number.');
}
// ─── رفع الصورة ──────────────────────────────────────────────────────────
if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, 'The picture is required.');
}

$file     = $_FILES['image'];
$maxBytes = 1.5 * 1024 * 1024;
if ($file['size'] > $maxBytes) {
    jsonResponse(false, 'The image size must be less than 1.5 MB.');
}

$allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
$ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    jsonResponse(false, 'The image type is not supported. Use JPG, PNG, or WEBP.');
}

// تأكد من وجود مجلد uploads
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'uploads/' . uniqid('item_', true) . '.' . $ext;
$savePath = __DIR__ . '/../' . $filename;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    jsonResponse(false, 'Failed to upload the image. ...');
}

// ─── حفظ في قاعدة البيانات ───────────────────────────────────────────────
$pdo  = getDB();
$stmt = $pdo->prepare(
    'INSERT INTO SecondUseItem (user_id, title, photo, description, price, contact_method)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$userId, $title, $filename, $description, $priceFloat, $contact]);
$newId = (int) $pdo->lastInsertId();

jsonResponse(true, 'Added successfully.', [
    'item' => [
        'id'          => $newId,
        'user_id'     => $userId,
        'title'       => $title,
        'image'       => $filename,
        'description' => $description,
        'price'       => number_format($priceFloat, 2, '.', ''),
        'contact'     => $contact,
    ]
]);
