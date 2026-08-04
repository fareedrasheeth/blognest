<?php
// index.php - Blog Home Page Listing All Posts
$pageTitle = "Home - Explore Stories & Insights";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db.php';

$db = getDBConnection();
$posts = [];
$dbError = false;

try {
    $stmt = $db->query("
        SELECT p.id, p.user_id, p.title, p.content, p.created_at, p.updated_at, u.username as author
        FROM blogPost p
        JOIN user u ON p.user_id = u.id
        ORDER BY p.created_at DESC
    ");
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $dbError = true;
}
?>

<section class="hero-section">
  <h1 class="hero-title">Welcome to <span>BlogNest</span></h1>
  <p class="hero-subtitle">Discover perspectives, technology insights, and stories published by community writers.</p>
  
  <?php if (!isLoggedIn()): ?>
    <div style="margin-top: 20px;">
      <a href="register.php" class="btn btn-primary">Start Writing Today</a>
      <a href="login.php" class="btn btn-secondary">Sign In</a>
    </div>
  <?php else: ?>
    <div style="margin-top: 20px;">
      <a href="editor.php" class="btn btn-primary">+ Publish New Article</a>
    </div>
  <?php endif; ?>
</section>

<?php if ($dbError): ?>
  <div class="alert alert-danger" style="margin-top: 30px;">
    <span>⚠️</span>
    <div>Could not load blog posts. Please ensure the MySQL database is imported and running properly. See <code>schema.sql</code>.</div>
  </div>
<?php elseif (empty($posts)): ?>
  <div style="text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color); margin-top: 30px;">
    <h3 style="margin-bottom: 10px;">No Blog Posts Yet</h3>
    <p class="text-muted" style="margin-bottom: 20px;">Be the first person to share a story on BlogNest!</p>
    <?php if (isLoggedIn()): ?>
      <a href="editor.php" class="btn btn-primary">+ Create First Post</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-secondary">Log In to Write</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <h2 style="font-size: 1.5rem; margin-bottom: 20px; font-weight: 700;">Latest Articles</h2>
  <div class="posts-grid">
    <?php foreach ($posts as $post): ?>
      <?php 
        $isOwner = isPostOwner($post['user_id']);
        $plainText = strip_tags($post['content']);
        $excerpt = mb_substr($plainText, 0, 160) . (mb_strlen($plainText) > 160 ? '...' : '');
        $postDate = date('M j, Y', strtotime($post['created_at']));
      ?>
      <article class="post-card">
        <div>
          <div class="post-meta">
            <span class="post-author">✍️ <?php echo sanitizeOutput($post['author']); ?></span>
            <span>•</span>
            <time><?php echo $postDate; ?></time>
          </div>
          <h3 class="post-title">
            <a href="post.php?id=<?php echo $post['id']; ?>">
              <?php echo sanitizeOutput($post['title']); ?>
            </a>
          </h3>
          <p class="post-excerpt"><?php echo sanitizeOutput($excerpt); ?></p>
        </div>

        <div class="post-card-footer">
          <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm">Read Full Article →</a>
          
          <?php if ($isOwner): ?>
            <div class="post-actions">
              <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Post">✏️ Edit</a>
              <button type="button" class="btn btn-danger btn-sm delete-post-btn" data-id="<?php echo $post['id']; ?>" title="Delete Post">🗑️ Delete</button>
            </div>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script src="assets/js/posts.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
