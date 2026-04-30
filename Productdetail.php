<?php
declare(strict_types=1);
session_start();


const DB_HOST = '127.0.0.1';
const DB_PORT = '8889';
const DB_USER = 'root';
const DB_PASS = 'root';
const DB_NAMES = ['loom_database', 'loom'];
const UPLOAD_DIR = __DIR__ . '/images/uploads/';
const UPLOAD_URL = 'images/uploads/';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $errors = [];
    foreach (DB_NAMES as $dbName) {
        foreach ([DB_PASS, ''] as $pass) {
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname={$dbName};charset=utf8mb4",
                    DB_USER,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
                return $pdo;
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
    throw new RuntimeException('Database connection failed: ' . implode(' | ', $errors));
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function item_image(string $photo, string $title = ''): string {
    $photo = trim($photo);

    $candidates = [];

    // 1) Try the exact DB path first.
    if ($photo !== '') {
        $candidates[] = $photo;
    }

    // 2) Your database paths mapped to the actual file names in /images.
    // These cover your current DB values:
    // images/grey-cardigan.jpg, images/striped-bow-dress.jpg,
    // images/draped-green-top.jpg, images/ivory-rose-top.jpg,
    // images/polka-dot-skirt.jpg, images/linen-trousers.jpg
    $byDbPhoto = [
        'images/grey-cardigan.jpg'       => ['images/grey-cardigan.jpg'],
        'images/striped-bow-dress.jpg'   => ['images/imagesstriped-bow-dress.jpg', 'images/striped-bow-dress.jpg'],
        'images/draped-green-top.jpg'    => ['images/Draped Green Top'],
        'images/ivory-rose-top.jpg'      => ['images/ivory-rose-top.jpg'],
        'images/polka-dot-skirt.jpg'     => ['images/Polka Dot Midi Skirt'],
        'images/linen-trousers.jpg'      => ['images/Wide-Leg Linen Trousers'],
    ];

    if (isset($byDbPhoto[$photo])) {
        foreach ($byDbPhoto[$photo] as $candidate) {
            $candidates[] = $candidate;
        }
    }

    // 3) Title-based backup.
    $byTitle = [
        'Grey Wool Cardigan' => ['images/grey-cardigan.jpg'],
        'Striped Bow-Back Dress' => ['images/imagesstriped-bow-dress.jpg', 'images/striped-bow-dress.jpg'],
        'Draped Green Top' => ['images/Draped Green Top'],
        'Ivory Rose Top' => ['images/ivory-rose-top.jpg'],
        'Polka Dot Midi Skirt' => ['images/Polka Dot Midi Skirt'],
        'Wide-Leg Linen Trousers' => ['images/Wide-Leg Linen Trousers'],
    ];

    if (isset($byTitle[$title])) {
        foreach ($byTitle[$title] as $candidate) {
            $candidates[] = $candidate;
        }
    }

    // 4) Smart search: try common hidden/real extensions and "file.jpg.png" style names.
    $expanded = [];
    foreach ($candidates as $candidate) {
        $candidate = trim($candidate);
        if ($candidate === '') continue;

        $expanded[] = $candidate;

        $pathInfo = pathinfo($candidate);
        $dir = $pathInfo['dirname'] ?? 'images';
        $filename = $pathInfo['filename'] ?? $candidate;
        $basename = $pathInfo['basename'] ?? $candidate;

        foreach (['jpg', 'jpeg', 'png', 'webp', 'avif'] as $ext) {
            $expanded[] = $dir . '/' . $filename . '.' . $ext;
            $expanded[] = $dir . '/' . $basename . '.' . $ext;
        }
    }

    foreach (array_unique($expanded) as $candidate) {
        if ($candidate !== '' && file_exists(__DIR__ . '/' . $candidate)) {
            return $candidate;
        }
    }

    // 5) Final glob fallback inside /images for names with hidden/different extensions.
    $searchNames = [];
    if ($photo !== '') {
        $searchNames[] = pathinfo($photo, PATHINFO_FILENAME);
        $searchNames[] = pathinfo($photo, PATHINFO_BASENAME);
    }
    if ($title !== '') {
        $searchNames[] = $title;
    }

    foreach (array_unique($searchNames) as $name) {
        $name = trim($name);
        if ($name === '') continue;

        foreach (glob(__DIR__ . '/images/' . $name . '*') ?: [] as $match) {
            if (is_file($match)) {
                return 'images/' . basename($match);
            }
        }
    }

    return $photo;
}

function current_user_id(): ?int {
    if (isset($_SESSION['user_id'])) return (int)$_SESSION['user_id'];
    if (isset($_SESSION['userID'])) return (int)$_SESSION['userID'];
    return null;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function ensure_upload_dir(): void {
    $imagesDir = __DIR__ . DIRECTORY_SEPARATOR . 'images';
    $uploadDir = $imagesDir . DIRECTORY_SEPARATOR . 'uploads';

    if (!is_dir($imagesDir)) {
        mkdir($imagesDir, 0775, true);
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (!is_writable($uploadDir)) {
        throw new RuntimeException('Upload folder is not writable: images/uploads');
    }
}

function validate_listing(array $post, array $files, bool $imageRequired): array {
    $title = trim((string)($post['title'] ?? ''));
    $description = trim((string)($post['description'] ?? ''));
    $price = trim((string)($post['price'] ?? ''));
    $contact = trim((string)($post['contact_method'] ?? ''));
    $errors = [];

    if ($title === '' || $description === '' || $price === '' || $contact === '') {
        $errors[] = 'Please fill in all the fields.';
    }

    if (mb_strlen($title) > 150) {
        $errors[] = 'The title must be 150 characters or less.';
    }

    if (mb_strlen($description) > 500) {
        $errors[] = 'The description must be 500 characters or less.';
    }

    if (!is_numeric($price) || (float)$price <= 0) {
        $errors[] = 'The price must be a positive number.';
    }

    $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    $phoneRegex = '/^(05\d{8}|(\+966|966)5\d{8})$/';
    if (!preg_match($emailRegex, $contact) && !preg_match($phoneRegex, $contact)) {
        $errors[] = 'The method of communication must be an email or a Saudi mobile number.';
    }

    $photo = null;
    $hasUpload = isset($files['photo']) && ($files['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if ($imageRequired && !$hasUpload) {
        $errors[] = 'Please fill in all the fields.';
    }

    if ($hasUpload) {
        $file = $files['photo'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'Failed to upload the image.';
        } elseif (($file['size'] ?? 0) > 1.5 * 1024 * 1024) {
            $errors[] = 'The image is larger than 1.5 MB.';
        } else {
            $originalName = (string)($file['name'] ?? '');
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

            if (!in_array($ext, $allowed, true)) {
                $errors[] = 'The image type is not supported. Use JPG, PNG, WEBP, or AVIF.';
            } else {
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    $errors[] = 'The uploaded file is not a valid image.';
                }
            }
        }

        if (!$errors) {
            ensure_upload_dir();

            $safeExt = $ext === 'jpeg' ? 'jpg' : $ext;
            $filename = 'item_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
            $targetPath = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $filename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $errors[] = 'Failed to upload the image.';
            } else {
                $photo = UPLOAD_URL . $filename;
            }
        }
    }

    return [$errors, [
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'contact_method' => $contact,
        'photo' => $photo,
    ]];
}

$userId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_bookmark') {
    if (!$userId) redirect('login.php');

    $itemId = (int)($_POST['item_id'] ?? 0);
    $exists = db()->prepare('SELECT bookmark_id FROM `bookmark` WHERE user_id = ? AND secondUseItem_id = ?');
    $exists->execute([$userId, $itemId]);
    $row = $exists->fetch();

    if ($row) {
        db()->prepare('DELETE FROM `bookmark` WHERE bookmark_id = ?')->execute([$row['bookmark_id']]);
    } else {
        db()->prepare('INSERT INTO `bookmark` (user_id, secondUseItem_id) VALUES (?, ?)')->execute([$userId, $itemId]);
    }

    redirect('Productdetail.php?id=' . $itemId);
}

$itemId = (int)($_GET['id'] ?? 1);
$stmt = db()->prepare(
    'SELECT s.secondUseItem_id AS id,
            s.user_id,
            s.title,
            s.photo AS image,
            s.description,
            s.price,
            s.contact_method AS contact,
            u.username,
            EXISTS(
                SELECT 1 FROM `bookmark` b
                WHERE b.user_id = :uid AND b.secondUseItem_id = s.secondUseItem_id
            ) AS is_bookmarked
     FROM `seconduseitem` s
     JOIN `user` u ON u.user_id = s.user_id
     WHERE s.secondUseItem_id = :id'
);
$stmt->execute(['uid' => $userId ?? 0, 'id' => $itemId]);
$item = $stmt->fetch();
if (!$item) {
    http_response_code(404);
    exit('Item not found');
}
$isLoggedIn = $userId !== null;
$username = $_SESSION['username'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LOOM - <?= e($item["title"]) ?></title>
  <style>
    :root {
      --dusty-blush: #dea4ad;
      --terracotta: #b56449;
      --sage: #6b7883;
      --charcoal: #434543;
      --soft-white: #fffdfb;
      --line: #eadfda;
      --shadow: 0 8px 24px rgba(67, 69, 67, 0.06);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, Helvetica, sans-serif;
      background: linear-gradient(to bottom, #f8f1ee, #fdfaf8);
      color: var(--charcoal);
      line-height: 1.6;
    }

    img {
      max-width: 100%;
      display: block;
    }

    /* TOPBAR */
    .topbar {
      background: rgba(255, 253, 251, 0.92);
      border-bottom: 1px solid #efe4df;
      position: sticky;
      top: 0;
      z-index: 20;
      backdrop-filter: blur(8px);
    }

    .topbar-inner {
      max-width: 1120px;
      margin: 0 auto;
      padding: 14px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 18px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--charcoal);
      text-decoration: none;
    }

    .brand-mark {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: linear-gradient(135deg, #f0c7cc, #e8d9cf);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--terracotta);
      font-size: 16px;
      box-shadow: var(--shadow);
    }

    .brand-name {
      font-family: Georgia, "Times New Roman", serif;
      font-size: 1.8rem;
      letter-spacing: 1px;
      font-weight: 500;
    }

    .nav-links {
      display: flex;
      gap: 22px;
      align-items: center;
      flex-wrap: wrap;
    }

    .nav-links a {
      text-decoration: none;
      color: #726f6b;
      font-size: 0.92rem;
      transition: 0.25s ease;
    }

    .nav-links a:hover,
    .nav-links a.active {
      color: var(--terracotta);
    }

    /* BREADCRUMB */
    .breadcrumb {
      max-width: 1120px;
      margin: 0 auto;
      padding: 18px 20px 0;
      font-size: 0.85rem;
      color: #9a9390;
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .breadcrumb a {
      color: #9a9390;
      text-decoration: none;
      transition: 0.2s;
    }

    .breadcrumb a:hover {
      color: var(--terracotta);
    }

    .breadcrumb span {
      color: #c5bbb7;
    }

    /* MAIN LAYOUT */
    .page-container {
      max-width: 1120px;
      margin: 0 auto;
      padding: 28px 20px 60px;
    }

    .product-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 52px;
      align-items: start;
    }

    /* IMAGE SIDE */
    .product-image-wrap {
      border-radius: 24px;
      overflow: hidden;
      background: #f4eeea;
      box-shadow: 0 12px 40px rgba(67, 69, 67, 0.08);
      position: sticky;
      top: 90px;
    }

    .product-image {
      width: 100%;
      aspect-ratio: 3 / 4;
      object-fit: cover;
      display: block;
    }

    /* INFO SIDE */
    .product-info {
      padding-top: 8px;
    }

    .product-tag {
      display: inline-block;
      background: #f3ece8;
      color: #8a7e79;
      font-size: 0.78rem;
      padding: 5px 12px;
      border-radius: 999px;
      border: 1px solid #ede0da;
      margin-bottom: 14px;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .product-title {
      font-family: Georgia, "Times New Roman", serif;
      font-size: 2.4rem;
      font-weight: 500;
      line-height: 1.15;
      color: var(--charcoal);
      margin-bottom: 12px;
    }

    .product-price {
      font-size: 1.7rem;
      color: var(--terracotta);
      font-weight: 600;
      margin-bottom: 22px;
    }

    .divider {
      border: none;
      border-top: 1px solid var(--line);
      margin: 22px 0;
    }

    .product-description {
      color: #6b6764;
      font-size: 0.95rem;
      line-height: 1.75;
      margin-bottom: 22px;
    }

    /* DETAILS LIST */
    .details-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 28px;
    }

    .detail-row {
      display: flex;
      gap: 10px;
      font-size: 0.9rem;
    }

    .detail-label {
      color: #9a9390;
      min-width: 80px;
    }

    .detail-value {
      color: var(--charcoal);
      font-weight: 500;
    }

    /* CONTACT SECTION */
    .contact-section {
      background: rgba(255, 253, 251, 0.9);
      border: 1px solid #f0e5e1;
      border-radius: 18px;
      padding: 20px;
      margin-bottom: 24px;
    }

    .contact-label {
      font-size: 0.82rem;
      color: #9a9390;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .contact-value {
      font-size: 1rem;
      color: var(--charcoal);
      font-weight: 500;
    }

    /* BUTTONS */
    .btn-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .btn-main {
      flex: 1;
      background: var(--terracotta);
      color: white;
      border: none;
      padding: 14px 22px;
      border-radius: 999px;
      cursor: pointer;
      font-size: 0.95rem;
      transition: 0.25s ease;
      text-align: center;
      text-decoration: none;
      display: inline-block;
    }

    .btn-main:hover {
      opacity: 0.93;
      transform: translateY(-2px);
    }

    .btn-outline {
      flex: 1;
      background: transparent;
      color: var(--charcoal);
      border: 1px solid var(--line);
      padding: 14px 22px;
      border-radius: 999px;
      cursor: pointer;
      font-size: 0.95rem;
      transition: 0.25s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
    }

    .btn-outline:hover {
      border-color: var(--dusty-blush);
      color: var(--terracotta);
      background: #fff8f5;
    }

    .btn-outline.saved {
      color: var(--terracotta);
      border-color: var(--dusty-blush);
      background: #fff8f5;
    }

    .bookmark-svg {
      width: 15px;
      height: 18px;
      fill: currentColor;
    }





    .header {
      position: sticky;
      top: 0;
      z-index: 60;
      backdrop-filter: blur(14px);
      background: rgba(243, 232, 232, .72);
      border-bottom: 1px solid rgba(67, 69, 67, .10);
    }

    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding: 14px 0;
    }

    .container {
      width: min(1180px, calc(100% - 40px));
      margin: 0 auto;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 180px;
      text-decoration: none;
      color: inherit;
    }

    .logo-mark {
      width: 38px;
      height: 38px;
      display: grid;
      place-items: center;
      border-radius: 14px;
      background: rgba(255, 255, 255, .66);
      border: 1px solid rgba(67, 69, 67, .10);
      box-shadow: 0 12px 32px rgba(67, 69, 67, .08);
    }

    .wordmark {
  font-family: "Cormorant Garamond", serif !important;
  font-size: 26px !important;
  font-weight: 500 !important; /* أعطِ أولوية للوزن */
  letter-spacing: .08em !important;
  color: #434543 !important;
}
    
    nav {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 0 12px;
      border-radius: 999px;
      border: 1px solid rgba(67, 69, 67, .10);
      background: rgba(255, 255, 255, .58);
      box-shadow: 0 12px 32px rgba(67, 69, 67, .08);
    }

    nav a {
      padding: 11px 10px;
      font-size: 13px;
      font-weight: 700;
      color: rgba(67, 69, 67, .84);
      position: relative;
      text-decoration: none;
    }

    nav a.active::after {
      content: "";
      position: absolute;
      left: 10px;
      right: 10px;
      bottom: 7px;
      height: 2px;
      border-radius: 999px;
      background: linear-gradient(90deg, #B56449, #DEA4AD);
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .pill {
      border-radius: 999px;
      border: 1px solid rgba(67, 69, 67, .10);
      padding: 11px 14px;
      font-size: 13px;
      font-weight: 800;
      background: rgba(255, 255, 255, .65);
      box-shadow: 0 12px 32px rgba(67, 69, 67, .08);
      color: #434543;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 60;
      backdrop-filter: blur(14px);
      background: rgba(243, 232, 232, .72);
      border-bottom: 1px solid rgba(67, 69, 67, .10);
    }

    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding: 14px 0;
    }

    .container {
      width: min(1180px, calc(100% - 40px));
      margin: 0 auto;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 180px;
      text-decoration: none;
      color: inherit;
    }

    .logo-mark {
      width: 38px;
      height: 38px;
      display: grid;
      place-items: center;
      border-radius: 14px;
      background: rgba(255, 255, 255, .66);
      border: 1px solid rgba(67, 69, 67, .10);
      box-shadow: 0 12px 32px rgba(67, 69, 67, .08);
    }

    .wordmark {
      font-family: "Cormorant Garamond", serif;
      font-size: 26px;
      font-weight: 700;
      letter-spacing: .08em;
      color: #434543;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 0 12px;
      border-radius: 999px;
      border: 1px solid rgba(67, 69, 67, .10);
      background: rgba(255, 255, 255, .58);
      box-shadow: 0 12px 32px rgba(67, 69, 67, .08);
    }

    nav a {
      padding: 11px 10px;
      font-size: 13px;
      font-weight: 700;
      color: rgba(67, 69, 67, .84);
      position: relative;
      text-decoration: none;
    }

    nav a.active::after {
      content: "";
      position: absolute;
      left: 10px;
      right: 10px;
      bottom: 7px;
      height: 2px;
      border-radius: 999px;
      background: linear-gradient(90deg, #B56449, #DEA4AD);
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .pill {
      border-radius: 999px;
      border: 1px solid rgba(67, 69, 67, .10);
      padding: 11px 14px;
      font-size: 13px;
      font-weight: 800;
      background: rgba(255, 255, 255, .65);
      box-shadow: 0 12px 32px rgba(67, 69, 67, .08);
      color: #434543;
    }

    .footer {
      margin-top: 10px;
      padding: 38px 0 0;
      border-top: 1px solid rgba(67, 69, 67, .10);
      background:
        radial-gradient(circle at top left, rgba(222, 164, 173, .20), transparent 35%),
        radial-gradient(circle at top right, rgba(107, 120, 131, .14), transparent 30%),
        linear-gradient(180deg, rgba(255, 255, 255, .82), rgba(243, 232, 232, .75), rgba(255, 255, 255, .88));
    }

    .footer-layout {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 56px;
      flex-wrap: wrap;
      padding-bottom: 28px;
    }

    .footer-left {
      flex: 1.2;
      min-width: 320px;
    }

    .footer-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 18px;
    }

    .footer-about h4,
    .footer-info h4 {
      margin: 0 0 8px;
      font-family: "Cormorant Garamond", serif;
      font-size: 22px;
      font-weight: 700;
      color: #B56449;
      letter-spacing: .01em;
    }

    .footer-about p,
    .footer-info p {
      margin: 0;
      color: rgba(67, 69, 67, .78);
      font-size: 14px;
      line-height: 1.9;
      font-family: Inter, sans-serif;
      max-width: 470px;
    }

    .footer-right {
      min-width: 240px;
      display: flex;
      flex-direction: column;
      gap: 24px;
      text-align: left;
      margin-right: 35px;
    }

    .footer-bottom {
      border-top: 1px solid rgba(67, 69, 67, .08);
      padding: 16px 0;
      text-align: center;
      background: rgba(255, 255, 255, .35);
    }

    .footer-bottom p {
      margin: 0;
      font-size: 12px;
      color: rgba(67, 69, 67, .60);
      letter-spacing: .02em;
    }

    @media (max-width: 980px) {
      nav {
        display: none;
      }
    }

    @media (max-width: 768px) {
      .footer-layout {
        flex-direction: column;
        gap: 24px;
      }

      .footer-right {
        text-align: left;
        margin-right: 0;
      }
    }

    @media (max-width: 980px) {
      nav {
        display: none;
      }
    }
  </style>
</head>

<body>

  
  <header class="header">
    <div class="container header-inner">
      <a class="brand" href="home.html" aria-label="LOOM Home">
        <div class="logo-mark">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M13 4L9.5 20" stroke="#6B7883" stroke-width="2" stroke-linecap="round" />
            <path d="M8.5 7.5c3-2 6-2 9 0" stroke="#DEA4AD" stroke-width="2" stroke-linecap="round" />
            <path d="M9.2 7.6c-3.2 2.2-3.5 5.2-.6 7.2 2.4 1.6 6.1 1.3 9.4-1.2" stroke="#B56449" stroke-width="2"
              stroke-linecap="round" />
          </svg>
        </div>
        <div class="wordmark">LOOM</div>
      </a>

      <nav>
        <a href="index.html">Home</a>
        <a href="brands.html">Brands</a>
        <a href="Blog.php">Blogs</a>
        <a href="SecondUse.php" class="active">Second Hand</a>
        <?php if ($isLoggedIn): ?><a href="user_account.php" id="profileLink">Profile</a><?php else: ?><a href="user_account.php" id="profileLink" style="display:none;">Profile</a><?php endif; ?>
        <?php if (!$isLoggedIn): ?><a href="login.php" id="loginLink">Login</a><?php else: ?><a href="login.php" id="loginLink" style="display:none;">Login</a><?php endif; ?>
      </nav>

      <div class="header-actions">
        <button class="pill" id="authPill"><?= e($username ?? "Guest Mode") ?></button>
      </div>
    </div>
  </header>


  <div class="breadcrumb">
    <a href="SecondUse.php">Second-Use</a>
    <span>›</span>
    <span><?= e($item["title"]) ?></span>
  </div>

  <main class="page-container">
    <div class="product-layout">

      <div class="product-image-wrap">
        <img class="product-image" src="<?= e(item_image($item["image"], $item["title"])) ?>" alt="<?= e($item["title"]) ?>">
      </div>

      <div class="product-info">
        <div class="product-tag">Second-Use</div>
        <h1 class="product-title"><?= e($item["title"]) ?></h1>
        <div class="product-price">⃁<?= e(number_format((float)$item["price"], 2, ".", "")) ?></div>

        <hr class="divider">

        <p class="product-description"><?= nl2br(e($item["description"])) ?></p>

        <div class="details-list">
          <div class="detail-row">
            <span class="detail-label">Seller</span>
            <span class="detail-value"><?= e($item["username"]) ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Listing</span>
            <span class="detail-value">#<?= (int)$item["id"] ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Type</span>
            <span class="detail-value">Second-Use</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value">Available</span>
          </div>
        </div>

        <hr class="divider">

        <div class="contact-section">
          <div class="contact-label">Contact seller</div>
          <div class="contact-value"><?= e($item["contact"]) ?></div>
        </div>

        <div class="btn-row">
          <?php if (filter_var($item["contact"], FILTER_VALIDATE_EMAIL)): ?><a href="mailto:<?= e($item["contact"]) ?>" class="btn-main">Contact Seller</a><?php else: ?><a href="tel:<?= e(preg_replace("/\s+/", "", $item["contact"])) ?>" class="btn-main">Contact Seller</a><?php endif; ?>
          <form method="post" style="flex:1;">
            <input type="hidden" name="action" value="toggle_bookmark">
            <input type="hidden" name="item_id" value="<?= (int)$item["id"] ?>">
            <button class="btn-outline <?= !empty($item["is_bookmarked"]) ? "saved" : "" ?>" id="saveBtn" type="submit" style="width:100%;">
              <svg class="bookmark-svg" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7 4.5C7 3.67 7.67 3 8.5 3h7C16.33 3 17 3.67 17 4.5V21l-5-3.8L7 21V4.5z"></path>
              </svg>
              <?= !empty($item["is_bookmarked"]) ? "Saved" : "Save" ?>
            </button>
          </form>
        </div>
      </div>

    </div>
  </main>

  <footer class="footer">
    <div class="container footer-layout">

      <div class="footer-left">
        <div class="footer-brand">
          <div class="logo-mark">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M13 4L9.5 20" stroke="#6B7883" stroke-width="2" stroke-linecap="round" />
              <path d="M8.5 7.5c3-2 6-2 9 0" stroke="#DEA4AD" stroke-width="2" stroke-linecap="round" />
              <path d="M9.2 7.6c-3.2 2.2-3.5 5.2-.6 7.2 2.4 1.6 6.1 1.3 9.4-1.2" stroke="#B56449" stroke-width="2"
                stroke-linecap="round" />
            </svg>
          </div>
          <div class="wordmark">LOOM</div>
        </div>

        <div class="footer-about">
          <h4>About LOOM</h4>
          <p>
            LOOM is a platform that helps users explore sustainable fashion brands,
            learn more about responsible fashion, and support second-hand clothing.
          </p>
        </div>
      </div>

      <div class="footer-right">
        <div class="footer-info">
          <h4>Contact</h4>
          <p>teamloom@gmail.com</p>
        </div>

        <div class="footer-info">
          <h4>Category</h4>
          <p>Sustainable Fashion</p>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <p>© 2026 Team LOOM. All rights reserved.</p>
    </div>
  </footer>

  
   <script></script>
</body>

</html>
