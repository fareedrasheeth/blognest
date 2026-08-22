<?php
// login.php - User Login Interface
$pageTitle = "Sign In";
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>

<div class="auth-wrapper">
  <div class="auth-box">
    <div class="auth-logo-header">
      <img src="/sources/textlogo.png?v=2" alt="BlogNest" class="auth-logo-img" width="240" height="96" fetchpriority="high" decoding="async">
      <h1 class="auth-title">Welcome Back</h1>
      <p class="auth-subtitle">Sign in to your BlogNest account to manage and publish stories.</p>
    </div>

    <div id="authAlertBox"></div>

    <form id="loginForm">
      <div class="form-group">
        <label for="identifier" class="form-label">Username or Email</label>
        <input type="text" id="identifier" name="identifier" class="form-control" placeholder="e.g. john_doe or john@example.com" required autocomplete="username">
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px; padding: 12px 24px;">Sign In to Account</button>
    </form>

    <p class="footer-text text-center" style="margin-top: 28px; text-align: center;">
      Don't have an account? <a href="register.php" style="font-weight: 700; color: var(--accent-primary);">Create one now</a>
    </p>
  </div>
</div>

<script src="assets/js/auth.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
