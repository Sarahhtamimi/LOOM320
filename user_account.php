<?php
declare(strict_types=1);
session_start();


const DB_HOST = '127.0.0.1';
const DB_PORT = '8889';
const DB_USER = 'root';
const DB_PASS = 'root';
const DB_NAMES = ['loom', 'loom_database'];
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
if (!$userId) redirect('login.php');

$editErrors = [];
$editOld = [
    'item_id' => '',
    'title' => '',
    'description' => '',
    'price' => '',
    'contact_method' => '',
];
$openEditModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'logout') {
        session_unset();
        session_destroy();
        redirect('logout.php');
    }

    if ($action === 'remove_bookmark') {
        db()->prepare('DELETE FROM `bookmark` WHERE user_id = ? AND secondUseItem_id = ?')
            ->execute([$userId, (int)($_POST['item_id'] ?? 0)]);
        redirect('user_account.php');
    }

    if ($action === 'delete_listing') {
        db()->prepare('DELETE FROM `seconduseitem` WHERE secondUseItem_id = ? AND user_id = ?')
            ->execute([(int)($_POST['item_id'] ?? 0), $userId]);
        redirect('user_account.php');
    }

    if ($action === 'edit_listing') {
        $itemId = (int)($_POST['item_id'] ?? 0);

        $editOld = [
            'item_id' => (string)$itemId,
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'price' => trim((string)($_POST['price'] ?? '')),
            'contact_method' => trim((string)($_POST['contact_method'] ?? '')),
        ];

        [$errors, $data] = validate_listing($_POST, $_FILES, false);

        if ($errors) {
            $editErrors = $errors;
            $openEditModal = true;
        } else {
            if ($data['photo']) {
                $stmt = db()->prepare(
                    'UPDATE `seconduseitem`
                     SET title = ?, photo = ?, description = ?, price = ?, contact_method = ?
                     WHERE secondUseItem_id = ? AND user_id = ?'
                );
                $stmt->execute([
                    $data['title'],
                    $data['photo'],
                    $data['description'],
                    (float)$data['price'],
                    $data['contact_method'],
                    $itemId,
                    $userId
                ]);
            } else {
                $stmt = db()->prepare(
                    'UPDATE `seconduseitem`
                     SET title = ?, description = ?, price = ?, contact_method = ?
                     WHERE secondUseItem_id = ? AND user_id = ?'
                );
                $stmt->execute([
                    $data['title'],
                    $data['description'],
                    (float)$data['price'],
                    $data['contact_method'],
                    $itemId,
                    $userId
                ]);
            }

            redirect('user_account.php');
        }
    }
}

$userStmt = db()->prepare('SELECT username, email FROM `user` WHERE user_id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();
if (!$user) {
    session_destroy();
    redirect('login.php');
}

$bookmarkStmt = db()->prepare(
    'SELECT s.secondUseItem_id AS id, s.title, s.photo AS image, s.description, s.price, s.contact_method AS contact
     FROM `bookmark` b
     JOIN `seconduseitem` s ON b.secondUseItem_id = s.secondUseItem_id
     WHERE b.user_id = ?
     ORDER BY b.bookmark_id DESC'
);
$bookmarkStmt->execute([$userId]);
$bookmarks = $bookmarkStmt->fetchAll();

$listingStmt = db()->prepare(
    'SELECT secondUseItem_id AS id, title, photo AS image, description, price, contact_method AS contact
     FROM `seconduseitem`
     WHERE user_id = ?
     ORDER BY secondUseItem_id DESC'
);
$listingStmt->execute([$userId]);
$listings = $listingStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LOOM - My Account</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">

  <style>
    :root {
      --dusty-blush: #dea4ad;
      --terracotta: #b56449;
      --sage: #6b7883;
      --warm-pink: #f3e8e8;
      --charcoal: #434543;
      --cream: #faf6f3;
      --soft-white: #fffdfb;
      --line: #eadfda;
      --shadow: 0 8px 24px rgba(67, 69, 67, 0.06);
      --radius-lg: 22px;
      --radius-md: 16px;
      --radius-sm: 12px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to bottom, #f8f1ee, #fdfaf8);
      color: var(--charcoal);
      line-height: 1.6;
    }

    img {
      max-width: 100%;
      display: block;
    }

    button,
    input,
    textarea,
    select {
      font: inherit;
    }

    .container {
      width: min(1180px, calc(100% - 40px));
      margin: 0 auto;
    }

    .page-container {
      max-width: 1120px;
      margin: 0 auto;
      padding: 20px 20px 40px;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(14px);
      background: rgba(243, 232, 232, 0.78);
      border-bottom: 1px solid rgba(67, 69, 67, 0.08);
    }

    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      padding: 14px 0;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      color: inherit;
      min-width: 180px;
    }

    .logo-mark {
      width: 38px;
      height: 38px;
      display: grid;
      place-items: center;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.68);
      border: 1px solid rgba(67, 69, 67, 0.10);
      box-shadow: 0 12px 24px rgba(67, 69, 67, 0.06);
    }

    .wordmark {
      font-family: "Cormorant Garamond", serif;
      font-size: 26px;
      font-weight: 700;
      letter-spacing: 0.08em;
      color: #434543;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 0 14px;
      min-height: 50px;
      border-radius: 999px;
      border: 1px solid rgba(67, 69, 67, 0.08);
      background: rgba(255, 255, 255, 0.72);
      box-shadow: 0 10px 24px rgba(67, 69, 67, 0.05);
    }

    nav a {
      padding: 11px 10px;
      font-size: 14px;
      font-weight: 700;
      color: rgba(67, 69, 67, 0.84);
      position: relative;
      text-decoration: none;
      white-space: nowrap;
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
      border: 1px solid rgba(67, 69, 67, 0.08);
      padding: 11px 16px;
      font-size: 13px;
      font-weight: 800;
      background: rgba(255, 255, 255, 0.72);
      box-shadow: 0 10px 24px rgba(67, 69, 67, 0.05);
      color: #434543;
    }

    .account-head {
      padding: 42px 0 18px;
      text-align: center;
    }

    .account-head h2 {
      font-family: "Cormorant Garamond", serif;
      font-size: 2.5rem;
      color: var(--charcoal);
      font-weight: 500;
      margin-bottom: 4px;
    }

    .section-card {
      background: rgba(255, 253, 251, 0.9);
      border: 1px solid #f0e5e1;
      border-radius: 24px;
      box-shadow: var(--shadow);
    }

    .profile-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      padding: 22px 26px;
      margin-bottom: 36px;
      flex-wrap: wrap;
    }

    .profile-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .avatar {
      width: 92px;
      height: 92px;
      border-radius: 50%;
      background: #e9e3e0;
      border: 3px solid #f6e3e6;
      box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.55);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #8e918b;
      overflow: hidden;
      position: relative;
    }

    .avatar svg {
      width: 42px;
      height: 42px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.9;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .profile-info h3 {
      font-family: "Cormorant Garamond", serif;
      font-size: 1.45rem;
      font-weight: 500;
      margin-bottom: 2px;
    }

    .profile-info .email {
      color: #7f7975;
      margin-bottom: 4px;
      font-size: 0.95rem;
    }

    .profile-info .small-text {
      color: #8d8783;
      font-size: 0.88rem;
    }

    .soft-btn {
      border: none;
      background: var(--terracotta);
      color: white;
      padding: 11px 18px;
      border-radius: 999px;
      cursor: pointer;
      transition: 0.25s ease;
      font-size: 0.95rem;
    }

    .soft-btn:hover {
      transform: translateY(-2px);
      opacity: 0.96;
    }

    .section-title-wrap {
      display: flex;
      align-items: end;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 14px;
      flex-wrap: wrap;
    }

    .section-title {
      font-family: "Cormorant Garamond", serif;
      font-size: 2rem;
      font-weight: 500;
      color: var(--charcoal);
      margin-bottom: 4px;
    }

    .section-subtext {
      color: #847e79;
      font-size: 0.92rem;
    }

    .section-space {
      margin-bottom: 48px;
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    .fashion-card {
      background: var(--soft-white);
      border: 1px solid var(--line);
      border-radius: 20px;
      overflow: hidden;
      position: relative;
      box-shadow: 0 6px 18px rgba(67, 69, 67, 0.05);
      transition: 0.25s ease;
      width: 100%;
    }

    .fashion-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 22px rgba(67, 69, 67, 0.08);
    }

    .card-image-wrap {
      position: relative;
      overflow: hidden;
      background: #f6f1ee;
    }

    .card-image {
      width: 100%;
      height: 280px;
      object-fit: cover;
    }

    .card-action-icons {
      position: absolute;
      top: 10px;
      right: 10px;
      display: flex;
      gap: 6px;
      opacity: 1;
      transform: translateY(0);
      transition: 0.28s ease;
      z-index: 5;
    }

    .icon-btn {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid rgba(255, 250, 247, 0.85);
      background: rgba(255, 253, 251, 0.95);
      color: var(--charcoal);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: 0.2s ease;
      box-shadow: 0 6px 16px rgba(67, 69, 67, 0.08);
      font-size: 0.8rem;
      padding: 0;
    }

    .icon-btn:hover {
      transform: scale(1.05);
      color: var(--terracotta);
      background: #fff8f5;
    }

    .delete-btn {
      font-size: 1.05rem;
    }

    .bookmark-btn {
      background: rgba(255, 253, 251, 0.98);
      width: 42px;
      height: 42px;
    }

    .bookmark-btn.active {
      color: var(--terracotta);
    }

    .bookmark-svg {
      width: 17px;
      height: 21px;
      fill: currentColor;
      display: block;
    }

    .card-body {
      padding: 14px 14px 16px;
    }

    .title-price-row {
      display: flex;
      justify-content: space-between;
      align-items: start;
      gap: 10px;
      margin-bottom: 8px;
    }

    .card-title {
      font-family: "Cormorant Garamond", serif;
      font-size: 1.3rem;
      line-height: 1.1;
      font-weight: 500;
      color: var(--charcoal);
    }

    .card-price {
      color: var(--terracotta);
      font-size: 1rem;
      font-weight: 600;
      white-space: nowrap;
    }

    .card-description {
      color: #716d69;
      font-size: 0.85rem;
      margin-bottom: 10px;
      min-height: 50px;
    }

    .contact-pill {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 999px;
      background: #f3ece8;
      color: #6f6c68;
      font-size: 0.8rem;
      border: 1px solid #eee2de;
      margin-bottom: 10px;
    }

    .card-bottom-line {
      margin-top: 10px;
      border-top: 1px solid #eee3de;
    }

    .empty-state {
      padding: 32px 20px;
      text-align: center;
      background: #fffdfb;
      border: 1px dashed #dfc9c2;
      border-radius: 20px;
      color: #7e7773;
      grid-column: 1 / -1;
    }

    .empty-state h4 {
      font-family: "Cormorant Garamond", serif;
      font-size: 1.5rem;
      margin-bottom: 6px;
      color: var(--charcoal);
      font-weight: 500;
    }

    .empty-state p {
      font-size: 0.92rem;
    }

    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(53, 48, 45, 0.34);
      display: none;
      justify-content: center;
      align-items: flex-start;
      padding: 100px 20px 20px;
      z-index: 1000;
      overflow-y: auto;
    }

    .modal-overlay.show {
      display: flex;
    }

    .modal-box {
      width: 100%;
      max-width: 520px;
      background: #fffaf8;
      border-radius: 22px;
      border: 1px solid #f1e3de;
      box-shadow: 0 20px 50px rgba(67, 69, 67, 0.14);
      padding: 24px;
      animation: popIn 0.22s ease;
    }

    @keyframes popIn {
      from {
        opacity: 0;
        transform: translateY(14px) scale(0.98);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .modal-title {
      font-family: "Cormorant Garamond", serif;
      font-size: 1.7rem;
      font-weight: 500;
      margin-bottom: 8px;
      color: var(--charcoal);
    }

    .modal-text {
      color: #79736f;
      margin-bottom: 18px;
      font-size: 0.92rem;
    }

    .form-group {
      margin-bottom: 14px;
    }

    .form-group label {
      display: block;
      margin-bottom: 6px;
      color: #5f5a56;
      font-size: 0.9rem;
    }

    .form-input,
    .form-textarea {
      width: 100%;
      border: 1px solid #eadad4;
      background: #fffefd;
      border-radius: 12px;
      padding: 11px 12px;
      outline: none;
      transition: 0.22s ease;
      color: var(--charcoal);
      font-size: 0.92rem;
    }

    .form-textarea {
      min-height: 96px;
      resize: vertical;
    }

    .form-input:focus,
    .form-textarea:focus {
      border-color: var(--dusty-blush);
      box-shadow: 0 0 0 4px rgba(222, 164, 173, 0.13);
    }

    .form-error-box {
      margin: 0 0 16px;
      padding: 12px 14px;
      border-radius: 14px;
      background: #fff1f1;
      border: 1px solid #f0cccc;
      color: #9f4e4e;
      font-size: 0.88rem;
    }

    .form-error-box ul {
      margin: 0;
      padding-left: 18px;
    }

    .form-error-box li {
      margin: 3px 0;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 16px;
      flex-wrap: wrap;
    }

    .btn-light,
    .btn-main,
    .btn-delete {
      padding: 10px 18px;
      border-radius: 999px;
      cursor: pointer;
      transition: 0.22s ease;
      font-size: 0.92rem;
    }

    .btn-light {
      background: #f7efeb;
      color: var(--charcoal);
      border: 1px solid #ecdcd5;
    }

    .btn-light:hover {
      background: #f3e6e1;
    }

    .btn-main {
      background: var(--terracotta);
      color: white;
      border: none;
    }

    .btn-main:hover {
      opacity: 0.95;
      transform: translateY(-1px);
    }

    .btn-delete {
      background: #f3e7e3;
      color: var(--terracotta);
      border: 1px solid #e8d0c8;
    }

    .btn-delete:hover {
      background: #efdfd9;
    }

    .footer {
      margin-top: 16px;
      padding: 38px 0 0;
      border-top: 1px solid rgba(67, 69, 67, 0.08);
      background:
        radial-gradient(circle at top left, rgba(222, 164, 173, 0.18), transparent 34%),
        radial-gradient(circle at top right, rgba(107, 120, 131, 0.10), transparent 28%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(243, 232, 232, 0.78), rgba(255, 255, 255, 0.9));
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
      color: rgba(67, 69, 67, 0.78);
      font-size: 14px;
      line-height: 1.9;
      font-family: Inter, sans-serif;
    }

    .footer-about p {
      max-width: 470px;
    }

    .footer-right {
      min-width: 220px;
      display: flex;
      flex-direction: column;
      gap: 22px;
      text-align: left;
      margin-right: 20px;
    }

    .footer-bottom {
      border-top: 1px solid rgba(67, 69, 67, 0.08);
      padding: 16px 0;
      text-align: center;
      background: rgba(255, 255, 255, 0.34);
    }

    .footer-bottom p {
      margin: 0;
      font-size: 12px;
      color: rgba(67, 69, 67, 0.60);
      letter-spacing: 0.02em;
    }

    @media (max-width: 1100px) {
      .card-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
      }
    }

    @media (max-width: 980px) {
      .header-inner {
        flex-wrap: wrap;
        justify-content: center;
      }

      nav {
        order: 3;
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
      }
    }

    @media (max-width: 768px) {
      .page-container {
        padding: 16px 16px 32px;
      }

      .account-head {
        padding: 30px 0 14px;
      }

      .account-head h2 {
        font-size: 2rem;
      }

      .profile-card {
        padding: 18px;
      }

      .profile-left {
        gap: 12px;
      }

      .avatar {
        width: 64px;
        height: 64px;
      }

      .avatar svg {
        width: 34px;
        height: 34px;
      }

      .profile-info h3 {
        font-size: 1.25rem;
      }

      .section-title {
        font-size: 1.7rem;
      }

      .card-grid {
        grid-template-columns: 1fr;
      }

      .card-image {
        height: 240px;
      }

      .footer-layout {
        flex-direction: column;
        gap: 24px;
      }

      .footer-right {
        margin-right: 0;
      }

      .title-price-row {
        flex-direction: column;
        gap: 6px;
      }

      .card-title {
        font-size: 1.2rem;
      }

      .card-price {
        font-size: 1rem;
      }

      .card-description {
        font-size: 0.82rem;
      }
    }
  </style>
</head>

<body>

  <header class="header">
    <div class="container header-inner">
      <a class="brand" href="index.php" aria-label="LOOM Home">
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
        <a href="index.php">Home</a>
        <a href="brands.php">Brands</a>
        <a href="Blog.php">Blogs</a>
        <a href="SecondUse.php">Second Hand</a>
        <a href="user_account.php" id="profileLink" class="active">Profile</a>
      </nav>

      <div class="header-actions">
        <button class="pill" id="authPill"><?= e($user["username"]) ?></button>
      </div>
    </div>
  </header>

  <main class="page-container">
    <section class="account-head">
      <h2>My Account</h2>
    </section>

    <section class="section-space">
      <div class="profile-card section-card">
        <div class="profile-left">
          <div class="avatar" aria-label="Default profile avatar">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="8" r="4"></circle>
              <path d="M5 19c0-3.3 2.7-6 6-6h2c3.3 0 6 2.7 6 6"></path>
            </svg>
          </div>
          <div class="profile-info">
            <h3 id="profileUsername"><?= e($user["username"]) ?></h3>
            <div class="email" id="profileEmail"><?= e($user["email"]) ?></div>
            <div class="small-text">Manage your profile and marketplace activity</div>
          </div>
        </div>

        <form method="post" style="margin:0;"><input type="hidden" name="action" value="logout"><button class="soft-btn" id="logoutBtn" type="submit">Logout</button></form>
      </div>
    </section>

    <section class="section-space">
      <div class="section-title-wrap">
        <div>
          <h3 class="section-title">Saved Pieces</h3>
          <p class="section-subtext">Your curated collection of favorite finds</p>
        </div>
      </div>

      <div class="card-grid" id="bookmarksGrid">
        <?php if (!$bookmarks): ?>
          <div class="empty-state">
            <h4>Your curated collection is empty</h4>
            <p>Pieces you save in Second-Use will appear here.</p>
          </div>
        <?php else: ?>
          <?php foreach ($bookmarks as $item): ?>
            <article class="fashion-card" data-id="<?= (int)$item['id'] ?>">
              <div class="card-image-wrap">
                <a href="Productdetail.php?id=<?= (int)$item['id'] ?>" style="display:block;color:inherit;text-decoration:none;">
                  <img class="card-image" src="<?= e(item_image($item['image'], $item['title'])) ?>" alt="<?= e($item['title']) ?>">
                </a>
                <div class="card-action-icons">
                  <form method="post">
                    <input type="hidden" name="action" value="remove_bookmark">
                    <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                    <button class="icon-btn bookmark-btn active" title="Remove from saved" type="submit">
                      <svg class="bookmark-svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7 4.5C7 3.67 7.67 3 8.5 3h7C16.33 3 17 3.67 17 4.5V21l-5-3.8L7 21V4.5z"></path>
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
              <div class="card-body">
                <div class="title-price-row">
                  <h4 class="card-title"><?= e($item['title']) ?></h4>
                  <div class="card-price">&#8203;<?= e(number_format((float)$item['price'], 2, '.', '')) ?></div>
                </div>
                <p class="card-description"><?= e($item['description']) ?></p>
                <div class="contact-pill"><?= e($item['contact']) ?></div>
                <div class="card-bottom-line"></div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <section class="section-space">
      <div class="section-title-wrap">
        <div>
          <h3 class="section-title">My Listings</h3>
          <p class="section-subtext">Pieces you currently have available for sale</p>
        </div>
      </div>

      <div class="card-grid" id="listingsGrid">
        <?php if (!$listings): ?>
          <div class="empty-state">
            <h4>You haven't listed any pieces yet.</h4>
            <p>Add your first listing in Second-Use to start selling on LOOM.</p>
          </div>
        <?php else: ?>
          <?php foreach ($listings as $item): ?>
            <article class="fashion-card listing-card"
                     data-id="<?= (int)$item['id'] ?>"
                     data-title="<?= e($item['title']) ?>"
                     data-description="<?= e($item['description']) ?>"
                     data-price="<?= e(number_format((float)$item['price'], 2, '.', '')) ?>"
                     data-contact="<?= e($item['contact']) ?>">
              <div class="card-image-wrap">
                <a href="Productdetail.php?id=<?= (int)$item['id'] ?>" style="display:block;color:inherit;text-decoration:none;">
                  <img class="card-image" src="<?= e(item_image($item['image'], $item['title'])) ?>" alt="<?= e($item['title']) ?>">
                </a>
                <div class="card-action-icons">
                  <button class="icon-btn edit-btn" title="Edit listing" type="button">✎</button>
                  <button class="icon-btn delete-btn" title="Delete listing" type="button">🗑</button>
                </div>
              </div>
              <div class="card-body">
                <div class="title-price-row">
                  <h4 class="card-title"><?= e($item['title']) ?></h4>
                  <div class="card-price">&#8203;<?= e(number_format((float)$item['price'], 2, '.', '')) ?></div>
                </div>
                <p class="card-description"><?= e($item['description']) ?></p>
                <div class="contact-pill"><?= e($item['contact']) ?></div>
                <div class="card-bottom-line"></div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
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

  <div class="modal-overlay <?= $openEditModal ? 'show' : '' ?>" id="editModal">
    <div class="modal-box">
      <h3 class="modal-title">Edit Listing</h3>
      <p class="modal-text">Update your piece details below.</p>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_listing">
        <input type="hidden" name="item_id" id="editItemId" value="<?= e($editOld['item_id']) ?>">

        <?php if ($editErrors): ?>
          <div class="form-error-box">
            <ul>
              <?php foreach ($editErrors as $error): ?>
                <li><?= e($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

      <div class="form-group">
        <label for="editTitle">Title</label>
        <input id="editTitle" name="title" class="form-input" type="text" value="<?= e($editOld['title']) ?>" />
      </div>

      <div class="form-group">
        <label for="editDescription">Description</label>
        <textarea id="editDescription" name="description" class="form-textarea"><?= e($editOld['description']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="editPrice">Price</label>
        <input id="editPrice" name="price" class="form-input" type="text" value="<?= e($editOld['price']) ?>" />
      </div>

      <div class="form-group">
        <label for="editContact">Contact method</label>
        <input id="editContact" name="contact_method" class="form-input" type="text" value="<?= e($editOld['contact_method']) ?>" />
      </div>

      <div class="form-group">
        <label for="editImage">Upload New Photo(Optional)</label>
        <input id="editImage" name="photo" class="form-input" type="file" accept="image/*" />
      </div>

      <div class="modal-actions">
        <button class="btn-light" id="cancelEditBtn" type="button">Cancel</button>
        <button class="btn-main" id="saveEditBtn" type="submit">Save Changes</button>
        
      </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <h3 class="modal-title">Delete Listing</h3>
      <p class="modal-text">Are you sure you want to delete this listing?</p>
      <form method="post">
        <input type="hidden" name="action" value="delete_listing">
        <input type="hidden" name="item_id" id="deleteItemId" value="">

      <div class="modal-actions">
        <button class="btn-light" id="cancelDeleteBtn" type="button">Cancel</button>
        <button class="btn-delete" id="confirmDeleteBtn" type="submit">Delete</button>
      </div>
      </form>
    </div>
  </div>

  <script>
  let currentDeleteId = null;

  const editModal        = document.getElementById("editModal");
  const deleteModal      = document.getElementById("deleteModal");
  const editTitleInput   = document.getElementById("editTitle");
  const editDescInput    = document.getElementById("editDescription");
  const editPriceInput   = document.getElementById("editPrice");
  const editContactInput = document.getElementById("editContact");
  const editImageInput   = document.getElementById("editImage");
  const editItemId       = document.getElementById("editItemId");
  const cancelEditBtn    = document.getElementById("cancelEditBtn");
  const deleteItemId     = document.getElementById("deleteItemId");
  const cancelDeleteBtn  = document.getElementById("cancelDeleteBtn");

  document.querySelectorAll(".edit-btn").forEach(function(btn) {
    btn.onclick = function() {
      const card = btn.closest(".listing-card");
      editItemId.value          = card.dataset.id;
      editTitleInput.value      = card.dataset.title;
      editDescInput.value       = card.dataset.description;
      editPriceInput.value      = card.dataset.price;
      editContactInput.value    = card.dataset.contact;
      editImageInput.value      = "";
      editModal.classList.add("show");
    };
  });

  document.querySelectorAll(".delete-btn").forEach(function(btn) {
    btn.onclick = function() {
      const card = btn.closest(".listing-card");
      currentDeleteId = parseInt(card.dataset.id, 10);
      deleteItemId.value = currentDeleteId;
      deleteModal.classList.add("show");
    };
  });

  function closeEditModal() {
    editModal.classList.remove("show");
    editImageInput.value = "";
  }

  cancelEditBtn.addEventListener("click", closeEditModal);
  editModal.addEventListener("click", function(e) { if (e.target === editModal) closeEditModal(); });

  cancelDeleteBtn.addEventListener("click", function() {
    deleteModal.classList.remove("show");
    currentDeleteId = null;
  });

  deleteModal.addEventListener("click", function(e) {
    if (e.target === deleteModal) {
      deleteModal.classList.remove("show");
      currentDeleteId = null;
    }
  });
  </script>
</body>

</html>
