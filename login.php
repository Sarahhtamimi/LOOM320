<?php
require_once "db/config.php";

$emailError = "";
$passwordError = "";
$formMessage = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim(strtolower($_POST["email"] ?? ""));
    $password = trim($_POST["password"] ?? "");

    $isValid = true;

    if ($email === "") {
        $emailError = "Please enter your email.";
        $isValid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Please enter a valid email address.";
        $isValid = false;
    }

    if ($password === "") {
        $passwordError = "Please enter your password.";
        $isValid = false;
    }

    if ($isValid) {
        try {
            $pdo = getDB();

            /* Check admin account first */
            $adminStmt = $pdo->prepare("SELECT admin_id, email, password FROM `Admin` WHERE LOWER(email) = ?");
            $adminStmt->execute([$email]);
            $admin = $adminStmt->fetch();

            if ($admin && password_verify($password, $admin["password"])) {
                $_SESSION["admin_id"] = $admin["admin_id"];
                $_SESSION["email"] = $admin["email"];
                $_SESSION["role"] = "admin";

                header("Location: Adminpage.php");
                exit;
            }

            /* Check normal user account */
            $userStmt = $pdo->prepare("SELECT user_id, username, email, password FROM `User` WHERE LOWER(email) = ?");
            $userStmt->execute([$email]);
            $user = $userStmt->fetch();

            if ($user && password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = "user";

                header("Location: index.php");
                exit;
            }

            $formMessage = "Incorrect email or password.";
            $messageType = "error";

        } catch (PDOException $e) {
            $formMessage = "Database error. Please try again.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LOOM — Sign In</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />

  <style>
    :root {
      --bg: #F3E8E8;
      --card: rgba(255, 255, 255, .72);
      --charcoal: #434543;
      --forest: #6B7883;
      --terracotta: #B56449;
      --blush: #DEA4AD;
      --muted: rgba(67, 69, 67, .68);
      --border: rgba(67, 69, 67, .10);
      --shadow: 0 22px 60px rgba(67, 69, 67, .10);
      --shadow-soft: 0 12px 32px rgba(67, 69, 67, .08);
      --error: #b54b4b;
      --success: #4d6a58;
      --max: 1180px;
      --ease: cubic-bezier(.2, .8, .2, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color: var(--charcoal);
      background:
        radial-gradient(900px 460px at 12% -5%, rgba(222, 164, 173, .33), transparent 60%),
        radial-gradient(900px 480px at 88% 0%, rgba(107, 120, 131, .18), transparent 60%),
        linear-gradient(180deg, #f6ecec 0%, var(--bg) 40%, #f4eaea 100%);
      overflow-x: hidden;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    button {
      font: inherit;
    }

    .container {
      width: min(var(--max), calc(100% - 40px));
      margin: 0 auto;
    }

    .auth-page {
      padding: 42px 0 60px;
    }

    .auth-shell {
      position: relative;
      overflow: hidden;
      border: 1px solid var(--border);
      border-radius: 34px;
      box-shadow: var(--shadow);
      background:
        radial-gradient(760px 340px at 0% 0%, rgba(222, 164, 173, .30), transparent 60%),
        radial-gradient(720px 320px at 100% 10%, rgba(107, 120, 131, .18), transparent 58%),
        rgba(255, 255, 255, .58);
      padding: 34px;
      min-height: calc(100vh - 180px);
      display: grid;
      place-items: center;
    }

    .auth-wrapper {
      width: 100%;
      max-width: 540px;
      text-align: center;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, .62);
      font-size: 12px;
      font-weight: 800;
      box-shadow: var(--shadow-soft);
      margin-bottom: 18px;
    }

    .eyebrow i {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: var(--terracotta);
      display: inline-block;
    }

    h1 {
      margin: 0 0 10px;
      font-family: "Cormorant Garamond", serif;
      font-size: clamp(42px, 5vw, 64px);
      line-height: .95;
      letter-spacing: .01em;
    }

    .subtitle {
      margin: 0 auto 24px;
      max-width: 42ch;
      font-size: 15px;
      line-height: 1.7;
      color: var(--muted);
    }

    .card {
      width: 100%;
      margin: 0 auto;
      padding: 26px;
      border-radius: 28px;
      background: rgba(255, 255, 255, .76);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-soft);
      text-align: left;
      backdrop-filter: blur(10px);
    }

    .form-grid {
      display: grid;
      gap: 14px;
    }

    .field label {
      display: block;
      margin-bottom: 8px;
      font-size: 13px;
      font-weight: 800;
      color: var(--charcoal);
    }

    .field input {
      width: 100%;
      height: 50px;
      border-radius: 16px;
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, .86);
      padding: 0 16px;
      font-size: 14px;
      color: var(--charcoal);
      outline: none;
      transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .field input::placeholder {
      color: rgba(67, 69, 67, .42);
    }

    .field input:focus {
      border-color: rgba(222, 164, 173, .95);
      box-shadow: 0 0 0 4px rgba(222, 164, 173, .16);
      transform: translateY(-1px);
    }

    .field input.invalid {
      border-color: var(--error);
      background: #fff8f8;
    }

    .error-text {
      display: block;
      min-height: 16px;
      margin-top: 6px;
      font-size: 12px;
      color: var(--error);
    }

    .message {
      display: none;
      margin: 4px 0 2px;
      padding: 12px 14px;
      border-radius: 16px;
      font-size: 13px;
      line-height: 1.5;
      border: 1px solid transparent;
    }

    .message.error {
      display: block;
      color: var(--error);
      background: #fff1f1;
      border-color: #efcaca;
    }

    .message.success {
      display: block;
      color: var(--success);
      background: #eef6f0;
      border-color: #cadecf;
    }

    .submit-btn {
      width: 100%;
      border: none;
      cursor: pointer;
      border-radius: 18px;
      padding: 14px 18px;
      font-weight: 800;
      font-size: 15px;
      color: var(--charcoal);
      background: linear-gradient(135deg, rgba(181, 100, 73, .24), rgba(222, 164, 173, .28));
      border: 1px solid rgba(181, 100, 73, .28);
      transition: transform .18s var(--ease), box-shadow .18s var(--ease);
      box-shadow: var(--shadow-soft);
      margin-top: 4px;
    }

    .submit-btn:hover {
      transform: translateY(-1px);
      box-shadow: var(--shadow);
    }

    .bottom-text {
      text-align: center;
      margin-top: 16px;
      font-size: 14px;
      color: var(--forest);
    }

    .switch-link {
      color: var(--terracotta);
      font-weight: 700;
    }

    @media (max-width: 620px) {
      .container {
        width: min(var(--max), calc(100% - 24px));
      }

      .auth-shell {
        padding: 18px;
        border-radius: 26px;
      }

      .card {
        padding: 20px 16px;
        border-radius: 22px;
      }

      h1 {
        font-size: 42px;
      }
    }
  </style>
</head>

<body>

  <main class="auth-page">
    <div class="container">
      <section class="auth-shell">

        <div class="auth-wrapper">
          <div class="eyebrow"><i></i> Welcome back to LOOM</div>

          <h1>Sign In</h1>

          <p class="subtitle">
            Sign in to manage your activity and continue your LOOM journey with the same elegant experience as the homepage.
          </p>

          <div class="card">
            <form id="loginForm" method="POST" action="login.php" novalidate>
              <div class="form-grid">

                <div class="field">
                  <label for="email">Email</label>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    class="<?php echo $emailError ? 'invalid' : ''; ?>"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                  />
                  <small class="error-text" id="emailError"><?php echo $emailError; ?></small>
                </div>

                <div class="field">
                  <label for="password">Password</label>
                  <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    class="<?php echo $passwordError ? 'invalid' : ''; ?>"
                  />
                  <small class="error-text" id="passwordError"><?php echo $passwordError; ?></small>
                </div>

                <div id="formMessage" class="message <?php echo $messageType; ?>">
                  <?php echo $formMessage; ?>
                </div>

                <button type="submit" class="submit-btn">Sign In</button>

                <p class="bottom-text">
                  Don’t have an account?
                  <a class="switch-link" href="register.php">Create Account</a>
                </p>

              </div>
            </form>
          </div>
        </div>

      </section>
    </div>
  </main>

</body>
</html>

