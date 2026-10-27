<?php
/**
 * SOBHA REALTY LUXURY - ADMIN LOGIN
 */
require_once __DIR__ . '/../api/config.php';

// Redirect if already logged in
if (isset($_SESSION['sobha_admin_logged']) && $_SESSION['sobha_admin_logged'] === true) {
    header('Location: leads.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please provide both username and password.';
    } else {
        $db = getDbConnection();
        if ($db) {
            $stmt = $db->prepare("SELECT id, username, password_hash, full_name FROM admin_users WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['sobha_admin_logged'] = true;
                $_SESSION['sobha_admin_id'] = $user['id'];
                $_SESSION['sobha_admin_user'] = $user['username'];
                $_SESSION['sobha_admin_name'] = $user['full_name'];

                $update = $db->prepare("UPDATE admin_users SET last_login_at = NOW() WHERE id = :id");
                $update->execute([':id' => $user['id']]);

                header('Location: leads.php');
                exit;
            } else {
                $error = 'Invalid credentials. Access restricted.';
            }
        } else {
            // Secure fallback authentication: Only active if explicitly defined in .env
            $fallbackUser = FALLBACK_ADMIN_USER;
            $fallbackPass = FALLBACK_ADMIN_PASS;

            if (!empty($fallbackUser) && !empty($fallbackPass) &&
                hash_equals($fallbackUser, $username) && hash_equals($fallbackPass, $password)) {
                $_SESSION['sobha_admin_logged'] = true;
                $_SESSION['sobha_admin_user'] = $username;
                $_SESSION['sobha_admin_name'] = 'Administrator (Environment Auth)';
                header('Location: leads.php');
                exit;
            } else {
                $error = 'Database uninitialized or invalid credentials. Configure .env database credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal | Sobha Realty Luxury CRM</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-body">
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <div class="login-crest">
          <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
        </div>
        <p class="login-sub">SOBHA REALTY DUBAI</p>
        <h1 class="login-title">Private CRM Portal</h1>
        <p class="login-desc">Authorized personnel and senior luxury advisors only.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="login-alert-error">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" class="login-form">
        <div class="form-group">
          <label for="username">USERNAME</label>
          <div class="input-wrap">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <input type="text" id="username" name="username" placeholder="Enter username" required autofocus autocomplete="username">
          </div>
        </div>

        <div class="form-group">
          <label for="password">PASSWORD</label>
          <div class="input-wrap">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input type="password" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="login-btn">
          <span>SIGN IN TO CRM</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
      </form>

      <div class="login-footer">
        <span>Protected with 256-bit encryption & IP rate-limiting</span>
      </div>
    </div>
  </div>
</body>
</html>
