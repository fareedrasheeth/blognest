<?php
// includes/header.php - Global Header & Navigation Component
require_once __DIR__ . '/functions.php';
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? sanitizeOutput($pageTitle) . ' - BlogNest' : 'BlogNest - Digital Publication Platform'; ?></title>
  <meta name="description" content="BlogNest is a modern, hostable full-stack publishing platform for creators and storytellers.">
  <link rel="preload" as="image" href="/sources/textlogo.png?v=2">
  <link rel="stylesheet" href="assets/css/style.css">
  <!-- Marked.js for client-side Markdown rendering -->
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body>
  
  <header class="site-header">
    <div class="container header-inner">
      <a href="index.php" class="logo-brand">
        <img src="/sources/textlogo.png?v=2" alt="BlogNest" class="logo-img" width="240" height="82" fetchpriority="high" decoding="async">
      </a>
      
      <nav>
        <ul class="nav-links">
          <li><a href="index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a></li>
          
          <?php if (isLoggedIn()): ?>
            <li><a href="editor.php" class="nav-link <?php echo $currentPage === 'editor.php' ? 'active' : ''; ?>">+ Create Article</a></li>
            <li>
              <div class="user-badge">
                <span class="user-avatar-circle"><?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?></span>
                <span><?php echo sanitizeOutput($currentUser['username']); ?></span>
              </div>
            </li>
            <li><a href="api/auth/logout.php" class="btn btn-secondary btn-sm" id="logoutBtn">Logout</a></li>
          <?php else: ?>
            <li><a href="login.php" class="nav-link <?php echo $currentPage === 'login.php' ? 'active' : ''; ?>">Login</a></li>
            <li><a href="register.php" class="btn btn-primary btn-sm">Get Started</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
