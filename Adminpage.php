<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ✅ السيشن هنا فقط */
session_start();

/* ✅ دالة الاتصال بقاعدة البيانات */
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=8889;dbname=loom;charset=utf8mb4",
        "root",
        "root",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

/* ✅ دالة الحماية */
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* ✅ تحقق من الأدمن */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $date    = $_POST['date'] ?? '';
    $image   = trim($_POST['image'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $adminId = $_SESSION['admin_id'];

    if ($title && $date && $image && $content) {
        try {
            $stmt = db()->prepare(
                "INSERT INTO blogpost (title, image, content, publish_date, admin_id)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$title, $image, $content, $date, $adminId]);

            $message = "✅ Blog published successfully";
            header("Refresh: 1; url=Blog.php");
        } catch (PDOException $ex) {
            die("DB ERROR: " . $ex->getMessage());
        }
    } else {
        $message = "❌ Please fill all fields";
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LOOM | Admin Add Blog</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">






    


  <style>
    :root {
      --bg: #f7f2ee;
      --card: #fffaf7;
      --rose: #d9b3a7;
      --rose-dark: #b78373;
      --text: #3e312d;
      --muted: #8d7a74;
      --line: #eadfd8;
      --shadow: 0 12px 30px rgba(87, 60, 51, 0.08);
    }


    

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to bottom right, #fbf7f4, #f4ebe5);
      color: var(--text);
      min-height: 100vh;
    }

    .container {
      width: min(1180px, calc(100% - 40px));
      margin: 0 auto;
    }

    /* Header */
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

    /* Page */
    .page {
      width: 90%;
      max-width: 1250px;
      margin: 50px auto;
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 30px;
      align-items: stretch;
    }

    .form-card {
      background: rgba(255, 255, 255, 0.72);
      border: 1px solid rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(16px);
      border-radius: 30px;
      padding: 40px;
      box-shadow: var(--shadow);
    }

    .badge {
      display: inline-block;
      background: #f4e1da;
      color: var(--rose-dark);
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 18px;
    }

    .title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 48px;
      line-height: 1.05;
      margin-bottom: 12px;
    }

    .subtitle {
      color: var(--muted);
      margin-bottom: 32px;
      max-width: 600px;
      line-height: 1.7;
      font-size: 15px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .full {
      grid-column: 1 / -1;
    }

    label {
      font-size: 14px;
      font-weight: 600;
      color: var(--text);
    }

    input,
    textarea {
      border: 1px solid var(--line);
      background: rgba(255, 255, 255, 0.85);
      border-radius: 18px;
      padding: 15px 16px;
      font-size: 14px;
      outline: none;
      transition: 0.25s ease;
      color: var(--text);
    }

    input:focus,
    textarea:focus {
      border-color: var(--rose);
      box-shadow: 0 0 0 4px rgba(217, 179, 167, 0.15);
    }

    textarea {
      min-height: 170px;
      resize: vertical;
      line-height: 1.7;
    }

    .actions {
      display: flex;
      gap: 14px;
      margin-top: 12px;
      flex-wrap: wrap;
    }

    .btn {
      border: none;
      border-radius: 999px;
      padding: 14px 24px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.25s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, #d7a89a, #b98272);
      color: white;
      box-shadow: 0 10px 24px rgba(185, 130, 114, 0.25);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: white;
      color: var(--text);
      border: 1px solid var(--line);
    }

    .message {
      margin-top: 18px;
      font-size: 14px;
      font-weight: 600;
      color: var(--rose-dark);
    }

    .side-card {
      background: white;
      border-radius: 30px;
      overflow: hidden;
      box-shadow: var(--shadow);
      display: flex;
      flex-direction: column;
    }

    .side-card img {
      width: 100%;
      height: 55%;
      object-fit: cover;
    }

    .side-content {
      padding: 32px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      justify-content: center;
      flex: 1;
    }

    .side-content h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 36px;
      line-height: 1.1;
    }

    .side-content p {
      color: var(--muted);
      line-height: 1.8;
      font-size: 15px;
    }

    .mini-boxes {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-top: 10px;
    }

    .mini-box {
      background: #fbf5f1;
      border: 1px solid #f0e3dc;
      border-radius: 20px;
      padding: 18px;
    }

    .mini-box h4 {
      font-size: 14px;
      margin-bottom: 8px;
    }

    .mini-box p {
      font-size: 13px;
      line-height: 1.6;
    }

    /* Footer */
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

    @media (max-width: 980px) {
      .page {
        grid-template-columns: 1fr;
      }

      .title {
        font-size: 38px;
      }

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
      .footer-layout {
        flex-direction: column;
        gap: 24px;
      }

      .footer-right {
        text-align: left;
        margin-right: 0;
      }
    }

    @media (max-width: 600px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }
    .admin-actions {
  display: flex;
  align-items: center;
  gap: 12px; /* المسافة بينهم */
}

.user-name {
  background: rgba(255, 255, 255, 0.9);
  font-weight: 700;
}

.logout {
  background: linear-gradient(135deg, #d7a89a, #b98272);
  color: white;
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
  <a href="Adminpage.php" class="active">Admin</a>
  <a href="Blog.php">Blog</a>
</nav>



      <div class="header-actions admin-actions">
  <span class="pill user-name" id="authPill">Admin</span>
  <button class="pill logout" id="logoutBtn">Logout</button>
</div>
    </div>
  </header>

  <section class="page">
    <div class="form-card">
      
      <span class="badge">Admin Dashboard</span>
      <h1 class="title">Create a new<br>LOOM blog post</h1>
      <p class="subtitle">
        Add educational and visually engaging blog content to inspire sustainable fashion choices and raise awareness.
      </p>
      <?php if (!empty($message)): ?>
  <p class="message"><?= e($message) ?></p>
<?php endif; ?>

      <form id="blogForm" method="post">
        <div class="form-grid">
          <div class="form-group">
            <label for="title">Blog Title</label>
            <input type="text" id="title" name="title" placeholder="Enter blog title" required>
          </div>

          <div class="form-group">
            <label for="date">Publish Date</label>
            <input type="date" id="date" name="date" required>
          </div>

          <div class="form-group full">
            <label for="image">Image Path / URL</label>
            <input type="text" id="image" name="image" placeholder="images/blog1.png or https://..." required>
          </div>

          <div class="form-group full">
            <label for="content">Blog Content</label>
            <textarea id="content" name="content" placeholder="Write the blog content here..." required></textarea>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn btn-primary">Publish Blog</button>
          <button type="reset" class="btn btn-secondary">Clear Form</button>
        </div>

        
      </form>
    </div>

    
    <div class="side-card">
      <img src="images/admin-blog-side.png" alt="LOOM Admin Visual">
      <div class="side-content">
        <h3>Write with elegance,<br>publish with purpose.</h3>
        <p>
          This panel helps the admin create meaningful content that educates users about sustainable fashion, conscious
          shopping, and circular style.
        </p>

        <div class="mini-boxes">
          <div class="mini-box">
            <h4>Recommended Topics</h4>
            <p>Second-use fashion, eco materials, conscious shopping, wardrobe tips.</p>
          </div>
          <div class="mini-box">
            <h4>Visual Tip</h4>
            <p>Use soft editorial images with neutral tones to match LOOM branding.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

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
<script>
  // تعيين تاريخ اليوم تلقائيًا
  const dateInput = document.getElementById("date");
  if (dateInput) {
    dateInput.value = new Date().toISOString().split("T")[0];
  }

  // تسجيل الخروج
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      window.location.href = "index.php";
    });
  }
</script>
</body>

</html>