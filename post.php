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

$coverImages = [
    "https://images.unsplash.com/photo-1499750310107-5fef28a66643?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1457369804613-52c61a468e7d?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1200&auto=format&fit=crop"
];
$coverImg = $coverImages[$postId % count($coverImages)];
?>

<div class="container-narrow">
  <div style="margin-bottom: 28px;">
    <a href="index.php" class="btn btn-secondary btn-sm">← Back to Publications</a>
  </div>

  <?php if ($error): ?>
    <div class="auth-box text-center" style="max-width: 500px; margin: 40px auto;">
      <h2 class="auth-title">⚠️ Article Not Found</h2>
      <p style="color: var(--text-muted); margin-bottom: 24px;"><?php echo sanitizeOutput($error); ?></p>
      <a href="index.php" class="btn btn-primary">Return to Home Page</a>
    </div>
  <?php else: ?>
    <?php 
      $isOwner = isPostOwner($post['user_id']);
      $createdDate = date('F j, Y', strtotime($post['created_at']));
      $updatedDate = date('F j, Y', strtotime($post['updated_at']));
      $isEdited = $post['updated_at'] !== $post['created_at'];
    ?>
    
    <article class="single-post-hero">
      <span class="category-badge">Publication</span>
      <h1 class="single-post-title"><?php echo sanitizeOutput($post['title']); ?></h1>
      
      <div class="single-post-meta">
        <div class="author-meta">
          <div class="author-avatar" style="width: 42px; height: 42px; font-size: 1.05rem;">
            <?php echo strtoupper(substr($post['author'], 0, 1)); ?>
          </div>
          <div class="author-info">
            <span class="author-name" style="font-size: 0.95rem;"><?php echo sanitizeOutput($post['author']); ?></span>
            <span class="post-date">Published on <?php echo $createdDate; ?>
              <?php if ($isEdited): ?>
                (Updated <?php echo $updatedDate; ?>)
              <?php endif; ?>
            </span>
          </div>
        </div>

        <?php if ($isOwner): ?>
          <div class="post-actions">
            <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm">✏️ Edit Article</a>
            <button type="button" class="btn btn-danger btn-sm delete-post-btn" data-id="<?php echo $post['id']; ?>">🗑️ Delete</button>
          </div>
        <?php endif; ?>
      </div>

      <img src="<?php echo $coverImg; ?>" alt="Article Cover" class="single-post-cover">

      <!-- Markdown content container -->
      <div class="post-content-body" id="postBody" data-markdown="<?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?>">
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
            bodyContainer.innerHTML = rawMarkdown.replace(/\n/g, '<br>');
          }
        }
      });
    </script>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
