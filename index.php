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

// Curated high quality editorial cover image placeholders
$coverImages = [
    "https://images.unsplash.com/photo-1499750310107-5fef28a66643?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1457369804613-52c61a468e7d?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1200&auto=format&fit=crop",
    "https://images.unsplash.com/photo-1434030216411-0b793f4b4173?q=80&w=1200&auto=format&fit=crop"
];

$categories = ["Technology", "Design", "Culture", "Business", "Engineering", "Ideas"];
?>

<!-- Editorial Hero Section -->
<section class="hero-editorial">
  <div class="hero-grid">
    <div>
      <h1 class="hero-headline">Stories that inspire. <span>Ideas that ignite growth.</span></h1>
    </div>
    <div class="hero-intro">
      <p class="hero-subtitle">Discover perspectives, technology insights, and long-form articles published by independent writers and creators on BlogNest.</p>
      <div class="hero-cta-group">
        <?php if (!isLoggedIn()): ?>
          <a href="register.php" class="btn btn-primary">Start Writing Today</a>
          <a href="login.php" class="btn btn-secondary">Explore Publications</a>
        <?php else: ?>
          <a href="editor.php" class="btn btn-primary">+ Publish New Article</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (!empty($posts)): ?>
    <?php 
      $featured = $posts[0];
      $featuredImage = $coverImages[0];
      $plainText = strip_tags($featured['content']);
      $featuredExcerpt = mb_substr($plainText, 0, 220) . (mb_strlen($plainText) > 220 ? '...' : '');
      $featuredDate = date('M j, Y', strtotime($featured['created_at']));
    ?>
    <!-- Large Featured Article Cover -->
    <div class="hero-featured-card">
      <div class="hero-featured-img-wrap">
        <img src="<?php echo $featuredImage; ?>" alt="Featured Cover" class="hero-featured-img">
      </div>
      <div class="hero-featured-content">
        <span class="category-badge">Featured Story</span>
        <h2 class="hero-featured-title">
          <a href="post.php?id=<?php echo $featured['id']; ?>">
            <?php echo sanitizeOutput($featured['title']); ?>
          </a>
        </h2>
        <p class="hero-featured-excerpt"><?php echo sanitizeOutput($featuredExcerpt); ?></p>
        
        <div class="author-meta">
          <div class="author-avatar"><?php echo strtoupper(substr($featured['author'], 0, 1)); ?></div>
          <div class="author-info">
            <span class="author-name"><?php echo sanitizeOutput($featured['author']); ?></span>
            <span class="post-date"><?php echo $featuredDate; ?> • 5 min read</span>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php if ($dbError): ?>
  <div class="alert alert-danger">
    <span>⚠️</span>
    <div>Could not load blog posts. Database connection error.</div>
  </div>
<?php elseif (empty($posts)): ?>
  <div style="text-align: center; padding: 72px 24px; background: var(--bg-card); border-radius: var(--radius-xl); border: 1px solid var(--border-color); box-shadow: var(--shadow-subtle);">
    <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-navy); margin-bottom: 12px;">No Articles Published Yet</h3>
    <p style="color: var(--text-muted); margin-bottom: 24px; max-width: 480px; margin-left: auto; margin-right: auto;">Be the first storyteller to share insights with the BlogNest community!</p>
    <?php if (isLoggedIn()): ?>
      <a href="editor.php" class="btn btn-primary">+ Create First Article</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-secondary">Sign In to Publish</a>
    <?php endif; ?>
  </div>
<?php else: ?>

  <!-- Latest Publications Grid Section -->
  <section>
    <div class="section-header">
      <div class="section-title-wrap">
        <h2>Latest Publications</h2>
        <p class="section-subtitle">Handpicked stories and fresh perspectives from our community.</p>
      </div>
    </div>

    <div class="posts-grid">
      <?php foreach ($posts as $idx => $post): ?>
        <?php 
          $isOwner = isPostOwner($post['user_id']);
          $plainText = strip_tags($post['content']);
          $excerpt = mb_substr($plainText, 0, 140) . (mb_strlen($plainText) > 140 ? '...' : '');
          $postDate = date('M j, Y', strtotime($post['created_at']));
          $imgUrl = $coverImages[$idx % count($coverImages)];
          $cat = $categories[$idx % count($categories)];
        ?>
        <article class="post-card">
          <div class="post-card-img-wrap">
            <a href="post.php?id=<?php echo $post['id']; ?>">
              <img src="<?php echo $imgUrl; ?>" alt="Article Cover" class="post-card-img">
            </a>
          </div>
          <div class="post-card-body">
            <span class="category-badge"><?php echo $cat; ?></span>
            <h3 class="post-card-title">
              <a href="post.php?id=<?php echo $post['id']; ?>">
                <?php echo sanitizeOutput($post['title']); ?>
              </a>
            </h3>
            <p class="post-excerpt"><?php echo sanitizeOutput($excerpt); ?></p>
            
            <div class="post-card-footer">
              <div class="author-meta">
                <div class="author-avatar"><?php echo strtoupper(substr($post['author'], 0, 1)); ?></div>
                <div class="author-info">
                  <span class="author-name"><?php echo sanitizeOutput($post['author']); ?></span>
                  <span class="post-date"><?php echo $postDate; ?></span>
                </div>
              </div>

              <?php if ($isOwner): ?>
                <div class="post-actions">
                  <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Post">✏️</a>
                  <button type="button" class="btn btn-danger btn-sm delete-post-btn" data-id="<?php echo $post['id']; ?>" title="Delete Post">🗑️</button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

<?php endif; ?>

<script src="assets/js/posts.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
