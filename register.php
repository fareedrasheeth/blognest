<?php
// register.php - User Registration Interface
$pageTitle = "Create Account";
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>

<div class="auth-box">
  <h1 class="auth-title">Join BlogNest</h1>
  <p class="auth-subtitle">Create a free account to write, edit, and publish articles</p>

  <div id="authAlertBox"></div>

  <form id="registerForm">
    <div class="form-group">
      <label for="username" class="form-label">Username</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Choose a unique username" required minlength="3" maxlength="50" autocomplete="username">
    </div>

    <div class="form-group">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required autocomplete="email">
    </div>

    <div class="form-group">
      <label for="password" class="form-label">Password</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required minlength="6" autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Register Account</button>
  </form>

  <p class="text-center text-muted" style="margin-top: 24px; font-size: 0.9rem;">
    Already have an account? <a href="login.php" style="font-weight: 600;">Sign in here</a>
  </p>
</div>

<script src="assets/js/auth.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
