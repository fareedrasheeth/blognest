<?php
// login.php - User Login Interface
$pageTitle = "Sign In";
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>

<div class="auth-box">
  <h1 class="auth-title">Welcome Back</h1>
  <p class="auth-subtitle">Sign in to your BlogNest account to manage your posts</p>

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

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Sign In</button>
  </form>

  <p class="text-center text-muted" style="margin-top: 24px; font-size: 0.9rem;">
    Don't have an account? <a href="register.php" style="font-weight: 600;">Create one now</a>
  </p>
</div>

<script src="assets/js/auth.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
