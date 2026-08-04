<?php
// post.php - Single Blog Post View Page
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post   = null;
$error  = null;

if ($postId > 0) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("
            SELECT p.id, p.user_id, p.title, p.content, p.created_at, p.updated_at, u.username as author
            FROM blogPost p
            JOIN user u ON p.user_id = u.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $postId]);
        $post = $stmt->fetch();

        if (!$post) {
            $error = "The requested blog post could not be found.";
        }
    } catch (PDOException $e) {
        $error = "Database connection error.";
    }
} else {
    $error = "Invalid post identifier specified.";
}

$pageTitle = $post ? $post['title'] : "Post Not Found";
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 24px;">
  <a href="index.php" class="btn btn-secondary btn-sm">← Back to Articles</a>
</div>

<?php if ($error): ?>
  <div class="auth-box text-center" style="max-width: 500px;">
    <h2 style="margin-bottom: 12px;">⚠️ Post Not Found</h2>
    <p class="text-muted" style="margin-bottom: 24px;"><?php echo sanitizeOutput($error); ?></p>
    <a href="index.php" class="btn btn-primary">Return to Home Page</a>
  </div>
<?php else: ?>
  <?php 
    $isOwner = isPostOwner($post['user_id']);
    $createdDate = date('F j, Y \a\t g:i A', strtotime($post['created_at']));
    $updatedDate = date('F j, Y \a\t g:i A', strtotime($post['updated_at']));
    $isEdited = $post['updated_at'] !== $post['created_at'];
  ?>
  
  <article class="single-post-container">
    <h1 class="single-post-title"><?php echo sanitizeOutput($post['title']); ?></h1>
    
    <div class="single-post-meta">
      <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; color: var(--text-muted);">
        <span style="font-weight: 700; color: #cbd5e1;">👤 <?php echo sanitizeOutput($post['author']); ?></span>
        <span>•</span>
        <time>📅 <?php echo $createdDate; ?></time>
        <?php if ($isEdited): ?>
          <span style="font-size: 0.82rem; color: var(--text-dim);">(Updated <?php echo $updatedDate; ?>)</span>
        <?php endif; ?>
      </div>

      <?php if ($isOwner): ?>
        <div class="post-actions">
          <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm">✏️ Edit Article</a>
          <button type="button" class="btn btn-danger btn-sm delete-post-btn" data-id="<?php echo $post['id']; ?>">🗑️ Delete</button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Raw Markdown content stored in dataset for JS rendering -->
    <div class="post-content-body" id="postBody" data-markdown="<?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?>">
      <!-- Fallback or rendered content will appear here -->
    </div>
  </article>

  <script src="assets/js/posts.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const bodyContainer = document.getElementById('postBody');
      if (bodyContainer) {
        const rawMarkdown = bodyContainer.dataset.markdown;
        if (typeof marked !== 'undefined') {
          bodyContainer.innerHTML = marked.parse(rawMarkdown);
        } else {
          // Standard line-break fallback if marked.js isn't loaded
          bodyContainer.innerHTML = rawMarkdown.replace(/\n/g, '<br>');
        }
      }
    });
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
